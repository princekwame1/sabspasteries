@php
    // Phones can't open links to this computer, so flag local-only addresses before printing.
    $isLocal = fn (string $url) => (bool) preg_match('#^https?://(localhost|127\.|0\.0\.0\.0|192\.168\.|10\.)#i', $url);
@endphp
<x-layouts.admin title="QR codes">
    <p class="max-w-2xl text-sm text-ink/55">
        Make a QR code for any link, then download it to print on boxes, cards, stickers or your stall.
        By default it opens your hidden <a href="{{ $followUrl }}" target="_blank" class="font-medium text-rose-600 underline underline-offset-2">Follow us page</a>,
        which shows your social media buttons (set in <a href="{{ route('admin.settings') }}" class="font-medium text-rose-600 underline underline-offset-2">Settings</a>).
    </p>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,26rem)_1fr]">
        {{-- New QR code --}}
        <form method="post" action="{{ route('admin.qr-codes.store') }}" class="card h-fit space-y-4 p-6"
            x-data="{ url: @js(old('url', $followUrl)), follow: @js($followUrl) }">
            @csrf
            <h2 class="font-semibold text-cocoa-950">New QR code</h2>
            <div>
                <label class="label" for="label">Name</label>
                <input id="label" name="label" value="{{ old('label', 'Follow us') }}" required maxlength="80" class="input" placeholder="e.g. Cake box sticker">
                <p class="mt-1.5 text-xs text-ink/45">Only you see this. It is also used for the file name.</p>
                @error('label')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="url">Link</label>
                <input id="url" name="url" type="url" x-model="url" required maxlength="500" class="input" placeholder="https://">
                @error('url')<p class="error">{{ $message }}</p>@enderror
                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                    <button type="button" @click="url = follow" class="rounded-full bg-rose-50 px-3 py-1.5 font-medium text-rose-700 hover:bg-rose-100">Use the Follow us page</button>
                    <a :href="url" target="_blank" rel="noopener" class="rounded-full bg-cocoa-50 px-3 py-1.5 font-medium text-cocoa-800 hover:bg-cocoa-100">Test this link</a>
                </div>
                <p x-show="/^https?:\/\/(localhost|127\.|0\.0\.0\.0|192\.168\.|10\.)/i.test(url)" x-cloak class="mt-3 rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-900">
                    This link only works on this computer, so phones won’t be able to open it. Make the codes you print from the live website’s admin.
                </p>
            </div>
            <button class="btn-primary w-full"><x-icon name="qr" class="size-4" /> Create QR code</button>
        </form>

        {{-- Saved QR codes --}}
        <section>
            @if ($codes->isEmpty())
                <p class="card px-6 py-16 text-center text-sm text-ink/50">No QR codes yet. Create your first one on the left.</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($codes as $qr)
                        <article class="card flex flex-col p-5">
                            <a href="{{ $qr->pngUrl() }}" target="_blank" class="block overflow-hidden rounded-2xl bg-white ring-1 ring-cocoa-900/8" title="Open full size">
                                <img src="{{ $qr->pngUrl() }}" alt="QR code for {{ $qr->label }}" loading="lazy" class="aspect-square w-full">
                            </a>
                            <h3 class="mt-4 font-semibold text-cocoa-950">{{ $qr->label }}</h3>
                            <a href="{{ $qr->url }}" target="_blank" rel="noopener" class="mt-0.5 truncate text-sm text-ink/55 hover:text-rose-600" title="{{ $qr->url }}">{{ $qr->url }}</a>
                            <p class="mt-1 text-xs text-ink/40">Created {{ $qr->created_at->format('j M Y') }}</p>
                            @if ($isLocal($qr->url))
                                <p class="mt-2 rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs text-amber-900">Local link: won’t open on phones.</p>
                            @endif
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('admin.qr-codes.download', [$qr, 'png']) }}" class="btn-primary btn-sm"><x-icon name="download" class="size-3.5" /> PNG</a>
                                <a href="{{ route('admin.qr-codes.download', [$qr, 'svg']) }}" class="btn-ghost btn-sm" title="Sharp at any size: best for large prints"><x-icon name="download" class="size-3.5" /> SVG</a>
                                <form method="post" action="{{ route('admin.qr-codes.destroy', $qr) }}" data-confirm="Delete this QR code? Printed copies will still work, but you won’t be able to download it again." class="ml-auto">
                                    @csrf @method('delete')
                                    <button class="btn-danger btn-sm" aria-label="Delete {{ $qr->label }}"><x-icon name="trash" class="size-3.5" /></button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-ink/45">PNG works everywhere (WhatsApp, Instagram, Canva, printing). SVG stays sharp at any size, which is best for banners and large prints.</p>
            @endif
        </section>
    </div>
</x-layouts.admin>
