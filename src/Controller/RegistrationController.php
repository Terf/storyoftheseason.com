<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity;

class RegistrationController extends AbstractController
{

    /**
     * registration-form
     */
    public function index()
    {
        return $this->render('registration/index.html.twig');
    }

    /**
     * registration-submit
     */
    public function submit(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        if (strlen($request->request->get('pass')) < 9) {
            return $this->redirectToRoute('registration-form');
        }

        $alreadyExists = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['email' => $request->request->get('email')]);
        if ($alreadyExists !== null) {
            $req = new Request;
            $req->request->set('email', $request->request->get('email'));
            $req->request->set('password', $request->request->get('pass'));
            $response = $this->forward('App\Controller\LoginController::submit', [
                'request'  => $req,
            ]);
            return $response;
        }

        $buyer = new Entity\Buyer;
        $seller = new Entity\Seller;
        $location = new Entity\Location;

        $location->setAddress($request->request->get('addr'));
        $location->setZip($request->request->get('zip'));
        $location->setCountry($request->request->get('country'));
        $location->setState($request->request->get('state'));
        $location->setCity($request->request->get('city'));

        $seller->setName($request->request->get('student'));
        $seller->setType($request->request->get('studentRelationship'));

        $buyer->setFirstName($request->request->get('fname'));
        $buyer->setLastName($request->request->get('lname'));
        $buyer->setEmail($request->request->get('email'));
        $buyer->setType($request->request->get('relationship'));
        $buyer->setPassword(password_hash($request->request->get('pass'), PASSWORD_DEFAULT));
        $buyer->setPhone($request->request->get('phone'));
        $buyer->setLocation($location);
        $buyer->setSeller($seller);
        $buyer->setToken(bin2hex(random_bytes(16)));

        $entityManager->persist($location);
        $entityManager->persist($buyer);
        $entityManager->persist($seller);
        $entityManager->flush();

        $this->registerWithKitaboo($buyer, $request->request->get('pass'));

        $logger->alert("{$request->request->get('email')} created an account");

        $purchase = $request->request->get('product');
        if ($purchase !== null) {
            $req = new Request;
            $req->request->set('user', $buyer->getToken());
            $req->request->set('product', $purchase);
            $response = $this->forward('App\Controller\ProductController::purchase', [
                'request'  => $req,
            ]);
            $response->headers->setCookie(Cookie::create('user_token', $token));
            $logger->alert("{$request->request->get('email')} being redirected to ProductController to purchase product {$purchase}");
            return $response;
        }
        $logger->alert("{$request->request->get('email')} account created but no purchase made");
        $response = new RedirectResponse($this->generateUrl('shop'));
        $response->headers->setCookie(Cookie::create('user_token', $token));
        return $response;
    }

    /**
     * validate-email
     */
    public function validateEmail(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['email' => $request->query->get('email')]);
        return new JsonResponse(($user === null) ? true : false);
    }

    private function registerWithKitaboo(Entity\Buyer $user, string $unhashedPassword)
    {
        $data = json_encode([
            'user' => [
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'userName' => $user->getEmail(),
                'password' => $unhashedPassword,
                'clientUserID' => $user->getId(),
                'email' => $user->getEmail()
            ]
        ]);
        shell_exec('sudo docker run --rm -e ACTION=register_user -e DATA='.escapeshellarg($data).' -e CONSUMER_KEY='.getenv('KITABOO_CONSUMER_KEY_PROD').' -e SECRET_KEY='.getenv('KITABOO_SECRET_KEY_PROD').' --name running-kitaboo kitaboo');
    }
}
