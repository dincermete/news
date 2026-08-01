@php
    use App\Filament\Resources\Customers\CustomerResource;
    use App\Filament\Resources\Invoices\InvoiceResource;
    use App\Filament\Resources\OrderGroups\OrderGroupResource;
    use App\Filament\Resources\Orders\OrderResource;
    use App\Support\OrderPresentation;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    /** @var \App\Models\Order $record */
    $title = OrderPresentation::displayTitle($record);
    $payloadRows = OrderPresentation::payloadRows($record);
    $payments = OrderPresentation::relevantPayments($record);
    $timeline = OrderPresentation::timeline($record);
    $group = $record->orderGroup;
    $billing = $record->invoice?->billingProfile ?? $group?->billingProfile;
    $invoice = $record->invoice ?? $group?->invoices?->first();
    $siblings = $group
        ? $group->orders->where('id', '!=', $record->id)->values()
        : collect();
    $currency = $record->currency?->value ?? 'TRY';
    $paidPayment = $payments->first(fn ($p) => $p->paid_at !== null) ?? $payments->first();
    $metaBits = collect([
        $record->product_type?->getLabel(),
        $record->content_source?->getLabel(),
        $record->articleWordPackage
            ? number_format((int) $record->articleWordPackage->word_count, 0, ',', '.').' kelime'
            : null,
        $record->footerLinkDurationOption?->name,
        $record->seoPackageDurationOption?->name,
    ])->filter()->values();
    $initials = Str::of($record->user?->name ?? '?')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');
@endphp

