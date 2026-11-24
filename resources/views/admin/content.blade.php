@php
    $current = $pages[$page];
    $previewUrl = match ($page) {
        'home' => route('home'), 'about' => route('about'), 'contact' => route('contact'), 'menu' => route('menu'),
        'gallery' => route('gallery'), 'order' => route('order'), 'reviews' => route('review'),
        default => route('home'),
    };
@endphp
<x-layouts.admin title="Site content">
    <x-slot:actions>
        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn-ghost"><x-icon name="eye" class="size-4" /> View page</a>
    </x-slot:actions>

    <p class="max-w-3xl text-sm text-ink/60">Change any wording, photo or section on the public site. Use <code class="rounded bg-cocoa-50 px-1.5 py-0.5 text-cocoa-800">{business}</code> for your business name and <code class="rounded bg-cocoa-50 px-1.5 py-0.5 text-cocoa-800">{tagline}</code> for the tagline. Business name, contact details, logo and social links are under <a href="{{ route('admin.settings') }}" class="font-medium text-cocoa-700 underline decoration-rose-400 underline-offset-4">Settings</a>.</p>

    {{-- One tab per page --}}
    <nav class="mt-6 flex gap-1 overflow-x-auto rounded-2xl bg-white p-1.5 shadow-soft scrollbar-none" aria-label="Pages">
        @foreach ($pages as $key => $p)
            <a href="{{ route('admin.content', $key) }}" @if ($key === $page) aria-current="page" @endif
                class="shrink-0 rounded-xl px-4 py-2 text-sm font-medium transition {{ $key === $page ? 'bg-cocoa-800 text-white' : 'text-ink/65 hover:bg-cocoa-50' }}">{{ $p['label'] }}</a>
        @endforeach
    </nav>

    <form method="post" action="{{ route('admin.content.update', $page) }}" enctype="multipart/form-data" class="mt-6 grid max-w-5xl gap-6">
        @csrf @method('put')

        @foreach ($current['sections'] as $section)
            @php
                $fields = collect($section['fields']);
                $toggles = $fields->where('type', 'toggle');
                $others = $fields->where('type', '!=', 'toggle');
            @endphp
            <section class="card grid gap-6 p-6 md:grid-cols-[14rem_1fr]">
                <div>
                    <h2 class="font-semibold text-cocoa-950">{{ $section['label'] }}</h2>
                    @foreach ($toggles as $key => $f)
                        <label class="mt-3 flex items-center gap-2.5 text-sm text-ink/75">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $s[$key] ?? '1') !== '0') class="size-4 accent-cocoa-700">
                            {{ $f['label'] }}
                        </label>
                    @endforeach
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($others as $key => $f)
                        <div class="{{ in_array($f['type'], ['textarea', 'image', 'video']) ? 'sm:col-span-2' : '' }}">
                            @if ($f['type'] === 'video')
                                <span class="label">{{ $f['label'] }}</span>
                                <div class="flex flex-wrap items-center gap-4">
                                    @if ($s[$key] ?? '')
                                        <video src="{{ asset('storage/'.$s[$key]) }}" muted loop playsinline preload="metadata" onmouseenter="this.play()" onmouseleave="this.pause()" class="h-24 w-40 rounded-2xl bg-cocoa-100 object-cover"></video>
                                    @else
                                        <div class="grid h-24 w-40 place-items-center rounded-2xl bg-cocoa-100 text-xs text-ink/45">No video</div>
                                    @endif
                                    <div class="space-y-2">
                                        <input name="{{ $key }}" type="file" accept="video/mp4,video/webm" aria-label="{{ $f['label'] }}"
                                            class="text-sm file:mr-3 file:rounded-full file:border-0 file:bg-cocoa-50 file:px-4 file:py-2 file:font-semibold file:text-cocoa-800">
                                        @if ($s[$key] ?? '')
                                            <label class="flex items-center gap-2 text-xs text-ink/60"><input type="checkbox" name="remove_{{ $key }}" value="1" class="accent-cocoa-700"> Remove this video</label>
                                        @endif
                                    </div>
                                </div>
                            @elseif ($f['type'] === 'image')
                                <span class="label">{{ $f['label'] }}</span>
                                <div class="flex flex-wrap items-center gap-4">
                                    <img src="{{ \App\Models\Setting::imageUrl($key) }}" alt="" class="h-24 w-40 rounded-2xl bg-cocoa-100 object-cover">
                                    <div class="space-y-2">
                                        <input name="{{ $key }}" type="file" accept="image/jpeg,image/png,image/webp" aria-label="{{ $f['label'] }}"
                                            class="text-sm file:mr-3 file:rounded-full file:border-0 file:bg-cocoa-50 file:px-4 file:py-2 file:font-semibold file:text-cocoa-800">
                                        @if ($s[$key] ?? '')
                                            <label class="flex items-center gap-2 text-xs text-ink/60"><input type="checkbox" name="remove_{{ $key }}" value="1" class="accent-cocoa-700"> Remove and use the original photo</label>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <label class="label" for="{{ $key }}">{{ $f['label'] }}</label>
                                @if ($f['type'] === 'select')
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-rose-100 text-rose-600"><x-icon :name="old($key, $s[$key] ?? $f['default'])" class="size-5" /></span>
                                        <select id="{{ $key }}" name="{{ $key }}" class="input">
                                            @foreach ($f['options'] as $value => $label)
                                                <option value="{{ $value }}" @selected(old($key, $s[$key] ?? $f['default']) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif ($f['type'] === 'textarea')
                                    <textarea id="{{ $key }}" name="{{ $key }}" rows="{{ $key === 'about' ? 7 : 3 }}" class="input">{{ old($key, $s[$key] ?? '') }}</textarea>
                                @else
                                    <input id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $s[$key] ?? '') }}" class="input">
                                @endif
                            @endif
                            @if ($f['hint'])<p class="mt-1.5 text-xs text-ink/45">{{ $f['hint'] }}</p>@endif
                            @error($key)<p class="error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-4 flex flex-wrap items-center justify-end gap-3">
            <button form="reset-content" class="btn-ghost bg-white shadow-soft">Reset this page to the original text</button>
            <button class="btn-primary px-8 py-3 shadow-lift">Save {{ Str::lower($current['label']) }}</button>
        </div>
    </form>
    <form id="reset-content" method="post" action="{{ route('admin.content.reset', $page) }}" data-confirm="Put every text, photo and switch on this tab back to the original? Your changes on this tab will be lost.">@csrf</form>
</x-layouts.admin>
