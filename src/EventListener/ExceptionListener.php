<?php

// src/EventListener/ExceptionListener.php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

#[AsEventListener]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = 500;
        $responseData = [
            'error' => $exception->getMessage(),
        ];

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $responseData = [
                'error' => $exception->getMessage(),
            ];
        }

        if ($exception instanceof UnprocessableEntityHttpException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $responseData = [
                'error' => 'Validation failed',
                'details' => json_decode($exception->getMessage(), true),
            ];
        }

        if ($exception instanceof NotEncodableValueException) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $responseData = [
                'error' => 'Invalid JSON',
                'details' => $exception->getMessage(),
            ];
        }
        if ($exception instanceof ExceptionInterface) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $responseData = [
                'error' => 'Deserialization error',
                'details' => $exception->getMessage(),
            ];
        }

        $event->setResponse(new JsonResponse($responseData, $statusCode));
    }
}
