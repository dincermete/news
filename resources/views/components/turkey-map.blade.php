@props([
    'provinces',
    'lazy' => true,
    'showList' => true,
    'heading' => null,
    'embed' => false,
])

@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Province>|\Illuminate\Database\Eloquent\Collection<int, \App\Models\Province> $provinces */
    $mapProvinces = $provinces->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'slug' => $p->slug,
        'plate_code' => $p->plate_code,
        'sites_count' => $p->sitesCount(),
        'bucket' => $p->bucket(),
        'url' => $p->url(),
    ])->values();

    $legend = [
        ['bucket' => 0, 'label' => '0 (yakında)'],
        ['bucket' => 1, 'label' => '1–2'],
        ['bucket' => 2, 'label' => '3–9'],
        ['bucket' => 3, 'label' => '10–24'],
        ['bucket' => 4, 'label' => '25+'],
    ];
@endphp

<section
    {{ $attributes->class(['turkey-map-block w-full min-w-0']) }}
    x-data="turkeyMap({
        provinces: {{ \Illuminate\Support\Js::from($mapProvinces) }},
        svgUrl: {{ \Illuminate\Support\Js::from(asset('images/turkey-provinces.svg')) }},
        lazy: {{ $lazy ? 'true' : 'false' }},
    })"
>
    @if ($heading)
        <div class="mb-6">
            {!! $heading !!}
        </div>
    @endif

    {{-- Interactive map (embed: her breakpoint; normal: desktop) --}}
    <div @class(['block w-full min-w-0' => $embed, 'hidden w-full min-w-0 md:block' => ! $embed])>
        <div
            @class([
                'turkey-map-host relative w-full min-w-0 overflow-visible',
                'mx-auto max-w-5xl rounded-[24px] border border-ink/10 bg-gradient-to-b from-[#fff8f3] to-paper p-3 sm:p-5' => ! $embed,
                'bg-transparent p-0' => $embed,
            ])
            x-ref="mapHost"
        >
            <div
                x-show="loading && !loaded"
                x-cloak
                @class([
                    'flex items-center justify-center text-sm text-ink-3',
                    'min-h-[220px] lg:min-h-[280px]' => ! $embed,
                    'min-h-[240px] sm:min-h-[300px] lg:min-h-[380px]' => $embed,
                ])
            >
                Harita yükleniyor…
            </div>

            {{-- SVG buraya enjekte edilir; tooltip/loading kardeş kalsın diye ayrı ref --}}
            <div
                x-ref="mapCanvas"
                @class([
                    'turkey-map-canvas w-full min-w-0',
                    'min-h-[180px] lg:min-h-[240px]' => ! $embed,
                    'min-h-[240px] sm:min-h-[300px] lg:min-h-[380px]' => $embed,
                ])
            ></div>

            <div
                x-show="tooltip.show"
                x-cloak
                x-transition.opacity.duration.150ms
                class="pointer-events-none absolute z-20 -translate-x-1/2 -translate-y-[120%] rounded-lg bg-ink px-3 py-1.5 text-xs font-semibold text-white shadow-lg"
                :style="`left:${tooltip.x}px;top:${tooltip.y}px`"
                x-text="tooltip.text"
            ></div>
        </div>

        <ul
            @class([
                'mt-3 flex flex-wrap gap-x-3 gap-y-1.5 text-[11px] text-ink-2 sm:text-xs',
                'justify-center' => ! $embed,
                'justify-start lg:justify-end' => $embed,
            ])
            aria-label="Site sayısı renk skalası"
        >
            @foreach ($legend as $item)
                <li class="inline-flex items-center gap-1.5">
                    <span class="turkey-legend-swatch bucket-{{ $item['bucket'] }}" aria-hidden="true"></span>
                    <span>{{ $item['label'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Mobile: decorative silhouette + searchable list (embed dışında) --}}
    <div @class(['md:hidden' => ! $embed, 'hidden' => $embed])>
        <div class="mx-auto mb-4 max-w-md overflow-hidden rounded-[20px] border border-ink/10 bg-gradient-to-b from-[#fff8f3] to-paper p-4 opacity-90" aria-hidden="true">
            <img
                src="{{ asset('images/turkey-silhouette.svg') }}"
                alt=""
                class="turkey-silhouette-img mx-auto h-auto w-full max-w-sm"
                loading="lazy"
                decoding="async"
            >
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <label class="relative block flex-1">
                <span class="sr-only">İl ara</span>
                <input
                    type="search"
                    x-model="query"
                    placeholder="İl adı veya plaka ara…"
                    class="w-full rounded-full border border-ink/10 bg-white px-4 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-accent-400 focus:outline-hidden focus:ring-2 focus:ring-accent-200"
                >
            </label>
            <div class="inline-flex rounded-full bg-paper p-1 text-xs font-semibold">
                <button type="button" @click="sort = 'name'" :class="sort === 'name' ? 'bg-white text-ink shadow-soft' : 'text-ink-2'" class="rounded-full px-3 py-1.5">A–Z</button>
                <button type="button" @click="sort = 'sites'" :class="sort === 'sites' ? 'bg-white text-ink shadow-soft' : 'text-ink-2'" class="rounded-full px-3 py-1.5">Site sayısı</button>
            </div>
        </div>

        <ul class="mt-4 divide-y divide-ink/5 rounded-[20px] border border-ink/10 bg-white">
            <template x-for="province in filteredProvinces" :key="province.slug">
                <li>
                    <a
                        :href="province.url"
                        @click="navigate(province.slug, province.url); $event.preventDefault()"
                        class="flex items-center justify-between gap-3 px-4 py-3 text-sm transition hover:bg-paper"
                    >
                        <span class="flex items-center gap-3 min-w-0">
                            <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-paper font-display text-xs font-bold text-ink" x-text="province.plate_code"></span>
                            <span class="truncate font-semibold text-ink" x-text="province.name"></span>
                        </span>
                        <span
                            class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                            :class="province.sites_count > 0 ? 'bg-orange-100 text-orange-800' : 'bg-paper-2 text-ink-3'"
                            x-text="province.sites_count > 0 ? (province.sites_count + ' site') : 'yakında'"
                        ></span>
                    </a>
                </li>
            </template>
        </ul>
    </div>

    @if ($showList)
        <div class="mt-10 hidden md:block">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-display text-lg font-bold text-ink">Tüm iller</h3>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label class="relative block sm:w-64">
                        <span class="sr-only">İl ara</span>
                        <input
                            type="search"
                            x-model="query"
                            placeholder="İl adı veya plaka ara…"
                            class="w-full rounded-full border border-ink/10 bg-white px-4 py-2 text-sm text-ink placeholder:text-ink-3 focus:border-accent-400 focus:outline-hidden focus:ring-2 focus:ring-accent-200"
                        >
                    </label>
                    <div class="inline-flex rounded-full bg-paper p-1 text-xs font-semibold">
                        <button type="button" @click="sort = 'name'" :class="sort === 'name' ? 'bg-white text-ink shadow-soft' : 'text-ink-2'" class="rounded-full px-3 py-1.5">A–Z</button>
                        <button type="button" @click="sort = 'sites'" :class="sort === 'sites' ? 'bg-white text-ink shadow-soft' : 'text-ink-2'" class="rounded-full px-3 py-1.5">Site sayısı</button>
                    </div>
                </div>
            </div>

            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <template x-for="province in filteredProvinces" :key="'desk-'+province.slug">
                    <a
                        :href="province.url"
                        @click="navigate(province.slug, province.url); $event.preventDefault()"
                        class="flex items-center justify-between gap-3 rounded-2xl border border-ink/10 bg-white px-4 py-3 text-sm transition hover:-translate-y-0.5 hover:shadow-soft"
                        :class="province.sites_count === 0 ? 'opacity-80' : ''"
                    >
                        <span class="flex items-center gap-3 min-w-0">
                            <span
                                class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl font-display text-xs font-bold"
                                :class="province.sites_count > 0 ? 'bg-orange-50 text-orange-900' : 'bg-paper-2 text-ink-3'"
                                x-text="province.plate_code"
                            ></span>
                            <span class="truncate font-semibold text-ink" x-text="province.name"></span>
                        </span>
                        <span
                            class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                            :class="province.sites_count > 0 ? 'bg-orange-100 text-orange-800' : 'bg-paper-2 text-ink-3'"
                            x-text="province.sites_count > 0 ? (province.sites_count + ' site') : 'yakında'"
                        ></span>
                    </a>
                </template>
            </div>
        </div>
    @endif
</section>
