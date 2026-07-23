<?php

use Filament\Schemas\Schema;

/**
 * @var \App\Filament\Pages\BudgetAssistant $this
 */
?>

<x-filament-panels::page>
    <form wire:submit="suggest" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Öneri Getir
        </x-filament::button>
    </form>

    @if (count($this->suggestions))
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mt-6">
            <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-base font-semibold">
                    Önerilen paket — Toplam ₺{{ number_format($this->suggestedTotal, 2) }}
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Domain</th>
                            <th class="px-6 py-3">Kategori</th>
                            <th class="px-6 py-3">Fiyat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->suggestions as $row)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="px-6 py-3">{{ $row['id'] }}</td>
                                <td class="px-6 py-3">{{ $row['domain'] }}</td>
                                <td class="px-6 py-3">{{ $row['category'] ?? '—' }}</td>
                                <td class="px-6 py-3">₺{{ $row['price'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
