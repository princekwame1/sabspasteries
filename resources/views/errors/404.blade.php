<x-layouts.site title="Page not found">
    <section class="page pb-10 pt-40 text-center">
        <p class="font-display text-9xl text-rose-300">404</p>
        <h1 class="mt-4 font-display text-3xl text-cocoa-950">This page could not be found.</h1>
        <p class="mx-auto mt-3 max-w-md text-ink/60">It may have moved, or been eaten. Have a look at the menu instead.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('menu') }}" class="btn-rose">See the menu</a>
            <a href="{{ route('home') }}" class="btn-outline">Go home</a>
        </div>
    </section>
</x-layouts.site>
