<?php

namespace Tests\Feature;

use App\Enums\SupportTicketSource;
use App\Models\SupportTicket;
use App\Services\AiChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatbotServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'openai.api_key' => 'test-key',
            'openai.base_url' => 'https://api.openai.com/v1',
            'openai.chatbot_model' => 'gpt-4o-mini',
            'openai.max_tokens.chatbot' => 400,
            'openai.tool_max_rounds' => 3,
            'whatsapp.support_number' => '905321234567',
        ]);

        Http::preventStrayRequests();
    }

    public function test_respond_returns_normal_assistant_reply(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Merhaba, nasıl yardımcı olabilirim?']],
                ],
            ]),
        ]);

        $result = app(AiChatbotService::class)->respond('sess-1', 'Merhaba');

        $this->assertSame('Merhaba, nasıl yardımcı olabilirim?', $result['reply']);
        $this->assertNull($result['escalation']);
    }

    public function test_respond_handles_tool_calls_then_final_answer(): void
    {
        Http::fakeSequence('https://api.openai.com/v1/chat/completions')
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => null,
                        'tool_calls' => [[
                            'id' => 'call_1',
                            'type' => 'function',
                            'function' => [
                                'name' => 'get_faq_answer',
                                'arguments' => json_encode(['topic' => 'yayın süresi'], JSON_THROW_ON_ERROR),
                            ],
                        ]],
                    ],
                ]],
            ])
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => 'Siparişler genellikle 2-7 iş gününde yayınlanır.',
                    ],
                ]],
            ]);

        \App\Models\FaqEntry::factory()->create([
            'question_topic' => 'yayın süresi',
            'answer' => '2-7 iş günü',
            'is_active' => true,
        ]);

        $result = app(AiChatbotService::class)->respond('sess-tools', 'Siparişler ne kadar sürede yayınlanır?');

        $this->assertSame('Siparişler genellikle 2-7 iş gününde yayınlanır.', $result['reply']);
        $this->assertNull($result['escalation']);
        Http::assertSentCount(2);
    }

    public function test_escalate_creates_support_ticket_and_whatsapp_link(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "Bu bilgiyi sistemden göremiyorum.\n[ESCALATE]",
                    ],
                ]],
            ]),
        ]);

        $result = app(AiChatbotService::class)->respond('sess-esc', 'Bakiyem ne kadar?');

        $this->assertStringContainsString('sistemden göremiyorum', $result['reply']);
        $this->assertStringNotContainsString('[ESCALATE]', $result['reply']);
        $this->assertNotNull($result['escalation']);
        $this->assertStringStartsWith('https://wa.me/905321234567', $result['escalation']['whatsapp_link']);

        $this->assertDatabaseHas(SupportTicket::class, [
            'id' => $result['escalation']['support_ticket_id'],
            'source' => SupportTicketSource::ChatbotEscalation->value,
        ]);
    }
}
