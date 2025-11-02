<?php

declare(strict_types=1);

use App\Exceptions\MyDomainException;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SubstituteUuids;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ForceJsonResponse::class);
        $middleware->api(SubstituteUuids::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (MyDomainException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        });

        $exceptions->render(function (BadRequestHttpException $e, Request $request) {
            return response()->json([
                'message' => $e->getPrevious()?->getMessage() ?? $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        });
    })->create();
