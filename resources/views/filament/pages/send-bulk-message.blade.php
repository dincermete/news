<?php

/**
 * @var \App\Filament\Pages\SendBulkMessage $this
 */
?>

<x-filament-panels::page>
    <form wire:submit="send" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Gönder
        </x-filament::button>
    </form>
</x-filament-panels::page>
