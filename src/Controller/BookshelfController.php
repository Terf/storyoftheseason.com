<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class BookshelfController extends AbstractController
{
    /**
     * bookshelf
     */
    public function index($id, Request $request, EntityManagerInterface $entityManager)
    {
        $user = $entityManager->getRepository(Entity\Buyer::class)->find($id);
        if ($user === null) {
            throw new \Exception("User #{$id} not found");
        }
        
        return $this->render('bookshelf/index.html.twig', [
            'purchases' => $user->getPurchases(),
        ]);
    }
}
