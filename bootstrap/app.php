<?php

use App\Http\Middleware\BilikAuth;
use App\Http\Middleware\CheckAdminStatus;
use App\Http\Middleware\CheckBilikPemilihan;
use App\Http\Middleware\CheckVotingSession;
use App\Http\Middleware\CheckVotingTimeout;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PreventBackAfterVoting;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register middleware untuk route-specific
        $middleware->alias([
            'auth.bilik' => BilikAuth::class,
            'check.bilik.pemilihan' => CheckBilikPemilihan::class,
            'voting.session' => CheckVotingSession::class,
            'voting.timeout' => CheckVotingTimeout::class,
            'prevent.back.after.voting' => PreventBackAfterVoting::class,
            'check.admin.status' => CheckAdminStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();