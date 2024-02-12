<?php 

// src/Middleware/JsonResponseMiddleware.php
namespace App\Middleware;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class JsonResponseMiddleware
{
    public function onKernelView(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        // Si le résultat est un tableau ou un objet, transformez-le en réponse JSON
        if (is_array($result) || is_object($result)) {
            $response = new JsonResponse($result);
            $event->setResponse($response);
        }
    }
}
