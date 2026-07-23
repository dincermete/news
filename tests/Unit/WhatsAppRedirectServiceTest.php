<?php

namespace Tests\Unit;

use App\Services\WhatsAppRedirectService;
use RuntimeException;
use Tests\TestCase;

class WhatsAppRedirectServiceTest extends TestCase
{
    public function test_build_link_formats_wa_me_url_with_encoded_context(): void
    {
        config(['whatsapp.support_number' => '+90 532 123 45 67']);

        $link = app(WhatsAppRedirectService::class)->buildLink('Merhaba destek');

        $this->assertSame(
            'https://wa.me/905321234567?text='.rawurlencode('Merhaba destek'),
            $link,
        );
    }

    public function test_build_link_without_context_omits_query(): void
    {
        config(['whatsapp.support_number' => '905321234567']);

        $link = app(WhatsAppRedirectService::class)->buildLink();

        $this->assertSame('https://wa.me/905321234567', $link);
    }

    public function test_build_link_throws_when_number_missing(): void
    {
        config(['whatsapp.support_number' => '']);

        $this->expectException(RuntimeException::class);

        app(WhatsAppRedirectService::class)->buildLink('x');
    }
}
