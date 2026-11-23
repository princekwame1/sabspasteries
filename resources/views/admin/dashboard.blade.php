@php
    $max = max(1, $chart->max('total'));
    $total30 = $chart->sum('total');
    $pipelineTotal = max(1, $pipeline->sum());
    $sourceTotal = max(1, $sources->sum());
@endphp
<x-layouts.admin title="Dashboard">
    <x-slot:actions>
        <a href="{{ route('admin.orders.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Add an order</a>
    </x-slot:actions>

    <p class="text-sm text-ink/55">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ Str::before(auth()->user()->name, ' ') }}. Here is what is happening in the kitchen.</p>

    @if ($overdue)
        <a href="{{ route('admin.calendar') }}" class="mt-4 flex items-center justify-between gap-4 rounded-2xl bg-amber-50 px-5 py-3.5 text-sm text-amber-900">
            <span>{{ $overdue }} {{ Str::plural('order', $overdue) }} past {{ $overdue === 1 ? 'its' : 'their' }} date {{ $overdue === 1 ? 'is' : 'are' }} not marked completed yet.</span>
            <span class="font-semibold underline">Review</span>
        </a>
    @endif

    {{-- Stats --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['New orders', $stats['new'], 'Waiting for you to confirm', route('admin.orders.index', ['status' => 'new'])],
            ['Due today', $stats['today'], $stats['week'].' due in the next 7 days', route('admin.orders.index', ['view' => 'upcoming'])],
            [now()->format('F').' orders', $stats['monthOrders'], $stats['monthValue'] > 0 ? money($stats['monthValue']).' in sales, '.money($stats['monthPaid']).' paid' : 'Due this month', route('admin.calendar')],
            ['Balance to collect', money($stats['outstanding']), 'Across open orders', route('admin.orders.index', ['payment' => 'balance'])],
        ] as $i => [$label, $value, $hint, $href])
            <a href="{{ $href }}" class="rounded-3xl p-6 shadow-soft transition hover:-translate-y-0.5 hover:shadow-lift {{ $i === 0 ? 'bg-rose-600 text-white' : 'bg-white' }}">
                <p class="text-sm {{ $i === 0 ? 'text-white/75' : 'text-ink/55' }}">{{ $label }}</p>
                <p class="mt-2 font-display text-4xl {{ $i === 0 ? 'text-white' : 'text-cocoa-950' }}">{{ $value }}</p>
                <p class="mt-1 text-xs {{ $i === 0 ? 'text-white/70' : 'text-ink/45' }}">{{ $hint }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_1.4fr]">
        {{-- Coming up --}}
        <section class="card p-6">
            <div class="flex items-baseline justify-between">
                <h2 class="font-semibold text-cocoa-950">Coming up</h2>
                <a href="{{ route('admin.calendar') }}" class="text-sm font-medium text-cocoa-700 hover:text-rose-600">Calendar</a>
            </div>
            <ul class="mt-4 space-y-1">
                @forelse ($upcoming as $o)
                    <li>
                        <a href="{{ route('admin.orders.show', $o) }}" class="flex items-center gap-4 rounded-2xl px-3 py-2.5 hover:bg-cocoa-50">
                            <span class="w-12 shrink-0 rounded-xl py-1.5 text-center {{ $o->needed_on->isToday() ? 'bg-rose-600' : 'bg-cocoa-950' }}">
                                <span class="block text-[10px] font-semibold uppercase {{ $o->needed_on->isToday() ? 'text-white/80' : 'text-rose-300' }}">{{ $o->needed_on->isToday() ? 'Today' : $o->needed_on->format('M') }}</span>
                                <span class="block font-display text-lg leading-none text-white">{{ $o->needed_on->format('j') }}</span>
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium">{{ $o->itemLine() }}</span>
                                <span class="block truncate text-xs text-ink/50">{{ $o->name }} · {{ $o->fulfilmentLabel() }}{{ $o->time_slot ? ' · '.ucfirst($o->time_slot) : '' }}</span>
                            </span>
                            <x-admin.status :status="$o->status" :label="$o->statusLabel()" class="ml-auto shrink-0" />
                        </a>
                    </li>
                @empty
                    <li class="px-3 py-8 text-center text-sm text-ink/50">Nothing due. A good day to bake for Instagram.</li>
                @endforelse
            </ul>
        </section>

        {{-- Orders chart --}}
        <section class="card p-6">
            <div class="flex items-baseline justify-between">
                <h2 class="font-semibold text-cocoa-950">Orders received, last 30 days</h2>
                <p class="text-sm text-ink/50">{{ $total30 }} total</p>
            </div>
            <div class="mt-6 flex h-48 items-end gap-1" role="img" aria-label="Orders per day for the last 30 days, {{ $total30 }} in total">
                @foreach ($chart as $day)
                    <div class="group relative flex h-full flex-1 items-end">
                        <div class="w-full rounded-t-md transition {{ $day['date']->isToday() ? 'bg-rose-500' : 'bg-rose-200 group-hover:bg-rose-400' }}"
                            style="height: {{ $day['total'] ? max(4, $day['total'] / $max * 100) : 2 }}%"></div>
                        <span class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-cocoa-950 px-2.5 py-1 text-xs text-white group-hover:block">
                            {{ $day['date']->format('D j M') }}: {{ $day['total'] }}
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex justify-between text-xs text-ink/40">
                <span>{{ $chart->first()['date']->format('j M') }}</span><span>Today</span>
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        {{-- Latest orders --}}
        <section class="card p-6">
            <div class="flex items-baseline justify-between">
                <h2 class="font-semibold text-cocoa-950">Latest orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-cocoa-700 hover:text-rose-600">All orders</a>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <tbody>
                        @forelse ($recent as $o)
                            <tr class="group">
                                <td class="rounded-l-2xl py-3 pl-3 group-hover:bg-cocoa-50">
                                    <a href="{{ route('admin.orders.show', $o) }}" class="font-medium text-ink">{{ $o->name }}</a>
                                    <p class="text-xs text-ink/50">{{ $o->reference() }} · {{ $o->sourceLabel() }}</p>
                                </td>
                                <td class="py-3 group-hover:bg-cocoa-50">
                                    <p class="max-w-56 truncate text-ink/70">{{ $o->itemLine() }}</p>
                                    <p class="text-xs text-ink/45">For {{ $o->needed_on->format('D j M') }}</p>
                                </td>
                                <td class="py-3 group-hover:bg-cocoa-50"><x-admin.status :status="$o->status" :label="$o->statusLabel()" /></td>
                                <td class="rounded-r-2xl py-3 pr-3 text-right text-xs text-ink/45 group-hover:bg-cocoa-50">{{ $o->created_at->diffForHumans(short: true) }}</td>
                            </tr>
                        @empty
                            <tr><td class="py-8 text-center text-ink/50">No orders yet. Website orders appear here, and you can add orders from DMs with “Add an order”.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-semibold text-cocoa-950">Open orders by stage</h2>
            @if ($pipeline->isEmpty())
                <p class="py-6 text-center text-sm text-ink/50">No open orders.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach (\App\Models\Order::OPEN as $key)
                        @continue(! ($pipeline[$key] ?? 0))
                        <li>
                            <a href="{{ route('admin.orders.index', ['status' => $key]) }}" class="block">
                                <span class="flex justify-between text-sm"><span class="text-ink/70">{{ \App\Models\Order::STATUSES[$key] }}</span><span class="font-semibold">{{ $pipeline[$key] }}</span></span>
                                <span class="mt-1.5 block h-1.5 overflow-hidden rounded-full bg-cocoa-50"><span class="block h-full rounded-full {{ $key === 'ready' ? 'bg-lime-500' : 'bg-rose-500' }}" style="width: {{ $pipeline[$key] / $pipelineTotal * 100 }}%"></span></span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($popular->isNotEmpty())
                <h3 class="mt-8 text-sm font-semibold text-cocoa-950">Most ordered, last 6 months</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($popular as $p)
                        <li class="flex justify-between gap-4"><a href="{{ route('admin.products.edit', $p) }}" class="truncate text-ink/65 hover:text-rose-600">{{ $p->name }}</a><span class="font-semibold">{{ $p->orders_count }}</span></li>
                    @endforeach
                </ul>
            @endif

            @if ($sources->isNotEmpty())
                <h3 class="mt-8 text-sm font-semibold text-cocoa-950">Where orders come from</h3>
                <ul class="mt-3 space-y-2.5 text-sm">
                    @foreach ($sources->sortDesc() as $source => $count)
                        <li>
                            <span class="flex justify-between"><span class="text-ink/65">{{ \App\Models\Order::SOURCES[$source] ?? $source }}</span><span class="font-semibold">{{ round($count / $sourceTotal * 100) }}%</span></span>
                            <span class="mt-1 block h-1.5 overflow-hidden rounded-full bg-cocoa-50"><span class="block h-full rounded-full bg-cocoa-700" style="width: {{ $count / $sourceTotal * 100 }}%"></span></span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-8 grid grid-cols-2 gap-3 text-center">
                <a href="{{ route('admin.products.index') }}" class="rounded-2xl bg-cocoa-50 p-4 hover:bg-cocoa-100"><span class="block font-display text-2xl text-cocoa-950">{{ $stats['products'] }}</span><span class="text-xs text-ink/55">Products on the menu</span></a>
                <a href="{{ route('admin.messages.index') }}" class="rounded-2xl bg-cocoa-50 p-4 hover:bg-cocoa-100"><span class="block font-display text-2xl text-cocoa-950">{{ $stats['unread'] }}</span><span class="text-xs text-ink/55">Unread messages</span></a>
            </div>
        </section>
    </div>
</x-layouts.admin>
