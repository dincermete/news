@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
@endphp

@section('content')
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <div class="rounded-[20px] border border-ink/10 bg-paper p-6 sm:p-8">
            <h1 class="font-display text-2xl font-medium text-ink">Kayıt ol</h1>
            <p class="mt-1.5 text-sm text-ink-2">Hesap oluşturarak sipariş vermeye başlayın.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-brand-200 bg-brand-50 px-3.5 py-2.5 text-sm text-brand-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('register.store') }}" class="mt-6 space-y-4">
                @csrf
                @if (! empty($ref))
                    <input type="hidden" name="ref" value="{{ $ref }}">
                @endif

                <div>
                    <label class="{{ $label }}">Ad soyad</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">E-posta</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Telefon <span class="font-normal normal-case text-ink-3">(opsiyonel)</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Şifre</label>
                    <input type="password" name="password" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Şifre tekrar</label>
                    <input type="password" name="password_confirmation" required class="{{ $input }}">
                </div>
                <button type="submit" class="group flex w-full items-center justify-center gap-x-2 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-3 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98]">
                    Kayıt ol
                    <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-ink-2">
                Zaten hesabınız var mı?
                <a href="{{ route('login') }}" class="font-semibold text-accent-700 hover:text-accent-800">Giriş yap</a>
            </p>
        </div>
    </div>
@endsection
