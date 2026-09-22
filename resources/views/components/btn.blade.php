@props(['variant' => 'primary', 'tag' => 'button'])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-line bg-card text-ink hover:border-navy/30 hover:bg-paper active:bg-paper',
        'ghost' => 'text-ink/65 hover:text-navy hover:bg-navy/5 active:bg-navy/10',
        'danger' => 'border border-line text-accent-hot hover:border-accent-hot/40 hover:bg-accent-hot/8',
        'gold' => 'bg-gold text-navy shadow-sm hover:bg-gold/90 hover:shadow-md',
        default => 'bg-navy text-white shadow-sm hover:bg-navy/90 hover:shadow-md',
    };
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => 'inline-flex min-h-11 cursor-pointer items-center justify-center rounded-xl px-3.5 py-2 text-sm font-medium transition active:scale-[0.97] '.$classes]) }}>
    {{ $slot }}
</{{ $tag }}>
