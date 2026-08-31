@props(['compact' => false])

@php
    $href = auth()->check() ? route('dashboard') : route('login');
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white ring-1 ring-line">
        <img
            src="{{ asset('images/unpam-logo.png') }}"
            alt="Universitas Pamulang"
            class="h-[118%] w-[118%] max-w-none object-cover"
        >
    </span>
    @unless ($compact)
        <span class="min-w-0">
            <span class="block text-[11px] font-medium tracking-[0.16em] text-navy/55 uppercase">Universitas Pamulang</span>
            <span class="block truncate text-base font-semibold text-navy">{{ config('kelas.name') }}</span>
        </span>
    @endunless
</a>
