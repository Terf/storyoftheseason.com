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
use App\Service\Mailer;
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

        $logger->alert("{$request->request->get('email')} created an account");

        $purchase = $request->request->get('product');
        if ($purchase !== null) {
            $req = new Request;
            $req->request->set('user', $buyer->getToken());
            $req->request->set('product', $purchase);
            $response = $this->forward('App\Controller\ProductController::purchase', [
                'request'  => $req,
            ]);
            $response->headers->setCookie(Cookie::create('user_token', $buyer->getToken()));
            $logger->alert("{$request->request->get('email')} being redirected to ProductController to purchase product {$purchase}");
            return $response;
        }
        $logger->alert("{$request->request->get('email')} account created but no purchase made");
        $response = new RedirectResponse($this->generateUrl('shop'));
        $response->headers->setCookie(Cookie::create('user_token', $buyer->getToken()));
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

    /**
     * registration-import
     */
    public function import(Request $request, EntityManagerInterface $entityManager, Mailer $mailer)
    {
        $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
        if ($admin !== null) {
            $product = $entityManager->getRepository(Entity\Product::class)->find($request->request->get('product'));
            $csv = $request->files->get('file');
            $path = $csv->getRealPath();
            $data = array_map('str_getcsv', explode("\n", file_get_contents($path)));
            $location = $entityManager->getRepository(Entity\Location::class)->find(-1);
            $seller = $entityManager->getRepository(Entity\Seller::class)->find(-1);
            for ($i = 1; $i < count($data); $i++) { // i = 1 bc first line is header
                $buyer = new Entity\Buyer;
                $purchase = new Entity\Purchase;
                $purchase->setProduct($product);
                $buyer->setEmail($data[$i][1]);
                $name = explode(' ', $data[$i][2]);
                $buyer->setFirstName($name[0]);
                $buyer->setLastName((count($name) > 1) ? $name[1] : '');
                $buyer->setPassword(password_hash($data[$i][3], PASSWORD_DEFAULT));
                $buyer->setPhone($data[$i][4]);
                $buyer->setType(7);
                foreach (Entity\Buyer::TYPES as $id => $type) {
                    if (trim(strtolower($data[$i][5])) === strtolower($type)) {
                        $buyer->setType($id);
                        break;
                    }
                }
                $buyer->setLocation($location);
                $buyer->setSeller($seller);
                $buyer->setToken(null);
                $buyer->addPurchase($purchase);
                $purchase->setUser($buyer);
                $entityManager->persist($buyer);
                $entityManager->persist($purchase);
                $mailer->send($buyer->getEmail(), 'Account created on storyoftheseason.com',  "<p>Hi {$buyer->getFirstName()},</p>"
                . "<p>Thank you for subscribing to the {$product->getName()} Story of the Season.  If you are receiving this email, you have been registered as a Story of the Season subscribed user.  You can go to Storyoftheseason.com and login to access your library.  Once you successfully log in, click on \"My Library\" and you can see your team's published content!</p>"
                . "<p>Your username is {$buyer->getEmail()}, and your password is {$data[$i][3]}.  You can change your password <a href='https://storyoftheseason.com/reset-password'>here</a> if you'd like.</p>"
                . "<p>Do you have your own photos, videos, articles, etc that you want to share?  You can upload content to Story of the Season at storyoftheseason.com.</p>"
                . "<p>If you have any questions, contact me at chris@storyoftheseason.co or call (518) 944-3311.</p>"
                . "<p>Thanks,</p>"
                . "<p>Chris Herman</p>");
            }
            $entityManager->flush();
            return new JsonResponse(true);
        }
        return new JsonResponse(false);
    }

}
