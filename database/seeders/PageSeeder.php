<?php

namespace Database\Seeders;

use App\Models\FaqEntry;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * CMS + sistem sayfaları (SEO / içerik).
 *
 * LEGAL PAGES WARNING:
 * Mesafeli Satış Sözleşmesi and Ön Bilgilendirme Formu contain PLACEHOLDER
 * section headings only.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSystemPages();
        $this->seedLegalPages();
        $this->seedMarketingPages();
        $this->seedBacklinkFaqs();
    }

    protected function seedSystemPages(): void
    {
        $brand = 'Tanıtım Yazısı';

        $pages = [
            [
                'route_key' => 'home',
                'slug' => 'anasayfa',
                'title' => 'Anasayfa',
                'meta_title' => $brand,
                'meta_description' => 'Kaliteli backlink, yazı ve medya paketleri — '.$brand,
                'meta_keywords' => 'tanıtım yazısı, backlink, basın bülteni',
            ],
            [
                'route_key' => 'sites.index',
                'slug' => 'siteler',
                'title' => 'Siteler',
                'meta_title' => 'Siteler | '.$brand,
                'meta_description' => 'Haber ve blog sitelerinde tanıtım yazısı ve backlink paketleri.',
                'meta_keywords' => 'siteler, tanıtım, backlink',
            ],
            [
                'route_key' => 'press-release.index',
                'slug' => 'basin-bulteni',
                'title' => 'Basın Bülteni',
                'meta_title' => 'Basın Bülteni | '.$brand,
                'meta_description' => 'Basın bülteni dağıtım paketleri.',
            ],
            [
                'route_key' => 'bundles.index',
                'slug' => 'tanitim-paketleri',
                'title' => 'Tanıtım Paketleri',
                'meta_title' => 'Tanıtım Paketleri | '.$brand,
                'meta_description' => 'Hazır tanıtım paketleri ile toplu yayın.',
            ],
            [
                'route_key' => 'footer-links.index',
                'slug' => 'footer-linkler',
                'title' => 'Footer Link',
                'meta_title' => 'Footer Link | '.$brand,
                'meta_description' => 'Footer link paketleri.',
            ],
            [
                'route_key' => 'geo.index',
                'slug' => 'geo',
                'title' => 'GEO',
                'meta_title' => 'GEO (Generative Engine Optimization) Hizmeti | '.$brand,
                'meta_description' => 'Yapay zeka arama motorlarında görünürlük (GEO) hizmetleri.',
                'meta_keywords' => 'geo, generative engine optimization',
            ],
            [
                'route_key' => 'seo-packages.index',
                'slug' => 'seo-paketleri',
                'title' => 'SEO Paketleri',
                'meta_title' => 'SEO Paketleri | '.$brand,
                'meta_description' => 'SEO paketleri ve süre seçenekleri.',
            ],
            [
                'route_key' => 'backlink-packages.index',
                'slug' => 'backlink-paketleri',
                'title' => 'Backlink Paketleri',
                'meta_title' => 'Backlink Paketleri | '.$brand,
                'meta_description' => 'Backlink paketleri hakkında bilgi ve fiyatlar.',
            ],
            [
                'route_key' => 'free-analysis.show',
                'slug' => 'ucretsiz-analiz',
                'title' => 'Ücretsiz Analiz',
                'meta_title' => 'Ücretsiz SEO ve AI Görünürlük Analizi | '.$brand,
                'meta_description' => 'Ücretsiz SEO ve yapay zeka görünürlük analizi talep edin.',
            ],
            [
                'route_key' => 'about.show',
                'slug' => 'hakkimizda',
                'title' => 'Hakkımızda',
                'meta_title' => 'Hakkımızda | '.$brand,
                'meta_description' => $brand.'; site yazısı, basın bülteni, backlink ve SEO/GEO hizmetlerini tek panelde birleştiren dijital tanıtım platformudur.',
                'content' => null,
            ],
            [
                'route_key' => 'contact.show',
                'slug' => 'iletisim',
                'title' => 'İletişim',
                'meta_title' => 'İletişim | '.$brand,
                'meta_description' => 'Telefon, e-posta, WhatsApp ve canlı destek üzerinden '.$brand.' ekibine ulaşın.',
                'content' => null,
            ],
        ];

        foreach ($pages as $page) {
            // Match route_key first, then legacy CMS rows by slug.
            $existing = Page::query()->where('route_key', $page['route_key'])->first()
                ?? Page::query()->where('slug', $page['slug'])->first();

            $payload = [
                'route_key' => $page['route_key'],
                'slug' => $page['slug'],
                'title' => $page['title'],
                'meta_title' => $page['meta_title'],
                'meta_description' => $page['meta_description'] ?? null,
                'meta_keywords' => $page['meta_keywords'] ?? null,
                'is_active' => true,
                'is_system' => true,
            ];

            if (array_key_exists('content', $page)) {
                $payload['content'] = $page['content'];
            }

            if ($existing !== null) {
                $existing->fill($payload)->save();
            } else {
                Page::query()->create($payload);
            }
        }
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
                'is_system' => false,
                'is_legal' => true,
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
                'is_system' => false,
                'is_legal' => true,
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'gizlilik'],
            [
                'title' => 'Gizlilik Politikası',
                'meta_title' => 'Gizlilik Politikası',
                'meta_description' => 'Gizlilik politikası (içerik admin panelden güncellenecek).',
                'content' => '<p>Bu sayfanın içeriği henüz eklenmedi.</p>',
                'is_active' => true,
                'is_system' => false,
                'is_legal' => true,
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'kvkk'],
            [
                'title' => 'KVKK Aydınlatma Metni',
                'meta_title' => 'KVKK Aydınlatma Metni',
                'meta_description' => 'Kişisel verilerin korunması aydınlatma metni.',
                'content' => $this->buildLegalSkeleton(
                    'KVKK Aydınlatma Metni',
                    [
                        'Veri Sorumlusu',
                        'İşlenen Kişisel Veriler',
                        'İşleme Amaçları',
                        'Hukuki Sebepler',
                        'Aktarım',
                        'Saklama Süresi',
                        'İlgili Kişi Hakları',
                        'Başvuru Yöntemi',
                    ],
                    $legalNote,
                ),
                'is_active' => true,
                'is_system' => false,
                'is_legal' => true,
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'cerez-politikasi'],
            [
                'title' => 'Çerez Politikası',
                'meta_title' => 'Çerez Politikası',
                'meta_description' => 'Çerez politikası.',
                'content' => $this->buildLegalSkeleton(
                    'Çerez Politikası',
                    [
                        'Çerez Nedir?',
                        'Kullanılan Çerez Türleri',
                        'Çerezlerin Kullanım Amaçları',
                        'Çerez Yönetimi',
                        'Üçüncü Taraf Çerezler',
                        'Güncellemeler',
                    ],
                    $legalNote,
                ),
                'is_active' => true,
                'is_system' => false,
                'is_legal' => true,
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'uyelik-sozlesmesi'],
            [
                'title' => 'Üyelik Sözleşmesi',
                'meta_title' => 'Üyelik Sözleşmesi',
                'meta_description' => 'Üyelik / kullanım koşulları.',
                'content' => $this->buildLegalSkeleton(
                    'Üyelik Sözleşmesi',
                    [
                        'Taraflar',
                        'Konu',
                        'Üyelik Koşulları',
                        'Hesap Güvenliği',
                        'Kullanıcı Yükümlülükleri',
                        'Hizmetin Kullanımı',
                        'Fikri Mülkiyet',
                        'Fesih',
                        'Uyuşmazlık',
                    ],
                    $legalNote,
                ),
                'is_active' => true,
                'is_system' => false,
                'is_legal' => true,
            ],
        );
    }

    protected function seedMarketingPages(): void
    {
        $marketingNote = '<p>Bu sayfanın içeriği henüz eklenmedi.</p><p><em>İçerik eklenecek — pazarlama/SEO ekibi tarafından admin panelden doldurulacak.</em></p>';

        Page::query()->updateOrCreate(
            ['slug' => 'yapay-zeka-gorunurluk'],
            [
                'title' => 'Yapay Zeka Görünürlük',
                'meta_title' => 'Yapay Zeka Görünürlük',
                'meta_description' => 'Yapay zeka görünürlük hizmetleri hakkında içerik yakında eklenecek.',
                'content' => $marketingNote,
                'is_active' => true,
                'is_system' => false,
            ],
        );
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
