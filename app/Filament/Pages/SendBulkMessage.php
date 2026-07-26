<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Jobs\SendBulkMailJob;
use App\Jobs\SendSmsJob;
use App\Models\User;
use App\Models\UserNotification;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SendBulkMessage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string|UnitEnum|null $navigationGroup = 'Bildirimler';

    protected static ?string $navigationLabel = 'Toplu Mesaj';

    protected static ?string $title = 'Toplu Mesaj Gönder';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.send-bulk-message';

    /**
     * @var array{
     *     selection_mode: string,
     *     user_ids: list<int>,
     *     role_filter: ?string,
     *     channel: string,
     *     subject: ?string,
     *     message: ?string
     * }
     */
    public array $data = [
        'selection_mode' => 'selected',
        'user_ids' => [],
        'role_filter' => null,
        'channel' => 'notification',
        'subject' => null,
        'message' => null,
    ];

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Alıcılar')
                    ->schema([
                        Select::make('selection_mode')
                            ->label('Seçim tipi')
                            ->options([
                                'selected' => 'Seçili kullanıcılar',
                                'filtered' => 'Role göre filtreli toplu',
                            ])
                            ->required()
                            ->live(),
                        CheckboxList::make('user_ids')
                            ->label('Kullanıcılar')
                            ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2)
                            ->visible(fn (Get $get): bool => $get('selection_mode') === 'selected')
                            ->required(fn (Get $get): bool => $get('selection_mode') === 'selected'),
                        Select::make('role_filter')
                            ->label('Rol filtresi')
                            ->options(UserRole::class)
                            ->visible(fn (Get $get): bool => $get('selection_mode') === 'filtered')
                            ->required(fn (Get $get): bool => $get('selection_mode') === 'filtered'),
                    ]),
                Section::make('Mesaj')
                    ->schema([
                        Select::make('channel')
                            ->label('Bildirim kanalı')
                            ->options([
                                'notification' => 'Site bildirimi',
                                'mail' => 'E-posta',
                                'sms' => 'SMS',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('subject')
                            ->label('Konu / Başlık')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => in_array($get('channel'), ['mail', 'notification'], true))
                            ->required(fn (Get $get): bool => in_array($get('channel'), ['mail', 'notification'], true)),
                        Textarea::make('message')
                            ->label('Mesaj metni')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $state = $this->form->getState();
        $users = $this->resolveRecipients($state);

        if ($users->isEmpty()) {
            Notification::make()
                ->title('Alıcı bulunamadı')
                ->danger()
                ->send();

            return;
        }

        $channel = $state['channel'];
        $message = (string) $state['message'];
        $queued = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if ($channel === 'sms') {
                if (blank($user->phone)) {
                    $skipped++;

                    continue;
                }

                SendSmsJob::dispatch($user->phone, $message);
                $queued++;

                continue;
            }

            if ($channel === 'notification') {
                UserNotification::query()->create([
                    'user_id' => $user->id,
                    'title' => (string) ($state['subject'] ?? 'Bildirim'),
                    'body' => $message,
                ]);
                $queued++;

                continue;
            }

            SendBulkMailJob::dispatch(
                $user,
                (string) ($state['subject'] ?? 'Bildirim'),
                $message,
            );
            $queued++;
        }

        Notification::make()
            ->title($channel === 'notification' ? 'Bildirimler oluşturuldu' : 'Mesajlar kuyruğa alındı')
            ->body("Gönderim: {$queued}".($skipped > 0 ? ", atlanan (telefonsuz): {$skipped}" : ''))
            ->success()
            ->send();
    }

    /**
     * @param  array<string, mixed>  $state
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function resolveRecipients(array $state): \Illuminate\Support\Collection
    {
        if (($state['selection_mode'] ?? null) === 'filtered') {
            return User::query()
                ->when(
                    filled($state['role_filter'] ?? null),
                    fn ($query) => $query->where('role', $state['role_filter']),
                )
                ->get();
        }

        $ids = $state['user_ids'] ?? [];

        if ($ids === []) {
            return collect();
        }

        return User::query()->whereIn('id', $ids)->get();
    }
}
