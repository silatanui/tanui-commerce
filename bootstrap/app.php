<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'store.resolve' => \App\Http\Middleware\ResolveStore::class,
            'demo.mode' => \App\Http\Middleware\DemoMode::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            $store = $request->route('store');

            if (is_string($store) && $store !== '') {
                return route('store.login', $store);
            }

            return route('home');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            $store = $request->route('store');

            if (is_string($store) && $store !== '') {
                return route('store.home', $store);
            }

            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
