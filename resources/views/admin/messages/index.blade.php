<x-layouts.admin title="Messages">
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <tbody>
                @forelse ($messages as $m)
                    <tr class="cursor-pointer transition odd:bg-cocoa-50/40 hover:bg-cocoa-50 {{ $m->read_at ? '' : 'font-semibold' }}" onclick="location.href='{{ route('admin.messages.show', $m) }}'">
                        <td class="w-56 px-6 py-4">
                            <a href="{{ route('admin.messages.show', $m) }}" class="text-ink">{{ $m->name }}</a>
                            <p class="text-xs font-normal text-ink/50">{{ $m->phone ?: $m->email }}</p>
                        </td>
                        <td class="py-4"><p class="max-w-xl truncate {{ $m->read_at ? 'text-ink/65' : 'text-ink' }}">{{ $m->message }}</p></td>
                        <td class="py-4">@unless ($m->read_at)<x-admin.status status="unread" label="New" />@endunless</td>
                        <td class="px-6 py-4 text-right text-xs font-normal text-ink/50">{{ $m->created_at->format('j M, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td class="px-6 py-16 text-center text-ink/50">No messages yet. Messages from the contact page land here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $messages->links() }}</div>
</x-layouts.admin>
