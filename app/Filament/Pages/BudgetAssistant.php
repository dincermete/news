<?php

namespace App\Filament\Pages;

use App\Models\SiteCategory;
use App\Services\BudgetPackageSuggester;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BudgetAssistant extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'Bütçe Asistanı';

    protected static ?string $title = 'Bütçe Asistanı';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.budget-assistant';

    /**
     * @var array{budget: ?float, category_id: ?int}
     */
    public array $data = [
        'budget' => null,
        'category_id' => null,
    ];

    /**
     * @var list<array{id: int, domain: string, category: ?string, price: string}>
     */
    public array $suggestions = [];

    public float $suggestedTotal = 0;

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bütçe parametreleri')
                    ->schema([
                        TextInput::make('budget')
                            ->label('Bütçe (₺)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->step(0.01),
                        Select::make('category_id')
                            ->label('Kategori (opsiyonel)')
                            ->options(fn (): array => SiteCategory::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->placeholder('Tüm kategoriler'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function suggest(BudgetPackageSuggester $suggester): void
    {
        $state = $this->form->getState();
        $budget = (float) ($state['budget'] ?? 0);
        $categoryId = filled($state['category_id'] ?? null) ? (int) $state['category_id'] : null;

        $result = $suggester->suggest($budget, $categoryId);

        $this->suggestions = collect($result['sites'])
            ->map(fn ($site): array => [
                'id' => $site->id,
                'domain' => $site->domain,
                'category' => $site->category?->name,
                'price' => number_format((float) $site->price, 2),
            ])
            ->all();

        $this->suggestedTotal = (float) $result['total'];

        Notification::make()
            ->title(count($this->suggestions) > 0 ? 'Öneri hazır' : 'Uygun site bulunamadı')
            ->body(count($this->suggestions) > 0
                ? count($this->suggestions).' site, toplam ₺'.number_format($this->suggestedTotal, 2)
                : 'Bütçeye uygun aktif site yok.')
            ->success()
            ->send();
    }
}
