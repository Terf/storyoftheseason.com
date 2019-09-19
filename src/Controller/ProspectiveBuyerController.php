<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class ProspectiveBuyerController extends AbstractController
{
    /**
     * prospective-buyer
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin !== null) {
                return $this->render('prospective_buyer/index.html.twig', ['products' => $entityManager->getRepository(Entity\Product::class)->findAll()]);
            }
        }
        return new Response('Access denied');
    }

    /**
     * prospective-buyer-create
     */
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin !== null) {
                $prospectiveBuyer = new Entity\ProspectiveBuyer;
                $prospectiveBuyer->setEmail($request->request->get('email'));
                $prospectiveBuyer->setProduct($entityManager->getRepository(Entity\Product::class)->find($request->request->get('product')));
                return new JsonResponse(true);
            }
        }
        return new Response('Access denied');
    }
}
