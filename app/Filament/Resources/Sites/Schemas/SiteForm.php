<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Enums\Currency;
use App\Enums\MetricSource;
use App\Enums\SiteStatus;
use App\Models\User;
use App\Support\SiteSeoMetrics;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('siteTabs')
                    ->tabs([
                        Tab::make('Genel Bilgi')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('domain')
                                        ->label('Domain')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->columnSpan(1),
                                    Select::make('site_category_id')
                                        ->label('Kategori')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan(1),
                                    Textarea::make('description')
                                        ->label('Açıklama')
                                        ->rows(3)
                                        ->hintAction(\App\Filament\Actions\AiSuggestFieldAction::make('description'))
                                        ->columnSpanFull(),
                                    TextInput::make('age')
                                        ->label('Yaş (yıl)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->columnSpan(1),
                                    Select::make('status')
                                        ->label('Durum')
                                        ->options(SiteStatus::class)
                                        ->required()
                                        ->default(SiteStatus::Draft)
                                        ->columnSpan(1),
                                    Toggle::make('is_dofollow')
                                        ->label('Dofollow')
                                        ->default(true)
                                        ->inline(false),
                                    Toggle::make('is_news_approved')
                                        ->label('News onaylı')
                                        ->default(false)
                                        ->inline(false),
                                ]),
                            ]),
                        Tab::make('Fiyat & Kapasite')
                            ->icon(Heroicon::OutlinedCurrencyDollar)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('price')
                                        ->label('Fiyat')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.01),
                                    TextInput::make('discount_price')
                                        ->label('İndirimli fiyat')
                                        ->numeric()
                                        ->minValue(0)
                                        ->step(0.01),
                                    Select::make('currency')
                                        ->label('Para birimi')
                                        ->options(Currency::class)
                                        ->required()
                                        ->default(Currency::Usd),
                                    TextInput::make('daily_capacity')
                                        ->label('Günlük kapasite')
                                        ->numeric()
                                        ->minValue(0),
                                    TextInput::make('weekly_capacity')
                                        ->label('Haftalık kapasite')
                                        ->numeric()
                                        ->minValue(0),
                                ]),
                            ]),
                        Tab::make('SEO Metrikleri')
                            ->icon(Heroicon::OutlinedChartBar)
                            ->schema(self::seoMetricSections()),
                        Tab::make('Etiketler')
                            ->icon(Heroicon::OutlinedBookmark)
                            ->schema([
                                CheckboxList::make('labels')
                                    ->label('Etiketler')
                                    ->relationship('labels', 'name')
                                    ->columns(3)
                                    ->searchable()
                                    ->bulkToggleable(),
                            ]),
                        Tab::make('Dahili Bilgiler')
                            ->icon(Heroicon::OutlinedLockClosed)
                            ->visible(fn (): bool => self::currentUserIsAdmin())
                            ->schema([
                                Textarea::make('internal_notes')
                                    ->label('Dahili notlar')
                                    ->rows(6)
                                    ->helperText('Yalnızca admin kullanıcılar görebilir.')
                                    ->dehydrated(fn (): bool => self::currentUserIsAdmin())
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Site Sahibi Bilgisi')
                            ->icon(Heroicon::OutlinedUser)
                            ->visible(fn (): bool => self::currentUserIsAdmin())
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('site_owner_name')
                                        ->label('Sahip adı')
                                        ->maxLength(255)
                                        ->dehydrated(fn (): bool => self::currentUserIsAdmin()),
                                    TextInput::make('site_owner_contact')
                                        ->label('İletişim')
                                        ->maxLength(255)
                                        ->dehydrated(fn (): bool => self::currentUserIsAdmin()),
                                    Textarea::make('site_owner_payment_info')
                                        ->label('Ödeme bilgisi')
                                        ->rows(4)
                                        ->dehydrated(fn (): bool => self::currentUserIsAdmin())
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    /**
     * @return array<int, Section>
     */
    protected static function seoMetricSections(): array
    {
        $sections = [];

        foreach (SiteSeoMetrics::definitions() as $key => $label) {
            $sections[] = Section::make($label)
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make("{$key}_value")
                            ->label('Değer')
                            ->numeric()
                            ->step(0.01),
                        ToggleButtons::make("{$key}_source")
                            ->label('Kaynak')
                            ->options(MetricSource::class)
                            ->default(MetricSource::Manual)
                            ->inline()
                            ->required(),
                        DateTimePicker::make("{$key}_updated_at")
                            ->label('Güncellendi')
                            ->disabled()
                            ->dehydrated()
                            ->seconds(false),
                        Actions::make([
                            Action::make("fetch_{$key}")
                                ->label("API'den Çek")
                                ->icon(Heroicon::ArrowPath)
                                ->color('gray')
                                ->action(function (Set $set) use ($key): void {
                                    $set("{$key}_updated_at", now());
                                    $set("{$key}_source", MetricSource::Api);
                                }),
                        ])
                            ->verticallyAlignEnd(),
                    ]),
                ])
                ->compact()
                ->columnSpanFull();
        }

        return $sections;
    }

    protected static function currentUserIsAdmin(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }
}
