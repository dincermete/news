<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Canlı Ziyaretçiler
            <span class="ms-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                ({{ $this->activeCount }} aktif)
            </span>
        </x-slot>

        <x-slot name="description">
            Son 2 dakikada heartbeat gönderen oturumlar. Reverb üzerinden anlık güncellenir.
        </x-slot>

        <div wire:poll.15s="refreshSessions" class="overflow-x-auto">
            @if (count($this->sessions) === 0)
                <p class="text-sm text-gray-500 dark:text-gray-400">Şu an aktif ziyaretçi yok.</p>
            @else
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-3 py-2 font-medium">#</th>
                            <th class="px-3 py-2 font-medium">URL</th>
                            <th class="px-3 py-2 font-medium">Kullanıcı</th>
                            <th class="px-3 py-2 font-medium">Son görülme</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->sessions as $session)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="px-3 py-2">{{ $session['id'] }}</td>
                                <td class="px-3 py-2 truncate max-w-md" title="{{ $session['current_url'] }}">
                                    {{ $session['current_url'] }}
                                </td>
                                <td class="px-3 py-2">{{ $session['user'] ?? 'Misafir' }}</td>
                                <td class="px-3 py-2">{{ $session['last_seen_at'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
