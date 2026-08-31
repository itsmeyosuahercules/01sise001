@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => $active
        ? 'cursor-pointer rounded-xl bg-navy/8 px-3 py-2 font-medium text-navy'
        : 'cursor-pointer rounded-xl px-3 py-2 text-ink/65 hover:bg-paper hover:text-navy']) }}
>
    {{ $slot }}
</a>
