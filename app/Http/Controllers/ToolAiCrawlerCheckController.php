<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ToolAiCrawlerCheckController extends Controller
{
    /**
     * @var list<array{token: string, label: string}>
     */
    private const BOTS = [
        ['token' => 'GPTBot', 'label' => 'OpenAI · GPTBot'],
        ['token' => 'ChatGPT-User', 'label' => 'OpenAI · ChatGPT-User'],
        ['token' => 'Google-Extended', 'label' => 'Google · Gemini eğitimi'],
        ['token' => 'ClaudeBot', 'label' => 'Anthropic · ClaudeBot'],
        ['token' => 'Claude-Web', 'label' => 'Anthropic · Claude-Web'],
        ['token' => 'PerplexityBot', 'label' => 'Perplexity'],
        ['token' => 'CCBot', 'label' => 'Common Crawl (CCBot)'],
        ['token' => 'Applebot-Extended', 'label' => 'Apple · Applebot-Extended'],
        ['token' => 'Bytespider', 'label' => 'ByteDance · Bytespider'],
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'domain' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Geçerli bir domain girin.'], 422);
        }

        $domain = $this->normalizeDomain((string) $request->input('domain'));

        if ($domain === null) {
            return response()->json(['message' => 'Geçerli bir domain girin, örn. siteniz.com'], 422);
        }

        $robotsTxt = null;

        foreach (["https://{$domain}/robots.txt", "http://{$domain}/robots.txt"] as $url) {
            try {
                $response = Http::timeout(6)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; '.site_setting('site_name', 'AraclarBot').'/1.0; AI erişim denetimi)',
                ])->get($url);
            } catch (\Throwable) {
                continue;
            }

            if ($response->successful()) {
                $robotsTxt = $response->body();
                break;
            }
        }

        if ($robotsTxt === null) {
            return response()->json([
                'domain' => $domain,
                'robots_found' => false,
                'bots' => collect(self::BOTS)->map(fn (array $bot): array => [
                    'label' => $bot['label'],
                    'status' => 'allowed',
                    'reason' => 'robots.txt bulunamadı — varsayılan olarak tüm botlara açık.',
                ])->all(),
            ]);
        }

        $groups = $this->parseGroups($robotsTxt);

        $results = collect(self::BOTS)->map(function (array $bot) use ($groups): array {
            [$status, $reason] = $this->resolveStatus($groups, $bot['token']);

            return [
                'label' => $bot['label'],
                'status' => $status,
                'reason' => $reason,
            ];
        })->all();

        return response()->json([
            'domain' => $domain,
            'robots_found' => true,
            'bots' => $results,
        ]);
    }

    private function normalizeDomain(string $input): ?string
    {
        $input = trim($input);
        $input = preg_replace('#^https?://#i', '', $input) ?? $input;
        $input = explode('/', $input)[0];
        $input = strtolower($input);

        if ($input === '' || ! preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$/', $input)) {
            return null;
        }

        return $input;
    }

    /**
     * Parses robots.txt into groups: [ ['agents' => ['gptbot', '*'], 'disallow' => [...], 'allow' => [...]], ... ]
     *
     * @return list<array{agents: list<string>, disallow: list<string>, allow: list<string>}>
     */
    private function parseGroups(string $body): array
    {
        $groups = [];
        $currentIndex = null;
        $collectingAgents = false;

        foreach (preg_split('/\r\n|\r|\n/', $body) ?: [] as $line) {
            $line = preg_replace('/#.*/', '', $line) ?? $line;
            $line = trim($line);

            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }

            [$field, $value] = array_map('trim', explode(':', $line, 2));
            $field = strtolower($field);

            if ($field === 'user-agent') {
                if (! $collectingAgents || $currentIndex === null) {
                    $groups[] = ['agents' => [], 'disallow' => [], 'allow' => []];
                    $currentIndex = array_key_last($groups);
                }
                $groups[$currentIndex]['agents'][] = strtolower($value);
                $collectingAgents = true;

                continue;
            }

            $collectingAgents = false;

            if ($currentIndex === null) {
                continue;
            }

            if ($field === 'disallow' && $value !== '') {
                $groups[$currentIndex]['disallow'][] = $value;
            } elseif ($field === 'allow') {
                $groups[$currentIndex]['allow'][] = $value;
            }
        }

        return $groups;
    }

    /**
     * @param  list<array{agents: list<string>, disallow: list<string>, allow: list<string>}>  $groups
     * @return array{0: string, 1: string}
     */
    private function resolveStatus(array $groups, string $token): array
    {
        $needle = strtolower($token);

        $specific = collect($groups)->first(fn (array $group): bool => in_array($needle, $group['agents'], true));

        if ($specific !== null) {
            if (in_array('/', $specific['disallow'], true) && $specific['allow'] === []) {
                return ['blocked', 'robots.txt bu botu adıyla tamamen engelliyor.'];
            }

            if ($specific['disallow'] !== []) {
                return ['partial', 'robots.txt bu bot için bazı yolları engelliyor.'];
            }

            return ['allowed', 'robots.txt bu botu adıyla açıkça izinli listede.'];
        }

        $wildcard = collect($groups)->first(fn (array $group): bool => in_array('*', $group['agents'], true));

        if ($wildcard !== null) {
            if (in_array('/', $wildcard['disallow'], true) && $wildcard['allow'] === []) {
                return ['blocked', 'Bot adı geçmiyor ama genel (*) kural tüm siteyi engelliyor.'];
            }

            if ($wildcard['disallow'] !== []) {
                return ['partial', 'Bot adı geçmiyor; genel (*) kural bazı yolları engelliyor.'];
            }
        }

        return ['allowed', 'robots.txt bu botu adıyla belirtmiyor — varsayılan olarak izinli.'];
    }
}
