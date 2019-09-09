<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class CouponController extends AbstractController
{
    /**
     * coupon-admin
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin !== null) {
                return $this->render('coupon/index.html.twig', [
                    'products' => $entityManager->getRepository(Entity\Product::class)->findAll()
                ]);
            }
        }
        return new Response('Access denied');
    }

    /**
     * coupon-create
     */
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin === null) {
                return new JsonResponse(false);
            }
        } else {
            return new JsonResponse(false);
        }

        $code = $request->request->get('code');
        $discount = $request->request->get('discount');
        $product = $request->request->get('product');

        if ($code === null || $discount === null || $product === null) {
            throw new \Exception("Missing fields: need to POST 'code', 'discount', 'product'; received " . print_r($request->request->all(), true));
        }

        $product = $entityManager->getRepository(Entity\Product::class)->find($product);

        if ($product === null) {
            throw new \Exception("Can not find requested product");
        }

        $coupon = new Entity\Coupon;
        $coupon->setCode($code);
        $coupon->setDiscount((int) $discount);
        $coupon->setProduct($product);
        $entityManager->persist($coupon);
        $entityManager->flush();
        return $this->redirectToRoute('coupon-admin');
    }
}
