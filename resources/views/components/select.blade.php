{{--
    Styled dropdown that submits like a <select>: a hidden input carries the value.
    <x-select name="beds" label="Bedrooms" placeholder="Any beds" icon="bed" :options="['1' => '1+ beds']" :value="request('beds')" />
--}}
@props(['name', 'options' => [], 'value' => null, 'placeholder' => 'Any', 'label' => null, 'icon' => null, 'id' => null])
@php
    $id ??= $name.'-'.Str::random(4);
    $items = collect(['' => $placeholder] + $options)->map(fn ($l, $v) => ['value' => (string) $v, 'label' => (string) $l])->values();
@endphp
<div {{ $attributes->merge(['class' => 'relative']) }}
    x-data="{
        open: false,
        value: @js((string) $value),
        items: @js($items),
        active: 0,
        get current() { return this.items.find(i => i.value === this.value) ?? this.items[0] },
        show() { this.open = true; this.active = Math.max(0, this.items.findIndex(i => i.value === this.value)); this.$nextTick(() => this.scrollToActive()) },
        choose(i) { this.value = this.items[i].value; this.open = false; this.$refs.button.focus() },
        move(step) { if (! this.open) return this.show(); this.active = (this.active + step + this.items.length) % this.items.length; this.scrollToActive() },
        jump(key) { const i = this.items.findIndex((it, n) => n > 0 && it.label.toLowerCase().startsWith(key.toLowerCase())); if (i > -1) { this.open || this.show(); this.active = i; this.scrollToActive() } },
        scrollToActive() { this.$refs.list?.children[this.active]?.scrollIntoView({ block: 'nearest' }) },
    }"
    @click.outside="open = false" @keydown.escape.prevent.stop="open = false; $refs.button.focus()">
    <input type="hidden" name="{{ $name }}" :value="value" :disabled="value === ''" value="{{ $value }}" @disabled(blank($value))>
    @if ($label)<span class="sr-only" id="{{ $id }}-label">{{ $label }}</span>@endif

    <button type="button" x-ref="button" id="{{ $id }}"
        @click="open ? open = false : show()"
        @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
        @keydown.enter.prevent="open ? choose(active) : show()" @keydown.space.prevent="open ? choose(active) : show()"
        @keydown.tab="open = false" @keydown="$event.key.length === 1 && $event.key !== ' ' && jump($event.key)"
        aria-haspopup="listbox" :aria-expanded="open" @if ($label) aria-labelledby="{{ $id }}-label {{ $id }}" @endif
        class="group flex h-full w-full items-center gap-2.5 rounded-xl bg-cocoa-50/60 px-4 py-3 text-left text-sm text-ink ring-1 ring-transparent transition hover:bg-cocoa-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400"
        :class="open && 'bg-white ring-2 ring-rose-400'">
        @if ($icon)<x-icon :name="$icon" class="size-4 shrink-0 text-cocoa-600" />@endif
        <span class="min-w-0 flex-1 truncate" :class="value === '' ? 'text-ink/60' : 'font-medium text-cocoa-950'" x-text="current.label">{{ $items->firstWhere('value', (string) $value)['label'] ?? $placeholder }}</span>
        <x-icon name="chevron-down" class="size-4 shrink-0 text-cocoa-600 transition duration-200" ::class="open && 'rotate-180'" />
    </button>

    <ul x-ref="list" x-show="open" x-cloak role="listbox" @if ($label) aria-labelledby="{{ $id }}-label" @endif
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute left-0 z-40 mt-2 max-h-72 w-full min-w-48 origin-top overflow-y-auto overscroll-contain rounded-2xl bg-white p-1.5 shadow-lift ring-1 ring-cocoa-100">
        <template x-for="(item, i) in items" :key="item.value">
            <li role="option" :aria-selected="item.value === value" @click="choose(i)" @mouseenter="active = i"
                class="flex cursor-pointer items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors"
                :class="{
                    'bg-cocoa-50 text-cocoa-950': active === i && item.value !== value,
                    'bg-cocoa-800 text-white': item.value === value,
                    'text-ink/75': active !== i && item.value !== value,
                }">
                <span x-text="item.label" :class="item.value === value && 'font-semibold'"></span>
                <svg x-show="item.value === value" class="size-4 shrink-0 text-rose-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0z"/></svg>
            </li>
        </template>
    </ul>
</div>
