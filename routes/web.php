<?php

use App\Http\Controllers\Account\AccountAffiliateController;
use App\Http\Controllers\Account\AccountDashboardController;
use App\Http\Controllers\Account\AccountFavoriteController;
use App\Http\Controllers\Account\AccountInvoiceController;
use App\Http\Controllers\Account\AccountOrderBillingController;
use App\Http\Controllers\Account\AccountOrderController;
use App\Http\Controllers\Account\AccountPaymentNotificationController;
use App\Http\Controllers\Account\AccountProfileController;
use App\Http\Controllers\Account\AccountSeoAnalysisController;
use App\Http\Controllers\Account\AccountSiteSubmissionController;
use App\Http\Controllers\Account\AccountSpinWheelController;
use App\Http\Controllers\Account\AccountSupportTicketController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BankTransferNotificationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatbotMessageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FooterLinkCatalogController;
use App\Http\Controllers\FreeAnalysisController;
use App\Http\Controllers\GeoPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LiveHeartbeatController;
use App\Http\Controllers\MarkNotificationReadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaytrCallbackController;
use App\Http\Controllers\PressReleaseCatalogController;
use App\Http\Controllers\SeoPackageCatalogController;
use App\Http\Controllers\SiteBundleCatalogController;
use App\Http\Controllers\SiteCatalogController;
use App\Http\Controllers\SiteFavoriteController;
use App\Http\Controllers\SiteQuestionController;
use App\Http\Controllers\SiteShowController;
use App\Http\Controllers\SiteViewController;
use App\Http\Controllers\StoryCatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public storefront (Blade + Alpine — no Livewire)
| Controllers are skeletons; Faz 11b–11g fill UI/behavior.
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');

Route::get('/siteler', SiteCatalogController::class)->name('sites.index');
Route::get('/basin-bulteni', PressReleaseCatalogController::class)->name('press-release.index');
Route::get('/tanitim-paketleri', SiteBundleCatalogController::class)->name('bundles.index');
Route::get('/story-satis', StoryCatalogController::class)->name('story.index');
Route::get('/footer-linkler', FooterLinkCatalogController::class)->name('footer-links.index');
Route::get('/geo', GeoPageController::class)->name('geo.index');
Route::get('/seo-paketleri', SeoPackageCatalogController::class)->name('seo-packages.index');
Route::get('/ucretsiz-analiz', [FreeAnalysisController::class, 'show'])->name('free-analysis.show');
// {slug} = Site.domain (public URL); do not change Site::getRouteKeyName (breaks /site/{site}/view id binding)
Route::get('/site/{slug}', SiteShowController::class)->name('sites.show');
Route::post('/site/{site}/favori', SiteFavoriteController::class)->name('sites.favorite');
Route::post('/site/{site}/soru', SiteQuestionController::class)->name('sites.question');

Route::get('/sepet', [CartController::class, 'index'])->name('cart.index');
Route::post('/sepet/ekle', [CartController::class, 'addItem'])->name('cart.add');
Route::delete('/sepet/kalem/{cartItem}', [CartController::class, 'removeItem'])->name('cart.remove');
Route::patch('/sepet/kalem/{cartItem}', [CartController::class, 'updateContent'])->name('cart.update-content');
Route::post('/sepet/kupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');

Route::middleware('guest')->group(function (): void {
    Route::get('/giris', [LoginController::class, 'create'])->name('login');
    Route::post('/giris', [LoginController::class, 'store'])->name('login.store');
    Route::get('/kayitol', [RegisterController::class, 'create'])->name('register');
    Route::post('/kayitol', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/cikis', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::post('/ucretsiz-analiz', [FreeAnalysisController::class, 'store'])->name('free-analysis.store');

    Route::get('/odeme', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/odeme', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/odeme/basarili/{orderGroup}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::patch('/bildirimler/{userNotification}/oku', MarkNotificationReadController::class)
        ->name('notifications.read');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::prefix('hesabim')->name('account.')->group(function (): void {
        Route::get('/', AccountDashboardController::class)->name('dashboard');
        Route::get('/profil', [AccountProfileController::class, 'edit'])->name('profile');
        Route::put('/profil', [AccountProfileController::class, 'update'])->name('profile.update');
        Route::get('/siparisler', AccountOrderController::class)->name('orders');
        Route::get('/siparisler/{orderGroup}', [AccountOrderController::class, 'show'])->name('orders.show');
        Route::post('/siparisler/{orderGroup}/fatura', AccountOrderBillingController::class)->name('orders.billing.store');
        Route::get('/faturalar', [AccountInvoiceController::class, 'index'])->name('invoices');
        Route::get('/faturalar/{invoice}/pdf', [AccountInvoiceController::class, 'download'])->name('invoices.download');
        Route::get('/favoriler', [AccountFavoriteController::class, 'index'])->name('favorites');
        Route::delete('/favoriler/{favorite}', [AccountFavoriteController::class, 'destroy'])->name('favorites.destroy');
        Route::get('/destek', [AccountSupportTicketController::class, 'index'])->name('support-tickets');
        Route::post('/destek', [AccountSupportTicketController::class, 'store'])->name('support-tickets.store');
        Route::get('/analizlerim', [AccountSeoAnalysisController::class, 'index'])->name('seo-analyses');
        Route::get('/odeme-bildirimi', AccountPaymentNotificationController::class)->name('payment-notification');
        Route::get('/site-basvurulari', [AccountSiteSubmissionController::class, 'index'])->name('site-submissions');
        Route::post('/site-basvurulari', [AccountSiteSubmissionController::class, 'store'])->name('site-submissions.store');
        Route::get('/cark', [AccountSpinWheelController::class, 'index'])->name('spin-wheel');
        Route::post('/cark/cevir', [AccountSpinWheelController::class, 'spin'])->name('spin-wheel.spin');
        Route::get('/satis-ortakligi', AccountAffiliateController::class)->name('affiliate');
    });
});

// Canonical CMS pages: /{slug}. Legacy /sayfa/{slug} redirects here.
Route::redirect('/sayfa/{slug}', '/{slug}', 301)
    ->where('slug', '^[a-z0-9]+(?:-[a-z0-9]+)*$');

Route::get('/sitemap.xml', function () {
    $path = public_path('sitemap.xml');

    abort_unless(is_file($path), 404);

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
})->name('sitemap');

// Faz 11f CMS pages — keep after specific routes so /siteler etc. win
Route::get('/{slug}', PageController::class)
    ->where('slug', '^[a-z0-9]+(?:-[a-z0-9]+)*$')
    ->name('pages.show');

/*
|--------------------------------------------------------------------------
| Integrations / existing endpoints
|--------------------------------------------------------------------------
*/
Route::post('/paytr/callback', PaytrCallbackController::class)
    ->name('paytr.callback');

Route::get('/paytr/ok', fn () => response('Ödeme başarılı', 200))->name('paytr.ok');
Route::get('/paytr/fail', fn () => response('Ödeme başarısız', 200))->name('paytr.fail');

Route::post('/odeme/havale-bildirimi', BankTransferNotificationController::class)
    ->middleware('auth')
    ->name('payment.bank-transfer-notify');

Route::post('/live/heartbeat', LiveHeartbeatController::class)
    ->middleware('throttle:live-heartbeat')
    ->name('live.heartbeat');

Route::post('/site/{site}/view', SiteViewController::class)
    ->name('site.view');

Route::post('/chatbot/message', ChatbotMessageController::class)
    ->name('chatbot.message');
