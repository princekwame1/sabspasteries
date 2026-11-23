{{-- Customer reviews (Admin → Reviews). Expects $testimonials. --}}
<div class="mt-12 grid gap-12 md:grid-cols-3 md:gap-10">
    @foreach ($testimonials as $i => $t)
        <figure class="flex flex-col items-center text-center" data-reveal style="--delay: {{ $i * 80 }}ms">
            <span class="grid size-16 place-items-center rounded-full bg-sand text-xl font-semibold text-rose-600" aria-hidden="true">{{ Str::substr($t->name, 0, 1) }}</span>
            <figcaption class="mt-4">
                <span class="block font-semibold text-cocoa-950">{{ $t->name }}</span>
                @if ($t->role)<span class="block text-sm text-ink/50">{{ $t->role }}</span>@endif
            </figcaption>
            <blockquote class="mt-3 max-w-sm leading-relaxed text-ink/70">“{{ $t->quote }}”</blockquote>
            <div class="mt-4 flex gap-0.5 text-rose-500" aria-label="{{ $t->rating }} out of 5 stars">
                @for ($n = 1; $n <= 5; $n++)<x-icon name="star" class="size-4 {{ $n <= $t->rating ? 'fill-current' : 'opacity-30' }}" />@endfor
            </div>
        </figure>
    @endforeach
</div>
