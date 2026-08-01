@php
    /** @var array<string, string> $metrics */
    $componentsByName = collect($getChildSchema()?->getComponents() ?? [])
        ->filter(fn ($component) => method_exists($component, 'getName'))
        ->keyBy(fn ($component) => $component->getName());
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-white/5">
            <tr class="text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <th class="w-1/2 px-4 py-3">Metrik</th>
                <th class="w-1/2 px-4 py-3">Değer</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
            @foreach ($metrics as $key => $label)
                @php $field = $componentsByName->get("{$key}_value"); @endphp
                <tr class="bg-white dark:bg-transparent">
                    <td class="px-4 py-2.5 font-medium text-gray-950 dark:text-white">{{ $label }}</td>
                    <td class="px-4 py-2.5">
                        @if ($field)
                            {!! $field->toHtml() !!}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
