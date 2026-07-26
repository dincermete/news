<?php

namespace App\Filament\Resources\PanelUsers;

use App\Enums\UserRole;
use App\Filament\Resources\PanelUsers\Pages\CreatePanelUser;
use App\Filament\Resources\PanelUsers\Pages\EditPanelUser;
use App\Filament\Resources\PanelUsers\Pages\ListPanelUsers;
use App\Filament\Resources\PanelUsers\Schemas\PanelUserForm;
use App\Filament\Resources\PanelUsers\Tables\PanelUsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PanelUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Panel Kullanıcıları';

    protected static ?string $modelLabel = 'Panel kullanıcısı';

    protected static ?string $pluralModelLabel = 'Panel kullanıcıları';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'panel-users';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('role', [UserRole::Admin, UserRole::Editor]);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        $auth = auth()->user();

        if (! $auth instanceof User || ! $auth->isAdmin()) {
            return false;
        }

        return (int) $record->getKey() !== (int) $auth->getKey();
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return PanelUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PanelUsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPanelUsers::route('/'),
            'create' => CreatePanelUser::route('/create'),
            'edit' => EditPanelUser::route('/{record}/edit'),
        ];
    }
}
