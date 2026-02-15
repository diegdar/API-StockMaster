<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(append: [
            ThrottleRequests::class . ':api',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (NotFoundHttpException $e) {
            Log::error('Recurso no encontrado: ' . $e->getMessage());
        });
        $exceptions->renderable(function (NotFoundHttpException $e) {
            return response()->json(['error' => 'Recurso no encontrado'], 404);
        });

        $exceptions->renderable(function (AuthenticationException $e) {
            return response()->json(['error' => 'No autenticado'], 401);
        });

        $exceptions->renderable(function (AuthorizationException $e) {
            return response()->json(['error' => 'No autorizado'], 403);
        });

        // Handle Spatie Permission UnauthorizedException
        $exceptions->renderable(function (UnauthorizedException $e) {
            return response()->json(['error' => 'No autorizado'], 403);
        });

        // Handle Rate Limiting (ThrottleRequestsException)
        $exceptions->renderable(function (ThrottleRequestsException $e) {
            return response()->json(['error' => 'Demasiadas solicitudes'], 429);
        });

        $exceptions->renderable(function (\Throwable $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        });
    })->create();
