<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;
use \DateTime;
use \DateTimeZone;

class LibraryController extends AbstractController
{
    /**
     * my-library
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        $user = null;
        $myLibrary = [];
        if ($request->cookies->has('user_token')) {
            $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $request->cookies->get('user_token')]);
            if ($user !== null) {
                // $myLibrary = $user->getUploads();
                foreach ($user->getPurchases() as $purchase) {
                    foreach ($purchase->getProduct()->getBooks() as $book) {
                        $myLibrary[] = $book;
                    }
                }
                $user = $user->getId();
            }
        }

        return $this->render('library/index.html.twig', [
            'user' => $user,
            'library' => $myLibrary
        ]);
    }

}
