<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Enums\CustomerStatus;
use App\Enums\WalletBalanceType;
use App\Models\User;
use App\Services\WalletAdjustmentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CustomerActions
{
    /**
     * @return list<Action>
     */
    public static function recordActions(): array
    {
        return [
            self::adjustBalanceAction(),
            self::toggleStatusAction(),
        ];
    }

    public static function adjustBalanceAction(): Action
    {
        return Action::make('adjustBalance')
            ->label('Bakiye Tanımla/Düzelt')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('warning')
            ->schema([
                Select::make('balance_type')
                    ->label('Kova')
                    ->options(WalletBalanceType::class)
                    ->required()
                    ->default(WalletBalanceType::Main->value),
                TextInput::make('amount')
                    ->label('Tutar')
                    ->helperText('Pozitif: ekle, negatif: düş')
                    ->numeric()
                    ->required(),
                Textarea::make('reason')
                    ->label('Sebep')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (User $record, array $data): void {
                /** @var User $admin */
                $admin = auth()->user();

                app(WalletAdjustmentService::class)->adjust(
                    $record,
                    WalletBalanceType::from($data['balance_type']),
                    (float) $data['amount'],
                    (string) $data['reason'],
                    $admin,
                );

                Notification::make()
                    ->title('Bakiye güncellendi')
                    ->success()
                    ->send();
            });
    }

    public static function toggleStatusAction(): Action
    {
        return Action::make('toggleStatus')
            ->label(fn (User $record): string => $record->status === CustomerStatus::Suspended
                ? 'Aktif Et'
                : 'Askıya Al')
            ->icon(fn (User $record) => $record->status === CustomerStatus::Suspended
                ? Heroicon::OutlinedCheckCircle
                : Heroicon::OutlinedNoSymbol)
            ->color(fn (User $record): string => $record->status === CustomerStatus::Suspended
                ? 'success'
                : 'danger')
            ->requiresConfirmation()
            ->action(function (User $record): void {
                $record->forceFill([
                    'status' => $record->status === CustomerStatus::Suspended
                        ? CustomerStatus::Active
                        : CustomerStatus::Suspended,
                ])->save();

                Notification::make()
                    ->title($record->status === CustomerStatus::Suspended
                        ? 'Müşteri askıya alındı'
                        : 'Müşteri aktifleştirildi')
                    ->success()
                    ->send();
            });
    }
}
