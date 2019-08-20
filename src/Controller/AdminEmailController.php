<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;
use App\Service\Mailer;
use Parsedown;

class AdminEmailController extends AbstractController
{
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin !== null) {
                return $this->render('admin_email/index.html.twig');
            }
        }
        return new Response('Access denied');
    }

    public function send(Request $request, EntityManagerInterface $entityManager, Mailer $mailer)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin !== null) {
                $emails = $request->request->get('emails');
                $subject = $request->request->get('subject');
                $message = $request->request->get('message');
                if ($emails === null || $message === null || $subject === null) {
                    return new JsonResponse(false);
                }
                $parsedown = new Parsedown;
                $emails = explode(',', $emails);
                $message = $parsedown->text($message);
                foreach ($emails as $email) {
                    $mailer->send($email, $subject, $message);
                }
                return new JsonResponse($emails);
            }
        }
        return new JsonResponse(false);
    }
}
