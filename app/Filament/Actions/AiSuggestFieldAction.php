<?php

namespace App\Filament\Actions;

use App\Services\AiSuggestionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Throwable;

class AiSuggestFieldAction
{
    public static function make(
        string $field,
        string $mode = 'meta_description',
        ?string $contextField = null,
    ): Action {
        return Action::make('aiSuggest_'.$field)
            ->label('AI Öner')
            ->icon(Heroicon::Sparkles)
            ->color('gray')
            ->action(function (Set $set, Get $get) use ($field, $mode, $contextField): void {
                $contextParts = [];

                if ($contextField !== null && filled($get($contextField))) {
                    $contextParts[] = (string) $get($contextField);
                }

                if (filled($get('domain'))) {
                    $contextParts[] = 'Domain: '.$get('domain');
                }

                if (filled($get('name'))) {
                    $contextParts[] = 'Ad: '.$get('name');
                }

                if (filled($get($field))) {
                    $contextParts[] = 'Mevcut metin: '.$get($field);
                }

                $context = trim(implode("\n", $contextParts));

                if ($context === '') {
                    Notification::make()
                        ->title('Bağlam eksik')
                        ->body('AI önerisi için önce alan veya domain/ad bilgisi girin.')
                        ->warning()
                        ->send();

                    return;
                }

                try {
                    $service = app(AiSuggestionService::class);
                    $suggestion = $mode === 'title'
                        ? $service->suggestTitle($context)
                        : $service->suggestMetaDescription($context);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('AI önerisi alınamadı')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                if ($suggestion === '') {
                    Notification::make()
                        ->title('Boş öneri')
                        ->warning()
                        ->send();

                    return;
                }

                $set($field, $suggestion);

                Notification::make()
                    ->title('AI önerisi uygulandı')
                    ->success()
                    ->send();
            });
    }
}
