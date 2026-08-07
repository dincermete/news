<?php

namespace App\Support;

final class Tools
{
    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'seo' => 'SEO Araçları',
            'ai' => 'AI Araçları',
            'icerik' => 'İçerik & Sosyal',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'slug' => 'seo-roi-hesaplama',
                'category' => 'seo',
                'name' => 'SEO ROI Hesaplayıcı',
                'excerpt' => 'Trafik, dönüşüm oranı ve sipariş değerinden SEO yatırımınızın geri dönüşünü hesaplayın.',
                'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22M18.75 12.75V8.25H14.25',
                'partial' => 'tools.partials.seo-roi-hesaplama',
                'related_route' => 'seo-packages.index',
                'related_cta' => 'SEO paketlerini incele',
                'faqs' => [
                    ['q' => 'SEO ROI nasıl hesaplanır?', 'a' => 'Ek organik trafiğin dönüşüm oranıyla çarpılmasından elde edilen tahmini gelir, SEO yatırım maliyetine bölünür ve yüzdeye çevrilir.'],
                    ['q' => 'Bu hesaplama gerçek sonucu garanti eder mi?', 'a' => 'Hayır, girdiğiniz varsayımlara dayanan bir tahmindir; gerçek sonuçlar trafik kalitesi ve rekabete göre değişir.'],
                ],
            ],
            [
                'slug' => 'tiklama-orani-hesaplama',
                'category' => 'seo',
                'name' => 'Tıklama Oranı (CTR) Hesaplayıcı',
                'excerpt' => 'Google sıralamasına göre ortalama tıklama oranını ve beklenen tıklama sayısını görün.',
                'icon' => 'm4.5 19.5 15-15m0 0H8.25m11.25 0v11.25',
                'partial' => 'tools.partials.tiklama-orani-hesaplama',
                'related_route' => 'seo-packages.index',
                'related_cta' => 'SEO paketlerini incele',
                'faqs' => [
                    ['q' => 'CTR verileri nereden geliyor?', 'a' => 'Yayınlanmış organik arama CTR çalışmalarının ortalamasına dayanan genel bir tabloyu baz alıyoruz; sektöre göre sapma gösterebilir.'],
                    ['q' => 'Sıralamamı yükseltmek için ne yapabilirim?', 'a' => 'Tanıtım yazısı, backlink ve SEO paketleriyle otorite ve alaka düzeyinizi güçlendirebilirsiniz.'],
                ],
            ],
            [
                'slug' => 'domain-deger-tahmini',
                'category' => 'seo',
                'name' => 'Domain Değer Tahmini',
                'excerpt' => 'Yaş, DA ve tahmini trafik gibi verileri girerek bir domainin kabaca değerini tahmin edin.',
                'icon' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3s-4.5 4.03-4.5 9 2.015 9 4.5 9Zm-9-9h18',
                'partial' => 'tools.partials.domain-deger-tahmini',
                'related_route' => 'sites.index',
                'related_cta' => 'Site kataloğunu incele',
                'faqs' => [
                    ['q' => 'Bu değer kesin midir?', 'a' => 'Hayır, yaş, otorite ve trafik gibi girdilerden türetilen kaba bir tahmindir; alım satım kararlarında tek başına kullanılmamalıdır.'],
                    ['q' => 'Domain otoritesi (DA) nedir?', 'a' => 'Bir domainin arama motorlarındaki genel güç göstergesi olarak kullanılan, üçüncü parti araçlarla ölçülen bir puandır.'],
                ],
            ],
            [
                'slug' => 'anahtar-kelime-fikir-uretici',
                'category' => 'seo',
                'name' => 'Anahtar Kelime Fikir Üretici',
                'excerpt' => 'Tek bir tohum kelimeden onlarca içerik ve arama niyeti fikri türetin.',
                'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z',
                'partial' => 'tools.partials.anahtar-kelime-fikir-uretici',
                'related_route' => 'blog.index',
                'related_cta' => 'İçerik örneklerini gör',
                'faqs' => [
                    ['q' => 'Bu araç arama hacmi gösteriyor mu?', 'a' => 'Hayır, kalıplardan türetilen fikir listesi üretir; hacim ve zorluk verisi içermez, beyin fırtınası için tasarlanmıştır.'],
                    ['q' => 'Üretilen fikirleri nasıl kullanmalıyım?', 'a' => 'İçerik takviminize veya tanıtım yazısı başlıklarınıza ilham olarak kullanabilirsiniz.'],
                ],
            ],
            [
                'slug' => 'llms-txt-olusturucu',
                'category' => 'ai',
                'name' => 'llms.txt Oluşturucu',
                'excerpt' => 'Sitenizin bölümlerini girin, yapay zekâ asistanları için standart llms.txt dosyanızı indirin.',
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
                'partial' => 'tools.partials.llms-txt-olusturucu',
                'related_route' => 'geo.index',
                'related_cta' => 'GEO hizmetimizi incele',
                'faqs' => [
                    ['q' => 'llms.txt nedir?', 'a' => 'Yapay zekâ modellerine ve asistanlarına sitenizin en önemli bölümlerini basit bir metin dosyasıyla anlatan, gelişmekte olan bir standarttır.'],
                    ['q' => 'Dosyayı nereye yüklemeliyim?', 'a' => 'İndirdiğiniz dosyayı sitenizin kök dizinine, /llms.txt olarak erişilebilecek şekilde yükleyin.'],
                ],
            ],
            [
                'slug' => 'ai-bot-erisim-denetimi',
                'category' => 'ai',
                'name' => 'AI Bot Erişim Denetimi',
                'excerpt' => 'Domaininizin robots.txt dosyasını tarayın, GPTBot, ClaudeBot gibi AI botlarının erişimini görün.',
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'partial' => 'tools.partials.ai-bot-erisim-denetimi',
                'related_route' => 'geo.index',
                'related_cta' => 'GEO hizmetimizi incele',
                'faqs' => [
                    ['q' => 'Hangi botları kontrol ediyorsunuz?', 'a' => 'GPTBot, Google-Extended, ClaudeBot, PerplexityBot, CCBot ve Applebot-Extended gibi bilinen büyük AI/veri botlarını kontrol ediyoruz.'],
                    ['q' => 'Bir bot engelliyse ne yapmalıyım?', 'a' => 'AI aramalarında görünmek istiyorsanız robots.txt kurallarınızı gözden geçirip ilgili botlara izin vermeyi değerlendirin.'],
                ],
            ],
            [
                'slug' => 'paket-onerici',
                'category' => 'ai',
                'name' => 'Paket Önerici',
                'excerpt' => 'Birkaç soruya cevap verin, hedefinize uygun tanıtım yazısı, backlink ya da SEO paketini bulalım.',
                'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.456-2.456L14.25 6l1.035-.259a3.375 3.375 0 0 0 2.456-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z',
                'partial' => 'tools.partials.paket-onerici',
                'related_route' => 'bundles.index',
                'related_cta' => 'Tüm paketleri gör',
                'faqs' => [
                    ['q' => 'Öneri nasıl belirleniyor?', 'a' => 'Bütçeniz, önceliğiniz (hız, otorite, hacim) ve içerik tercihinize göre basit bir kural tablosuyla eşleştirme yapıyoruz.'],
                    ['q' => 'Önerilen paketi değiştirebilir miyim?', 'a' => 'Evet, öneri bir başlangıç noktasıdır; tüm katalog sayfalarından dilediğiniz paketi seçebilirsiniz.'],
                ],
            ],
            [
                'slug' => 'kelime-karakter-sayaci',
                'category' => 'icerik',
                'name' => 'Kelime ve Karakter Sayacı',
                'excerpt' => 'Tanıtım yazısı, meta açıklama veya sosyal medya metninizin kelime ve karakter sayısını anlık görün.',
                'icon' => 'M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m3-6h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5',
                'partial' => 'tools.partials.kelime-karakter-sayaci',
                'related_route' => 'bundles.index',
                'related_cta' => 'Tanıtım paketlerini incele',
                'faqs' => [
                    ['q' => 'Meta açıklama için ideal uzunluk nedir?', 'a' => 'Google genellikle 150-160 karakter civarını kesmeden gösterir; bu aracı yazarken canlı takip için kullanabilirsiniz.'],
                    ['q' => 'Boşluklar karakter sayısına dahil mi?', 'a' => 'Evet, hem boşluklu hem boşluksuz karakter sayısını ayrı ayrı gösteriyoruz.'],
                ],
            ],
            [
                'slug' => 'cekilis-rastgele-secici',
                'category' => 'icerik',
                'name' => 'Çekiliş / Rastgele Seçici',
                'excerpt' => 'Katılımcı listenizi yapıştırın, kamera önünde adil ve şeffaf bir kazanan seçin.',
                'icon' => 'M9 9V4.5M9 9H4.5M9 9 3.75 3.75M15 9V4.5M15 9h4.5M15 9l5.25-5.25M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 15v4.5M15 15h4.5m-4.5 0 5.25 5.25',
                'partial' => 'tools.partials.cekilis-rastgele-secici',
                'related_route' => 'bundles.index',
                'related_cta' => 'Tanıtım paketlerini incele',
                'faqs' => [
                    ['q' => 'Seçim gerçekten rastgele mi?', 'a' => 'Evet, tarayıcınızın kriptografik rastgele sayı üretecini kullanır; kayıt tutmaz, sonucu kimse önceden belirleyemez.'],
                    ['q' => 'Aynı kişi birden fazla çekilebilir mi?', 'a' => 'Kazananı listeden çıkarma seçeneğiyle art arda birden fazla, tekrarsız kazanan seçebilirsiniz.'],
                ],
            ],
            [
                'slug' => 'youtube-gelir-hesaplama',
                'category' => 'icerik',
                'name' => 'YouTube Gelir Tahmini Hesaplayıcı',
                'excerpt' => 'İzlenme sayısı ve tahmini RPM değerinden bir YouTube videosunun reklam gelirini tahmin edin.',
                'icon' => 'M21.75 12a9.75 9.75 0 1 1-19.5 0 9.75 9.75 0 0 1 19.5 0ZM9.75 9v6l5.25-3-5.25-3Z',
                'partial' => 'tools.partials.youtube-gelir-hesaplama',
                'related_route' => 'agency-services.index',
                'related_cta' => 'Prodüksiyon hizmetimizi incele',
                'faqs' => [
                    ['q' => 'RPM nedir?', 'a' => 'Reklam gösterimi başına yayıncıya ödenen, bin izlenme başına ortalama geliri ifade eden bir ölçüttür ve nişe göre büyük farklılık gösterir.'],
                    ['q' => 'Bu rakam gerçek YouTube ödememle aynı mı?', 'a' => 'Hayır, yalnızca girdiğiniz varsayımlara dayanan kaba bir tahmindir; gerçek ödeme YouTube tarafından hesaplanır.'],
                ],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        foreach (self::all() as $tool) {
            if ($tool['slug'] === $slug) {
                return $tool;
            }
        }

        return null;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::all() as $tool) {
            $grouped[$tool['category']][] = $tool;
        }

        return $grouped;
    }
}
