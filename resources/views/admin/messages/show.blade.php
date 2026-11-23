@php($first = Str::before($message->name, ' '))
<x-layouts.admin :title="'Message from '.$message->name">
    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <section class="card p-6 sm:p-8">
            <p class="text-sm text-ink/50">Received {{ $message->created_at->format('l j F Y, H:i') }}</p>
            <p class="mt-5 whitespace-pre-line text-lg leading-relaxed text-ink/85">{{ $message->message }}</p>
        </section>
        <div class="space-y-6">
            <section class="card p-6">
                <h2 class="font-semibold text-cocoa-950">{{ $message->name }}</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-ink/45">Phone</dt><dd class="font-medium">{{ $message->phone ?: '-' }}</dd></div>
                    <div><dt class="text-ink/45">Email</dt><dd class="break-all font-medium">{{ $message->email ?: '-' }}</dd></div>
                </dl>
                <div class="mt-5 grid grid-cols-2 gap-2">
                    @if ($message->phone)
                        <a href="tel:{{ \App\Models\Setting::tel($message->phone) }}" class="btn-ghost"><x-icon name="phone" class="size-4" /> Call</a>
                        <a href="{{ \App\Models\Setting::whatsappUrl('Hello '.$first.', thank you for your message to '.\App\Models\Setting::get('business_name').'.', $message->phone) }}" target="_blank" rel="noopener" class="btn-ghost"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                    @endif
                    @if ($message->email)<a href="mailto:{{ $message->email }}" class="btn-ghost col-span-2"><x-icon name="mail" class="size-4" /> Email</a>@endif
                </div>
                <a href="{{ route('admin.orders.create') }}" class="btn-primary mt-3 w-full"><x-icon name="plus" class="size-4" /> Add an order</a>
            </section>
            <form method="post" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="Delete this message?">
                @csrf @method('delete')
                <button class="btn-danger w-full"><x-icon name="trash" class="size-4" /> Delete</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
