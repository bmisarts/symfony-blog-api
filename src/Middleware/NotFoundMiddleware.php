<?php 

// src/Middleware/NotFoundMiddleware.php
namespace App\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundMiddleware
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            // Message d'erreur personnalisé
            $message = "La page que vous recherchez n'existe pas.";

            // Créer une réponse HTTP avec le message d'erreur
            $response = new JsonResponse(['Error' => $message], Response::HTTP_NOT_FOUND);

            // Définir la réponse dans l'événement
            $event->setResponse($response);
        }
    }
}
