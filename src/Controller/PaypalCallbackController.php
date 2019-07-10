<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;
use App\Service\PaypalIPN as PaypalService;

class PaypalCallbackController extends AbstractController
{
    /**
     * paypal-webhook
     */
    public function receiveEvent(PaypalService $paypal, Request $request, EntityManagerInterface $entityManager)
    {
        $ipn = new PaypalIPN;
        if (getenv('APP_ENV') === 'debug') {
            $ipn->useSandbox();
        }
        $verified = $ipn->verifyIPN();
        if ($verified) {
            /*
             * Process IPN
             * A list of variables is available here:
             * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
            */
            // todo get $id vars from paypal post data
            $product = $entityManager->getRepository(Entity\Product::class)->find($id);
            $user = $entityManager->getRepository(Entity\Buyer::class)->find($id);

            $purchase = new Entity\Purchase;
            $purchase->setProduct($product);
            $purchase->setUser($user);
        }

        return new JsonResponse(true);
    }

}
