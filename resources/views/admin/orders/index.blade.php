@php
    $keep = fn (array $extra) => array_filter(array_merge(request()->only(['status', 'source', 'q', 'view', 'payment']), $extra), fn ($v) => $v !== null && $v !== '');
@endphp
<x-layouts.admin title="Orders">
    <x-slot:actions>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn-ghost hidden bg-white md:inline-flex"><x-icon name="download" class="size-4" /> Export</a>
        <a href="{{ route('admin.orders.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Add order</a>
    </x-slot:actions>

    <div class="flex gap-2 overflow-x-auto scrollbar-none">
        <a href="{{ route('admin.orders.index', $keep(['view' => request('view') === 'upcoming' ? null : 'upcoming', 'status' => null])) }}"
            class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition {{ request('view') === 'upcoming' ? 'bg-rose-600 text-white' : 'bg-white text-ink/65 shadow-soft hover:text-cocoa-950' }}">Coming up</a>
        @foreach (['' => 'All'] + \App\Models\Order::STATUSES as $key => $label)
            @php($active = request('status', '') === $key && request('view') !== 'upcoming')
            <a href="{{ route('admin.orders.index', $keep(['status' => $key ?: null, 'view' => null])) }}"
                class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition {{ $active ? 'bg-cocoa-950 text-white' : 'bg-white text-ink/65 shadow-soft hover:text-cocoa-950' }}">
                {{ $label }} @if ($key && ($counts[$key] ?? 0))<span class="ml-1 {{ $active ? 'text-rose-300' : 'text-ink/40' }}">{{ $counts[$key] }}</span>@endif
            </a>
        @endforeach
    </div>

    <form method="get" class="mt-4 flex flex-wrap gap-3">
        @foreach (request()->only(['status', 'view']) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
        <div class="relative min-w-60 flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-ink/40" />
            <input name="q" value="{{ request('q') }}" class="input bg-white pl-10 shadow-soft" placeholder="Search name, phone, item or SP-0001" aria-label="Search">
        </div>
        <select name="source" class="input w-auto bg-white shadow-soft" data-autosubmit aria-label="Source">
            <option value="">All sources</option>
            @foreach (\App\Models\Order::SOURCES as $key => $label)<option value="{{ $key }}" @selected(request('source') === $key)>{{ $label }}</option>@endforeach
        </select>
        <select name="payment" class="input w-auto bg-white shadow-soft" data-autosubmit aria-label="Payment">
            <option value="">Any payment</option>
            <option value="balance" @selected(request('payment') === 'balance')>Balance owing</option>
        </select>
        <noscript><button class="btn-primary">Filter</button></noscript>
    </form>

    <div class="card mt-6 overflow-x-auto">
        <table class="w-full min-w-[56rem] text-left text-sm">
            <thead class="text-xs uppercase tracking-wider text-ink/45">
                <tr>
                    <th class="px-6 py-4 font-semibold">Customer</th>
                    <th class="py-4 font-semibold">Order</th>
                    <th class="py-4 font-semibold">Needed</th>
                    <th class="py-4 font-semibold">Payment</th>
                    <th class="py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 text-right font-semibold">Received</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr class="cursor-pointer transition odd:bg-cocoa-50/40 hover:bg-cocoa-50 {{ $o->status === 'new' ? 'font-medium' : '' }}" onclick="location.href='{{ route('admin.orders.show', $o) }}'">
                        <td class="px-6 py-3.5">
                            <a href="{{ route('admin.orders.show', $o) }}" class="text-ink">{{ $o->name }}</a>
                            <p class="text-xs font-normal text-ink/50">{{ $o->reference() }} · {{ $o->phone }}</p>
                        </td>
                        <td class="py-3.5">
                            <p class="max-w-64 truncate font-normal text-ink/75">{{ $o->itemLine() }}</p>
                            <p class="max-w-64 truncate text-xs font-normal text-ink/45">{{ collect([$o->size, $o->flavour, $o->sourceLabel()])->filter()->implode(' · ') }}</p>
                        </td>
                        <td class="py-3.5 font-normal">
                            <p class="{{ $o->needed_on->isToday() ? 'font-semibold text-rose-600' : 'text-ink/70' }}">{{ $o->needed_on->isToday() ? 'Today' : $o->needed_on->format('D j M') }}</p>
                            <p class="text-xs text-ink/45">{{ $o->fulfilmentLabel() }}{{ $o->time_slot ? ' · '.ucfirst($o->time_slot) : '' }}</p>
                        </td>
                        <td class="py-3.5 font-normal">
                            @if ($o->total !== null)
                                <p class="text-ink/75">{{ money($o->total) }}</p>
                                @if ($o->paymentState())<x-admin.status :status="$o->paymentState()" :label="$o->paymentLabel()" class="mt-0.5 !px-2 !py-0.5 !text-[11px]" />@endif
                            @else
                                <span class="text-xs text-ink/40">Not priced</span>
                            @endif
                        </td>
                        <td class="py-3.5"><x-admin.status :status="$o->status" :label="$o->statusLabel()" /></td>
                        <td class="px-6 py-3.5 text-right text-xs font-normal text-ink/50">{{ $o->created_at->format('j M, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-ink/50">No orders here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.admin>
