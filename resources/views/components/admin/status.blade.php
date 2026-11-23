@props(['status', 'label' => null])
@php
    $styles = [
        'new' => 'bg-rose-100 text-rose-800',
        'pending' => 'bg-rose-100 text-rose-800',
        'confirmed' => 'bg-cocoa-100 text-cocoa-800',
        'baking' => 'bg-amber-100 text-amber-800',
        'ready' => 'bg-lime-100 text-lime-800',
        'completed' => 'bg-emerald-50 text-emerald-700',
        'cancelled' => 'bg-ink/5 text-ink/50',
        'unpaid' => 'bg-red-50 text-red-700',
        'deposit' => 'bg-amber-50 text-amber-800',
        'paid' => 'bg-emerald-50 text-emerald-700',
        'published' => 'bg-emerald-50 text-emerald-700',
        'active' => 'bg-emerald-50 text-emerald-700',
        'featured' => 'bg-rose-100 text-rose-800',
        'hidden' => 'bg-ink/5 text-ink/55',
        'unread' => 'bg-rose-100 text-rose-800',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold '.($styles[$status] ?? 'bg-cocoa-50 text-cocoa-700')]) }}>{{ $label ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
