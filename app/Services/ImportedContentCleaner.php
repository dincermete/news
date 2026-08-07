<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

/**
 * Normalises product copy that was migrated from the WooCommerce CSV export.
 *
 * The export stores newlines as the literal two-character sequence "\n" and the
 * original WordPress editor content often carries pasted ChatGPT wrappers plus
 * rating widget markup that has no meaning on the storefront.
 */
class ImportedContentCleaner
{
    /**
     * Inline and block tags kept in rich text; everything else is unwrapped.
     *
     * @var list<string>
     */
    protected const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a', 'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    /**
     * Elements dropped with their children because they only render widgets.
     *
     * @var list<string>
     */
    protected const DROPPED_TAGS = ['script', 'style', 'iframe', 'noscript', 'form', 'button', 'svg'];

    /**
     * Class or id fragments that identify content-free plugin widgets.
     *
     * @var list<string>
     */
    protected const WIDGET_MARKERS = ['yasr', 'wp-block-buttons', 'sharedaddy', 'addtoany'];

    public function plainText(?string $value, int $limit = 500): string
    {
        $text = strip_tags($this->unescapeSequences((string) $value));
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));

        return $limit > 0 ? Str::limit($text, $limit, '') : $text;
    }

    public function richText(?string $value): string
    {
        $html = trim($this->unescapeSequences((string) $value));

        if ($html === '') {
            return '';
        }

        $html = $this->autoParagraph($html);

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"?><body>'.$html.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $document->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMElement) {
            return $this->collapseBlankLines(strip_tags($html));
        }

        $this->sanitizeChildren($body);

        $clean = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $clean .= $document->saveHTML($child);
        }

        return $this->collapseBlankLines($clean);
    }

    /**
     * Turns literal "\n", "\r" and "\t" sequences back into real whitespace.
     */
    protected function unescapeSequences(string $value): string
    {
        return str_replace(['\\r\\n', '\\n', '\\r', '\\t'], ["\n", "\n", "\n", ' '], $value);
    }

    /**
     * Wraps blank-line separated text in paragraphs, mirroring WordPress' wpautop.
     *
     * The legacy editor stored bare text blocks and added the markup at render
     * time, so without this the migrated copy collapses into a single block.
     */
    protected function autoParagraph(string $html): string
    {
        $blocks = 'p|div|section|article|aside|header|footer|ul|ol|li|dl|dt|dd|table|thead|tbody|tfoot|tr|td|th'
            .'|h[1-6]|blockquote|figure|figcaption|pre|iframe|script|style|form|hr';

        $spaced = (string) preg_replace('/(<\/?(?:'.$blocks.')\b[^>]*>)/i', "\n\n$1\n\n", $html);

        $paragraphs = '';
        foreach (preg_split('/\n\s*\n/u', $spaced) ?: [] as $chunk) {
            $chunk = trim($chunk);

            if ($chunk === '') {
                continue;
            }

            $paragraphs .= preg_match('/^<\/?(?:'.$blocks.')\b/i', $chunk) === 1
                ? $chunk."\n"
                : '<p>'.$chunk."</p>\n";
        }

        return $paragraphs;
    }

    protected function sanitizeChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROPPED_TAGS, true) || $this->isWidget($child)) {
                $node->removeChild($child);

                continue;
            }

            $this->sanitizeChildren($child);
            $this->stripAttributes($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->unwrap($child);
            }
        }
    }

    protected function isWidget(DOMElement $element): bool
    {
        $haystack = Str::lower($element->getAttribute('class').' '.$element->getAttribute('id'));

        foreach (self::WIDGET_MARKERS as $marker) {
            if (str_contains($haystack, $marker)) {
                return true;
            }
        }

        return false;
    }

    protected function stripAttributes(DOMElement $element): void
    {
        $keep = strtolower($element->nodeName) === 'a' ? ['href', 'title', 'target', 'rel'] : [];

        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            if (! in_array(strtolower($attribute->nodeName), $keep, true)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }
    }

    protected function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if ($parent === null) {
            return;
        }

        foreach (iterator_to_array($element->childNodes) as $child) {
            $parent->insertBefore($child, $element);
        }

        $parent->removeChild($element);
    }

    protected function collapseBlankLines(string $html): string
    {
        $html = (string) preg_replace('/<p>(\s|&nbsp;|<br\s*\/?>)*<\/p>/iu', '', $html);
        $html = (string) preg_replace('/[ \t]+\n/u', "\n", $html);
        $html = (string) preg_replace('/\n{3,}/u', "\n\n", $html);

        return trim($html);
    }
}
