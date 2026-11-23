<x-layouts.admin title="Reviews">
    <x-slot:actions>
        <a href="{{ route('admin.testimonials.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> New review</a>
    </x-slot:actions>

    {{-- Public link clients use to leave a review --}}
    <div class="card mb-6 flex flex-wrap items-center gap-4 p-5" x-data="{ copied: false }">
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-cocoa-950">Review link for clients</p>
            <p class="mt-0.5 text-sm text-ink/55">Send this to clients after their event. New reviews wait here until you approve them.</p>
            <p class="mt-2 break-all font-mono text-sm text-cocoa-700">{{ route('review') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn-ghost" @click="navigator.clipboard.writeText(@js(route('review'))); copied = true; setTimeout(() => copied = false, 2000)">
                <x-icon name="copy" class="size-4" /> <span x-text="copied ? 'Copied' : 'Copy link'">Copy link</span>
            </button>
            {{-- Opens WhatsApp with the message ready; pick the client's chat --}}
            <a href="https://wa.me/?text={{ rawurlencode('Thank you for choosing '.\App\Models\Setting::get('business_name').'! We would love to hear how it went: '.route('review')) }}" target="_blank" rel="noopener" class="btn-ghost"><x-icon name="whatsapp" class="size-4" /> Share on WhatsApp</a>
            <a href="{{ route('review') }}" target="_blank" rel="noopener" class="btn-ghost"><x-icon name="eye" class="size-4" /> Open</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($testimonials as $t)
            <div class="card flex flex-col p-6 {{ $t->is_published ? '' : 'ring-2 ring-rose-300' }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-rose-500">{{ str_repeat('★', $t->rating) }}<span class="text-cocoa-100">{{ str_repeat('★', 5 - $t->rating) }}</span></p>
                    @unless ($t->is_published)
                        <x-admin.status status="draft" :label="$t->source === 'website' ? 'Waiting for approval' : 'Hidden'" />
                    @endunless
                </div>
                <p class="mt-3 flex-1 text-sm text-ink/75">“{{ Str::limit($t->quote, 220) }}”</p>
                <div class="mt-4 text-sm">
                    <p><span class="font-medium">{{ $t->name }}</span> <span class="text-ink/50">{{ $t->role }}</span></p>
                    @if ($t->source === 'website')
                        <p class="mt-0.5 text-xs text-ink/45">From the review page, {{ $t->created_at?->format('j M Y') }}@if ($t->phone) · <a href="tel:{{ \App\Models\Setting::tel($t->phone) }}" class="text-cocoa-700 hover:text-rose-600">{{ $t->phone }}</a>@endif</p>
                    @endif
                </div>
                <div class="mt-4 flex flex-wrap gap-1">
                    @unless ($t->is_published)
                        <form method="post" action="{{ route('admin.testimonials.publish', $t) }}">@csrf<button class="btn-primary btn-sm">Approve</button></form>
                    @endunless
                    <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn-ghost btn-sm">Edit</a>
                    <form method="post" action="{{ route('admin.testimonials.destroy', $t) }}" data-confirm="Delete this review?">@csrf @method('delete')<button class="btn-danger btn-sm">Delete</button></form>
                </div>
            </div>
        @empty
            <p class="card col-span-full px-6 py-16 text-center text-ink/50">No reviews yet.</p>
        @endforelse
    </div>
</x-layouts.admin>
