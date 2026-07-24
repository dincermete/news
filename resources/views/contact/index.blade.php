@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
    $sub = 'text-lg font-medium leading-relaxed text-ink-2';

    $channels = [
        ['title' => 'Telefon', 'text' => 'Hafta içi 09:00–18:00 arası bizi arayabilirsiniz.', 'action' => '0850 305 22 41', 'href' => 'tel:08503052241', 'icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'],
        ['title' => 'E-posta', 'text' => 'Detaylı sorularınız için e-posta gönderin.', 'action' => 'info@newstanitim.com', 'href' => 'mailto:info@newstanitim.com', 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
        ['title' => 'Canlı Destek', 'text' => 'Sağ alttaki sohbet asistanımız sorularınızı hemen yanıtlar.', 'action' => 'Sohbeti Başlat', 'href' => '#', 'onclick' => "document.querySelector('[data-chatbot-toggle]')?.click()", 'icon' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z'],
        ['title' => 'WhatsApp', 'text' => 'Sohbet asistanına "Destek istiyorum" yazın; sizi anında WhatsApp\'a yönlendirsin.', 'action' => 'Sohbeti Başlat', 'href' => '#', 'onclick' => "document.querySelector('[data-chatbot-toggle]')?.click()", 'icon' => 'M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z'],
    ];

    $faqItems = [
        ['q' => 'Ne kadar sürede dönüş alırım?', 'a' => 'Canlı sohbet asistanımız yazdığınız anda yanıt verir. Destek talebi veya e-posta ile ulaştığınızda ekibimiz en kısa sürede sizinle iletişime geçer.'],
        ['q' => 'Telefonla sipariş verebilir miyim?', 'a' => 'Siparişler katalog üzerinden sepete eklenerek oluşturulur; telefonda ise hangi ürünün size uygun olduğu konusunda yardımcı oluruz.'],
        ['q' => 'Fatura kesiyor musunuz?', 'a' => 'Evet. Siparişleriniz için kesilen faturalara hesabım panelindeki Faturalarım bölümünden ulaşabilirsiniz.'],
        ['q' => 'Destek talebimin durumunu nereden görürüm?', 'a' => 'Gönderdiğiniz mesaj hesabım panelinizdeki Destek Taleplerim bölümüne düşer; durumunu oradan takip edebilirsiniz.'],
    ];
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-2xl flex-col items-center px-5 pb-14 pt-16 text-center sm:px-8 lg:pb-16 lg:pt-20" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">İletişim</span>
                    Bize Ulaşın
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl" data-reveal>
                    Sizinle konuşmak isteriz
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-white/65" data-reveal>
                    Ürünlerimiz, bir siparişiniz veya iş birliği fırsatları hakkında konuşmak isterseniz aşağıdaki kanallardan bize ulaşabilirsiniz.
                </p>
            </div>
        </div>
    </section>

    {{-- ================= KANALLAR ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($channels as $channel)
                <div class="rounded-[20px] border border-ink/10 bg-white p-6" data-reveal>
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-ink text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $channel['icon'] }}"/></svg>
                    </span>
                    <h3 class="mt-4 font-display text-base font-semibold text-ink">{{ $channel['title'] }}</h3>
                    <p class="mt-1.5 text-[13px] font-medium leading-relaxed text-ink-2">{{ $channel['text'] }}</p>
                    <a
                        href="{{ $channel['href'] }}"
                        @if (isset($channel['onclick'])) onclick="{{ $channel['onclick'] }}; return false;" @endif
                        class="mt-4 inline-flex items-center gap-x-1.5 text-sm font-semibold text-ink transition hover:text-accent-700"
                    >
                        {{ $channel['action'] }}
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= MESAJ FORMU ================= --}}
    <section class="mx-auto max-w-3xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="rounded-[20px] border border-ink/10 bg-paper p-6 sm:p-8">
            <h2 class="font-display text-xl font-semibold text-ink">Mesaj Gönderin</h2>
            <p class="mt-1.5 text-sm font-medium text-ink-2">Mesajınız hesabım panelinizdeki destek taleplerine düşer; oradan takip edebilirsiniz.</p>

            @guest
                <div class="mt-6 flex flex-col items-center gap-y-3 rounded-2xl border border-ink/10 bg-white px-6 py-10 text-center">
                    <span class="inline-flex size-11 items-center justify-center rounded-full bg-ink/5 text-ink">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                    </span>
                    <p class="font-display text-base font-semibold text-ink">Mesaj göndermek için giriş yapın</p>
                    <p class="max-w-sm text-sm text-ink-2">Mesajınızı hesabınızdan takip edebilmeniz için önce giriş yapmanız gerekiyor. Acil durumlarda telefon veya canlı destek kanallarını kullanabilirsiniz.</p>
                    <button
                        type="button"
                        class="inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]"
                        onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))"
                    >
                        Giriş yap
                    </button>
                </div>
            @else
                <form method="post" action="{{ route('account.support-tickets.store') }}" class="mt-6 space-y-4" x-data="{ phone: '', message: '' }">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Ad Soyad</label>
                            <input type="text" value="{{ auth()->user()->name }}" disabled class="block w-full rounded-xl border border-ink/10 bg-ink/5 px-3 py-2.5 text-sm text-ink-2">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">E-posta</label>
                            <input type="text" value="{{ auth()->user()->email }}" disabled class="block w-full rounded-xl border border-ink/10 bg-ink/5 px-3 py-2.5 text-sm text-ink-2">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Konu</label>
                        <select name="subject" required class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0">
                            <option value="Genel Bilgi">Genel Bilgi</option>
                            <option value="Fiyat Teklifi">Fiyat Teklifi</option>
                            <option value="Teknik Destek">Teknik Destek</option>
                            <option value="İş Ortaklığı">İş Ortaklığı</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Telefon (opsiyonel)</label>
                        <input type="tel" x-model="phone" placeholder="05xx xxx xx xx" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Mesajınız</label>
                        <textarea x-model="message" rows="4" required class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0" placeholder="Size nasıl yardımcı olabiliriz?"></textarea>
                    </div>

                    <input type="hidden" name="body" :value="phone.trim() !== '' ? ('Telefon: ' + phone + '\n\n' + message) : message">

                    <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                        Mesaj Gönder
                    </button>
                </form>
            @endguest
        </div>
    </section>

    {{-- ================= SSS ================= --}}
    <section class="mx-auto max-w-3xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p><span class="{{ $chip }}">Sıkça Sorulan Sorular</span></p>
            <h2 class="mt-5 {{ $h2 }}">Merak Edilenler</h2>
        </div>

        <div class="mt-10 space-y-3">
            @foreach ($faqItems as $index => $faq)
                <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="rounded-2xl bg-paper">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start focus:outline-hidden"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                    >
                        <span class="text-sm font-medium text-ink">{{ $faq['q'] }}</span>
                        <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-white text-ink transition-transform duration-300" :class="open ? 'rotate-45' : ''">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </span>
                    </button>
                    <div x-show="open" x-cloak class="px-6 pb-5 text-[13px] font-medium leading-relaxed text-ink-2">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
