<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for all API exceptions
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data yang dikirimkan tidak valid. Periksa kembali isian form Anda.',
                        'errors'  => $e->errors(),
                    ], 422);
                }

                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda belum login atau sesi telah berakhir. Silakan login kembali.',
                    ], 401);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Endpoint atau resource yang diminta tidak ditemukan.',
                    ], 404);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'HTTP method yang digunakan tidak diizinkan untuk endpoint ini.',
                    ], 405);
                }

                if ($e instanceof \Illuminate\Database\QueryException) {
                    report($e);
                    return response()->json([
                        'success' => false,
                        'message' => 'Terjadi kesalahan pada database. Silakan coba lagi.',
                    ], 500);
                }

                if (config('app.debug')) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'trace'   => $e->getTrace(),
                    ], 500);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada server. Silakan hubungi administrator.',
                ], 500);
            }
        });
    })->create();

