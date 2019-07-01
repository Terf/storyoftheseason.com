<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class RegistrationController extends AbstractController
{
    public function index()
    {
        return $this->render('registration/index.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }

    public function submit(Request $request, EntityManagerInterface $entityManager)
    {
        $buyer = new Entity\Buyer;
        $seller = new Entity\Seller;
        $location = new Entity\Location;

        $location->setAddress($request->request->get('addr'));
        $location->setZip($request->request->get('zip'));
        $location->setCountry($request->request->get('country'));

        $seller->setName($request->request->get('student'));
        $seller->setType($request->request->get('studentRelationship'));

        $buyer->setFirstName($request->request->get('fname'));
        $buyer->setLastName($request->request->get('lname'));
        $buyer->setLocation($location);
        $buyer->setSeller($seller);

        $entityManager->persist($location);
        $entityManager->persist($buyer);
        $entityManager->persist($seller);
        $entityManager->flush();

        return new JsonResponse(true);
    }

}
