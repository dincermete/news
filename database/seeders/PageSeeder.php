<?php

namespace Database\Seeders;

use App\Models\FaqEntry;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * CMS page skeletons for legal and marketing URLs.
 *
 * LEGAL PAGES WARNING:
 * Mesafeli Satış Sözleşmesi and Ön Bilgilendirme Formu contain PLACEHOLDER
 * section headings only. Do NOT treat this content as real legal text.
 * Replace every "[BU BÖLÜM AVUKAT/HUKUK MÜŞAVİRİ TARAFINDAN DOLDURULACAK]"
 * block with counsel-approved copy before relying on these pages in production.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLegalPages();
        $this->seedMarketingPages();
        $this->seedBacklinkFaqs();
    }

    protected function seedLegalPages(): void
    {
        $legalNote = '<p><em>[BU BÖLÜM AVUKAT/HUKUK MÜŞAVİRİ TARAFINDAN DOLDURULACAK]</em></p>';

        $mesafeliSections = [
            'Taraflar',
            'Konu',
            'Sözleşme Konusu Ürün / Hizmet',
            'Sözleşmenin Kurulması',
            'Ödeme ve Faturalandırma',
            'Teslimat / İfa',
            'Cayma Hakkı',
            'Sorumluluk',
            'Uyuşmazlık Çözümü',
            'Yürürlük',
        ];

        $onBilgilendirmeSections = [
            'Satıcı Bilgileri',
            'Konu',
            'Ürün / Hizmet Temel Nitelikleri',
            'Fiyat ve Ödeme Bilgileri',
            'Teslimat ve İfa Süresi',
            'Cayma Hakkı Bilgilendirmesi',
            'Şikayet ve Başvuru Yolları',
            'Diğer Hususlar',
        ];

        Page::query()->updateOrCreate(
            ['slug' => 'mesafeli-satis-sozlesmesi'],
            [
                'title' => 'Mesafeli Satış Sözleşmesi',
                'meta_title' => 'Mesafeli Satış Sözleşmesi',
                'meta_description' => 'Mesafeli satış sözleşmesi (hukuki metin henüz yerleştirilmedi — iskelet).',
                'content' => $this->buildLegalSkeleton(
                    'Mesafeli Satış Sözleşmesi',
                    $mesafeliSections,
                    $legalNote,
                ),
                'is_active' => true,
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'on-bilgilendirme-formu'],
            [
                'title' => 'Ön Bilgilendirme Formu',
                'meta_title' => 'Ön Bilgilendirme Formu',
                'meta_description' => 'Ön bilgilendirme formu (hukuki metin henüz yerleştirilmedi — iskelet).',
                'content' => $this->buildLegalSkeleton(
                    'Ön Bilgilendirme Formu',
                    $onBilgilendirmeSections,
                    $legalNote,
                ),
                'is_active' => true,
            ],
        );
    }

    protected function seedMarketingPages(): void
    {
        $marketingNote = '<p>Bu sayfanın içeriği henüz eklenmedi.</p><p><em>İçerik eklenecek — pazarlama/SEO ekibi tarafından admin panelden doldurulacak.</em></p>';

        $pages = [
            [
                'slug' => 'geo',
                'title' => 'GEO',
                'meta_title' => 'GEO — Generative Engine Optimization',
                'meta_description' => 'GEO hizmetleri hakkında içerik yakında eklenecek.',
            ],
            [
                'slug' => 'yapay-zeka-gorunurluk',
                'title' => 'Yapay Zeka Görünürlük',
                'meta_title' => 'Yapay Zeka Görünürlük',
                'meta_description' => 'Yapay zeka görünürlük hizmetleri hakkında içerik yakında eklenecek.',
            ],
            [
                'slug' => 'backlink-paketleri',
                'title' => 'Backlink Paketleri',
                'meta_title' => 'Backlink Paketleri',
                'meta_description' => 'Backlink paketleri hakkında içerik yakında eklenecek.',
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'meta_title' => $page['meta_title'],
                    'meta_description' => $page['meta_description'],
                    'content' => $marketingNote,
                    'is_active' => true,
                ],
            );
        }
    }

    protected function seedBacklinkFaqs(): void
    {
        $faqs = [
            [
                'question_topic' => 'Backlink paketi nedir?',
                'answer' => 'İçerik eklenecek — SSS cevabı admin panelden güncellenecek.',
            ],
            [
                'question_topic' => 'Paketler nasıl seçilir?',
                'answer' => 'İçerik eklenecek — SSS cevabı admin panelden güncellenecek.',
            ],
            [
                'question_topic' => 'Yayın süresi ne kadar?',
                'answer' => 'İçerik eklenecek — SSS cevabı admin panelden güncellenecek.',
            ],
        ];

        foreach ($faqs as $faq) {
            FaqEntry::query()->updateOrCreate(
                [
                    'category' => 'backlink-paketleri',
                    'question_topic' => $faq['question_topic'],
                ],
                [
                    'answer' => $faq['answer'],
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @param  list<string>  $sections
     */
    protected function buildLegalSkeleton(string $heading, array $sections, string $legalNote): string
    {
        $html = '<p><strong>UYARI:</strong> Bu metin hukuki geçerliliği olan bir sözleşme değildir. '
            .'Yayına alınmadan önce avukat/hukuk müşaviri tarafından gerçek metinle değiştirilmelidir.</p>';

        $html .= '<h2>'.$heading.'</h2>';

        foreach ($sections as $section) {
            $html .= '<h3>'.e($section).'</h3>'.$legalNote;
        }

        return $html;
    }
}
