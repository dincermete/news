<?php

namespace Tests\Feature;

use App\Services\ImportedContentCleaner;
use Tests\TestCase;

class ImportedContentCleanerTest extends TestCase
{
    protected ImportedContentCleaner $cleaner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleaner = new ImportedContentCleaner;
    }

    public function test_plain_text_unescapes_literal_newlines_and_collapses_whitespace(): void
    {
        $raw = '\n \n \n \n aws.amazon.com Tanıtım Yazısı, Markanızın Görünürlüğünü Artırır. \n \n \n';

        $this->assertSame(
            'aws.amazon.com Tanıtım Yazısı, Markanızın Görünürlüğünü Artırır.',
            $this->cleaner->plainText($raw),
        );
    }

    public function test_plain_text_strips_tags_and_respects_limit(): void
    {
        $this->assertSame('Kisa', $this->cleaner->plainText('<p><strong>Kisa metin</strong></p>', 4));
    }

    public function test_rich_text_unwraps_pasted_editor_containers_but_keeps_content(): void
    {
        $raw = '<div class="qMYqUG_convSearchResultHighlightRoot">\n<section class="text-token-text-primary" data-turn-id="request-1">'
            .'<div class="markdown prose"><p data-start="0"><strong>Aws.amazon.com</strong> bulut çözümleri sunar.</p></div>'
            .'</section>\n</div>';

        $clean = $this->cleaner->richText($raw);

        $this->assertStringNotContainsString('data-turn-id', $clean);
        $this->assertStringNotContainsString('text-token-text-primary', $clean);
        $this->assertStringNotContainsString('<div', $clean);
        $this->assertStringNotContainsString('\n', $clean);
        $this->assertStringContainsString('<p><strong>Aws.amazon.com</strong> bulut çözümleri sunar.</p>', $clean);
    }

    public function test_rich_text_removes_rating_widget_markup(): void
    {
        $raw = '<p>Tanıtım yazısı.</p>\n<div class="yasr-auto-insert-visitor"><div id="yasr_visitor_votes_1819" class="yasr-visitor-votes"></div></div>';

        $clean = $this->cleaner->richText($raw);

        $this->assertStringNotContainsString('yasr', $clean);
        $this->assertSame('<p>Tanıtım yazısı.</p>', $clean);
    }

    public function test_rich_text_keeps_links_with_href_only(): void
    {
        $raw = '<p><a href="https://example.com" class="btn" data-x="1" title="Örnek">Örnek</a></p>';

        $clean = $this->cleaner->richText($raw);

        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('title="Örnek"', $clean);
        $this->assertStringNotContainsString('class="btn"', $clean);
        $this->assertStringNotContainsString('data-x', $clean);
    }

    public function test_rich_text_wraps_blank_line_separated_text_in_paragraphs(): void
    {
        $raw = '<strong>Birinci</strong> paragraf.\n\n<strong>İkinci</strong> paragraf.';

        $clean = $this->cleaner->richText($raw);

        $this->assertStringContainsString('<p><strong>Birinci</strong> paragraf.</p>', $clean);
        $this->assertStringContainsString('<p><strong>İkinci</strong> paragraf.</p>', $clean);
    }

    public function test_rich_text_does_not_double_wrap_existing_paragraphs(): void
    {
        $clean = $this->cleaner->richText('<p>Zaten paragraf.</p>');

        $this->assertSame('<p>Zaten paragraf.</p>', $clean);
    }

    public function test_rich_text_returns_empty_string_for_blank_input(): void
    {
        $this->assertSame('', $this->cleaner->richText(null));
        $this->assertSame('', $this->cleaner->richText('   '));
    }
}
