<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_chatbot_widget_and_deferred_script(): void
    {
        $this->withVite();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="chatbot-widget"', false);
        $response->assertSee('Sohbet başlat');
        $response->assertSee('5000 TL');
        $response->assertSee('data-chatbot-endpoint', false);
        $response->assertSee('data-chip', false);

        $html = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/<script[^>]+type=["\']module["\'][^>]*src=["\'][^"\']*chatbot[^"\']*["\']/i',
            $html,
            'chatbot.js should load as a deferred ES module (type=module).',
        );

        $headEnd = strpos($html, '</head>');
        $chatbotPos = stripos($html, 'chatbot');
        $this->assertNotFalse($headEnd);
        $this->assertNotFalse($chatbotPos);
        $this->assertGreaterThan(
            $headEnd,
            $chatbotPos,
            'chatbot.js should load after </head> (not in the critical head bundle).',
        );
    }
}
