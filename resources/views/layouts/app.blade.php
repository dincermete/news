<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @hasSection('meta')
        @yield('meta')
    @else
        @include('partials.seo-meta', ['meta' => $meta ?? app(\App\Services\SeoMetaService::class)->forDefault()])
    @endif

    {{-- Scroll-reveal başlangıç durumu yalnızca JS aktifken uygulanır (bkz. app.css) --}}
    <script>document.documentElement.classList.add('js');</script>

    <link rel="preload" href="{{ asset('fonts/stack-sans-headline-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/InterDisplay-Medium.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">

    @vite(['resources/css/app.css', 'resources/js/public.js'])
</head>
<body class="storefront flex min-h-screen flex-col bg-white font-sans text-ink antialiased">
    @include('partials.header')

    <main id="main" class="@yield('mainClass', 'mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 lg:px-8')">
        @yield('content')
    </main>

    @include('partials.footer')

    @include('components.fake-order-toast')
    @include('components.chatbot-widget')
    @include('components.login-required-modal')

    {{-- chatbot.js is a separate Vite entry: type=module ⇒ deferred, idle-mounted --}}
    @vite(['resources/js/chatbot.js'])
    @stack('scripts')
</body>
</html>
