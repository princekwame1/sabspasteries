<x-layouts.site title="Leave a review" :description="site('review_text')">
    {{-- Wording: Admin → Site content → Review page --}}
    <x-page-hero :eyebrow="site('review_eyebrow')" :title="site('review_title')">
        @if (site('review_text'))<p class="mt-6 max-w-xl text-lg text-ink/70">{{ site('review_text') }}</p>@endif
    </x-page-hero>
    <section class="page max-w-3xl pt-14">
        <div class="panel p-6 sm:p-12">
            @if (session('sent'))
                <div class="py-10 text-center">
                    <p class="text-4xl text-rose-500" aria-hidden="true">★★★★★</p>
                    <p class="mt-4 font-display text-3xl text-cocoa-950">{{ site('review_success_title') }}</p>
                    <p class="mt-3 text-ink/65">{{ session('sent') }}</p>
                    <a href="{{ route('home') }}" class="btn-rose mt-8">Back to home</a>
                </div>
            @else
                <form method="post" action="{{ route('review.store') }}" class="space-y-5" x-data="{ rating: {{ (int) old('rating', 0) }}, hover: 0 }">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <fieldset>
                        <legend class="field-label">Your rating</legend>
                        <div class="flex gap-1" @mouseleave="hover = 0">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer" @mouseenter="hover = {{ $i }}">
                                    <input type="radio" name="rating" value="{{ $i }}" x-model.number="rating" class="peer sr-only" @checked((int) old('rating') === $i) required>
                                    <span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span>
                                    <svg class="size-10 transition peer-focus-visible:rounded peer-focus-visible:outline-2 peer-focus-visible:outline-rose-400 {{ (int) old('rating') >= $i ? 'text-rose-400' : 'text-cocoa-100' }}"
                                        :class="(hover || rating) >= {{ $i }} ? 'text-rose-400 scale-110' : 'text-cocoa-100'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9z"/>
                                    </svg>
                                </label>
                            @endfor
                        </div>
                        @error('rating')<p class="error">{{ $message }}</p>@enderror
                    </fieldset>

                    <div>
                        <label class="field-label" for="r-quote">Your review</label>
                        <textarea id="r-quote" name="quote" rows="5" required maxlength="1000" class="field bg-cocoa-50/60" placeholder="What did you order? How did it look and taste?">{{ old('quote') }}</textarea>
                        @error('quote')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="field-label" for="r-name">Your name</label>
                            <input id="r-name" name="name" value="{{ old('name') }}" required class="field bg-cocoa-50/60" autocomplete="name">
                            @error('name')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="r-role">About you <span class="font-normal normal-case tracking-normal text-ink/40">(optional)</span></label>
                            <input id="r-role" name="role" value="{{ old('role') }}" class="field bg-cocoa-50/60" placeholder="e.g. Birthday cake, May 2026">
                            @error('role')<p class="error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="field-label" for="r-phone">Phone <span class="font-normal normal-case tracking-normal text-ink/40">(optional, never shown)</span></label>
                        <input id="r-phone" name="phone" type="tel" value="{{ old('phone') }}" class="field bg-cocoa-50/60 sm:max-w-xs" autocomplete="tel">
                        <p class="mt-1.5 text-xs text-ink/45">Helps us match your review to your order.</p>
                        @error('phone')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <button class="btn-rose w-full py-3.5">{{ site('review_button') }}</button>
                </form>
            @endif
        </div>
    </section>
</x-layouts.site>
