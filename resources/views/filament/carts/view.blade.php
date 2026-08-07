@php
    use App\Filament\Resources\Customers\CustomerResource;
    use App\Support\OrderPresentation;
    use Illuminate\Support\Str;

    /** @var \App\Models\Cart $record */
    $items = $record->items;
    $subtotal = $record->subtotal();
    $currency = $items->first()?->currency?->value ?? 'TRY';
    $initials = Str::of($record->user?->name ?? '?')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');
    $configuredCount = $items->filter(fn ($item) => $item->isConfigured())->count();
    $pendingCount = $items->count() - $configuredCount;

    $pill = fn (?string $color): string => match ($color) {
        'success' => 'ty-pill ty-pill--success',
        'warning' => 'ty-pill ty-pill--warning',
        'danger' => 'ty-pill ty-pill--danger',
        'info' => 'ty-pill ty-pill--info',
        'primary' => 'ty-pill ty-pill--primary',
        default => 'ty-pill ty-pill--gray',
    };
@endphp

@include('filament.partials.ty-order-styles')

<div class="ty-order">
    <div class="ty-meta">
        <span class="{{ $pill($record->status?->getColor()) }}">{{ $record->status?->getLabel() ?? '—' }}</span>
        <span class="ty-pill ty-pill--gray">{{ $items->count() }} kalem</span>
        @if ($pendingCount > 0)
            <span class="ty-pill ty-pill--warning">{{ $pendingCount }} yapılandırma bekliyor</span>
        @elseif ($items->isNotEmpty())
            <span class="ty-pill ty-pill--success">Hazır</span>
        @endif
        <span>{{ $record->updated_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</span>
    </div>

    <div class="ty-order__layout">
        <div class="ty-order__main">
            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">Sepet kalemleri</h3>
                    <span class="{{ $pill($record->status?->getColor()) }}">{{ $record->status?->getLabel() }}</span>
                </div>
                <div class="ty-card__body">
                    @forelse ($items as $item)
                        @php
                            $title = $item->displayTitle();
                            $itemCurrency = $item->currency?->value ?? $currency;
                            $metaBits = collect([
                                $item->product_type?->getLabel(),
                                $item->content_mode?->getLabel(),
                                $item->durationLabel(),
                                $item->articleWordPackage
                                    ? number_format((int) $item->articleWordPackage->word_count, 0, ',', '.').' kelime'
                                    : null,
                            ])->filter()->values();
                            $payloadRows = OrderPresentation::payloadRowsFromArray(
                                is_array($item->content_payload) ? $item->content_payload : []
                            );
                        @endphp

                        <div style="{{ ! $loop->first ? 'margin-top:16px;padding-top:16px;border-top:1px solid var(--ty-border);' : '' }}">
                            <div class="ty-line">
                                <div class="ty-thumb">
                                    @if ($item->site?->domain)
                                        <img
                                            src="https://www.google.com/s2/favicons?domain={{ urlencode($item->site->domain) }}&sz=64"
                                            alt=""
                                            loading="lazy"
                                        >
                                    @else
                                        {{ Str::upper(Str::substr($title, 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="ty-line__title">{{ $title }}</p>
                                    @if ($metaBits->isNotEmpty())
                                        <p class="ty-line__sub">{{ $metaBits->implode(' · ') }}</p>
                                    @endif
                                    <div style="margin-top:6px;">
                                        <span class="{{ $pill($item->isConfigured() ? 'success' : 'warning') }}">
                                            {{ $item->isConfigured() ? 'Yapılandırıldı' : 'Yapılandırma bekliyor' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <div class="ty-line__price">{{ OrderPresentation::money($item->price, $itemCurrency) }}</div>
                                    <div class="ty-line__qty">× 1</div>
                                    @if ($item->hasForeignSourceCurrency() && $item->source_price !== null && $item->exchange_rate !== null)
                                        <div class="ty-line__sub" style="margin-top:4px;">
                                            {{ OrderPresentation::money($item->source_price, $item->source_currency?->value) }}
                                            × {{ number_format((float) $item->exchange_rate, 4, ',', '.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($payloadRows !== [])
                                <div class="ty-kv">
                                    @foreach ($payloadRows as $row)
                                        <div class="ty-kv__row">
                                            <div class="ty-kv__label">{{ $row['label'] }}</div>
                                            <div class="ty-kv__value">
                                                @if ($row['href'])
                                                    <a class="ty-link" href="{{ $row['href'] }}" target="_blank" rel="noopener">{{ $row['value'] }}</a>
                                                @else
                                                    {{ $row['value'] }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="ty-empty">Sepette kalem yok.</p>
                    @endforelse

                    @if ($items->isNotEmpty())
                        <div class="ty-totals">
                            <div class="ty-totals__row ty-totals__row--muted">
                                <span>Kalemler</span>
                                <span>{{ $items->count() }}</span>
                            </div>
                            <div class="ty-totals__row ty-totals__row--total">
                                <span>Ara toplam</span>
                                <span>{{ OrderPresentation::money($subtotal, $currency) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <aside class="ty-order__side">
            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">Müşteri</h3>
                </div>
                <div class="ty-card__body">
                    @if ($record->user)
                        <div class="ty-customer">
                            <div class="ty-avatar">{{ $initials !== '' ? $initials : '?' }}</div>
                            <div class="ty-stack">
                                <a class="ty-link" href="{{ CustomerResource::getUrl('view', ['record' => $record->user]) }}" style="font-weight:650;">
                                    {{ $record->user->name }}
                                </a>
                                <a href="mailto:{{ $record->user->email }}">{{ $record->user->email }}</a>
                                @if (filled($record->user->phone))
                                    <a href="tel:{{ $record->user->phone }}">{{ $record->user->phone }}</a>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="ty-empty">Misafir sepet</p>
                        @if (filled($record->session_token))
                            <div class="ty-divider"></div>
                            <div class="ty-side-row">
                                <span>Oturum</span>
                                <span title="{{ $record->session_token }}">{{ Str::limit($record->session_token, 16) }}</span>
                            </div>
                        @endif
                    @endif
                </div>
            </section>

            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">Sepet özeti</h3>
                </div>
                <div class="ty-card__body" style="display:grid;gap:10px;">
                    <div class="ty-side-row">
                        <span>Durum</span>
                        <span>{{ $record->status?->getLabel() ?? '—' }}</span>
                    </div>
                    <div class="ty-side-row">
                        <span>Kalem</span>
                        <span>{{ $items->count() }}</span>
                    </div>
                    <div class="ty-side-row">
                        <span>Yapılandırıldı</span>
                        <span>{{ $configuredCount }} / {{ $items->count() }}</span>
                    </div>
                    <div class="ty-side-row">
                        <span>Ara toplam</span>
                        <span>{{ OrderPresentation::money($subtotal, $currency) }}</span>
                    </div>
                    <div class="ty-side-row">
                        <span>Oluşturulma</span>
                        <span>{{ $record->created_at?->format('d M Y, H:i') ?? '—' }}</span>
                    </div>
                    <div class="ty-side-row">
                        <span>Güncelleme</span>
                        <span>{{ $record->updated_at?->format('d M Y, H:i') ?? '—' }}</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
