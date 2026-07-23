<?php

namespace App\Filament\Resources\UserNotifications;

use App\Filament\Resources\UserNotifications\Pages\ListUserNotifications;
use App\Filament\Resources\UserNotifications\Pages\ViewUserNotification;
use App\Filament\Resources\UserNotifications\Schemas\UserNotificationInfolist;
use App\Filament\Resources\UserNotifications\Tables\UserNotificationsTable;
use App\Models\UserNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserNotificationResource extends Resource
{
    protected static ?string $model = UserNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|UnitEnum|null $navigationGroup = 'Bildirimler';

    protected static ?string $navigationLabel = 'Kullanıcı Bildirimleri';

    protected static ?string $modelLabel = 'Kullanıcı Bildirimi';

    protected static ?string $pluralModelLabel = 'Kullanıcı Bildirimleri';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserNotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserNotificationsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserNotifications::route('/'),
            'view' => ViewUserNotification::route('/{record}'),
        ];
    }
}
