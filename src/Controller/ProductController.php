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
    public function shop(EntityManagerInterface $entityManager)
    {
        return $this->render('product/index.html.twig', [
            'products' => $entityManager->getRepository(Entity\Product::class)->findAll(),
        ]);
    }

    public function purchase(Request $request)
    {
        $price = $request->request->get('price'); // todo verify this is actual price
        $name = $request->request->get('name');
        $userId = $request->request->get('user');

        if ($price === null || $name === null || $userId === null) {
            throw new \Exception("Missing fields: need to POST 'name', 'price', 'user'; received " . print_r($request->request->all(), true));
        }

        $query = [];
        $query['business'] = 'chris@storyoftheseason.co';
        $query['item_name'] = $name;
        $query['amount'] = $price;
        $query['currency_code'] = 'USD';
        $query['return_url'] = 'https://storyoftheseason.co?paypal=success';
        $query['cancel_url'] = 'https://storyoftheseason.co?paypal=cancel';
        $query['ipn_notification_url'] = 'https://storyoftheseason.co/webhooks/paypal';
        $query['custom'] = $userId;

        $query_string = http_build_query($query);
        return $this->redirect('https://www.paypal.com/cgi-bin/webscr?' . $query_string);
        
    }

}
