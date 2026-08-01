<?php

namespace App\Filament\Schemas;

use App\Services\ProductPublicUrl;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class StorefrontSeoForm
{
    /**
     * @return list<Component>
     */
    public static function section(): array
    {
        return [
            Section::make('SEO')
                ->schema([
                    TextInput::make('public_path')
                        ->label('Kanonik URL (1. seviye)')
                        ->prefix('/')
                        ->maxLength(255)
                        ->helperText('Boşsa teknik URL kullanılır. Doluysa kök path kanonik olur: /ornek-path')
                        ->regex('/'.ProductPublicUrl::PATH_PATTERN.'/u')
                        ->dehydrateStateUsing(fn (?string $state): ?string => app(ProductPublicUrl::class)->normalize($state))
                        ->rule(function (?Model $record) {
                            return function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                                try {
                                    app(ProductPublicUrl::class)->assertPathAvailable(
                                        is_string($value) ? $value : null,
                                        $record,
                                    );
                                } catch (ValidationException $exception) {
                                    $fail(collect($exception->errors())->flatten()->first() ?? 'Geçersiz URL.');
                                }
                            };
                        }),
                    TextInput::make('meta_title')
                        ->label('Meta başlık')
                        ->maxLength(255),
                    TextInput::make('meta_keywords')
                        ->label('Meta keywords')
                        ->maxLength(255)
                        ->helperText('Virgülle ayırın'),
                    Textarea::make('meta_description')
                        ->label('Meta açıklama')
                        ->rows(3)
                        ->maxLength(512)
                        ->columnSpanFull(),
                    FileUpload::make('og_image')
                        ->label('Sosyal paylaşım görseli (OG)')
                        ->image()
                        ->disk('public')
                        ->directory('products/og')
                        ->imageEditor()
                        ->maxSize(4096)
                        ->helperText('Önerilen: 1200×630. Boşsa site varsayılan OG / favicon kullanılır.')
                        ->visibility('public')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
