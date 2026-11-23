import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;

document.documentElement.classList.add('js');

// Ask before destructive admin actions: <form data-confirm="Delete this product?">.
document.addEventListener('submit', (e) => {
    const message = e.target.getAttribute('data-confirm');
    if (message && !window.confirm(message)) e.preventDefault();
});

// Shrink big photos in the browser before they upload. Phone photos are often 3–8 MB;
// this resizes to at most 1600px on the long side (plenty for the site) and re-compresses,
// usually to 200–400 KB. Opt out with <input type="file" data-no-shrink> (e.g. the logo).
const SHRINK_MAX_SIDE = 1600;
const SHRINK_QUALITY = 0.82;
const shrinkJobs = new WeakMap();

async function shrinkImage(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 300 * 1024) return file;
    let bitmap;
    try {
        bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    } catch {
        return file; // unreadable here: upload the original
    }
    const scale = Math.min(1, SHRINK_MAX_SIDE / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    const ctx = canvas.getContext('2d');
    // JPEGs have no transparency: paint white first. PNG/WebP keep it and become WebP.
    const type = file.type === 'image/jpeg' ? 'image/jpeg' : 'image/webp';
    if (type === 'image/jpeg') {
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }
    ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close?.();
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, type, SHRINK_QUALITY));
    // Keep the original if the browser can't encode this type, or the result isn't smaller.
    if (!blob || blob.type !== type || blob.size >= file.size) return file;
    const name = file.name.replace(/\.[^.]+$/, '') + (type === 'image/jpeg' ? '.jpg' : '.webp');
    return new File([blob], name, { type, lastModified: file.lastModified });
}

document.addEventListener('change', (e) => {
    const input = e.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file' || input.hasAttribute('data-no-shrink') || !input.files?.length) return;
    const originals = [...input.files];
    const job = Promise.all(originals.map(shrinkImage)).then((files) => {
        if (shrinkJobs.get(input) !== job || files.every((f, i) => f === originals[i])) return; // newer pick, or nothing changed
        const list = new DataTransfer();
        files.forEach((f) => list.items.add(f));
        input.files = list.files;
    }).catch(() => {}).finally(() => {
        if (shrinkJobs.get(input) === job) shrinkJobs.delete(input);
    });
    shrinkJobs.set(input, job);
});

// If a form is sent while photos are still shrinking, wait for them, then send it.
document.addEventListener('submit', async (e) => {
    if (e.defaultPrevented) return;
    const jobs = [...e.target.querySelectorAll('input[type=file]')].map((input) => shrinkJobs.get(input)).filter(Boolean);
    if (!jobs.length) return;
    e.preventDefault();
    await Promise.allSettled(jobs);
    e.target.requestSubmit(e.submitter ?? undefined);
});

// Auto-submit filter forms when a select changes: <select data-autosubmit>.
document.addEventListener('change', (e) => {
    if (e.target.matches('[data-autosubmit]')) e.target.form?.requestSubmit();
});

// Fade elements in as they scroll into view: <div data-reveal style="--delay: 100ms">.
const revealer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('revealed');
        revealer.unobserve(entry.target);
    });
}, { rootMargin: '0px 0px -6% 0px' });
document.querySelectorAll('[data-reveal]').forEach((el) => revealer.observe(el));

// Full-screen viewer for photos and videos (arrow keys and swipe).
// items: [{ src, video: bool, caption }]
Alpine.data('gallery', (items) => ({
    items,
    open: false,
    index: 0,
    startX: null,
    get current() { return this.items[this.index] ?? {}; },
    show(i) { this.index = i; this.open = true; document.body.style.overflow = 'hidden'; },
    close() { this.open = false; document.body.style.overflow = ''; },
    next() { this.index = (this.index + 1) % this.items.length; },
    prev() { this.index = (this.index - 1 + this.items.length) % this.items.length; },
    touchStart(e) { this.startX = e.touches[0].clientX; },
    touchEnd(e) {
        if (this.startX === null) return;
        const dx = e.changedTouches[0].clientX - this.startX;
        if (Math.abs(dx) > 40) (dx < 0 ? this.next() : this.prev());
        this.startX = null;
    },
}));

