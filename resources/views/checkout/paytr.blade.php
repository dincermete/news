@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
        <div>
            <h1 class="font-display text-2xl font-medium text-ink sm:text-[28px]">Kart ile ödeme</h1>
            <p class="mt-1.5 text-sm text-ink-2">Sipariş #{{ $orderGroup->id }} — güvenli PayTR ödeme ekranı</p>
        </div>

        <div class="mt-6 overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
            <iframe
                src="https://www.paytr.com/odeme/guvenli/{{ $token }}"
                id="paytriframe"
                frameborder="0"
                scrolling="no"
                style="width: 100%; min-height: 600px;"
            ></iframe>
            <script>iFrameResize({}, '#paytriframe');</script>
        </div>
    </div>
@endsection
