@props([
    'descriptionHtml' => null,
    'descriptionText' => null,
    'emptyDescription' => 'Bu ürün için henüz açıklama eklenmedi.',
    'deliveryDetails' => null,
    'reviews',
    'questions',
    'reviewAction',
    'questionAction',
    'faqs' => null,
])

@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteReview> $reviews */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteQuestion> $questions */
    /** @var \Illuminate\Support\Collection<int, mixed>|null $faqs */

    $faqItems = collect($faqs ?? []);
    $deliveryDetails = filled($deliveryDetails) && trim(strip_tags((string) $deliveryDetails)) !== ''
        ? (string) $deliveryDetails
        : null;

    $tabs = collect([
        ['key' => 'aciklama', 'label' => 'Açıklamalar'],
        $deliveryDetails !== null ? ['key' => 'teslimat', 'label' => 'Teslimat Detayları'] : null,
        ['key' => 'yorumlar', 'label' => 'Kullanıcı Yorumları'],
        ['key' => 'soru-cevap', 'label' => 'Kullanıcı Soruları & Yanıtları'],
        $faqItems->isNotEmpty() ? ['key' => 'sss', 'label' => 'Sık Sorulan Sorular'] : null,
    ])->filter()->values();

    $inputClass = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:ring-0';
    $labelClass = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $outlineBtn = 'inline-flex w-full items-center justify-center gap-x-1.5 rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-medium text-ink transition hover:border-ink/20 hover:bg-paper';
@endphp

