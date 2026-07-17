<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a success JSON response.
     */
    protected function success(mixed $data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function error(string $message = '', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Return a not found JSON response.
     */
    protected function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return an unauthorized JSON response.
     */
    protected function unauthorized(string $message = 'Unauthorized.'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Return a validation error JSON response.
     */
    protected function validationError(mixed $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }
}
