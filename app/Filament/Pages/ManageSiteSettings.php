<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Site Ayarları';

    protected static ?string $title = 'Site Ayarları';

    protected static ?string $slug = 'site-ayarlari';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-site-settings';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::current();
        $this->form->fill($settings->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Ayarlar')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Marka')
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->schema([
                                Section::make('Kimlik')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label('Site adı')
                                            ->required()
                                            ->maxLength(120)
                                            ->helperText('Fallback: Tanıtım Yazısı'),
                                        TextInput::make('site_domain')
                                            ->label('Alan adı')
                                            ->required()
                                            ->maxLength(120)
                                            ->helperText('Fallback: tanitimyazisi.com.tr'),
                                        TextInput::make('legal_name')
                                            ->label('Yasal / fatura unvanı')
                                            ->maxLength(180),
                                        TextInput::make('tagline')
                                            ->label('Kısa slogan')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Textarea::make('meta_description')
                                            ->label('Varsayılan meta açıklama')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Görseller')
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('logo_path')
                                            ->label('Logo (açık zemin)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('site')
                                            ->imageEditor()
                                            ->maxSize(2048),
                                        FileUpload::make('logo_dark_path')
                                            ->label('Logo (koyu zemin)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('site')
                                            ->imageEditor()
                                            ->maxSize(2048),
                                        FileUpload::make('favicon_path')
                                            ->label('Favicon')
                                            ->image()
                                            ->disk('public')
                                            ->directory('site')
                                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml', 'image/webp'])
                                            ->maxSize(1024),
                                        FileUpload::make('og_image_path')
                                            ->label('Varsayılan sosyal paylaşım görseli (OG)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('site')
                                            ->imageEditor()
                                            ->maxSize(4096)
                                            ->helperText('Önerilen: 1200×630'),
                                    ]),
                            ]),
                        Tab::make('İletişim')
                            ->icon(Heroicon::OutlinedPhone)
                            ->schema([
                                Section::make('İletişim bilgileri')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('support_phone')
                                            ->label('Telefon (tel: link)')
                                            ->tel()
                                            ->maxLength(32)
                                            ->helperText('Örn: 08503052241'),
                                        TextInput::make('support_phone_display')
                                            ->label('Telefon (görünen)')
                                            ->maxLength(64)
                                            ->helperText('Örn: 0850 305 22 41'),
                                        TextInput::make('support_email')
                                            ->label('E-posta')
                                            ->email()
                                            ->maxLength(180),
                                        TextInput::make('whatsapp_number')
                                            ->label('WhatsApp numarası')
                                            ->tel()
                                            ->maxLength(32)
                                            ->helperText('Ülke kodu ile, örn: 905xxxxxxxxx'),
                                        TextInput::make('address')
                                            ->label('Adres')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Sosyal medya')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('social_instagram')
                                            ->label('Instagram URL')
                                            ->url()
                                            ->maxLength(255),
                                        TextInput::make('social_x')
                                            ->label('X (Twitter) URL')
                                            ->url()
                                            ->maxLength(255),
                                        TextInput::make('social_youtube')
                                            ->label('YouTube URL')
                                            ->url()
                                            ->maxLength(255),
                                        TextInput::make('social_linkedin')
                                            ->label('LinkedIn URL')
                                            ->url()
                                            ->maxLength(255),
                                    ]),
                            ]),
                        Tab::make('API & Entegrasyonlar')
                            ->icon(Heroicon::OutlinedKey)
                            ->schema([
                                Section::make('PayTR')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('paytr_merchant_id')
                                            ->label('Merchant ID')
                                            ->maxLength(255),
                                        Toggle::make('paytr_test_mode')
                                            ->label('Test modu')
                                            ->inline(false),
                                        TextInput::make('paytr_merchant_key')
                                            ->label('Merchant Key')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255),
                                        TextInput::make('paytr_merchant_salt')
                                            ->label('Merchant Salt')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255),
                                    ]),
                                Section::make('Netgsm SMS')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('netgsm_username')
                                            ->label('Kullanıcı adı')
                                            ->maxLength(255),
                                        TextInput::make('netgsm_header')
                                            ->label('Başlık (header)')
                                            ->maxLength(20),
                                        TextInput::make('netgsm_password')
                                            ->label('Şifre')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('OpenAI')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('openai_api_key')
                                            ->label('API Key')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        TextInput::make('openai_model')
                                            ->label('Varsayılan model')
                                            ->maxLength(80),
                                        TextInput::make('openai_chatbot_model')
                                            ->label('Chatbot modeli')
                                            ->maxLength(80),
                                        TextInput::make('openai_article_model')
                                            ->label('Makale modeli')
                                            ->maxLength(80),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        if (blank($state['site_name'] ?? null)) {
            $state['site_name'] = SiteSetting::DEFAULT_SITE_NAME;
        }

        if (blank($state['site_domain'] ?? null)) {
            $state['site_domain'] = SiteSetting::DEFAULT_SITE_DOMAIN;
        }

        $settings = SiteSetting::current();
        $settings->fill($state);
        $settings->save();

        Notification::make()
            ->title('Site ayarları kaydedildi')
            ->success()
            ->send();
    }
}
