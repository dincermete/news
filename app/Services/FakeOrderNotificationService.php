<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Enums\SiteStatus;
use App\Models\Site;

class FakeOrderNotificationService
{
    private const DISPLAY_INTERVAL_SECONDS = 28;

    /**
     * Social-proof product types (wallet top-up excluded).
     *
     * @var list<ProductType>
     */
    private const PRODUCT_TYPES = [
        ProductType::SiteArticle,
        ProductType::PressRelease,
        ProductType::FooterLink,
        ProductType::Bundle,
        ProductType::SeoPackage,
        ProductType::BacklinkPackage,
    ];

    /**
     * @var list<string>
     */
    private const FIRST_NAMES = [
        'Ayşe', 'Mehmet', 'Elif', 'Ahmet', 'Zeynep', 'Mustafa', 'Fatma', 'Emre',
        'Selin', 'Burak', 'Deniz', 'Can', 'Merve', 'Ece', 'Hakan', 'İrem',
        'Yusuf', 'Seda', 'Onur', 'Ceren', 'Kerem', 'Büşra', 'Oğuz', 'Gizem',
        'Berk', 'Melis', 'Tolga', 'Derya', 'Serkan', 'Pınar',
    ];

    /**
     * @var list<string>
     */
    private const CITIES = [
        'İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya', 'Adana', 'Konya',
        'Gaziantep', 'Kayseri', 'Mersin', 'Eskişehir', 'Samsun', 'Trabzon',
        'Diyarbakır', 'Sakarya', 'Kocaeli', 'Balıkesir', 'Manisa', 'Hatay', 'Denizli',
    ];

    /**
     * @var list<string>
     */
    private const MESSAGE_TEMPLATES = [
        '{isim} ({sehir}) az önce {urun} siparişi verdi',
        '{isim}, {sehir} şehrinden {urun} satın aldı',
        '{sehir}\'dan {isim} {urun} siparişini tamamladı',
        '{isim} az önce {urun} ekledi',
        'Yeni sipariş: {isim} · {sehir} · {urun}',
    ];

    /**
     * @return array{message: string, display_interval_seconds: int, name: string, city: string}|null
     */
    public function next(): ?array
    {
        $name = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
        $city = self::CITIES[array_rand(self::CITIES)];
        $productType = self::PRODUCT_TYPES[array_rand(self::PRODUCT_TYPES)];
        $product = $this->productLabel($productType);
        $template = self::MESSAGE_TEMPLATES[array_rand(self::MESSAGE_TEMPLATES)];

        $message = str_replace(
            ['{isim}', '{sehir}', '{urun}'],
            [$name, $city, $product],
            $template,
        );

        return [
            'message' => $message,
            'display_interval_seconds' => self::DISPLAY_INTERVAL_SECONDS,
            'name' => $name,
            'city' => $city,
        ];
    }

    private function productLabel(ProductType $type): string
    {
        if ($type === ProductType::SiteArticle) {
            $domain = Site::query()
                ->where('status', SiteStatus::Active)
                ->inRandomOrder()
                ->value('domain');

            if (filled($domain)) {
                return $domain.' için site yazısı';
            }
        }

        if ($type === ProductType::Bundle) {
            return 'tanıtım paketi';
        }

        return mb_strtolower($type->getLabel());
    }
}
