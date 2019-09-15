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
        return $this->render('login/index.html.twig', ['admin' => true]);
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
            return new JsonResponse(['result' => false, 'reason' => 'email']);
        } else {
            if (password_verify($password, $user->getPassword())) {
                $token = bin2hex(random_bytes(16));
                $user->setToken($token);
                $entityManager->merge($user);
                $entityManager->flush();
                return new JsonResponse(['result' => true, 'token' => $user->getToken(), 'redirect' => $this->generateUrl('product-admin')]);
            }
            return new JsonResponse(['result' => false, 'reason' => 'password']);
        }
    }
}
