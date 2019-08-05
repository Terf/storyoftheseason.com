<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class ProductController extends AbstractController
{
    public function shop(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('userToken')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            $admin = ($admin === null) ? false : true;
        } else {
            $admin = false;
        }
        return $this->render('product/index.html.twig', [
            'products' => $entityManager->getRepository(Entity\Product::class)->findAll(),
            'isAdmin' => $admin
        ]);
    }

    public function purchase(Request $request, EntityManagerInterface $entityManager)
    {
        $productId = $request->request->get('product');
        $userId = $request->request->get('user');
        if ($productId === null || $userId === null) {
            throw new \Exception("Missing fields: need to POST 'product', 'user'; received " . print_r($request->request->all(), true));
        }

        $product = $entityManager->getRepository(Entity\Product::class)->find($productId);
        if ($product === null) {
            throw new \Exception("Product #{$productId} not found");
        }

        $discount = 1;
        $couponCode = $request->request->get('coupon');
        if ($couponCode !== null) {
            $coupon = $entityManager->getRepository(Entity\Coupon::class)->findOneBy(['code' => $couponCode]);
            if ($coupon !== null) {
                $discount = 1 - ($coupon->getDiscount() / 100);
            }
        }

        $query = [];
        $query['cmd'] = '_xclick';
        $query['business'] = 'chris@storyoftheseason.co';
        $query['item_name'] = $product->getName();
        $query['amount'] = $product->getPrice() * $discount;
        $query['currency_code'] = 'USD';
        $query['return_url'] = 'https://storyoftheseason.co/user/' . $userId . '/bookshelf';
        $query['cancel_url'] = 'https://storyoftheseason.co?paypal=cancel';
        $query['ipn_notification_url'] = 'https://storyoftheseason.co/webhooks/paypal';
        $query['custom'] = "{$productId},{$userId}";

        $query_string = http_build_query($query);
        return $this->redirect('https://www.paypal.com/cgi-bin/webscr?' . $query_string);
        
    }

}
