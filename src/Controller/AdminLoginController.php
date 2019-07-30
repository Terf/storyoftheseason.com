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

class AdminLoginController extends AbstractController
{
    public function index()
    {
        return $this->render('login/admin.html.twig');
    }

    public function submit(Request $request, EntityManagerInterface $entityManager)
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        if ($email === null || $password === null) {
            throw new \Exception("Missing fields: need to POST 'email', 'password'; received " . print_r($request->request->all(), true));
        }

        $user = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['email' => $email]);
        if ($user === null) {
            return $this->redirectToRoute('login-form-admin');
        } else {
            if (password_verify($password, $user->getPassword())) {
                $token = bin2hex(random_bytes(16));
                $user->setToken($token);
                $entityManager->merge($user);
                $entityManager->flush();
                $response = new RedirectResponse($this->generateUrl('product-admin'));
                $response->headers->setCookie(Cookie::create('admin_token', $token));
                return $response;
            }
            return $this->redirectToRoute('login-form-admin');
        }
    }

    public function signOut(Request $request)
    {
        $response = $this->redirectToRoute('index');
        $response->headers->clearCookie('user_id');
        return $response;
    }
}
