<?php

namespace App\Filament\Imports;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Models\Site;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class SiteImporter extends Importer
{
    protected static ?string $model = Site::class;

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
                ->label('Fiyat')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0'])
                ->example('150.00'),
            ImportColumn::make('discount_price')
                ->label('İndirimli fiyat')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('120.00'),
            ImportColumn::make('currency')
                ->label('Para birimi')
                ->rules(['nullable', Rule::enum(Currency::class)])
                ->example('USD'),
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
            ImportColumn::make('semrush_authority_score_value')
                ->label('Semrush Authority Score')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('organic_traffic_value')
                ->label('Organic Traffic')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('backlinks_value')
                ->label('Backlinks')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
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
        $this->data['status'] ??= SiteStatus::Draft->value;
        $this->data['currency'] ??= Currency::Usd->value;
        $this->data['is_dofollow'] ??= true;
        $this->data['is_news_approved'] ??= false;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Site içe aktarma tamamlandı. '
            . Number::format($import->successful_rows) . ' satır başarıyla aktarıldı.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' satır başarısız oldu (hata raporuna bakın).';
        }

        return $body;
    }
}
