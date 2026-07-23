<?php

namespace App\Filament\Resources\Orders\Actions;

use App\Enums\ContentMode;
use App\Enums\OrderStatus;
use App\Events\WalletRefundRequested;
use App\Jobs\GenerateArticleJob;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class OrderStatusActions
{
    /**
     * @return array<Action>
     */
    public static function make(): array
    {
        return [
            Action::make('generateAiArticle')
                ->label('AI ile Makale Oluştur')
                ->icon(Heroicon::Sparkles)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('AI makale üret')
                ->modalDescription('Bu sipariş için arka planda makale üretimi başlatılacak.')
                ->visible(fn (Order $record): bool => $record->content_mode === ContentMode::AiArticle)
                ->action(function (Order $record): void {
                    GenerateArticleJob::dispatch($record);

                    Notification::make()
                        ->title('Makale üretimi kuyruğa alındı')
                        ->success()
                        ->send();
                }),
            self::makeTransition(
                name: 'approveContent',
                label: 'İçeriği Onayla',
                target: OrderStatus::Review,
                icon: Heroicon::DocumentCheck,
                color: 'primary',
            ),
            self::makeTransition(
                name: 'queueForPublish',
                label: 'Yayına Al',
                target: OrderStatus::InQueue,
                icon: Heroicon::QueueList,
                color: 'info',
            ),
            self::makeTransition(
                name: 'markPublished',
                label: 'Yayınlandı Olarak İşaretle',
                target: OrderStatus::Published,
                icon: Heroicon::CheckBadge,
                color: 'success',
            ),
            self::makeTransition(
                name: 'markReportSent',
                label: 'Rapor Gönderildi',
                target: OrderStatus::ReportSent,
                icon: Heroicon::PaperAirplane,
                color: 'success',
            ),
            Action::make('refund')
                ->label('İade Et')
                ->icon(Heroicon::Banknotes)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Siparişi iade et')
                ->modalDescription('Bu sipariş iade edildi olarak işaretlenecek ve cüzdan iadesi talebi oluşturulacak.')
                ->visible(fn (Order $record): bool => $record->canTransitionTo(OrderStatus::Refunded))
                ->action(function (Order $record): void {
                    $record->transitionTo(OrderStatus::Refunded);

                    WalletRefundRequested::dispatch($record);

                    Notification::make()
                        ->title('İade talebi oluşturuldu')
                        ->body('Sipariş iade edildi olarak işaretlendi.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected static function makeTransition(
        string $name,
        string $label,
        OrderStatus $target,
        Heroicon $icon,
        string $color,
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => $record->canTransitionTo($target))
            ->action(function (Order $record) use ($target, $label): void {
                $record->transitionTo($target);

                Notification::make()
                    ->title($label)
                    ->body('Sipariş durumu: '.$target->getLabel())
                    ->success()
                    ->send();
            });
    }
}
