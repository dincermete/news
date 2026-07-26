<?php

namespace App\Filament\Resources\SupportTickets\Actions;

use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketStatus;
use App\Enums\UserRole;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserNotification;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class SupportTicketActions
{
    /**
     * @return array<Action>
     */
    public static function make(): array
    {
        return [
            self::replyAction(),
            self::assignAction(),
            self::priorityAction(),
            self::markInProgressAction(),
            self::reopenAction(),
            self::closeAction(),
        ];
    }

    public static function replyAction(): Action
    {
        return Action::make('reply')
            ->label('Yanıtla')
            ->icon(Heroicon::ChatBubbleLeftRight)
            ->color('primary')
            ->form([
                Textarea::make('body')
                    ->label('Yanıt')
                    ->required()
                    ->rows(5),
            ])
            ->action(function (SupportTicket $record, array $data): void {
                /** @var User $staff */
                $staff = auth()->user();

                $record->addMessage($staff, (string) $data['body'], isStaff: true);
                $record->markInProgress();

                if ($record->assigned_to === null) {
                    $record->forceFill(['assigned_to' => $staff->id])->save();
                }

                if ($record->user_id) {
                    UserNotification::query()->create([
                        'user_id' => $record->user_id,
                        'title' => 'Destek talebinize yanıt geldi',
                        'body' => '#'.$record->id.' — '.$record->subject,
                    ]);
                }

                Notification::make()
                    ->title('Yanıt gönderildi')
                    ->success()
                    ->send();
            });
    }

    public static function assignAction(): Action
    {
        return Action::make('assign')
            ->label('Ata')
            ->icon(Heroicon::UserPlus)
            ->color('gray')
            ->form([
                Select::make('assigned_to')
                    ->label('Atanan')
                    ->options(
                        fn (): array => User::query()
                            ->whereIn('role', [UserRole::Admin, UserRole::Editor])
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()
                    )
                    ->searchable()
                    ->nullable()
                    ->placeholder('Atanmamış'),
            ])
            ->fillForm(fn (SupportTicket $record): array => [
                'assigned_to' => $record->assigned_to,
            ])
            ->action(function (SupportTicket $record, array $data): void {
                $record->forceFill([
                    'assigned_to' => $data['assigned_to'] ?: null,
                ])->save();

                Notification::make()
                    ->title('Atama güncellendi')
                    ->success()
                    ->send();
            });
    }

    public static function priorityAction(): Action
    {
        return Action::make('setPriority')
            ->label('Öncelik')
            ->icon(Heroicon::Flag)
            ->color('gray')
            ->form([
                Select::make('priority')
                    ->label('Öncelik')
                    ->options(SupportTicketPriority::class)
                    ->required(),
            ])
            ->fillForm(fn (SupportTicket $record): array => [
                'priority' => $record->priority?->value ?? SupportTicketPriority::Normal->value,
            ])
            ->action(function (SupportTicket $record, array $data): void {
                $record->forceFill([
                    'priority' => $data['priority'],
                ])->save();

                Notification::make()
                    ->title('Öncelik güncellendi')
                    ->success()
                    ->send();
            });
    }

    public static function markInProgressAction(): Action
    {
        return Action::make('markInProgress')
            ->label('İşleme Al')
            ->icon(Heroicon::Play)
            ->color('info')
            ->visible(fn (SupportTicket $record): bool => $record->status === SupportTicketStatus::Open)
            ->action(function (SupportTicket $record): void {
                $record->markInProgress();

                if ($record->assigned_to === null && auth()->id()) {
                    $record->forceFill(['assigned_to' => auth()->id()])->save();
                }

                Notification::make()
                    ->title('Ticket işleme alındı')
                    ->success()
                    ->send();
            });
    }

    public static function reopenAction(): Action
    {
        return Action::make('reopen')
            ->label('Yeniden Aç')
            ->icon(Heroicon::ArrowPath)
            ->color('warning')
            ->visible(fn (SupportTicket $record): bool => $record->status === SupportTicketStatus::Closed)
            ->action(function (SupportTicket $record): void {
                $record->reopen();

                Notification::make()
                    ->title('Ticket yeniden açıldı')
                    ->success()
                    ->send();
            });
    }

    public static function closeAction(): Action
    {
        return Action::make('close')
            ->label('Kapat')
            ->icon(Heroicon::CheckCircle)
            ->color('gray')
            ->requiresConfirmation()
            ->visible(fn (SupportTicket $record): bool => $record->status !== SupportTicketStatus::Closed)
            ->action(function (SupportTicket $record): void {
                $record->markClosed();

                Notification::make()
                    ->title('Ticket kapatıldı')
                    ->success()
                    ->send();
            });
    }
}
