<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class ExportController extends AbstractController
{
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin !== null) {
                $rows = ["first,last,address,zip,state,country,seller_name,seller_type,phone,email,type,purchased_product,product_price"];
                $repository = $entityManager->getRepository(Entity\Purchase::class);
                foreach ($repository->findBy([], ['product_id' => 'ASC']) as $purchase) {
                    $user = $purchase->getUser();
                    $product = $purchase->getProduct();
                    $location = $user->getLocation();
                    $seller = $user->getSeller();
                    $rows[] = implode(',', [
                        $user->getFirstName(),
                        $user->getLastName(),
                        $location->getAddress(),
                        $location->getZip(),
                        $location->getState(),
                        $location->getCountry(),
                        $seller->getName(),
                        $seller->getType(),
                        $user->getPhone(),
                        $user->getEmail(),
                        $user->getType(),
                        $product->getName(),
                        $product->getPrice()
                    ]);
                }
                $response = new Response(implode("\n", $rows));
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', 'attachment; filename="purchases.csv"');
                return $response;
            }
        }
        return new Response('Access denied');
    }
}
