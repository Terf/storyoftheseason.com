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
    public function receiveEvent(PaypalService $ipn, Request $request, EntityManagerInterface $entityManager)
    {
        $ipn->usePHPCerts();
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
            $custom = $request->request->get('custom');
            $product = $entityManager->getRepository(Entity\Product::class)->find($custom[0]);
            $user = $entityManager->getRepository(Entity\Buyer::class)->find($custom[1]);

            $purchase = new Entity\Purchase;
            $purchase->setProduct($product);
            $purchase->setUser($user);
            $entityManager->persist($purchase);
            $entityManager->flush();
            return new JsonResponse(true);
        }

        return new JsonResponse(false);
    }

}
