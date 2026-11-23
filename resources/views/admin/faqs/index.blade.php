<x-layouts.admin title="FAQs">
    <x-slot:actions>
        <a href="{{ route('admin.faqs.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> New question</a>
    </x-slot:actions>

    <p class="max-w-2xl text-sm text-ink/55">Shown on the Services page and on each service page.</p>

    <div class="mt-6 space-y-3">
        @forelse ($faqs as $faq)
            <div class="card flex flex-wrap items-start gap-4 p-5">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-cocoa-950">{{ $faq->question }} @unless ($faq->is_published)<x-admin.status status="hidden" label="Hidden" class="ml-2 align-middle" />@endunless</p>
                    <p class="mt-1 text-sm text-ink/60">{{ Str::limit($faq->answer, 200) }}</p>
                </div>
                <div class="flex gap-1">
                    <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn-ghost btn-sm">Edit</a>
                    <form method="post" action="{{ route('admin.faqs.destroy', $faq) }}" data-confirm="Delete this question?">@csrf @method('delete')<button class="btn-danger btn-sm">Delete</button></form>
                </div>
            </div>
        @empty
            <p class="card px-6 py-16 text-center text-ink/50">No questions yet.</p>
        @endforelse
    </div>
</x-layouts.admin>
