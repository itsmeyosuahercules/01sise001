@php
    $likers = $announcement->likes->map->user->filter();
    $names = $likers->take(3)->pluck('name')->filter();
    $rest = max($likers->count() - $names->count(), 0);
    $summary = $names->implode(', ');

    if ($rest > 0) {
        $summary .= ' dan '.$rest.' lainnya';
    }
@endphp

<div data-like-list class="flex min-h-8 flex-wrap items-center gap-2">
    @if ($likers->isNotEmpty())
        <div class="flex items-center -space-x-2">
            @foreach ($likers->take(8) as $liker)
                <a href="{{ route('profiles.show', $liker) }}" title="{{ $liker->name }}" class="block">
                    <x-avatar :user="$liker" size="sm" class="ring-2 ring-card" />
                </a>
            @endforeach
        </div>
        <p class="text-sm text-ink/60">{{ $summary }} menyukai</p>
    @endif
</div>
