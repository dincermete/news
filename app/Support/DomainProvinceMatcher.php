<?php

namespace App\Support;

use App\Models\Province;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DomainProvinceMatcher
{
    /**
     * Extra ASCII needles per province slug (domain variants).
     *
     * @var array<string, list<string>>
     */
    private const ALIASES = [
        'afyonkarahisar' => ['afyon'],
        'kahramanmaras' => ['maras', 'kahraman'],
        'sanliurfa' => ['urfa'],
        'gaziantep' => ['antep'],
        'kocaeli' => ['izmit'],
    ];

    /**
     * @param  Collection<int, Province>  $provinces
     * @return list<int>
     */
    public function matchIds(string $domain, Collection $provinces): array
    {
        $haystack = $this->normalize($domain);

        if ($haystack === '') {
            return [];
        }

        $matched = [];

        $sorted = $provinces
            ->sortByDesc(fn (Province $province): int => strlen($province->slug))
            ->values();

        foreach ($sorted as $province) {
            foreach ($this->needles($province) as $needle) {
                if ($this->matches($haystack, $needle)) {
                    $matched[] = (int) $province->id;
                    break;
                }
            }
        }

        return array_values(array_unique($matched));
    }

    public function normalize(string $domain): string
    {
        $host = Str::lower(trim($domain));
        $host = preg_replace('#^https?://#', '', $host) ?? $host;
        $host = explode('/', $host)[0];
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        $ascii = Str::ascii($host);

        return preg_replace('/[^a-z0-9.-]/', '', Str::lower($ascii)) ?? '';
    }

    /**
     * @return list<string>
     */
    protected function needles(Province $province): array
    {
        $slug = Str::lower($province->slug);
        $aliases = self::ALIASES[$slug] ?? [];

        return array_values(array_unique([$slug, ...$aliases]));
    }

    protected function matches(string $haystack, string $needle): bool
    {
        if ($needle === '' || ! str_contains($haystack, $needle)) {
            return false;
        }

        // Short needles false-positive easily (mus ⊆ mustafa)
        if (strlen($needle) < 4) {
            $pattern = '/(?:^|[^a-z0-9])'.preg_quote($needle, '/').'(?:haber|gazete|gundem|son|spor|port|life|times|debugun|da|de|ta|te|li|den|dan|[^a-z0-9]|$)/';

            return preg_match($pattern, $haystack) === 1;
        }

        return preg_match('/'.preg_quote($needle, '/').'/', $haystack) === 1;
    }
}
