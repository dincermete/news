<?php

namespace App\Providers;

use App\Contracts\SmsServiceInterface;
use App\Models\SiteSetting;
use App\Services\CartService;
use App\Services\CatalogCache;
use App\Services\NetgsmService;
use App\View\Composers\AccountLayoutComposer;
use App\View\Composers\ChatbotWidgetComposer;
use App\View\Composers\StorefrontHeaderComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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

        $this->app->booted(function (): void {
            $this->applySiteSettingOverrides();
        });

        View::composer(['layouts.app', 'partials.footer', 'partials.header', 'layouts.account', 'components.chatbot-widget', 'about.index', 'contact.index', 'home', 'geo.index', 'backlink-packages.index', 'seo-packages.index', 'agency-services.index', 'agency-services.show', 'tools.index', 'tools.show', 'auth.login', 'auth.register'], function ($view): void {
            $view->with('siteSettings', SiteSetting::current());
        });

        View::composer(['layouts.app', 'partials.footer'], function ($view): void {
            $view->with('footerLinks', app(CatalogCache::class)->footerLinks());
        });

        View::composer(['partials.header', 'layouts.app'], function ($view): void {
            $view->with('cartCount', app(CartService::class)->itemCount(request()));
        });

        View::composer('partials.header', StorefrontHeaderComposer::class);

        View::composer('layouts.account', AccountLayoutComposer::class);

        View::composer('components.chatbot-widget', ChatbotWidgetComposer::class);

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

    private function applySiteSettingOverrides(): void
    {
        try {
            if (! Schema::hasTable('site_settings')) {
                return;
            }

            $s = SiteSetting::current();

            if (filled($s->paytr_merchant_id)) {
                config(['paytr.merchant_id' => $s->paytr_merchant_id]);
            }
            if (filled($s->paytr_merchant_key)) {
                config(['paytr.merchant_key' => $s->paytr_merchant_key]);
            }
            if (filled($s->paytr_merchant_salt)) {
                config(['paytr.merchant_salt' => $s->paytr_merchant_salt]);
            }
            config(['paytr.test_mode' => $s->paytr_test_mode ? '1' : '0']);

            if (filled($s->netgsm_username)) {
                config(['netgsm.username' => $s->netgsm_username]);
            }
            if (filled($s->netgsm_password)) {
                config(['netgsm.password' => $s->netgsm_password]);
            }
            if (filled($s->netgsm_header)) {
                config(['netgsm.header' => $s->netgsm_header]);
            }

            if (filled($s->openai_api_key)) {
                config(['openai.api_key' => $s->openai_api_key]);
            }
            if (filled($s->openai_model)) {
                config(['openai.model' => $s->openai_model]);
            }
            if (filled($s->openai_chatbot_model)) {
                config(['openai.chatbot_model' => $s->openai_chatbot_model]);
            }
            if (filled($s->openai_article_model)) {
                config(['openai.article_model' => $s->openai_article_model]);
            }

            if (filled($s->whatsapp_number)) {
                config(['whatsapp.support_number' => $s->whatsapp_number]);
            }

            if (filled($s->google_client_id)) {
                config(['services.google.client_id' => $s->google_client_id]);
            }
            if (filled($s->google_client_secret)) {
                config(['services.google.client_secret' => $s->google_client_secret]);
            }
        } catch (\Throwable) {
            // migrations / early boot
        }
    }
}
