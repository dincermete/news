<?php

namespace App\Support;

use Illuminate\Support\Str;

class BlogContent
{
    /**
     * Inject heading IDs and build a table of contents from h2/h3 tags.
     *
     * @return array{html: string, items: list<array{id: string, text: string, level: int}>}
     */
    public static function withTableOfContents(?string $html): array
    {
        $html = (string) $html;
        $items = [];
        $usedIds = [];

        if ($html === '' || ! preg_match('/<h[23]\b/i', $html)) {
            return [
                'html' => $html,
                'items' => [],
            ];
        }

        $processed = preg_replace_callback(
            '/<(h([23]))(\b[^>]*)>(.*?)<\/\1>/is',
            function (array $matches) use (&$items, &$usedIds): string {
                $tag = strtolower($matches[1]);
                $level = (int) $matches[2];
                $attributes = $matches[3];
                $inner = $matches[4];
                $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                if ($text === '') {
                    return $matches[0];
                }

                $id = null;
                if (preg_match('/\bid=(["\'])(.*?)\1/i', $attributes, $idMatch) === 1) {
                    $id = trim($idMatch[2]);
                }

                if ($id === null || $id === '') {
                    $id = Str::slug($text) ?: 'bolum';
                }

                $baseId = $id;
                $suffix = 2;
                while (isset($usedIds[$id])) {
                    $id = $baseId.'-'.$suffix;
                    $suffix++;
                }
                $usedIds[$id] = true;

                $items[] = [
                    'id' => $id,
                    'text' => $text,
                    'level' => $level,
                ];

                $attributes = preg_replace('/\s*\bid=(["\']).*?\1/i', '', $attributes) ?? $attributes;

                return '<'.$tag.$attributes.' id="'.e($id).'">'.$inner.'</'.$tag.'>';
            },
            $html,
        );

        return [
            'html' => is_string($processed) ? $processed : $html,
            'items' => $items,
        ];
    }
}
