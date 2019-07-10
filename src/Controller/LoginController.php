<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $name = $request->request->get('name');
        $password = $request->request->get('password');

        if ($name === null || $password === null) {
            throw new \Exception("Missing fields: need to POST 'name', 'password'; received " . print_r($request->request->all(), true));
        }

        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['firstName' => $name]);
        if (password_verify($password, $user->getPassword())) {
            $response = new JsonResponse(true);
            $response->headers->setCookie(Cookie::create('user_id', $user->getId()));
            return $response;
        }
        return new JsonResponse(false);
    }
}
