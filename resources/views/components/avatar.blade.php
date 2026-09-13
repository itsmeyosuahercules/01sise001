@props(['user', 'size' => 'md', 'lazy' => true])

@php
    $box = match ($size) {
        'sm' => 'size-8 text-[11px]',
        'lg' => 'size-24 text-2xl',
        default => 'size-10 text-sm',
    };
@endphp

@if ($user?->hasAvatar())
    <img
        src="{{ route('profiles.photo', $user) }}?v={{ $user->updated_at?->timestamp ?? 0 }}"
        alt=""
        @if ($lazy)
            loading="lazy"
            decoding="async"
        @endif
        {{ $attributes->merge(['class' => $box.' shrink-0 rounded-full bg-navy/10 object-cover']) }}
    >
@else
    <span
        {{ $attributes->merge(['class' => $box.' inline-flex shrink-0 items-center justify-center rounded-full bg-navy/10 font-semibold text-navy']) }}
        aria-hidden="true"
    >{{ $user?->initials() ?? '?' }}</span>
@endif
