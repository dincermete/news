@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@section('content')
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <div class="rounded-[20px] border border-ink/10 bg-paper p-6 text-center sm:p-8">
            <span class="mx-auto inline-flex size-12 items-center justify-center rounded-full bg-accent-100 text-accent-600">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
            </span>

            <h1 class="mt-4 font-display text-2xl font-medium text-ink">E-posta doğrulama</h1>
            <p class="mt-2 text-sm text-ink-2">Devam etmeden önce e-posta adresinizi doğrulayın. Bağlantı gelmediyse tekrar gönderebilirsiniz.</p>

            @if (session('status'))
                <p class="mt-3 text-sm font-medium text-emerald-700">{{ session('status') }}</p>
            @endif

            <form method="post" action="{{ route('verification.send') }}" class="mt-6">
                @csrf
                <button type="submit" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                        <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                    Doğrulama bağlantısını gönder
                </button>
            </form>

            <a href="{{ route('account.dashboard') }}" class="mt-5 inline-block text-sm font-medium text-ink-3 hover:text-ink">Panele dön</a>
        </div>
    </div>
@endsection
