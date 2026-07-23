<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiClient
{
    /**
     * @param  list<array{role: string, content?: string|null, tool_calls?: mixed, tool_call_id?: string, name?: string}>  $messages
     * @param  list<array<string, mixed>>|null  $tools
     * @return array{content: ?string, tool_calls: list<array<string, mixed>>, raw: array<string, mixed>}
     */
    public function chat(
        array $messages,
        ?int $maxTokens = null,
        ?array $tools = null,
        ?string $model = null,
    ): array {
        $apiKey = (string) config('openai.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('OpenAI API anahtarı yapılandırılmamış.');
        }

        $payload = [
            'model' => $model ?? (string) config('openai.model'),
            'messages' => $messages,
            'max_tokens' => $maxTokens ?? (int) config('openai.max_tokens.suggestion'),
        ];

        if ($tools !== null && $tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout((int) config('openai.timeout', 60))
                ->post(rtrim((string) config('openai.base_url'), '/').'/chat/completions', $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new RuntimeException('OpenAI isteği başarısız: '.$exception->getMessage(), previous: $exception);
        }

        $message = $response['choices'][0]['message'] ?? [];
        $toolCalls = $message['tool_calls'] ?? [];

        return [
            'content' => isset($message['content']) ? (string) $message['content'] : null,
            'tool_calls' => is_array($toolCalls) ? array_values($toolCalls) : [],
            'raw' => is_array($response) ? $response : [],
        ];
    }

    /**
     * @param  list<array{role: string, content?: string|null, tool_calls?: mixed, tool_call_id?: string, name?: string}>  $messages
     */
    public function chatText(array $messages, ?int $maxTokens = null, ?string $model = null): string
    {
        $result = $this->chat($messages, $maxTokens, null, $model);

        return trim((string) ($result['content'] ?? ''));
    }

    /**
     * @param  list<array{role: string, content?: string|null}>  $messages
     * @param  list<array<string, mixed>>  $tools
     * @param  callable(string $name, array<string, mixed> $arguments): mixed  $toolHandler
     */
    public function chatWithTools(
        array $messages,
        array $tools,
        callable $toolHandler,
        ?int $maxTokens = null,
        ?string $model = null,
        ?int $maxRounds = null,
    ): string {
        $maxRounds ??= (int) config('openai.tool_max_rounds', 3);
        $conversation = $messages;

        for ($round = 0; $round < $maxRounds; $round++) {
            $result = $this->chat($conversation, $maxTokens, $tools, $model);

            if ($result['tool_calls'] === []) {
                return trim((string) ($result['content'] ?? ''));
            }

            $conversation[] = [
                'role' => 'assistant',
                'content' => $result['content'],
                'tool_calls' => $result['tool_calls'],
            ];

            foreach ($result['tool_calls'] as $toolCall) {
                $name = (string) data_get($toolCall, 'function.name', '');
                $rawArgs = (string) data_get($toolCall, 'function.arguments', '{}');
                /** @var array<string, mixed> $arguments */
                $arguments = json_decode($rawArgs, true) ?: [];

                $toolResult = $toolHandler($name, $arguments);

                $conversation[] = [
                    'role' => 'tool',
                    'tool_call_id' => (string) data_get($toolCall, 'id', ''),
                    'name' => $name,
                    'content' => is_string($toolResult)
                        ? $toolResult
                        : json_encode($toolResult, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ];
            }
        }

        $final = $this->chat($conversation, $maxTokens, null, $model);

        return trim((string) ($final['content'] ?? ''));
    }
}
