<?php

namespace Tests\Feature;

use App\Enums\ChatbotMessageRole;
use App\Models\ChatbotConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotConversationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_messages_for_unknown_session_token(): void
    {
        $this->getJson(route('chatbot.conversation', ['session_token' => 'unknown-token']))
            ->assertOk()
            ->assertExactJson(['messages' => []]);
    }

    public function test_returns_user_and_assistant_messages_excluding_system_prompt(): void
    {
        $conversation = ChatbotConversation::factory()->create(['session_token' => 'abc-123']);

        $conversation->messages()->create([
            'session_token' => 'abc-123',
            'role' => ChatbotMessageRole::System,
            'content' => 'system prompt, should never leak',
        ]);
        $conversation->messages()->create([
            'session_token' => 'abc-123',
            'role' => ChatbotMessageRole::User,
            'content' => 'Merhaba',
        ]);
        $conversation->messages()->create([
            'session_token' => 'abc-123',
            'role' => ChatbotMessageRole::Assistant,
            'content' => 'Merhaba! Yardımcı olabilir miyim?',
        ]);

        $response = $this->getJson(route('chatbot.conversation', ['session_token' => 'abc-123']))
            ->assertOk();

        $messages = $response->json('messages');

        $this->assertCount(2, $messages);
        $this->assertSame('user', $messages[0]['role']);
        $this->assertSame('Merhaba', $messages[0]['content']);
        $this->assertSame('assistant', $messages[1]['role']);
        $this->assertStringNotContainsString('system prompt', json_encode($messages));
    }

    public function test_session_token_is_required(): void
    {
        // Non-api routes in this app don't auto-render JSON validation errors
        // (see bootstrap/app.php shouldRenderJsonWhen), matching chatbot.message's
        // existing behavior: an invalid request redirects rather than returning 422.
        $this->get(route('chatbot.conversation'))->assertRedirect();
    }
}
