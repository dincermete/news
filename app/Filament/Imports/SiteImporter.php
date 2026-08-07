<?php

namespace App\Filament\Imports;

use App\Enums\Currency;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\PromotionalListing;
use App\Models\Site;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class SiteImporter extends Importer
{
    protected static ?string $model = Site::class;

    /**
     * @var array{price?: mixed, discount_price?: mixed, currency?: mixed, press_release_price?: mixed}
     */
    protected array $listingPayload = [];

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('domain')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('example.com'),
            ImportColumn::make('category')
                ->label('Kategori')
                ->requiredMapping()
                ->relationship(resolveUsing: 'name')
                ->rules(['required'])
                ->example('Teknoloji'),
            ImportColumn::make('short_description')
                ->label('Kısa açıklama')
                ->example('Kısa ürün özeti'),
            ImportColumn::make('description')
                ->label('Açıklama')
                ->example('Site açıklaması'),
            ImportColumn::make('age')
                ->label('Yaş')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0'])
                ->example('5'),
            ImportColumn::make('is_dofollow')
                ->label('Dofollow')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->example('true'),
            ImportColumn::make('is_news_approved')
                ->label('News onaylı')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->example('false'),
            ImportColumn::make('status')
                ->label('Durum')
                ->rules(['nullable', Rule::enum(SiteStatus::class)])
                ->example('active'),
            ImportColumn::make('price')
                ->label('Tanıtım yazısı fiyatı')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('150.00'),
            ImportColumn::make('discount_price')
                ->label('İndirimli fiyat')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('120.00'),
            ImportColumn::make('press_release_price')
                ->label('Basın bülteni fiyatı')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('200.00'),
            ImportColumn::make('currency')
                ->label('Para birimi')
                ->rules(['nullable', Rule::enum(Currency::class)])
                ->example('TRY'),
            ImportColumn::make('daily_capacity')
                ->label('Günlük kapasite')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),
            ImportColumn::make('weekly_capacity')
                ->label('Haftalık kapasite')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),
            ImportColumn::make('da_value')
                ->label('DA')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('45'),
            ImportColumn::make('pa_value')
                ->label('PA')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('38'),
            ImportColumn::make('spam_score_value')
                ->label('Spam Score')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('moz_rank_value')
                ->label('Moz Rank')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('majestic_cf_value')
                ->label('Majestic CF')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('majestic_tf_value')
                ->label('Majestic TF')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('ahrefs_dr_value')
                ->label('Ahrefs DR')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('ahrefs_keywords_value')
                ->label('Ahrefs Kelime')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('semrush_authority_score_value')
                ->label('Semrush Authority Score')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('monthly_traffic_value')
                ->label('Aylık Trafik')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('backlinks_value')
                ->label('Backlinks')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('max_link_count')
                ->label('Link Çıkışı')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),
        ];
    }

    public function resolveRecord(): Site
    {
        return Site::firstOrNew([
            'domain' => $this->data['domain'],
        ]);
    }

    protected function beforeFill(): void
    {
        $this->listingPayload = [
            'price' => $this->data['price'] ?? null,
            'discount_price' => $this->data['discount_price'] ?? null,
            'press_release_price' => $this->data['press_release_price'] ?? null,
            'currency' => $this->data['currency'] ?? Currency::Try->value,
        ];

        unset(
            $this->data['price'],
            $this->data['discount_price'],
            $this->data['press_release_price'],
            $this->data['currency'],
        );

        $this->data['status'] ??= SiteStatus::Draft->value;
        $this->data['is_dofollow'] ??= true;
        $this->data['is_news_approved'] ??= false;
    }

    protected function afterSave(): void
    {
        /** @var Site $site */
        $site = $this->record;
        $currency = Currency::tryFrom((string) ($this->listingPayload['currency'] ?? '')) ?? Currency::Try;
        $status = $site->status instanceof SiteStatus ? $site->status : SiteStatus::Draft;

        if (filled($this->listingPayload['price'] ?? null)) {
            PromotionalListing::query()->updateOrCreate(
                [
                    'site_id' => $site->id,
                    'type' => PromotionalListingType::SiteArticle,
                ],
                [
                    'price' => (float) $this->listingPayload['price'],
                    'discount_price' => filled($this->listingPayload['discount_price'] ?? null)
                        ? (float) $this->listingPayload['discount_price']
                        : null,
                    'currency' => $currency,
                    'status' => $status,
                    'name' => $site->domain.' — '.PromotionalListingType::SiteArticle->getLabel(),
                    'short_description' => $site->short_description,
                    'description' => $site->description,
                ],
            );

            PromotionalListing::query()->updateOrCreate(
                [
                    'site_id' => $site->id,
                    'type' => PromotionalListingType::FooterLink,
                ],
                [
                    'price' => (float) (
                        filled($this->listingPayload['discount_price'] ?? null)
                        && (float) $this->listingPayload['discount_price'] < (float) $this->listingPayload['price']
                            ? $this->listingPayload['discount_price']
                            : $this->listingPayload['price']
                    ),
                    'discount_price' => null,
                    'currency' => $currency,
                    'status' => $status,
                    'name' => $site->domain.' — '.PromotionalListingType::FooterLink->getLabel(),
                    'short_description' => $site->short_description,
                    'description' => $site->description,
                ],
            );
        }

        if (filled($this->listingPayload['press_release_price'] ?? null)) {
            PromotionalListing::query()->updateOrCreate(
                [
                    'site_id' => $site->id,
                    'type' => PromotionalListingType::PressRelease,
                ],
                [
                    'price' => (float) $this->listingPayload['press_release_price'],
                    'discount_price' => null,
                    'currency' => $currency,
                    'status' => $status,
                    'name' => $site->domain.' — '.PromotionalListingType::PressRelease->getLabel(),
                    'short_description' => $site->short_description,
                    'description' => $site->description,
                ],
            );
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Site içe aktarma tamamlandı. '
            .Number::format($import->successful_rows).' satır başarıyla aktarıldı.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' satır başarısız oldu (hata raporuna bakın).';
        }

        return $body;
    }
}
