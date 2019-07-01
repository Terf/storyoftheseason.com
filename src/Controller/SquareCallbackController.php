<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SquareCallbackController extends AbstractController
{
    /**
     * square-webhook
     */
    public function receiveEvent()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            # code...
        }
        return new JsonResponse(true);
    }
}