<style>
    .ty-order {
        --ty-border: color-mix(in oklab, var(--gray-950, #111) 12%, transparent);
        --ty-muted: color-mix(in oklab, var(--gray-950, #111) 55%, transparent);
        --ty-soft: color-mix(in oklab, var(--gray-950, #111) 4%, transparent);
        --ty-card: var(--color-white, #fff);
        --ty-radius: 12px;
        --ty-gap: 16px;
        color: var(--gray-950, #111827);
    }

    .dark .ty-order,
    html.dark .ty-order {
        --ty-border: color-mix(in oklab, white 12%, transparent);
        --ty-muted: color-mix(in oklab, white 55%, transparent);
        --ty-soft: color-mix(in oklab, white 6%, transparent);
        --ty-card: color-mix(in oklab, white 4%, transparent);
        color: #f3f4f6;
    }

    .ty-order * { box-sizing: border-box; }

    .ty-order__layout {
        display: flex;
        flex-direction: column;
        gap: var(--ty-gap);
        align-items: stretch;
    }

    @media (min-width: 960px) {
        .ty-order__layout {
            flex-direction: row;
            align-items: flex-start;
        }

        .ty-order__main {
            flex: 1 1 0;
            min-width: 0;
        }

        .ty-order__side {
            flex: 0 0 320px;
            width: 320px;
            position: sticky;
            top: 1rem;
        }
    }

    .ty-order__main,
    .ty-order__side {
        display: flex;
        flex-direction: column;
        gap: var(--ty-gap);
    }

    .ty-card {
        background: var(--ty-card);
        border: 1px solid var(--ty-border);
        border-radius: var(--ty-radius);
        overflow: hidden;
    }

    .ty-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--ty-border);
    }

    .ty-card__title {
        margin: 0;
        font-size: 13px;
        font-weight: 650;
        letter-spacing: -0.01em;
    }

    .ty-card__body { padding: 14px 16px; }

    .ty-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 24px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .ty-pill--warning { background: #fef3c7; color: #92400e; }
    .ty-pill--success { background: #d1fae5; color: #065f46; }
    .ty-pill--danger { background: #fee2e2; color: #991b1b; }
    .ty-pill--info { background: #dbeafe; color: #1e40af; }
    .ty-pill--primary { background: #ede9fe; color: #5b21b6; }
    .ty-pill--gray { background: var(--ty-soft); color: var(--ty-muted); border-color: var(--ty-border); }

    .dark .ty-pill--warning { background: rgba(245, 158, 11, .18); color: #fcd34d; }
    .dark .ty-pill--success { background: rgba(16, 185, 129, .18); color: #6ee7b7; }
    .dark .ty-pill--danger { background: rgba(239, 68, 68, .18); color: #fca5a5; }
    .dark .ty-pill--info { background: rgba(59, 130, 246, .18); color: #93c5fd; }
    .dark .ty-pill--primary { background: rgba(139, 92, 246, .18); color: #c4b5fd; }

    .ty-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
        color: var(--ty-muted);
        font-size: 13px;
    }

    .ty-line {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: start;
    }

    .ty-thumb {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        border: 1px solid var(--ty-border);
        background: var(--ty-soft);
        display: grid;
        place-items: center;
        overflow: hidden;
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 700;
        color: var(--ty-muted);
    }

    .ty-thumb img { width: 100%; height: 100%; object-fit: cover; }

    .ty-line__title {
        margin: 0;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.35;
        word-break: break-word;
    }

    .ty-line__sub {
        margin: 4px 0 0;
        font-size: 12px;
        color: var(--ty-muted);
        line-height: 1.4;
    }

    .ty-line__price {
        font-size: 14px;
        font-weight: 650;
        white-space: nowrap;
        text-align: right;
    }

    .ty-line__qty {
        margin-top: 2px;
        font-size: 12px;
        color: var(--ty-muted);
        text-align: right;
    }

    .ty-kv {
        display: grid;
        gap: 10px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--ty-border);
    }

    .ty-kv__row {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
        gap: 12px;
        font-size: 13px;
    }

    @media (max-width: 640px) {
        .ty-kv__row { grid-template-columns: 1fr; gap: 2px; }
    }

    .ty-kv__label { color: var(--ty-muted); }
    .ty-kv__value { word-break: break-word; }

    .ty-totals {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid var(--ty-border);
        display: grid;
        gap: 8px;
        max-width: 280px;
        margin-left: auto;
        width: 100%;
    }

    .ty-totals__row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        font-size: 13px;
    }

    .ty-totals__row--muted { color: var(--ty-muted); }
    .ty-totals__row--total {
        padding-top: 8px;
        border-top: 1px solid var(--ty-border);
        font-weight: 700;
        font-size: 14px;
    }

    .ty-totals__discount { color: #059669; }
    .dark .ty-totals__discount { color: #34d399; }

    .ty-empty {
        margin: 0;
        font-size: 13px;
        color: var(--ty-muted);
    }

    .ty-link {
        color: #2563eb;
        text-decoration: none;
    }

    .ty-link:hover { text-decoration: underline; }
    .dark .ty-link { color: #93c5fd; }

    .ty-customer {
        display: grid;
        grid-template-columns: 40px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
    }

    .ty-avatar {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        background: linear-gradient(135deg, #111827, #4b5563);
        color: #fff;
        display: grid;
        place-items: center;
        font-size: 12px;
        font-weight: 700;
    }

    .ty-stack { display: grid; gap: 6px; font-size: 13px; }
    .ty-stack a { color: inherit; text-decoration: none; }
    .ty-stack a:hover { text-decoration: underline; }
    .ty-stack__muted { color: var(--ty-muted); font-size: 12px; }

    .ty-divider {
        height: 1px;
        background: var(--ty-border);
        margin: 12px 0;
    }

    .ty-side-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
    }

    .ty-side-row span:first-child { color: var(--ty-muted); }
    .ty-side-row span:last-child { text-align: right; word-break: break-word; }

    .ty-timeline { list-style: none; margin: 0; padding: 0; }
    .ty-timeline li {
        position: relative;
        padding: 0 0 14px 18px;
        border-left: 2px solid var(--ty-border);
    }
    .ty-timeline li:last-child { padding-bottom: 0; border-left-color: transparent; }
    .ty-timeline li::before {
        content: "";
        position: absolute;
        left: -5px;
        top: 4px;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #9ca3af;
        box-shadow: 0 0 0 3px var(--ty-card);
    }
    .ty-timeline li[data-color="success"]::before { background: #10b981; }
    .ty-timeline li[data-color="warning"]::before { background: #f59e0b; }
    .ty-timeline li[data-color="danger"]::before { background: #ef4444; }
    .ty-timeline li[data-color="primary"]::before { background: #8b5cf6; }
    .ty-timeline li[data-color="info"]::before { background: #3b82f6; }

    .ty-timeline__label { font-size: 13px; font-weight: 600; margin: 0; }
    .ty-timeline__desc { margin: 2px 0 0; font-size: 12px; color: var(--ty-muted); word-break: break-word; }
    .ty-timeline__at { margin: 2px 0 0; font-size: 11px; color: var(--ty-muted); }

    .ty-sibling {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 8px 0;
        border-top: 1px solid var(--ty-border);
        font-size: 12px;
    }
    .ty-sibling:first-child { border-top: 0; padding-top: 0; }
</style>

@php
    $pill = fn (?string $color): string => match ($color) {
        'success' => 'ty-pill ty-pill--success',
        'warning' => 'ty-pill ty-pill--warning',
        'danger' => 'ty-pill ty-pill--danger',
        'info' => 'ty-pill ty-pill--info',
        'primary' => 'ty-pill ty-pill--primary',
        default => 'ty-pill ty-pill--gray',
    };
@endphp

<div class="ty-order">
    <div class="ty-meta">
        <span class="{{ $pill($record->status?->getColor()) }}">{{ $record->status?->getLabel() ?? '—' }}</span>
        @if ($record->product_type)
            <span class="ty-pill ty-pill--gray">{{ $record->product_type->getLabel() }}</span>
        @endif
        @if ($paidPayment?->status)
            <span class="{{ $pill($paidPayment->status->getColor()) }}">
                {{ $paidPayment->method?->getLabel() ? $paidPayment->method->getLabel().' · ' : '' }}{{ $paidPayment->status->getLabel() }}
            </span>
        @endif
        <span>{{ $record->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</span>
        @if ($record->due_date)
            <span>Teslim {{ $record->due_date->format('d M Y') }}</span>
        @endif
    </div>

    <div class="ty-order__layout">
        <div class="ty-order__main">
            {{-- Fulfillment / product card --}}
            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">
                        @if ($record->publishedLinks->isNotEmpty())
                            Yayınlandı
                        @elseif (in_array($record->status?->value, ['in_queue', 'review', 'content_pending'], true))
                            Hazırlanıyor
                        @else
                            Sipariş kalemi
                        @endif
                    </h3>
                    <span class="{{ $pill($record->status?->getColor()) }}">{{ $record->status?->getLabel() }}</span>
                </div>
                <div class="ty-card__body">
                    <div class="ty-line">
                        <div class="ty-thumb">
                            @if ($record->site?->domain)
                                <img
                                    src="https://www.google.com/s2/favicons?domain={{ urlencode($record->site->domain) }}&sz=64"
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
                            @if ($record->content_mode)
                                <p class="ty-line__sub">Mod: {{ $record->content_mode->getLabel() }}</p>
                            @endif
                        </div>
                        <div>
                            <div class="ty-line__price">{{ OrderPresentation::money($record->price, $currency) }}</div>
                            <div class="ty-line__qty">× 1</div>
                            @if ($record->hasForeignSourceCurrency() && $record->source_price !== null && $record->exchange_rate !== null)
                                <div class="ty-line__sub" style="margin-top:4px;">
                                    {{ OrderPresentation::money($record->source_price, $record->source_currency?->value) }}
                                    × {{ number_format((float) $record->exchange_rate, 4, ',', '.') }}
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

                    <div class="ty-totals">
                        <div class="ty-totals__row ty-totals__row--muted">
                            <span>Kalem</span>
                            <span>{{ OrderPresentation::money($record->price, $currency) }}</span>
                        </div>
                        @if ($group)
                            @if ((float) $group->discount_tier_amount > 0)
                                <div class="ty-totals__row ty-totals__row--muted">
                                    <span>Kademe indirimi</span>
                                    <span class="ty-totals__discount">−{{ OrderPresentation::money($group->discount_tier_amount, $group->currency?->value) }}</span>
                                </div>
                            @endif
                            @if ((float) $group->coupon_discount_amount > 0)
                                <div class="ty-totals__row ty-totals__row--muted">
                                    <span>Kupon</span>
                                    <span class="ty-totals__discount">−{{ OrderPresentation::money($group->coupon_discount_amount, $group->currency?->value) }}</span>
                                </div>
                            @endif
                            <div class="ty-totals__row ty-totals__row--total">
                                <span>Toplam</span>
                                <span>{{ OrderPresentation::money($group->total, $group->currency?->value) }}</span>
                            </div>
                        @else
                            <div class="ty-totals__row ty-totals__row--total">
                                <span>Toplam</span>
                                <span>{{ OrderPresentation::money($record->price, $currency) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Payment card --}}
            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">Ödeme</h3>
                    @if ($paidPayment?->status)
                        <span class="{{ $pill($paidPayment->status->getColor()) }}">{{ $paidPayment->status->getLabel() }}</span>
                    @endif
                </div>
                <div class="ty-card__body">
                    @if ($payments->isEmpty())
                        <p class="ty-empty">Ödeme kaydı yok.</p>
                    @else
                        @foreach ($payments as $payment)
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;{{ ! $loop->first ? 'margin-top:12px;padding-top:12px;border-top:1px solid var(--ty-border);' : '' }}">
                                <div>
                                    <div style="font-size:13px;font-weight:650;">
                                        {{ $payment->method?->getLabel() ?? 'Ödeme' }}
                                        · {{ OrderPresentation::money($payment->amount, $payment->currency?->value) }}
                                    </div>
                                    <div class="ty-stack__muted" style="margin-top:4px;">
                                        {{ $payment->paid_at?->format('d M Y, H:i') ?? $payment->created_at?->format('d M Y, H:i') }}
                                        @if (filled($payment->reference_code))
                                            · Ref {{ $payment->reference_code }}
                                        @endif
                                    </div>
                                    @if (filled($payment->payer_name) || filled($payment->bank_name))
                                        <div class="ty-stack__muted">
                                            {{ collect([$payment->payer_name, $payment->bank_name])->filter()->implode(' · ') }}
                                        </div>
                                    @endif
                                </div>
                                @if ($payment->status)
                                    <span class="{{ $pill($payment->status->getColor()) }}">{{ $payment->status->getLabel() }}</span>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            {{-- Published links --}}
            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">Yayın</h3>
                </div>
                <div class="ty-card__body">
                    @if ($record->publishedLinks->isEmpty())
                        <p class="ty-empty">Henüz yayın linki yok.</p>
                    @else
                        @foreach ($record->publishedLinks as $link)
                            <div style="{{ ! $loop->first ? 'margin-top:12px;padding-top:12px;border-top:1px solid var(--ty-border);' : '' }}">
                                <a class="ty-link" href="{{ $link->published_url }}" target="_blank" rel="noopener" style="font-size:13px;font-weight:600;word-break:break-all;">
                                    {{ $link->published_url }}
                                </a>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
                                    <span class="{{ $pill($link->is_live ? 'success' : 'danger') }}">{{ $link->is_live ? 'Canlı' : 'Kapalı' }}</span>
                                    <span class="{{ $pill($link->is_dofollow_verified ? 'success' : 'warning') }}">{{ $link->is_dofollow_verified ? 'Dofollow' : 'Nofollow?' }}</span>
                                </div>
                                <p class="ty-stack__muted" style="margin-top:6px;">
                                    {{ $link->published_at?->format('d M Y') ?? '—' }}
                                    @if ($link->guarantee_until)
                                        · Garanti {{ $link->guarantee_until->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            @if ($record->contentReviews->isNotEmpty())
                <section class="ty-card">
                    <div class="ty-card__head">
                        <h3 class="ty-card__title">İçerik incelemeleri</h3>
                    </div>
                    <div class="ty-card__body">
                        @foreach ($record->contentReviews->sortByDesc('version') as $review)
                            <div style="{{ ! $loop->first ? 'margin-top:12px;padding-top:12px;border-top:1px solid var(--ty-border);' : '' }}">
                                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                                    <strong style="font-size:13px;">v{{ $review->version }}</strong>
                                    @if ($review->status)
                                        <span class="{{ $pill($review->status->getColor()) }}">{{ $review->status->getLabel() }}</span>
                                    @endif
                                    <span class="ty-stack__muted">{{ $review->editor?->name ?? 'Editör yok' }} · {{ $review->created_at?->format('d M Y, H:i') }}</span>
                                </div>
                                @if (filled($review->content_body))
                                    <p style="margin:8px 0 0;font-size:13px;line-height:1.5;white-space:pre-wrap;">{{ Str::limit(strip_tags($review->content_body), 420) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">Zaman çizelgesi</h3>
                </div>
                <div class="ty-card__body">
                    @if ($timeline === [])
                        <p class="ty-empty">Henüz olay yok.</p>
                    @else
                        <ul class="ty-timeline">
                            @foreach ($timeline as $event)
                                <li data-color="{{ $event['color'] }}">
                                    <p class="ty-timeline__label">{{ $event['label'] }}</p>
                                    @if ($event['description'])
                                        <p class="ty-timeline__desc">{{ $event['description'] }}</p>
                                    @endif
                                    <p class="ty-timeline__at">{{ $event['at']->timezone(config('app.timezone'))->format('d M Y, H:i') }}</p>
                                </li>
                            @endforeach
                        </ul>
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
                        <p class="ty-empty">Müşteri yok.</p>
                    @endif
                </div>
            </section>

            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">Atama</h3>
                </div>
                <div class="ty-card__body" style="display:grid;gap:10px;">
                    <div class="ty-side-row">
                        <span>Editör</span>
                        <span>{{ $record->assignedEditor?->name ?? 'Atanmadı' }}</span>
                    </div>
                    <div class="ty-side-row">
                        <span>İçerik modu</span>
                        <span>{{ $record->content_mode?->getLabel() ?? '—' }}</span>
                    </div>
                    <div class="ty-side-row">
                        <span>Teslim</span>
                        <span>{{ $record->due_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                </div>
            </section>

            <section class="ty-card">
                <div class="ty-card__head">
                    <h3 class="ty-card__title">Fatura</h3>
                </div>
                <div class="ty-card__body">
                    @if ($invoice)
                        <div class="ty-stack">
                            <a class="ty-link" href="{{ InvoiceResource::getUrl('view', ['record' => $invoice]) }}" style="font-weight:650;">
                                {{ $invoice->invoice_number }}
                            </a>
                            <span class="ty-stack__muted">{{ $invoice->created_at?->format('d M Y, H:i') }}</span>
                            @if (filled($invoice->pdf_path) && Storage::disk('local')->exists($invoice->pdf_path))
                                <span class="ty-stack__muted">PDF mevcut</span>
                            @endif
                        </div>
                    @else
                        <p class="ty-empty">Fatura henüz oluşmadı.</p>
                    @endif

                    @if ($billing)
                        <div class="ty-divider"></div>
                        <div style="display:grid;gap:8px;">
                            <div class="ty-side-row"><span>Tip</span><span>{{ $billing->type?->getLabel() ?? '—' }}</span></div>
                            <div class="ty-side-row"><span>TCKN / VKN</span><span>{{ $billing->tax_id ?: '—' }}</span></div>
                            @if (filled($billing->company_name))
                                <div class="ty-side-row"><span>Ünvan</span><span>{{ $billing->company_name }}</span></div>
                            @endif
                            @if (filled($billing->address))
                                <div class="ty-side-row"><span>Adres</span><span>{{ $billing->address }}</span></div>
                            @endif
                            @if (filled($billing->tax_office))
                                <div class="ty-side-row"><span>Vergi dairesi</span><span>{{ $billing->tax_office }}</span></div>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            @if ($group)
                <section class="ty-card">
                    <div class="ty-card__head">
                        <h3 class="ty-card__title">Sipariş grubu</h3>
                    </div>
                    <div class="ty-card__body">
                        <a class="ty-link" href="{{ OrderGroupResource::getUrl('view', ['record' => $group]) }}" style="font-size:13px;font-weight:650;">
                            Grup #{{ $group->id }}
                        </a>
                        <div class="ty-stack__muted" style="margin-top:4px;">{{ $group->orders->count() }} kalem · {{ OrderPresentation::money($group->total, $group->currency?->value) }}</div>

                        @if ($siblings->isNotEmpty())
                            <div style="margin-top:12px;">
                                @foreach ($siblings as $sibling)
                                    <div class="ty-sibling">
                                        <a class="ty-link" href="{{ OrderResource::getUrl('view', ['record' => $sibling]) }}">
                                            #{{ $sibling->id }} · {{ OrderPresentation::displayTitle($sibling) }}
                                        </a>
                                        <span class="ty-stack__muted">{{ $sibling->status?->getLabel() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
