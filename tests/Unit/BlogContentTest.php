<?php

namespace Tests\Unit;

use App\Support\BlogContent;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogContentTest extends TestCase
{
    #[Test]
    public function it_builds_toc_and_injects_heading_ids(): void
    {
        $html = '<h2>Birinci Bölüm</h2><p>Metin</p><h3>Alt başlık</h3><h2>Birinci Bölüm</h2>';

        $result = BlogContent::withTableOfContents($html);

        $this->assertCount(3, $result['items']);
        $this->assertSame('birinci-bolum', $result['items'][0]['id']);
        $this->assertSame(2, $result['items'][0]['level']);
        $this->assertSame('alt-baslik', $result['items'][1]['id']);
        $this->assertSame(3, $result['items'][1]['level']);
        $this->assertSame('birinci-bolum-2', $result['items'][2]['id']);
        $this->assertStringContainsString('id="birinci-bolum"', $result['html']);
        $this->assertStringContainsString('id="alt-baslik"', $result['html']);
        $this->assertStringContainsString('id="birinci-bolum-2"', $result['html']);
    }

    #[Test]
    public function it_returns_empty_toc_when_no_headings(): void
    {
        $result = BlogContent::withTableOfContents('<p>Sadece paragraf</p>');

        $this->assertSame([], $result['items']);
        $this->assertSame('<p>Sadece paragraf</p>', $result['html']);
    }
}
