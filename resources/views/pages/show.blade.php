@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    <article class="prose prose-zinc max-w-none prose-sm">
        <h1>{{ $page->title }}</h1>
        {!! $page->content !!}
    </article>

    @if (($faqs ?? collect())->isNotEmpty())
        <section class="mt-10 border-t border-zinc-200 pt-8">
            <h2 class="text-lg font-semibold tracking-tight text-zinc-900">Sıkça Sorulan Sorular</h2>
            <div class="mt-4 divide-y divide-zinc-100 overflow-hidden rounded-md border border-zinc-200 bg-white">
                @foreach ($faqs as $faq)
                    <details class="group px-4 py-3">
                        <summary class="cursor-pointer list-none text-sm font-medium text-zinc-900 marker:content-none">
                            {{ $faq->question_topic }}
                        </summary>
                        <div class="mt-2 text-sm text-zinc-600">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif
@endsection
