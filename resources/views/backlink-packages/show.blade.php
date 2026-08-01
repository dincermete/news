@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Models\BacklinkPackage $package */
    $money = fn (float $amount): string => number_format($amount, 0, ',', '.').' ₺';
@endphp

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <a href="{{ route('backlink-packages.index') }}" class="text-sm font-medium text-ink-2 hover:text-ink">← Backlink Paketleri</a>

        <div class="mt-6 rounded-3xl border border-ink/10 bg-white p-6 shadow-soft sm:p-8">
            <h1 class="font-display text-3xl font-semibold text-ink">{{ $package->name }}</h1>
            @if (filled($package->competition_label))
                <p class="mt-2 text-sm font-medium text-ink-3">{{ $package->competition_label }}</p>
            @endif
            @if (filled($package->description))
                <p class="mt-3 text-base leading-relaxed text-ink-2">{{ $package->description }}</p>
            @endif

            <p class="mt-6 font-display text-3xl font-semibold text-ink">{{ $money((float) $package->price) }}</p>

            @if (is_array($package->features) && count($package->features) > 0)
                <ul class="mt-6 space-y-2">
                    @foreach ($package->features as $feature)
                        <li class="flex items-start gap-2 text-sm text-ink-2">
                            <span class="mt-1 size-1.5 shrink-0 rounded-full bg-brand-500"></span>
                            <span>{{ is_array($feature) ? ($feature['feature'] ?? reset($feature)) : $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form method="post" action="{{ route('cart.add') }}" class="mt-8">
                @csrf
                <input type="hidden" name="product_type" value="backlink_package">
                <input type="hidden" name="backlink_package_id" value="{{ $package->id }}">
                <button type="submit" class="inline-flex rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white">
                    Sepete ekle
                </button>
            </form>
        </div>

        @if ($relatedPackages->isNotEmpty())
            <div class="mt-10">
                <h2 class="font-display text-xl font-semibold text-ink">Diğer paketler</h2>
                <ul class="mt-4 space-y-2">
                    @foreach ($relatedPackages as $related)
                        <li>
                            <a href="{{ $related->canonicalUrl() }}" class="text-sm font-medium text-accent-700 hover:underline">
                                {{ $related->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
@endsection
