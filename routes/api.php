<?php

use App\Enums\ApiTokenAbility;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\SiteController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\FakeNotificationController;
use App\Http\Controllers\MarkNotificationReadController;
use App\Http\Controllers\PublicStatsController;
use App\Http\Middleware\EnsureApiAbility;
use App\Http\Middleware\LogApiRequest;
use Illuminate\Support\Facades\Route;

Route::get('/public-stats', PublicStatsController::class)
    ->name('api.public-stats');

Route::get('/fake-notification', FakeNotificationController::class)
    ->name('api.fake-notification');

Route::prefix('v1')
    ->middleware([
        'auth:sanctum',
        'throttle:api',
        LogApiRequest::class,
    ])
    ->group(function (): void {
        Route::get('/sites', [SiteController::class, 'index'])
            ->middleware(EnsureApiAbility::class.':'.ApiTokenAbility::ReadCatalog->value)
            ->name('api.v1.sites.index');

        Route::get('/sites/{site}', [SiteController::class, 'show'])
            ->middleware(EnsureApiAbility::class.':'.ApiTokenAbility::ReadCatalog->value)
            ->name('api.v1.sites.show');

        Route::post('/orders', [OrderController::class, 'store'])
            ->middleware(EnsureApiAbility::class.':'.ApiTokenAbility::CreateOrder->value)
            ->name('api.v1.orders.store');

        Route::get('/orders/{order}', [OrderController::class, 'show'])
            ->middleware(EnsureApiAbility::class.':'.ApiTokenAbility::ReadOrders->value)
            ->name('api.v1.orders.show');

        Route::get('/wallet/balance', [WalletController::class, 'balance'])
            ->middleware(EnsureApiAbility::class.':'.ApiTokenAbility::ReadWallet->value)
            ->name('api.v1.wallet.balance');
    });
