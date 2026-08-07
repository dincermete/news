@php
    use App\Filament\Resources\Customers\CustomerResource;
    use App\Filament\Resources\Invoices\InvoiceResource;
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

@include('filament.partials.ty-order-styles')


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

            @if ($siblings->isNotEmpty())
                <section class="ty-card">
                    <div class="ty-card__head">
                        <h3 class="ty-card__title">Diğer kalemler</h3>
                    </div>
                    <div class="ty-card__body">
                        <div class="ty-stack__muted">{{ $group->orders->count() }} kalem · {{ OrderPresentation::money($group->total, $group->currency?->value) }}</div>
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
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
