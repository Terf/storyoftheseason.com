<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;
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

        $productId = $request->request->get('productSelect');
        $books = $request->request->get('books');
        $json = $request->request->get('json');

        if ($productId === null || $books === null || $json === null) {
            throw new \Exception("Missing fields: need to POST 'productSelect', 'books', 'json'; received " . print_r($request->request->all(), true));
        }

        $product = $entityManager->getRepository(Entity\Product::class)->find($productId);
        $bookJson = json_decode($json, true);

        foreach ($books as $bookId) {
            $bookId = (int) $bookId;
            $existingBook = $entityManager->getRepository(Entity\Book::class)->find($bookId);
            $book = ($existingBook === null) ? new Entity\Book : $existingBook;
            $book->setProduct($product);
            foreach ($bookJson as $item) {
                if ($item['book']['id'] === $bookId) {
                    $book->setKitabooId($item['book']['id']);
                    $book->setIsbn($item['book']['isbn']);
                    $book->setImage($item['book']['thumbURL']);
                    $book->setTitle($item['book']['title']);
                    if ($existingBook === null) {
                        $entityManager->persist($book);
                    } else {
                        $entityManager->merge($book);
                    }
                    break;
                }
            }
        }

        $entityManager->flush();
        return new JsonResponse(true);
    }
}
