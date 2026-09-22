@props(['href', 'active' => false, 'icon' => null, 'stacked' => false])

@php
    $icons = [
        'home' => 'M11.47 3.84a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 1-1.06 1.06l-.44-.44V19.5a1.5 1.5 0 0 1-1.5 1.5h-3a.75.75 0 0 1-.75-.75V16.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3.5a.75.75 0 0 1-.75.75h-3a1.5 1.5 0 0 1-1.5-1.5v-6.35l-.44.44a.75.75 0 1 1-1.06-1.06l8.69-8.69Z',
        'info' => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm0 5.25a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm1.125 9.75h-2.25a.75.75 0 0 1 0-1.5h.375v-4.125h-.375a.75.75 0 0 1 0-1.5h1.5a.75.75 0 0 1 .75.75v4.875h.375a.75.75 0 0 1 0 1.5Z',
        'check' => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm4.28 7.28-4.5 4.5a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 1 1 1.06-1.06l1.72 1.72 3.97-3.97a.75.75 0 1 1 1.06 1.06Z',
        'users' => 'M8.25 9a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM3.75 20.25a5.25 5.25 0 0 1 10.5 0 .75.75 0 0 1-.75.75h-9a.75.75 0 0 1-.75-.75Zm12.62-9.13a2.63 2.63 0 1 0 0-5.26 2.63 2.63 0 0 0 0 5.26Zm1.4 1.63a4.35 4.35 0 0 0-2.34.7c1.44.97 2.44 2.53 2.7 4.38h3.62a.75.75 0 0 0 .75-.75 4.35 4.35 0 0 0-4.73-4.33Z',
        'question' => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm.1 11.6a.9.9 0 1 1 0 1.8.9.9 0 0 1 0-1.8Zm.15-6.85c1.2 0 2.15.9 2.15 2.03 0 .78-.4 1.25-1.1 1.75-.55.4-.8.65-.8 1.17a.75.75 0 0 1-1.5 0c0-1.05.55-1.6 1.15-2.03.5-.36.75-.58.75-.9 0-.42-.42-.77-1-.77-.55 0-.95.3-1.05.78a.75.75 0 0 1-1.47-.28c.24-1.13 1.24-1.75 2.37-1.75Z',
        'message' => 'M4.5 4.5h15a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5h-8.19l-3.06 3.06a.75.75 0 0 1-1.28-.53V16.5H4.5A1.5 1.5 0 0 1 3 15V6a1.5 1.5 0 0 1 1.5-1.5Z',
        'profile' => 'M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 1.5c-4.14 0-7.5 2.46-7.5 5.5v.75c0 .41.34.75.75.75h13.5a.75.75 0 0 0 .75-.75v-.75c0-3.04-3.36-5.5-7.5-5.5Z',
    ];

    $iconPath = $icons[$icon] ?? null;

    $classes = $active
        ? 'font-medium text-navy'
        : 'text-ink/65 hover:text-navy';

    $layout = $stacked
        ? 'flex flex-col items-center gap-1'
        : 'flex items-center gap-3';
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group cursor-pointer rounded-xl px-3 py-2 transition-colors duration-150 '.$layout.' '.$classes]) }}
>
    @if ($iconPath)
        <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-lg transition-colors {{ $active ? 'text-navy' : 'text-ink/45 group-hover:text-navy' }}">
            <svg viewBox="0 0 24 24" fill="currentColor" class="size-5">
                <path d="{{ $iconPath }}" />
            </svg>
        </span>
    @endif
    <span>{{ $slot }}</span>
</a>
