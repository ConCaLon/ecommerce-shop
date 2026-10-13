<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(function (Request $request) {
            if (!$request->user()->hasVerifiedEmail()) {
                return route('verification.notice');
            }

            return route($request->user()->isStaff() ? 'admin.dashboard' : 'home');
        });

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'check.banned' => \App\Http\Middleware\CheckBanned::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Exclude MoMo IPN from CSRF verification (external POST from MoMo servers)
        $middleware->validateCsrfTokens(except: [
            'momo/ipn',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidSignatureException $exception, Request $request) {
            if ($request->routeIs('verification.verify')) {
                return redirect()->route('verification.notice')
                    ->with('error', 'Liên kết xác thực không hợp lệ hoặc đã hết hạn. Vui lòng gửi lại email xác thực.');
            }
        });
    })->create();
