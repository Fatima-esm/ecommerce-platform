<?php

namespace App\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiErrors
{
    /** استخدمه في الكونترولر بدل response()->json(['message'=>...], 409) */
    public static function json(int $status, string $code, string $message, mixed $details = null, array $headers = []): JsonResponse
    {
        return response()->json(
            ['error' => ['status' => $status, 'code' => $code, 'message' => $message, 'details' => $details]],
            $status,
            $headers
        );
    }

    public static function fromThrowable(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return self::json(422, 'VALIDATION_FAILED', 'The given data was invalid.', $e->errors());
        }
        if ($e instanceof AuthenticationException) {
            return self::json(401, 'UNAUTHENTICATED', 'Authentication is required or the token is invalid.');
        }
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $code = match ($status) {
                400 => 'BAD_REQUEST', 403 => 'FORBIDDEN', 404 => 'NOT_FOUND',
                405 => 'METHOD_NOT_ALLOWED', 409 => 'CONFLICT', 429 => 'TOO_MANY_REQUESTS',
                503 => 'SERVICE_UNAVAILABLE', default => 'HTTP_ERROR',
            };
            $message = $status === 404 ? 'Resource not found.' : (Response::$statusTexts[$status] ?? 'Error');
            return self::json($status, $code, $message, null, $e->getHeaders());
        }

        return self::json(500, 'INTERNAL_ERROR', 'Unexpected server error.');
    }
}