// Horizontal slider on a scroll-snap track: swipe/trackpad natively, arrows here.
// Optional autoplay: x-data="slider({ autoplay: 4500 })". It pauses while hovered,
// touched or focused, loops back to the start, and is off for reduced-motion users.
Alpine.data('slider', ({ autoplay = 0 } = {}) => ({
    atStart: true,
    atEnd: false,
    timer: null,
    paused: false,
    init() {
        this.$nextTick(() => this.update());
        window.addEventListener('resize', () => this.update());
        if (!autoplay || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const pause = () => (this.paused = true);
        const resume = () => (this.paused = false);
        this.$el.addEventListener('mouseenter', pause);
        this.$el.addEventListener('mouseleave', resume);
        this.$el.addEventListener('focusin', pause);
        this.$el.addEventListener('focusout', resume);
        this.$el.addEventListener('touchstart', pause, { passive: true });
        this.$el.addEventListener('touchend', () => setTimeout(resume, 4000), { passive: true });
        this.timer = setInterval(() => {
            if (this.paused || document.hidden) return;
            this.atEnd ? this.$refs.track.scrollTo({ left: 0, behavior: 'smooth' }) : this.next();
        }, autoplay);
    },
    destroy() { clearInterval(this.timer); },
    step() {
        const slide = this.$refs.track.children[0];
        return slide ? slide.getBoundingClientRect().width + parseFloat(getComputedStyle(this.$refs.track).columnGap || 0) : 300;
    },
    next() { this.$refs.track.scrollBy({ left: this.step(), behavior: 'smooth' }); },
    prev() { this.$refs.track.scrollBy({ left: -this.step(), behavior: 'smooth' }); },
    update() {
        const t = this.$refs.track;
        const max = t.scrollWidth - t.clientWidth;
        this.atStart = t.scrollLeft < 8;
        this.atEnd = t.scrollLeft > max - 8;
    },
}));

// Order form: the chosen product decides the size options and the earliest date.
// products: { id: { sizes: [...], notice: days, name } }
Alpine.data('orderForm', (products, defaultNotice, initial) => ({
    products,
    product: initial.product ?? '',
    fulfilment: initial.fulfilment ?? 'collection',
    previews: [],
    get sizes() { return this.products[this.product]?.sizes ?? []; },
    get notice() { return this.products[this.product]?.notice ?? defaultNotice; },
    get minDate() {
        const d = new Date();
        d.setDate(d.getDate() + this.notice);
        return d.toISOString().slice(0, 10);
    },
    pick(e) {
        const files = [...e.target.files].slice(0, 3);
        this.previews.forEach((url) => URL.revokeObjectURL(url));
        this.previews = files.map((f) => URL.createObjectURL(f));
    },
}));

// Share a page: native share sheet on phones, copy link elsewhere.
Alpine.data('share', (title, url) => ({
    copied: false,
    async go() {
        if (navigator.share) {
            try { await navigator.share({ title, url }); } catch { /* cancelled */ }
            return;
        }
        await navigator.clipboard.writeText(url);
        this.copied = true;
        setTimeout(() => (this.copied = false), 2000);
    },
}));

// Order chat bubble. The conversation itself lives server-side in the session;
// the visible bubbles are mirrored in sessionStorage so they survive page changes.
// cfg: { url, resetUrl, token, greeting }
Alpine.data('orderChat', (cfg) => ({
    open: false,
    sending: false,
    input: '',
    placed: null,
    messages: [],
    init() {
        try {
            const saved = JSON.parse(sessionStorage.getItem('order-chat') || 'null');
            if (saved?.messages?.length) Object.assign(this, { messages: saved.messages, placed: saved.placed, open: saved.open });
        } catch { /* ignore */ }
        if (!this.messages.length) this.messages = [{ from: 'bot', text: cfg.greeting }];
        // Links ending in ?chat (e.g. from Instagram) open the chat straight away.
        if (new URLSearchParams(location.search).has('chat')) this.open = true;
        this.$watch('open', () => this.save());
        if (this.open) this.$nextTick(() => this.scroll());
    },
    save() {
        try { sessionStorage.setItem('order-chat', JSON.stringify({ messages: this.messages.slice(-60), placed: this.placed, open: this.open })); } catch { /* full or blocked */ }
    },
    scroll() { this.$refs.log.scrollTop = this.$refs.log.scrollHeight; },
    toggle() {
        this.open = !this.open;
        if (this.open) this.$nextTick(() => { this.scroll(); this.$refs.input.focus(); });
    },
    post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': cfg.token },
            body: JSON.stringify(body ?? {}),
        });
    },
    async send(text) {
        text = (text ?? this.input).trim();
        if (!text || this.sending) return;
        this.messages.push({ from: 'me', text });
        this.input = '';
        this.sending = true;
        this.$nextTick(() => this.scroll());
        let reply;
        try {
            const res = await this.post(cfg.url, { message: text });
            const data = await res.json().catch(() => ({}));
            if (res.status === 429) reply = 'You’re sending messages quickly. Give it a few seconds and try again.';
            else if (res.status === 419) reply = 'This page has been open a while. Please refresh it and try again.';
            else reply = data.reply || 'Sorry, something went wrong. Please try again, or use the order form.';
            if (data.order) this.placed = data.order.reference;
        } catch {
            reply = 'I couldn’t reach the bakery just now. Check your connection and try again.';
        }
        this.messages.push({ from: 'bot', text: reply });
        this.sending = false;
        this.save();
        this.$nextTick(() => { this.scroll(); this.$refs.input.focus(); });
    },
    async reset() {
        if (this.sending) return;
        await this.post(cfg.resetUrl).catch(() => {});
        this.messages = [{ from: 'bot', text: cfg.greeting }];
        this.placed = null;
        this.save();
    },
}));

