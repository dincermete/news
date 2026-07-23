<?php

namespace App\Filament\Resources\FakeOrderNotificationTemplates;

use App\Filament\Resources\FakeOrderNotificationTemplates\Pages\CreateFakeOrderNotificationTemplate;
use App\Filament\Resources\FakeOrderNotificationTemplates\Pages\EditFakeOrderNotificationTemplate;
use App\Filament\Resources\FakeOrderNotificationTemplates\Pages\ListFakeOrderNotificationTemplates;
use App\Filament\Resources\FakeOrderNotificationTemplates\Schemas\FakeOrderNotificationTemplateForm;
use App\Filament\Resources\FakeOrderNotificationTemplates\Tables\FakeOrderNotificationTemplatesTable;
use App\Models\FakeOrderNotificationTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FakeOrderNotificationTemplateResource extends Resource
{
    protected static ?string $model = FakeOrderNotificationTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Bildirimler';

    protected static ?string $navigationLabel = 'Sahte Sipariş Şablonları';

    protected static ?string $modelLabel = 'Sahte Sipariş Şablonu';

    protected static ?string $pluralModelLabel = 'Sahte Sipariş Şablonları';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return FakeOrderNotificationTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FakeOrderNotificationTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFakeOrderNotificationTemplates::route('/'),
            'create' => CreateFakeOrderNotificationTemplate::route('/create'),
            'edit' => EditFakeOrderNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
