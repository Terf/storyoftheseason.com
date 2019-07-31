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
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin !== null) {
                return $this->render('product_manager/index.html.twig');
            }
        }
        return new Response('Access denied');
    }

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

        $name = $request->request->get('name');
        $price = $request->request->get('price');
        $image = $request->files->get('image');
        $description = $request->request->get('description');

        if ($name === null || $price === null || $image === null || $description === null) {
            throw new \Exception("Missing fields: need to POST 'name', 'price', 'image', 'description'; received " . print_r($request->request->all(), true));
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
        $product->setDescription($description);
        $entityManager->persist($product);
        $entityManager->flush();
        return new JsonResponse(true);
    }

    public function delete(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $admin = $entityManager->getRepository(Entity\User::class)->findOneBy(['token' => $request->request->get('token')]);
        if ($admin !== null) {
            $product = $entityManager->getRepository(Entity\Product::class)->find($id);
            $entityManager->remove($product);
            $entityManager->flush();
            return $this->redirectToRoute('shop');
        }
        return new JsonResponse(false);
    }
}
