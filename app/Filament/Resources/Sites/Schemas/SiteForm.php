<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Enums\SiteStatus;
use App\Filament\Actions\AiSuggestFieldAction;
use App\Models\User;
use App\Support\SiteSeoMetrics;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
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
                                    Textarea::make('short_description')
                                        ->label('Kısa açıklama')
                                        ->rows(2)
                                        ->maxLength(500)
                                        ->helperText('Kaynak site özeti (ürün kartı metinleri Tanıtım Siteleri ürününde yönetilir).')
                                        ->hintAction(AiSuggestFieldAction::make('short_description'))
                                        ->columnSpanFull(),
                                    RichEditor::make('description')
                                        ->label('Açıklama')
                                        ->helperText('Kaynak site açıklaması.')
                                        ->columnSpanFull(),
                                    TextInput::make('age')
                                        ->label('Yaş (yıl)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->columnSpan(1),
                                    Select::make('status')
                                        ->label('Kaynak durumu')
                                        ->options(SiteStatus::class)
                                        ->required()
                                        ->default(SiteStatus::Draft)
                                        ->helperText('Satış durumu Tanıtım Siteleri ürününden yönetilir.')
                                        ->columnSpan(1),
                                    Toggle::make('is_dofollow')
                                        ->label('Dofollow')
                                        ->default(true)
                                        ->inline(false),
                                    Toggle::make('is_news_approved')
                                        ->label('News onaylı')
                                        ->default(false)
                                        ->inline(false),
                                    Toggle::make('is_google_indexed')
                                        ->label('Google Index')
                                        ->default(true)
                                        ->inline(false),
                                    FileUpload::make('logo_path')
                                        ->label('Site logosu')
                                        ->image()
                                        ->disk('public')
                                        ->directory('site-logos')
                                        ->imageEditor()
                                        ->imageEditorAspectRatios(['40:9'])
                                        ->helperText('Yatay marka logosu, 40:9 en-boy oranında (ör. 800×180px). Yüklenmezse site favicon\'u otomatik gösterilir.')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                        Tab::make('Kapasite')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('daily_capacity')
                                        ->label('Günlük kapasite')
                                        ->numeric()
                                        ->minValue(0),
                                    TextInput::make('weekly_capacity')
                                        ->label('Haftalık kapasite')
                                        ->numeric()
                                        ->minValue(0),
                                    TextInput::make('max_link_count')
                                        ->label('Max link çıkışı')
                                        ->numeric()
                                        ->minValue(0)
                                        ->helperText('Bir yazı içinde izin verilen azami link sayısı.'),
                                ]),
                            ]),
                        Tab::make('SEO Metrikleri')
                            ->icon(Heroicon::OutlinedChartBar)
                            ->schema([
                                View::make('filament.sites.seo-metrics-table')
                                    ->viewData([
                                        'metrics' => SiteSeoMetrics::definitions(),
                                    ])
                                    ->schema(self::seoMetricInputs())
                                    ->columnSpanFull(),
                            ]),
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
     * @return array<int, TextInput>
     */
    protected static function seoMetricInputs(): array
    {
        $inputs = [];

        foreach (SiteSeoMetrics::definitions() as $key => $label) {
            $inputs[] = TextInput::make("{$key}_value")
                ->label($label)
                ->numeric()
                ->step(0.01)
                ->placeholder('—')
                ->hiddenLabel();
        }

        return $inputs;
    }

    protected static function currentUserIsAdmin(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }
}
