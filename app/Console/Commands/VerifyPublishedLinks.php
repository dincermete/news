<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\PublishedLink;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VerifyPublishedLinks extends Command
{
    protected $signature = 'links:verify-published';

    protected $description = 'Guarantee süresi içindeki yayınlanmış linkleri canlılık ve dofollow açısından doğrula';

    public function handle(): int
    {
        $links = PublishedLink::query()
            ->whereNotNull('guarantee_until')
            ->where('guarantee_until', '>=', now())
            ->with(['order.site'])
            ->get();

        if ($links->isEmpty()) {
            $this->info('Doğrulanacak yayın linki bulunamadı.');

            return self::SUCCESS;
        }

        $this->info("{$links->count()} yayın linki kontrol ediliyor...");

        $admins = User::query()
            ->where('role', UserRole::Admin)
            ->get();

        foreach ($links as $link) {
            $this->verifyLink($link, $admins);
        }

        $this->info('Doğrulama tamamlandı.');

        return self::SUCCESS;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $admins
     */
    protected function verifyLink(PublishedLink $link, $admins): void
    {
        $isLive = false;
        $isDofollowVerified = false;
        $html = null;

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'NewstanitimLinkVerifier/1.0',
                ])
                ->get($link->published_url);

            $isLive = $response->successful();
            $html = $isLive ? $response->body() : null;
        } catch (\Throwable $exception) {
            $this->warn("Kontrol başarısız (#{$link->id}): {$exception->getMessage()}");
        }

        if (is_string($html)) {
            $isDofollowVerified = $this->hasDofollowBacklink($html, $link);
        }

        $wasLive = $link->is_live;
        $wasDofollow = $link->is_dofollow_verified;

        $link->forceFill([
            'is_live' => $isLive,
            'is_dofollow_verified' => $isDofollowVerified,
            'last_checked_at' => now(),
        ])->save();

        $becameDown = $wasLive && ! $isLive;
        $becameNofollow = $wasDofollow && ! $isDofollowVerified;
        $isProblematic = ! $isLive || ! $isDofollowVerified;

        if ($isProblematic && ($becameDown || $becameNofollow || ! $wasLive || ! $wasDofollow)) {
            $this->notifyAdmins($link, $admins, $isLive, $isDofollowVerified);
        }

        $status = ($isLive && $isDofollowVerified) ? 'OK' : 'SORUN';
        $this->line("#{$link->id} {$link->published_url} → {$status}");
    }

    protected function hasDofollowBacklink(string $html, PublishedLink $link): bool
    {
        $order = $link->order;
        $siteDomain = $order?->site?->domain;

        if (blank($siteDomain)) {
            // Domain bilinmiyorsa sayfada rel="nofollow" olmayan en az bir dış link var mı bak.
            return (bool) preg_match(
                '/<a\b(?![^>]*\brel=(["\'])[^"\']*nofollow[^"\']*\1)[^>]*\bhref=(["\'])https?:\/\/[^"\']+\2[^>]*>/i',
                $html,
            );
        }

        $escapedDomain = preg_quote($siteDomain, '/');

        // Hedef domain'e giden ve nofollow olmayan anchor ara.
        $pattern = '/<a\b(?![^>]*\brel=(["\'])[^"\']*nofollow[^"\']*\1)[^>]*\bhref=(["\'])[^"\']*'.$escapedDomain.'[^"\']*\2[^>]*>/i';

        if (preg_match($pattern, $html) === 1) {
            return true;
        }

        // rel attribute href'ten sonra gelebilir; ters sıra için ikinci pattern.
        $patternAlt = '/<a\b[^>]*\bhref=(["\'])[^"\']*'.$escapedDomain.'[^"\']*\1(?![^>]*\brel=(["\'])[^"\']*nofollow[^"\']*\2)[^>]*>/i';

        return preg_match($patternAlt, $html) === 1;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $admins
     */
    protected function notifyAdmins(PublishedLink $link, $admins, bool $isLive, bool $isDofollowVerified): void
    {
        if ($admins->isEmpty()) {
            return;
        }

        $issues = collect([
            ! $isLive ? 'link canlı değil' : null,
            ! $isDofollowVerified ? 'dofollow doğrulanamadı / nofollow' : null,
        ])->filter()->implode(', ');

        Notification::make()
            ->title('Yayın linki sorunu')
            ->body('Sipariş #'.$link->order_id.' — '.Str::limit($link->published_url, 80).': '.$issues)
            ->danger()
            ->sendToDatabase($admins);
    }
}
