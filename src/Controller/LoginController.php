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
        return $this->render('login/index.html.twig');
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
            return $this->redirectToRoute('login-form');
        } else {
            if (password_verify($password, $user->getPassword())) {
                $token = bin2hex(random_bytes(16));
                $user->setToken($token);
                $entityManager->merge($user);
                $entityManager->flush();
                $response = new RedirectResponse($this->generateUrl('shop'));
                $response->headers->setCookie(Cookie::create('user_token', $token));
                return $response;
            }
            return $this->redirectToRoute('login-form');
        }
    }

    public function signOut(Request $request)
    {
        $response = $this->redirectToRoute('index');
        $response->headers->clearCookie('user_token');
        return $response;
    }
}
