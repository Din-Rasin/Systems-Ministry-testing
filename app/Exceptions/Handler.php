<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log all exceptions
            Log::error('Exception occurred: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
            ]);
        });

        // Handle authentication exceptions
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        });

        // Handle authorization exceptions
        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden.'], 403);
            }

            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        });

        // Handle model not found exceptions
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Resource not found.'], 404);
            }

            return redirect()->back()->with('error', 'The requested resource was not found.');
        });

        // Handle not found HTTP exceptions
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Endpoint not found.'], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // Handle method not allowed HTTP exceptions
        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Method not allowed.'], 405);
            }

            return redirect()->back()->with('error', 'Method not allowed.');
        });

        // Handle general HTTP exceptions
        $this->renderable(function (HttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            }

            // Check if we have a custom error view for this status code
            if (view()->exists('errors.' . $e->getStatusCode())) {
                return response()->view('errors.' . $e->getStatusCode(), [], $e->getStatusCode());
            }

            // Fallback to generic error view
            return response()->view('errors.general', [
                'code' => $e->getStatusCode(),
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        });

        // Handle validation exceptions
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Validation failed.',
                    'messages' => $e->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withInput($request->input())
                ->withErrors($e->errors());
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $exception)
    {
        // Handle specific exceptions with custom responses
        if ($exception instanceof \Exception) {
            // Log the exception for debugging
            Log::error('Unhandled exception: ' . $exception->getMessage(), [
                'exception' => $exception,
                'user_id' => Auth::id(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'input' => $request->all(),
            ]);
        }

        return parent::render($request, $exception);
    }
}
