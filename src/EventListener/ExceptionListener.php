<?php

// src/EventListener/ExceptionListener.php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[AsEventListener]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = 500;
        $responseData = ['error' => $exception->getMessage()];

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $responseData = ['error' => $exception->getMessage()];
        }

        if ($exception instanceof UnprocessableEntityHttpException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $responseData = [
                'error' => 'Validation failed',
                'details' => json_decode($exception->getMessage(), true),
            ];
        }

        $event->setResponse(new JsonResponse($responseData, $statusCode));
    }
}