// Home hero: cross-fade between cake photos every few seconds.
// x-data="heroRotator(count, 5000)". Pauses while the tab is hidden; clicking a dot restarts the timer.
Alpine.data('heroRotator', (count, interval = 5000) => ({
    current: 0,
    timer: null,
    init() { this.start(); },
    destroy() { clearInterval(this.timer); },
    start() {
        clearInterval(this.timer);
        if (count < 2) return;
        this.timer = setInterval(() => {
            if (!document.hidden) this.current = (this.current + 1) % count;
        }, interval);
    },
    go(i) { this.current = i; this.start(); },
}));

// First-visit "follow us" pop-up: shows after `delay` seconds and stays up until the visitor closes it
// (✕, "Maybe later" or Esc). Only closing marks it as seen, so it returns on the next page until then.
// It waits while the order chat or a full-screen photo is open so it never covers them.
Alpine.data('followPopup', ({ delay = 6 } = {}) => ({
    open: false,
    init() {
        let seen = false;
        try { seen = localStorage.getItem('follow-popup-seen') === '1'; } catch { /* private mode */ }
        if (seen || /[?&]chat\b/.test(location.search)) return;
        const tryOpen = () => {
            const busy = document.querySelector('#order-chat:not([style*="display: none"])') || document.body.style.overflow === 'hidden';
            if (busy) return setTimeout(tryOpen, 4000);
            this.open = true;
            this.$nextTick(() => this.$refs.close?.focus());
        };
        setTimeout(tryOpen, Math.max(0, delay) * 1000);
    },
    remember() { try { localStorage.setItem('follow-popup-seen', '1'); } catch { /* ignore */ } },
    close() { this.open = false; this.remember(); },
}));

Alpine.start();
