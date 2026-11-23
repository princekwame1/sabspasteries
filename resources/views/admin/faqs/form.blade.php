<x-layouts.admin :title="$faq->exists ? 'Edit question' : 'New question'">
    <form method="post" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="card max-w-2xl space-y-4 p-6">
        @csrf
        @if ($faq->exists) @method('put') @endif
        <div>
            <label class="label" for="question">Question</label>
            <input id="question" name="question" value="{{ old('question', $faq->question) }}" required class="input text-base">
            @error('question')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="answer">Answer</label>
            <textarea id="answer" name="answer" rows="6" required class="input">{{ old('answer', $faq->answer) }}</textarea>
            @error('answer')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="sm:max-w-48">
            <label class="label" for="sort">Order</label>
            <input id="sort" name="sort" type="number" min="0" value="{{ old('sort', $faq->sort) }}" class="input">
        </div>
        <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $faq->is_published)) class="size-4 accent-cocoa-900"> Show on the website</label>
        <div class="flex gap-2 pt-2">
            <button class="btn-primary">Save</button>
            <a href="{{ route('admin.faqs.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
