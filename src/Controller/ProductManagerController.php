<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class ProductManagerController extends AbstractController
{
    public function index($password, Request $request)
    {
        return (getenv('ADMIN_PASSWORD') === $password) ?
            $this->render('product_manager/index.html.twig') :
            new Response('Invalid password');
    }

    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        $name = $request->request->get('name');
        $price = $request->request->get('price');
        $image = $request->files->get('image');

        if ($name === null || $price === null || $image === null) {
            throw new \Exception("Missing fields: need to POST 'name', 'price', 'image'; received " . print_r($request->request->all(), true));
        }

        $product = new Entity\Product;

        if ($image->isValid()) {
            $path = '/var/www/html/public/uploads/';
            $fn = $image->getClientOriginalName();
            if (file_exists($path . $fn)) {
                $fn = uniqid() . '.' . $image->guessClientExtension();
            }
            $image->move($path, $fn);
            $product->setImage($fn);
        }

        $product->setName($name);
        $product->setPrice((float) $price);
        $entityManager->persist($product);
        $entityManager->flush();
        return new JsonResponse(true);
    }
}
