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
                // todo: reading directly from stdout of docker run doesn't seem to work
                $result = shell_exec('sudo docker run --rm -e ACTION=list_books -e CONSUMER_KEY='.getenv('KITABOO_CONSUMER_KEY_PROD').' -e SECRET_KEY='.getenv('KITABOO_SECRET_KEY_PROD').' --name running-kitaboo kitaboo > result && cat result');
                $books = json_decode($result, true)['bookList'];
                return $this->render('book_manager/index.html.twig', [
                    'products' => $entityManager->getRepository(Entity\Product::class)->findAll(),
                    'books' => $books
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
            $upload->setFile($fn);
        }

        $book = new Entity\Book;
        $book->setTitle($title);
        $book->setUrl($url);
        $book->setImage($fn);
        // tmp values until were sure were moving away from kitaboo
        $book->setIsbn(time());
        $book->kitabooId(time());

        foreach ($products as $productId) {
            $product = $entityManager->getRepository(Entity\Product::class)->find($productId);
            $productId->addBook($book);
            $entityManager->merge($product);
        }

        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(true);
    }
}
