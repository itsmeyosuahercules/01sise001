@props(['variant' => 'primary', 'tag' => 'button'])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-line bg-card text-ink hover:bg-paper',
        'ghost' => 'text-ink/65 hover:text-navy',
        'danger' => 'border border-line text-accent-hot hover:bg-accent-hot/8',
        'gold' => 'bg-gold text-navy hover:bg-gold/90',
        default => 'bg-navy text-white hover:bg-navy/90',
    };
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => 'inline-flex cursor-pointer items-center justify-center rounded-xl px-3.5 py-2 text-sm font-medium transition '.$classes]) }}>
    {{ $slot }}
</{{ $tag }}>
