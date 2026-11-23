@php
    $chip = fn ($o) => match ($o->status) {
        'new' => 'bg-rose-100 text-rose-900',
        'ready' => 'bg-lime-100 text-lime-900',
        'completed' => 'bg-emerald-50 text-emerald-800',
        default => 'bg-cocoa-950 text-rose-100',
    };
@endphp
<x-layouts.admin title="Calendar">
    <x-slot:actions>
        <div class="flex items-center gap-1 rounded-full bg-white p-1 shadow-soft">
            <a href="{{ route('admin.calendar', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="grid size-9 place-items-center rounded-full hover:bg-cocoa-50" aria-label="Previous month"><x-icon name="chevron-left" class="size-4" /></a>
            <a href="{{ route('admin.calendar') }}" class="rounded-full px-3 py-1.5 text-sm font-medium hover:bg-cocoa-50">Today</a>
            <a href="{{ route('admin.calendar', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="grid size-9 place-items-center rounded-full hover:bg-cocoa-50" aria-label="Next month"><x-icon name="chevron-right" class="size-4" /></a>
        </div>
    </x-slot:actions>

    <div class="flex flex-wrap items-baseline justify-between gap-3">
        <h2 class="font-display text-3xl text-cocoa-950">{{ $month->format('F Y') }}</h2>
        <p class="flex flex-wrap items-center gap-4 text-xs text-ink/55">
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-rose-200"></span> New</span>
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-cocoa-950"></span> Confirmed / baking</span>
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-lime-300"></span> Ready</span>
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-emerald-200"></span> Completed</span>
        </p>
    </div>

    {{-- Month grid (large screens) --}}
    <div class="card mt-6 hidden overflow-hidden md:block">
        <div class="grid grid-cols-7 bg-cocoa-950 text-center text-xs font-semibold uppercase tracking-wider text-rose-200">
            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d)<div class="py-3">{{ $d }}</div>@endforeach
        </div>
        <div class="grid grid-cols-7 gap-px bg-cocoa-100">
            @foreach ($days as $day)
                @php($inMonth = $day['date']->isSameMonth($month))
                <div class="group min-h-32 p-2 {{ $inMonth ? 'bg-white' : 'bg-cocoa-50/70' }}">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.orders.create', ['date' => $day['date']->toDateString()]) }}" class="grid size-6 place-items-center rounded-full text-ink/40 opacity-0 hover:bg-rose-100 hover:text-rose-700 group-hover:opacity-100" aria-label="Add order on {{ $day['date']->format('j F') }}"><x-icon name="plus" class="size-3.5" /></a>
                        <p class="text-xs font-semibold {{ $day['date']->isToday() ? 'text-white' : ($inMonth ? 'text-ink/55' : 'text-ink/25') }}">
                            @if ($day['date']->isToday())<span class="inline-grid size-6 place-items-center rounded-full bg-rose-600">{{ $day['date']->day }}</span>@else{{ $day['date']->day }}@endif
                        </p>
                    </div>
                    <div class="mt-1 space-y-1">
                        @foreach ($day['orders'] as $o)
                            <a href="{{ route('admin.orders.show', $o) }}" title="{{ $o->itemLine() }} · {{ $o->name }} · {{ $o->statusLabel() }}"
                                class="block truncate rounded-lg px-2 py-1 text-xs font-medium {{ $chip($o) }}">{{ $o->itemLine() }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- List (all screens) --}}
    <section class="card mt-6 p-6">
        <h2 class="font-semibold text-cocoa-950">Due in {{ $month->format('F') }}</h2>
        <ul class="mt-4 space-y-1">
            @forelse ($list as $o)
                <li>
                    <a href="{{ route('admin.orders.show', $o) }}" class="flex items-center gap-4 rounded-2xl px-3 py-2.5 hover:bg-cocoa-50">
                        <span class="w-12 shrink-0 rounded-xl bg-cocoa-950 py-1.5 text-center">
                            <span class="block text-[10px] font-semibold uppercase text-rose-300">{{ $o->needed_on->format('D') }}</span>
                            <span class="block font-display text-lg leading-none text-white">{{ $o->needed_on->format('j') }}</span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium">{{ $o->itemLine() }}</span>
                            <span class="block truncate text-xs text-ink/50">{{ $o->name }} · {{ $o->fulfilmentLabel() }}{{ $o->timeLabel() ? ' · '.$o->timeLabel() : '' }}</span>
                        </span>
                        <x-admin.status :status="$o->status" :label="$o->statusLabel()" class="shrink-0" />
                    </a>
                </li>
            @empty
                <li class="px-3 py-8 text-center text-sm text-ink/50">No orders due this month.</li>
            @endforelse
        </ul>
    </section>
</x-layouts.admin>
