<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LandingController extends AbstractController
{
    /**
     * index
     */
    public function index()
    {
        return $this->render('landing/index.html.twig');
    }

    /**
     * privacy-policy
     */
    public function privacyPolicy()
    {
        return $this->render('landing/privacy.html.twig');
    }
}
