<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require_once __DIR__.'/mockery.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active.user' => \App\Modules\Auth\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Ошибка валидации.',
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage() !== '' ? $exception->getMessage() : 'Требуется авторизация.',
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $exception, $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage() !== '' ? $exception->getMessage() : 'Доступ запрещен.',
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $exception, $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Запись не найдена.',
            ], 404);
        });

        $exceptions->render(function (ThrottleRequestsException $exception, $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Слишком много запросов. Попробуйте позже.',
            ], 429);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            if (! in_array($exception->getStatusCode(), [400, 404, 405, 409, 422], true)) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage() !== '' ? $exception->getMessage() : 'Запрос не может быть обработан.',
            ], $exception->getStatusCode());
        });
    })
    ->create();
