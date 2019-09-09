<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class BookManagerController extends AbstractController
{
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->cookies->has('admin_token')) {
            $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
            if ($admin !== null) {
                return $this->render('book_manager/index.html.twig', [
                    'products' => $entityManager->getRepository(Entity\Product::class)->findAll(),
                    'books' => $entityManager->getRepository(Entity\Book::class)->findAll()
                ]);
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

        $title = $request->request->get('title');
        $products = $request->request->get('products');
        $url = $request->request->get('url');
        $image = $request->files->get('image');

        if ($title === null || $products === null || $url === null || $image === null) {
            throw new \Exception("Missing fields: need to POST 'title', 'products', 'url', 'image'; received " . print_r($request->request->all(), true));
        }

        if ($image->isValid()) {
            $path = '/var/www/html/public/uploads/';
            $fn = $image->getClientOriginalName();
            if (file_exists($path . $fn)) {
                $fn = uniqid() . '.' . $image->guessClientExtension();
            }
            $image->move($path, $fn);
        }

        $book = new Entity\Book;
        $book->setTitle($title);
        $book->setUrl($url);
        $book->setImage($fn);

        foreach ($products as $productId) {
            $product = $entityManager->getRepository(Entity\Product::class)->find($productId);
            $product->addBook($book);
            $entityManager->merge($product);
        }

        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(true);
    }

    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
        if ($admin !== null) {
            $id = $request->request->get('id');
            $book = $entityManager->getRepository(Entity\Book::class)->find($id);
            if ($book !== null) {
                $entityManager->remove($book);
                $entityManager->flush();
                return $this->redirectToRoute('shop');
            }
        }
        return new JsonResponse(false);
    }

    public function edit(Request $request, EntityManagerInterface $entityManager)
    {
        $admin = $entityManager->getRepository(Entity\Admin::class)->findOneBy(['token' => $request->cookies->get('admin_token')]);
        if ($admin !== null) {
            $book = $entityManager->getRepository(Entity\Book::class)->find($request->request->get('id'));
            $book->setTitle($request->request->get('title'));
            $book->setUrl($request->request->get('url'));
            $image = $request->files->get('file');
            if ($image !== null && $image->isValid()) {
                $path = '/var/www/html/public/uploads/';
                $fn = $image->getClientOriginalName();
                if (file_exists($path . $fn)) {
                    $fn = uniqid() . '.' . $image->guessClientExtension();
                }
                $image->move($path, $fn);
                $book->setImage($fn);
            }

            $entityManager->merge($book);
            $entityManager->flush();
            return $this->redirectToRoute('shop');
        }
        return new JsonResponse(false);
    }
}
