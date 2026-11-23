{{-- Frequently asked questions (Admin → FAQs). Expects $faqs. --}}
<section class="page pt-24">
    <div class="grid gap-10 lg:grid-cols-[1fr_1.6fr] lg:gap-20">
        <div>
            <x-section-heading :eyebrow="site('faq_eyebrow')" :title="site('faq_title')" />
            <p class="mt-6 text-ink/60" data-reveal>Something else on your mind? <a href="{{ route('contact') }}" class="font-medium text-rose-700 underline decoration-rose-300 underline-offset-4 hover:text-rose-900">Ask us</a>.</p>
        </div>
        <div class="divide-y divide-cocoa-900/10 border-y border-cocoa-900/10" x-data="{ open: 0 }">
            @foreach ($faqs as $i => $faq)
                <div>
                    <button type="button" class="flex w-full items-center justify-between gap-6 py-5 text-left text-lg font-medium text-cocoa-950 transition hover:text-rose-600"
                        @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}">
                        {{ $faq->question }}
                        <span class="shrink-0 text-rose-600 transition" :class="open === {{ $i }} && 'rotate-45'"><x-icon name="plus" class="size-5" /></span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse @if ($i !== 0) x-cloak @endif>
                        <p class="max-w-2xl pb-6 leading-relaxed text-ink/65">{{ $faq->answer }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
