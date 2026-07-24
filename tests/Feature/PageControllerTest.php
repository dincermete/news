<?php

namespace Tests\Feature;

use App\Models\FaqEntry;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_page_renders_with_seo_meta(): void
    {
        Page::factory()->create([
            'slug' => 'test-cms-sayfasi',
            'title' => 'GEO',
            'meta_title' => 'GEO Meta Başlık',
            'meta_description' => 'GEO meta açıklaması buradadır.',
            'content' => '<p>İçerik eklenecek</p>',
            'is_active' => true,
        ]);

        $this->get(route('pages.show', 'test-cms-sayfasi'))
            ->assertOk()
            ->assertSee('GEO', false)
            ->assertSee('GEO Meta Başlık', false)
            ->assertSee('GEO meta açıklaması buradadır.', false)
            ->assertSee('İçerik eklenecek');
    }

    public function test_inactive_page_returns_404(): void
    {
        Page::factory()->inactive()->create([
            'slug' => 'gizli-sayfa',
            'title' => 'Gizli',
        ]);

        $this->get(route('pages.show', 'gizli-sayfa'))
            ->assertNotFound();
    }

    public function test_legal_pages_render_placeholder_skeleton(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);

        $this->get(route('pages.show', 'mesafeli-satis-sozlesmesi'))
            ->assertOk()
            ->assertSee('Mesafeli Satış Sözleşmesi')
            ->assertSee('Cayma Hakkı')
            ->assertSee('AVUKAT/HUKUK MÜŞAVİRİ TARAFINDAN DOLDURULACAK');

        $this->get(route('pages.show', 'on-bilgilendirme-formu'))
            ->assertOk()
            ->assertSee('Ön Bilgilendirme Formu')
            ->assertSee('AVUKAT/HUKUK MÜŞAVİRİ TARAFINDAN DOLDURULACAK');
    }

    public function test_legacy_sayfa_prefix_redirects_to_canonical_slug(): void
    {
        Page::factory()->create([
            'slug' => 'mesafeli-satis-sozlesmesi',
            'title' => 'Mesafeli Satış Sözleşmesi',
            'is_active' => true,
        ]);

        $this->get('/sayfa/mesafeli-satis-sozlesmesi')
            ->assertRedirect('/mesafeli-satis-sozlesmesi');
    }

    public function test_generic_cms_page_shows_category_faqs(): void
    {
        Page::factory()->create([
            'slug' => 'ornek-hizmet-sayfasi',
            'title' => 'Örnek Hizmet Sayfası',
            'content' => '<p>İçerik eklenecek</p>',
            'is_active' => true,
        ]);

        FaqEntry::factory()->create([
            'category' => 'ornek-hizmet-sayfasi',
            'question_topic' => 'Backlink paketi nedir?',
            'answer' => 'SSS cevabı örnek',
            'is_active' => true,
        ]);

        $this->get(route('pages.show', 'ornek-hizmet-sayfasi'))
            ->assertOk()
            ->assertSee('Sıkça Sorulan Sorular')
            ->assertSee('Backlink paketi nedir?')
            ->assertSee('SSS cevabı örnek');
    }
}
