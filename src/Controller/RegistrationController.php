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

        $this->registerWithKitaboo($buyer, $request->request->get('pass'));

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

    private function registerWithKitaboo(Entity\Buyer $user, string $unhashedPassword)
    {
        $data = json_encode([
            'user' => [
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'userName' => $user->getEmail(),
                'password' => $unhashedPassword,
                'clientUserID' => $user->getId(),
                'email' => $user->getEmail()
            ]
        ]);
        shell_exec('sudo docker run --rm -e ACTION=register_user -e DATA='.escapeshellarg($data).' -e CONSUMER_KEY='.getenv('KITABOO_CONSUMER_KEY_PROD').' -e SECRET_KEY='.getenv('KITABOO_SECRET_KEY_PROD').' --name running-kitaboo kitaboo');
    }
}
