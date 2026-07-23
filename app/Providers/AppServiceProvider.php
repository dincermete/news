<?php

namespace App\Providers;

use App\Contracts\SmsServiceInterface;
use App\Services\CartService;
use App\Services\CatalogCache;
use App\Services\NetgsmService;
use App\View\Composers\AccountLayoutComposer;
use App\View\Composers\StorefrontHeaderComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsServiceInterface::class, NetgsmService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->environment(['production', 'testing']));

        View::composer(['layouts.app', 'partials.footer'], function ($view): void {
            $view->with('footerLinks', app(CatalogCache::class)->footerLinks());
        });

        View::composer(['partials.header', 'layouts.app'], function ($view): void {
            $view->with('cartCount', app(CartService::class)->itemCount(request()));
        });

        View::composer('partials.header', StorefrontHeaderComposer::class);

        View::composer('layouts.account', AccountLayoutComposer::class);

        RateLimiter::for('live-heartbeat', function (Request $request): Limit {
            $key = (string) ($request->input('session_token') ?: $request->ip());

            return Limit::perSecond(1, 5)->by('live-heartbeat:'.$key);
        });

        RateLimiter::for('api', function (Request $request): Limit {
            $token = $request->user()?->currentAccessToken();
            $key = $token
                ? 'api-token:'.$token->id
                : 'api-ip:'.$request->ip();

            return Limit::perMinute((int) config('sanctum.api_rate_limit_per_minute', 60))
                ->by($key);
        });
    }
}
