<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class KitabooController extends AbstractController
{
    /**
     * kitaboo-webhook-create
     */
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        $product = new Entity\Product;

        $product->setImage('');
        $product->setName($request->request->get('name'));
        $product->setPrice(0.0);
        $entityManager->persist($product);
        $entityManager->flush();
        return new JsonResponse(true);
    }

}
