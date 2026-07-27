<?php

namespace App\Support;

final class AgencyServicePages
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'slug' => 'medya-satin-alma',
                'name' => 'Medya Satın Alma',
                'excerpt' => 'TV, radyo, gazete ve dergide doğru zamanda doğru yerleşim için planlama ve satın alma.',
                'subtitle' => 'Markanızın mesajını, hedef kitlenizin gerçekten bulunduğu geleneksel mecralarda; en uygun bütçeyle, en doğru zaman diliminde yayına taşıyoruz.',
                'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22',
                'coverage' => [
                    ['title' => 'Televizyon', 'text' => 'Jenerik, spot ve banner reklam yerleşimleri için kanal ve kuşak planlaması.'],
                    ['title' => 'Radyo', 'text' => 'Spot reklam, sponsorluk ve köşe sponsorluğu formatlarıyla geniş dinleyici erişimi.'],
                    ['title' => 'Gazete', 'text' => 'İlan, mahkeme ilanı, sürmanşet ve gazete eki yerleşimleriyle güvenilir görünürlük.'],
                    ['title' => 'Dergi', 'text' => 'Hedef kitleye özel yayınlarda marka mesajınızı doğru bağlamda konumlandırma.'],
                ],
                'benefits' => [
                    'Yaş, cinsiyet ve satın alma alışkanlığına göre veri temelli mecra seçimi',
                    'Kuşak ve konum bazlı fiyat/erişim optimizasyonu',
                    'Tek noktadan planlama, satın alma ve raporlama',
                    'Deneyimli ajans ekibiyle uçtan uca kampanya koordinasyonu',
                ],
                'faqs' => [
                    ['q' => 'Medya satın alma hizmeti kimler için uygundur?', 'a' => 'TV, radyo, gazete veya dergi gibi geleneksel mecralarda yer almak isteyen, ancak doğru kuşak/yerleşim seçimi konusunda ajans desteğine ihtiyaç duyan tüm markalar için uygundur.'],
                    ['q' => 'Bütçeme uygun bir plan hazırlanır mı?', 'a' => 'Evet. Hedef kitle, kampanya süresi ve bütçenize göre farklı mecra kombinasyonlarını içeren alternatif planlar sunuyoruz.'],
                    ['q' => 'Kampanya sonrası raporlama var mı?', 'a' => 'Yayın kanıtları, erişim tahminleri ve harcama özetini içeren bir raporla süreci şeffaf şekilde takip edebilirsiniz.'],
                ],
            ],
            [
                'slug' => 'dijital-medya-planlama',
                'name' => 'Dijital Medya Planlama',
                'excerpt' => 'Google, sosyal medya ve video reklamlarını tek stratejide birleştiren dijital medya planlaması.',
                'subtitle' => 'Görüntülü reklam, video, arama ve sosyal medyayı tek bir stratejide birleştirip bütçenizi en çok dönüşüm getiren kanala yönlendiriyoruz.',
                'icon' => 'M9.348 14.652a3.75 3.75 0 0 1 0-5.304m5.304 0a3.75 3.75 0 0 1 0 5.304m-7.425 2.121a6.75 6.75 0 0 1 0-9.546m9.546 0a6.75 6.75 0 0 1 0 9.546M5.106 18.894c-3.808-3.807-3.808-9.98 0-13.788m13.788 0c3.808 3.807 3.808 9.98 0 13.788M12 12h.008v.008H12V12Z',
                'coverage' => [
                    ['title' => 'Arama & Görüntülü Reklam', 'text' => 'Google Ads ve Gmail reklamlarıyla arama anında ve gezinme sırasında görünürlük.'],
                    ['title' => 'Sosyal Medya', 'text' => 'Facebook, Instagram, X ve YouTube\'da hedef kitleye özel reklam kurgusu.'],
                    ['title' => 'Video Reklam', 'text' => 'Kısa ve etkili video formatlarıyla marka hatırlanırlığını artıran kampanyalar.'],
                    ['title' => 'İçerik & Advertorial', 'text' => 'Haber sitelerinde SEO uyumlu tanıtım yazısı ve advertorial içerik dağıtımı.'],
                ],
                'benefits' => [
                    'Demografik ve ilgi alanına göre hassas hedefleme',
                    'Kampanya performansını gerçek zamanlı ölçme',
                    'Esnek bütçe dağılımıyla en verimli kanala yönlendirme',
                    'Görsel ve işitsel etkiyle daha yüksek akılda kalıcılık',
                ],
                'faqs' => [
                    ['q' => 'Hangi platformlarda reklam veriyorsunuz?', 'a' => 'Google Ads, Meta (Facebook/Instagram), X, YouTube ve haber sitelerinde advertorial/tanıtım yazısı dağıtımı üzerinden çalışıyoruz.'],
                    ['q' => 'Sonuçları nasıl takip edebilirim?', 'a' => 'Kampanya süresince tıklama, gösterim ve dönüşüm verilerini düzenli raporlarla paylaşıyoruz.'],
                    ['q' => 'Küçük bütçelerle de çalışabilir misiniz?', 'a' => 'Evet, bütçenize göre kanal önceliklendirmesi yaparak en verimli dağılımı kurguluyoruz.'],
                ],
            ],
            [
                'slug' => 'produksiyon-hizmeti',
                'name' => 'Prodüksiyon Hizmeti',
                'excerpt' => 'Reklam filmi, tanıtım videosu ve seslendirmeden renk düzenlemeye uçtan uca prodüksiyon.',
                'subtitle' => 'Senaryodan son kesime; reklam filmi, tanıtım videosu ve ses prodüksiyonunu tek ekiple, tutarlı bir kalite anlayışıyla üretiyoruz.',
                'icon' => 'M15.75 10.5 19.5 8.25v7.5l-3.75-2.25m-9-3.75h6a2.25 2.25 0 0 1 2.25 2.25v3a2.25 2.25 0 0 1-2.25 2.25h-6a2.25 2.25 0 0 1-2.25-2.25v-3a2.25 2.25 0 0 1 2.25-2.25Z',
                'coverage' => [
                    ['title' => 'Video Prodüksiyon', 'text' => 'Reklam filmi, tanıtım videosu ve sosyal medya içerikleri için çekim ve kurgu.'],
                    ['title' => 'Drone & Özel Çekim', 'text' => 'Havadan görüntü ve süper ağır çekim gibi özel tekniklerle etkileyici sahneler.'],
                    ['title' => 'Post-prodüksiyon', 'text' => 'Kurgu, renk düzenleme, altyazı ve dublaj ile yayına hazır son ürün.'],
                    ['title' => 'Ses Prodüksiyonu', 'text' => 'Seslendirme, jingle ve mağaza içi anons sistemleri için stüdyo kalitesinde ses.'],
                ],
                'benefits' => [
                    'Deneyimli yönetmen ve kurgu ekibiyle profesyonel üretim',
                    'Senaryo aşamasından teslime kadar tek noktadan yönetim',
                    'Esnek revizyon süreciyle markanıza tam uyum',
                    'Görüntü, ses ve kurgunun uyumlu çalıştığı yüksek kaliteli çıktı',
                ],
                'faqs' => [
                    ['q' => 'Senaryo yazımı da hizmete dahil mi?', 'a' => 'Evet, marka brief\'inize göre senaryo ve storyboard hazırlığı sürecin bir parçasıdır.'],
                    ['q' => 'Çekim süresi ne kadar sürer?', 'a' => 'Proje kapsamına göre değişir; küçük ölçekli bir tanıtım videosu birkaç günde, reklam filmi projeleri birkaç haftada tamamlanabilir.'],
                    ['q' => 'Revizyon hakkı var mı?', 'a' => 'Kurgu sürecinde markanızın onayına kadar makul sayıda revizyon yapılır.'],
                ],
            ],
            [
                'slug' => 'acik-hava-reklam-hizmeti',
                'name' => 'Açık Hava Reklam Hizmeti',
                'excerpt' => 'Billboard, LED ekran, otobüs giydirme ve totemle şehrin her noktasında marka görünürlüğü.',
                'subtitle' => 'Billboard\'dan otobüs giydirmeye, metro istasyonundan LED ekranlara; markanızı şehrin günlük akışının içine yerleştiriyoruz.',
                'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m3-3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
                'coverage' => [
                    ['title' => 'Billboard & Megaboard', 'text' => 'Ana arterlerde ve şehir girişlerinde büyük format görünürlük.'],
                    ['title' => 'LED Ekran & Totem', 'text' => 'Dijital ekranlarla dinamik, güncellenebilir kampanya içerikleri.'],
                    ['title' => 'Otobüs & Köprü Giydirme', 'text' => 'Toplu taşıma araçları ve köprülerde hareketli, geniş kapsamlı reklam.'],
                    ['title' => 'Metro İstasyonu & Duvar Afişi', 'text' => 'Yüksek yaya trafiğine sahip noktalarda tekrarlayan marka teması.'],
                ],
                'benefits' => [
                    'Kısa sürede geniş kitlelere ulaşan yüksek etkili format',
                    'Yürüyen, araç kullanan ve toplu taşıma kullanan herkese erişim',
                    'Marka bilinirliği ve prestijini güçlendiren büyük ölçekli görünürlük',
                    'Atlanamayan, sürekli tekrar eden temas noktası',
                ],
                'faqs' => [
                    ['q' => 'Hangi şehirlerde açık hava reklamı planlayabiliyorsunuz?', 'a' => 'Türkiye genelinde büyükşehirlerde farklı format ve lokasyon seçenekleri sunuyoruz; hedef bölgenizi belirttiğinizde uygun noktaları listeliyoruz.'],
                    ['q' => 'Minimum kampanya süresi nedir?', 'a' => 'Format ve lokasyona göre değişir; kısa süreli taktiksel kampanyalardan uzun soluklu marka konumlandırmasına kadar farklı süre seçenekleri mevcuttur.'],
                    ['q' => 'Görsel tasarımı siz mi hazırlıyorsunuz?', 'a' => 'İsterseniz hazır görselinizi kullanırız, isterseniz prodüksiyon ekibimiz formata uygun tasarımı sizin için hazırlar.'],
                ],
            ],
            [
                'slug' => 'kapali-hava-reklam-hizmeti',
                'name' => 'Kapalı Hava Reklam Hizmeti',
                'excerpt' => 'AVM, sinema ve havalimanı gibi yoğun kapalı alanlarda kaçınılmaz marka teması.',
                'subtitle' => 'AVM\'ler, sinema salonları ve havalimanları gibi yoğun ve hedef kitlenin uzun süre vakit geçirdiği kapalı alanlarda markanızı öne çıkarıyoruz.',
                'icon' => 'M2.25 21h19.5M4.5 3h15M4.5 3v18M19.5 3v18M9 6.75h1.5m-1.5 3h1.5m3-3H15m-1.5 3H15m-6 8.25v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
                'coverage' => [
                    ['title' => 'AVM İçi Reklam', 'text' => 'Duvar panosu, lightbox, yürüyen merdiven ve asansör gibi yüksek trafikli alanlarda konumlandırma.'],
                    ['title' => 'Sinema Reklamı', 'text' => 'Film öncesi ekran reklamı, fuaye alanında numune tanıtımı ve sponsorluk.'],
                    ['title' => 'Havalimanı Reklamı', 'text' => 'Billboard, megaboard ve LED ekranlarla yolcu yoğunluğunun yüksek olduğu noktalarda görünürlük.'],
                    ['title' => 'Dijital & Özel Uygulamalar', 'text' => 'İnteraktif ekranlar, vitrin giydirme ve cam kaplama gibi özel format seçenekleri.'],
                ],
                'benefits' => [
                    'Ziyaretçilerin uzun süre vakit geçirdiği alanlarda tekrarlayan temas',
                    'Farklı sosyoekonomik gruplara aynı anda ulaşım',
                    'Yüksek trafikli, popüler lokasyonlarda kaçınılmaz görünürlük',
                    'Statik ve dijital formatların bir arada kullanılabilmesi',
                ],
                'faqs' => [
                    ['q' => 'Hangi AVM ve lokasyonlarda yer alınabilir?', 'a' => 'Hedef şehir ve bütçenize göre uygun AVM, sinema zinciri ve havalimanı seçeneklerini birlikte belirliyoruz.'],
                    ['q' => 'Sinema reklamı için içerik hazırlığı gerekir mi?', 'a' => 'Evet, ekran öncesi reklam için kısa bir video içeriği gerekir; prodüksiyon ekibimiz bu içeriği de hazırlayabilir.'],
                    ['q' => 'Kampanya süresi ne kadar esnek?', 'a' => 'Haftalık dilimlerden aylık paketlere kadar farklı süre seçenekleri sunuyoruz.'],
                ],
            ],
            [
                'slug' => 'online-itibar-ve-marka-yonetimi',
                'name' => 'Online İtibar ve Marka Yönetimi',
                'excerpt' => 'Yorumları, şikayetleri ve dijital algıyı yöneterek markanızı güvenilir kılan itibar yönetimi.',
                'subtitle' => 'Markanız hakkında çevrimiçi konuşulanları takip ediyor, olumsuz geri bildirimleri profesyonelce yönetiyor ve güvenilir bir dijital algı inşa ediyoruz.',
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'coverage' => [
                    ['title' => 'Yorum & Şikayet Takibi', 'text' => 'Farklı platformlardaki yorum, değerlendirme ve şikayetlerin düzenli izlenmesi.'],
                    ['title' => 'Profesyonel Yanıt Yönetimi', 'text' => 'Müşteri geri bildirimlerine zamanında ve markaya uygun tonda yanıt verilmesi.'],
                    ['title' => 'Dijital PR Koordinasyonu', 'text' => 'Olumlu içerik ve haberlerin planlı şekilde dijital mecralara taşınması.'],
                    ['title' => 'Profil & Algı Yönetimi', 'text' => 'Marka profillerinin güncel, tutarlı ve güven verici şekilde yönetilmesi.'],
                ],
                'benefits' => [
                    'Olumlu yorumlarla artan müşteri güveni ve yeni müşteri kazanımı',
                    'Şikayetlere hızlı ve profesyonel yanıtla itibar koruma',
                    'Şeffaflık, adillik ve hesap verebilirlik ilkeleriyle yönetilen süreç',
                    'Marka bilinirliğini ve saygınlığını destekleyen bütüncül yaklaşım',
                ],
                'faqs' => [
                    ['q' => 'Hangi platformları takip ediyorsunuz?', 'a' => 'Google yorumları, sosyal medya, şikayet platformları ve markanızla ilgili haber/içerikleri düzenli olarak izliyoruz.'],
                    ['q' => 'Olumsuz bir yorum çıkarsa ne yapıyorsunuz?', 'a' => 'Yorumu markanız adına, çözüm odaklı ve profesyonel bir dille yanıtlıyor; gerekirse süreci sizinle birlikte yönetiyoruz.'],
                    ['q' => 'Sonuçlar nasıl raporlanıyor?', 'a' => 'Yorum hacmi, yanıt süresi ve genel duygu durumu (olumlu/olumsuz) özetini düzenli raporlarla paylaşıyoruz.'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        foreach (self::all() as $service) {
            if ($service['slug'] === $slug) {
                return $service;
            }
        }

        return null;
    }
}
