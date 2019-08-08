<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;
use App\Service\PaypalIPN as PaypalService;
use App\Service\Mailer;

class PaypalCallbackController extends AbstractController
{
    /**
     * paypal-webhook
     */
    public function receiveEvent(PaypalService $ipn, Mailer $mailer, Request $request, EntityManagerInterface $entityManager)
    {
        $ipn->usePHPCerts();
        if (getenv('APP_ENV') === 'debug') {
            $ipn->useSandbox();
        }
        // $verified = $ipn->verifyIPN();
        // if ($verified) {
        /*
         * Process IPN
         * A list of variables is available here:
         * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
        */
        $custom = $request->request->get('custom');
        $custom = explode(',', $custom);
        if (count($custom) !== 2 || !is_numeric($custom[0]) || !is_numeric($custom[1])) {
            throw new \Exception("custom field not POSTed correctly");
        }
        $product = $entityManager->getRepository(Entity\Product::class)->find($custom[0]);
        $user = $entityManager->getRepository(Entity\Buyer::class)->find($custom[1]);
        if ($product === null) {
            throw new \Exception("Product #{$product} not found");
        }
        if ($user === null) {
            throw new \Exception("User #{$user} not found");
        }

        $purchase = new Entity\Purchase;
        $purchase->setProduct($product);
        $purchase->setUser($user);
        $entityManager->persist($purchase);
        $entityManager->flush();
        // assign all created books to user
        foreach ($product->getBooks() as $book) {
            $data = "bookID={$book->getKitabooId()}&userID={$user->getId()}";
            shell_exec('sudo docker run --rm -e ACTION=purchase -e DATA='.escapeshellarg($data).' -e CONSUMER_KEY='.getenv('KITABOO_CONSUMER_KEY_PROD').' -e SECRET_KEY='.getenv('KITABOO_SECRET_KEY_PROD').' --name running-kitaboo kitaboo');
        }

        $mailer->send($user->getEmail(), "Story of the Season subscription", "
        <p>Thank you for subscribing to {$product->getName()}.  Our goal is to deeply engage fans, patrons, friends and family, across the country with their favorite team and create a community around your program by telling your team’s story every week throughout the season.</p>
        <p>We will publish the first newsletter on Wednesday after the first game of the season.  When the first newsletter is published, we will email you information and instructions for how to download and login to our website and app to view your Story of the Season weekly newsletters.</p>
        <p>In the meantime, do you have content that you want to share in the Story of the Season?  You can upload your own content <a href='https://storyoftheseason.co/upload'>by clicking here</a>!</p>
        <p>We are excited to be telling the {$product->getName()} and for you to be a part of it.  Look for a message from me soon!</p>
        <br/>
        <p>Best,</p>
        <p>Chris Herman</p>
        <p>Founder and CEO</p>
        ");
            
        return new JsonResponse(true);
        // }

        // return new JsonResponse(false);
    }
}
