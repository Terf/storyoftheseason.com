<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class LoginController extends AbstractController
{
    public function index()
    {
        return $this->render('login/index.html.twig', ['admin' => false]);
    }

    public function submit(Request $request, EntityManagerInterface $entityManager)
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        if ($email === null || $password === null) {
            throw new \Exception("Missing fields: need to POST 'email', 'password'; received " . print_r($request->request->all(), true));
        }

        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['email' => $email]);
        if ($user === null) {
            return new JsonResponse(['result' => false, 'reason' => 'email']);
        } else {
            if (password_verify($password, $user->getPassword())) {
                $user = $this->ensureKitabooRegistration($user, $password, $entityManager);
                $token = bin2hex(random_bytes(16));
                $user->setToken($token);
                $entityManager->merge($user);
                $entityManager->flush();
                return new JsonResponse(['result' => true, 'token' => $user->getToken()]);
            }
            return new JsonResponse(['result' => false, 'reason' => 'password']);
        }
    }

    public function signOut(Request $request)
    {
        $response = $this->redirectToRoute('index');
        $response->headers->clearCookie('user_token');
        return $response;
    }

    private function ensureKitabooRegistration($user, $unhashedPassword, $entityManager)
    {
        // these users didnt have their accounts created correctly on Kitaboo's end, if they log in again make sure to create their account
        $corruptedAccounts = ['content@storyoftheseason.com', 'downingchris33@gmail.com', 'omartabdoun@gmail.com', 'rachaelristas@roadrunner.com', 'theo@planetkipp.com', 'gypsyheiress@gmail.com', 'sharon217@comcast.net', 'vricken95@gmail.com', 'ejwssse@gma', 'kbinkowski35@yahoo.com', 'jr.ogle@abcglobal.net'];
        if (in_array($user->getEmail(), $corruptedAccounts)) {
            $purchases = $user->getPurchases();
            $newUser = clone $user;
            $entityManager->remove($user);
            $entityManager->flush();
            $entityManager->persist($newUser);
            foreach ($purchases as $purchase) {
                $purchase->setUser($newUser);
                $entityManager->merge($purchase);
            }
            $entityManager->flush();
            $data = json_encode([
                'user' => [
                    'firstName' => $newUser->getFirstName(),
                    'lastName' => $newUser->getLastName(),
                    'userName' => $newUser->getEmail(),
                    'password' => $unhashedPassword,
                    'clientUserID' => $newUser->getId(),
                    'email' => $newUser->getEmail()
                ]
            ]);
            shell_exec('sudo docker run --rm -e ACTION=register_user -e DATA='.escapeshellarg($data).' -e CONSUMER_KEY='.getenv('KITABOO_CONSUMER_KEY_PROD').' -e SECRET_KEY='.getenv('KITABOO_SECRET_KEY_PROD').' --name running-kitaboo kitaboo');
            return $newUser;
        }
        return $user;
    }
}