<div {{ $attributes->class('min-w-0 overflow-hidden rounded-[20px] border border-ink/10 bg-white shadow-soft') }} x-data="{ tab: window.location.hash === '#yorumlar' ? 'yorumlar' : 'aciklama' }">
    <div class="no-scrollbar flex gap-1 overflow-x-auto border-b border-ink/10 px-2 pt-2">
        @foreach ($tabs as $t)
            <button
                type="button"
                @click="tab = '{{ $t['key'] }}'"
                :class="tab === '{{ $t['key'] }}' ? 'border-ink text-ink' : 'border-transparent text-ink-3 hover:text-ink-2'"
                class="shrink-0 whitespace-nowrap border-b-2 px-3 py-3 text-xs font-semibold transition sm:px-4 sm:text-sm"
            >{{ $t['label'] }}</button>
        @endforeach
    </div>

    <div class="p-5 sm:p-6">
        <div x-show="tab === 'aciklama'">
            @if (filled($descriptionHtml))
                <div class="prose prose-sm max-w-none prose-headings:text-ink prose-p:text-ink-2 prose-a:text-ink prose-strong:text-ink">
                    {!! $descriptionHtml !!}
                </div>
            @elseif (filled($descriptionText))
                <p class="text-sm leading-relaxed text-ink-2">{{ $descriptionText }}</p>
            @else
                <p class="py-6 text-center text-sm text-ink-3">{{ $emptyDescription }}</p>
            @endif
        </div>

        @if ($deliveryDetails !== null)
            <div x-show="tab === 'teslimat'" x-cloak>
                <div class="prose prose-sm max-w-none prose-headings:text-ink prose-p:text-ink-2 prose-a:text-ink prose-strong:text-ink">
                    {!! $deliveryDetails !!}
                </div>
            </div>
        @endif

        <div x-show="tab === 'yorumlar'" x-cloak id="yorumlar">
            <div class="grid gap-5 lg:grid-cols-2 lg:gap-6">
                <div class="overflow-hidden rounded-[20px] border border-ink/10">
                    <div class="border-b border-ink/10 px-5 py-4">
                        <h3 class="font-display text-base font-semibold text-ink">Kullanıcı yorumları</h3>
                    </div>
                    <div class="max-h-[420px] divide-y divide-ink/10 overflow-y-auto">
                        @forelse ($reviews as $review)
                            <div class="px-5 py-4">
                                <p class="text-sm font-semibold text-ink">{{ $review->name }}</p>
                                <p class="mt-1.5 text-sm leading-relaxed text-ink-2">{{ $review->message }}</p>
                                @if ($review->approved_at)
                                    <p class="mt-2 text-[11px] font-medium text-ink-3">{{ $review->approved_at->format('d.m.Y') }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-ink-3">Henüz onaylanmış yorum yok.</div>
                        @endforelse
                    </div>
                </div>

                <div class="overflow-hidden rounded-[20px] border border-ink/10">
                    <div class="border-b border-ink/10 px-5 py-4">
                        <h3 class="font-display text-base font-semibold text-ink">Yorum yaz</h3>
                        <p class="mt-1 text-sm text-ink-2">Üye olmadan gönderebilirsiniz. Onaylandıktan sonra yayınlanır.</p>
                    </div>

                    <form method="post" action="{{ $reviewAction }}" class="space-y-4 p-5">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="review_name" class="{{ $labelClass }}">Ad soyad</label>
                                <input
                                    id="review_name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name', auth()->user()?->name) }}"
                                    required
                                    maxlength="120"
                                    class="{{ $inputClass }}"
                                >
                                @error('name')
                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="review_email" class="{{ $labelClass }}">E-posta</label>
                                <input
                                    id="review_email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', auth()->user()?->email) }}"
                                    required
                                    class="{{ $inputClass }}"
                                >
                                @error('email')
                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="review_phone" class="{{ $labelClass }}">Telefon</label>
                                <input
                                    id="review_phone"
                                    type="tel"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    required
                                    maxlength="40"
                                    class="{{ $inputClass }}"
                                >
                                @error('phone')
                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label for="review_message" class="{{ $labelClass }}">Mesaj</label>
                            <textarea
                                id="review_message"
                                name="message"
                                rows="4"
                                required
                                minlength="10"
                                class="{{ $inputClass }}"
                            >{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="{{ $outlineBtn }} sm:w-auto sm:px-6">
                            Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="tab === 'soru-cevap'" x-cloak>
            <div class="grid gap-5 lg:grid-cols-2 lg:gap-6">
                <div class="overflow-hidden rounded-[20px] border border-ink/10">
                    <div class="border-b border-ink/10 px-5 py-4">
                        <h3 class="font-display text-base font-semibold text-ink">Sorular &amp; yanıtlar</h3>
                    </div>
                    <div class="max-h-[420px] divide-y divide-ink/10 overflow-y-auto">
                        @forelse ($questions as $item)
                            <div class="px-5 py-4">
                                <p class="text-sm font-semibold text-ink">{{ $item->question }}</p>
                                <p class="mt-1.5 text-sm leading-relaxed text-ink-2">{{ $item->answer }}</p>
                                @if ($item->answered_at)
                                    <p class="mt-2 text-[11px] font-medium text-ink-3">{{ $item->answered_at->format('d.m.Y') }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-ink-3">Henüz yanıtlanmış soru yok.</div>
                        @endforelse
                    </div>
                </div>

                <div class="overflow-hidden rounded-[20px] border border-ink/10">
                    <div class="border-b border-ink/10 px-5 py-4">
                        <h3 class="font-display text-base font-semibold text-ink">Soru sor</h3>
                        <p class="mt-1 text-sm text-ink-2">Yanıtlandıktan sonra herkese açık olarak yayınlanır.</p>
                    </div>

                    <form method="post" action="{{ $questionAction }}" class="space-y-4 p-5">
                        @csrf
                        @guest
                            <div>
                                <label for="guest_email" class="{{ $labelClass }}">E-posta</label>
                                <input
                                    id="guest_email"
                                    type="email"
                                    name="guest_email"
                                    value="{{ old('guest_email') }}"
                                    required
                                    class="{{ $inputClass }}"
                                >
                                @error('guest_email')
                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endguest
                        <div>
                            <label for="question" class="{{ $labelClass }}">Sorunuz</label>
                            <textarea
                                id="question"
                                name="question"
                                rows="4"
                                required
                                minlength="10"
                                class="{{ $inputClass }}"
                            >{{ old('question') }}</textarea>
                            @error('question')
                                <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="{{ $outlineBtn }} sm:w-auto sm:px-6">
                            Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if ($faqItems->isNotEmpty())
            <div x-show="tab === 'sss'" x-cloak class="space-y-3">
                @foreach ($faqItems as $index => $faq)
                    <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="rounded-2xl bg-paper">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start focus:outline-hidden"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                        >
                            <span class="text-sm font-medium text-ink">{{ $faq->question_topic ?? $faq['question_topic'] ?? '' }}</span>
                            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-white text-ink transition-transform duration-300" :class="open ? 'rotate-45' : ''">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </span>
                        </button>
                        <div x-show="open" x-cloak class="px-5 pb-4 text-[13px] font-medium leading-relaxed text-ink-2">
                            {{ $faq->answer ?? $faq['answer'] ?? '' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
