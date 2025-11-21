<?php


namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Traits\ApiResponse; // Import your trait

class Handler extends ExceptionHandler
{
    use ApiResponse;

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e)
    {
        // Handle validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Handle unauthenticated
        if ($e instanceof AuthenticationException) {
            return response()->json($this->withError('Unauthenticated'), 401);
        }

        // Handle 404 Not Found
        if ($e instanceof NotFoundHttpException) {
            return response()->json($this->withError('Resource not found'), 404);
        }

        // Handle 405 Method Not Allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json($this->withError('Method not allowed'), 405);
        }

        // Optional: Handle other HTTP exceptions
        if (method_exists($e, 'getStatusCode')) {
            return response()->json($this->withError($e->getMessage()), $e->getStatusCode());
        }

        // Fallback for other exceptions
        return response()->json($this->withError('Server Error'), 500);
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json($this->withError('Unauthenticated'), 401);
    }
}