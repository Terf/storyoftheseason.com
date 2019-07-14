<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class UploadController extends AbstractController
{
    /**
     * upload-form
     */
    public function index()
    {
        return $this->render('upload/index.html.twig');
    }

    /**
     * upload-submit
     */
    public function submit()
    {
        return new JsonResponse(true);
    }
}
