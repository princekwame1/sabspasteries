{{-- Full-screen viewer. Use inside x-data="gallery(items)". --}}
<template x-teleport="body">
    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-[90] grid place-items-center bg-cocoa-950/95 p-4"
        @keydown.escape.window="close()" @keydown.arrow-right.window="open && next()" @keydown.arrow-left.window="open && prev()"
        @touchstart="touchStart($event)" @touchend="touchEnd($event)" @click.self="close()" role="dialog" aria-modal="true" aria-label="Photo viewer">
        <button type="button" @click="close()" class="absolute right-4 top-4 z-10 grid size-11 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Close"><x-icon name="x" /></button>
        <template x-if="items.length > 1">
            <div>
                <button type="button" @click="prev()" class="absolute left-3 top-1/2 z-10 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Previous"><x-icon name="chevron-left" /></button>
                <button type="button" @click="next()" class="absolute right-3 top-1/2 z-10 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Next"><x-icon name="chevron-right" /></button>
            </div>
        </template>
        <figure class="flex max-h-full flex-col items-center">
            <template x-if="open && current.video">
                <video :src="current.src" controls autoplay playsinline class="max-h-[82vh] max-w-full rounded-2xl"></video>
            </template>
            <template x-if="open && !current.video">
                <img :src="current.src" :alt="current.caption || ''" class="max-h-[82vh] max-w-full rounded-2xl object-contain">
            </template>
            <figcaption class="mt-4 text-center text-sm text-white/70" x-show="current.caption" x-text="current.caption"></figcaption>
            <p class="mt-1 text-xs text-white/40" x-show="items.length > 1" x-text="(index + 1) + ' / ' + items.length"></p>
        </figure>
    </div>
</template>
