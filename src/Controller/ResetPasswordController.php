<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Mailer;
use App\Entity;

class ResetPasswordController extends AbstractController
{
    public function index()
    {
        return $this->render('reset_password/index.html.twig');
    }

    public function sendResetEmail(Request $request, EntityManagerInterface $entityManager, Mailer $mailer)
    {
        $email = $request->request->get('email');

        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['email' => $email]);
        if ($user === null) {
            return new JsonResponse(false);
        }

        $token = bin2hex(random_bytes(16));
        $user->setToken($token);
        $entityManager->merge($user);
        $entityManager->flush();

        $mailer->send($user->getEmail(), "Story of the Season password reset", "
        <p>Hello {$user->getFirstName()},<p> 
        <p>A request was sent to reset your password. You can follow the special link <a href='{$this->generateUrl('new-password')}?token={$token}'>here</a> to enter a new password.</p>
        <br/>
        ");
        return $this->render('reset_password/info.html.twig');
    }

    public function newPassword()
    {
        return $this->render('reset_password/reset.html.twig');
    }

    public function resetPassword(Request $request, EntityManagerInterface $entityManager)
    {
        $password = $request->request->get('password');
        $token = $request->request->get('token');

        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $token]);
        if ($user === null || $password === null) {
            return new JsonResponse(false);
        }

        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $entityManager->merge($user);
        $entityManager->flush();

        $response = new RedirectResponse($this->generateUrl('shop'));
        $response->headers->setCookie(Cookie::create('user_token', $user->getToken()));
        return $response;
    }
}
