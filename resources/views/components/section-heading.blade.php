{{-- Script label in pink above a short, bold heading. --}}
@props(['eyebrow' => null, 'title', 'center' => false, 'text' => null])
<div {{ $attributes->merge(['class' => $center ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl']) }} data-reveal>
    @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
    <h2 class="mt-1 text-3xl leading-tight text-cocoa-950 sm:text-[2.5rem]">{{ rich($title) }}</h2>
    @if ($text)<p class="mt-4 text-ink/65 {{ $center ? 'mx-auto max-w-xl' : 'max-w-xl' }}">{{ $text }}</p>@endif
</div>
