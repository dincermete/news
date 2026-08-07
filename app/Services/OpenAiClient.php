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
        ?int $timeoutSeconds = null,
    ): array {
        $apiKey = (string) config('openai.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('OpenAI API anahtarı yapılandırılmamış.');
        }

        $resolvedModel = $model ?? (string) config('openai.model');
        $tokenLimit = $maxTokens ?? (int) config('openai.max_tokens.suggestion');

        $payload = [
            'model' => $resolvedModel,
            'messages' => $messages,
            ...$this->tokenLimitPayload($resolvedModel, $tokenLimit),
        ];

        if ($tools !== null && $tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout($timeoutSeconds ?? (int) config('openai.timeout', 60))
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
    public function chatText(array $messages, ?int $maxTokens = null, ?string $model = null, ?int $timeoutSeconds = null): string
    {
        $result = $this->chat($messages, $maxTokens, null, $model, $timeoutSeconds);

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
        ?int $timeoutSeconds = null,
    ): string {
        $maxRounds ??= (int) config('openai.tool_max_rounds', 2);
        $conversation = $messages;

        for ($round = 0; $round < $maxRounds; $round++) {
            $result = $this->chat($conversation, $maxTokens, $tools, $model, $timeoutSeconds);

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

        $final = $this->chat($conversation, $maxTokens, null, $model, $timeoutSeconds);

        return trim((string) ($final['content'] ?? ''));
    }

    /**
     * Newer OpenAI models (gpt-5*, o-series) reject max_tokens and require max_completion_tokens.
     *
     * @return array{max_tokens: int}|array{max_completion_tokens: int}
     */
    private function tokenLimitPayload(string $model, int $tokenLimit): array
    {
        if ($this->usesMaxCompletionTokens($model)) {
            return ['max_completion_tokens' => $tokenLimit];
        }

        return ['max_tokens' => $tokenLimit];
    }

    private function usesMaxCompletionTokens(string $model): bool
    {
        $normalized = strtolower(trim($model));

        return str_starts_with($normalized, 'gpt-5')
            || str_starts_with($normalized, 'o1')
            || str_starts_with($normalized, 'o3')
            || str_starts_with($normalized, 'o4');
    }
}
