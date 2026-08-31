<article data-comment class="rounded-2xl border border-line bg-card p-4">
    <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-start gap-3">
            @if ($comment->author)
                <a href="{{ route('profiles.show', $comment->author) }}" class="mt-0.5 shrink-0">
                    <x-avatar :user="$comment->author" />
                </a>
            @endif
            <div class="min-w-0">
                @if ($comment->author)
                    <a href="{{ route('profiles.show', $comment->author) }}" class="text-sm font-medium hover:text-navy">{{ $comment->author->name }}</a>
                    @if ($comment->author->bio)
                        <p class="line-clamp-1 text-xs text-ink/55">{{ $comment->author->bio }}</p>
                    @endif
                @else
                    <p class="text-sm font-medium">Anggota</p>
                @endif
                <p class="text-xs text-ink/45">{{ $comment->author?->nim }} · {{ $comment->created_at?->format('d M Y H:i') }}</p>
            </div>
        </div>
        @can('delete', $comment)
            <form method="POST" action="{{ route('announcements.comments.destroy', [$announcement ?? $comment->announcement, $comment]) }}" data-remote="comment-delete" onsubmit="return confirm('Hapus komentar ini?')">
                @csrf
                @method('DELETE')
                <x-btn variant="ghost" type="submit" class="text-xs">Hapus</x-btn>
            </form>
        @endcan
    </div>
    <p class="mt-2 min-w-0 whitespace-pre-wrap break-words text-sm leading-6 [overflow-wrap:anywhere]">{{ $comment->body }}</p>
</article>
