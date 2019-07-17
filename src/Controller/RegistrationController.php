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
        return $this->render('registration/index.html.twig');
    }

    public function submit(Request $request, EntityManagerInterface $entityManager)
    {
        $buyer = new Entity\Buyer;
        $seller = new Entity\Seller;
        $location = new Entity\Location;

        $location->setAddress($request->request->get('addr'));
        $location->setZip($request->request->get('zip'));
        $location->setCountry($request->request->get('country'));
        $location->setState($request->request->get('state'));

        $seller->setName($request->request->get('student'));
        $seller->setType($request->request->get('studentRelationship'));

        $buyer->setFirstName($request->request->get('fname'));
        $buyer->setLastName($request->request->get('lname'));
        $buyer->setEmail($request->request->get('email'));
        $buyer->setType($request->request->get('relationship'));
        $buyer->setPassword(password_hash($request->request->get('pass'), PASSWORD_DEFAULT));
        $buyer->setPhone($request->request->get('phone'));
        $buyer->setLocation($location);
        $buyer->setSeller($seller);

        $entityManager->persist($location);
        $entityManager->persist($buyer);
        $entityManager->persist($seller);
        $entityManager->flush();

        $purchase = $request->request->get('product');
        if ($purchase !== null) {
            $req = new Request;
            $req->request->set('user', $buyer->getId());
            $req->request->set('product', $purchase);
            return $this->forward('App\Controller\ProductController::purchase', array(
                'request'  => $req,
            ));
        }
        return new JsonResponse(true);
    }

}
