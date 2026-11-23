{{-- The Sabs Pastries logo (public/images/logo.png), or the one uploaded in Settings. --}}
@props(['light' => false])
<img src="{{ \App\Models\Setting::logoUrl() ?? asset('images/logo.png') }}" alt="{{ \App\Models\Setting::get('business_name') }}" width="360" height="368"
    {{ $attributes->merge(['class' => 'h-14 w-auto']) }}>
