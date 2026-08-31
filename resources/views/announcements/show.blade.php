@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs text-ink/50">
                {{ $announcement->category->label() }}
                @if ($announcement->is_pinned) · Pin @endif
                · {{ $announcement->published_at?->format('d M Y H:i') }}
                · Sudah dibaca otomatis
            </p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight break-words [overflow-wrap:anywhere]">{{ $announcement->title }}</h1>
            @if ($announcement->author)
                <a href="{{ route('profiles.show', $announcement->author) }}" class="mt-2 flex items-center gap-2 text-sm text-ink/55 hover:text-navy">
                    <x-avatar :user="$announcement->author" size="sm" />
                    <span>{{ $announcement->author->name }}</span>
                </a>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $announcement)
                <x-btn tag="a" variant="secondary" href="{{ route('announcements.edit', $announcement) }}">Ubah</x-btn>
            @endcan
            @can('delete', $announcement)
                <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                    @csrf
                    @method('DELETE')
                    <x-btn variant="danger" type="submit">Hapus</x-btn>
                </form>
            @endcan
        </div>
    </div>

    <article class="mt-6 min-w-0 max-w-full overflow-hidden whitespace-pre-wrap break-words rounded-2xl border border-line bg-card p-4 text-sm leading-7 [overflow-wrap:anywhere] sm:p-6">{{ $announcement->body }}</article>

    @if ($announcement->attachments->isNotEmpty())
        @php
            $images = $announcement->attachments->filter->isImage();
            $files = $announcement->attachments->reject->isImage();
        @endphp
        <section class="mt-4 space-y-4">
            @if ($images->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($images as $attachment)
                        <a href="{{ route('announcements.attachments.show', [$announcement, $attachment]) }}" class="overflow-hidden rounded-2xl border border-line bg-card">
                            <img
                                src="{{ route('announcements.attachments.show', [$announcement, $attachment]) }}"
                                alt="{{ $attachment->original_name }}"
                                class="max-h-80 w-full object-contain bg-paper"
                            >
                            <p class="truncate px-3 py-2 text-xs text-ink/55">{{ $attachment->original_name }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
            @if ($files->isNotEmpty())
                <div class="rounded-2xl border border-line bg-card p-5">
                    <h2 class="text-sm font-semibold">File lampiran</h2>
                    <ul class="mt-2 space-y-2 text-sm">
                        @foreach ($files as $attachment)
                            <li>
                                <a href="{{ route('announcements.attachments.show', [$announcement, $attachment]) }}" class="break-all text-navy hover:underline [overflow-wrap:anywhere]">
                                    {{ $attachment->original_name }}
                                </a>
                                <span class="text-ink/45">· {{ $attachment->humanSize() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    @endif

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('announcements.likes.store', $announcement) }}" data-remote="like">
            @csrf
            <x-btn
                variant="{{ $announcement->isLikedBy(auth()->user()) ? 'gold' : 'secondary' }}"
                type="submit"
                data-like-button
            >
                {{ $announcement->isLikedBy(auth()->user()) ? 'Disukai' : 'Suka' }}
                · {{ $announcement->likes_count }}
            </x-btn>
        </form>
        <span class="text-sm text-ink/50">
            <span data-comments-count>{{ $announcement->comments_count }}</span> komentar · {{ $announcement->reads_count }} sudah baca
        </span>
    </div>

    <div class="mt-3">
        @include('announcements._likes')
    </div>

    <section class="mt-6 min-w-0 max-w-full overflow-hidden rounded-2xl border border-line bg-card p-4 sm:p-5">
        <h2 class="text-sm font-semibold">Salin ke WhatsApp</h2>
        <textarea id="wa-text" readonly rows="7" class="mt-2 w-full min-w-0 max-w-full overflow-x-auto whitespace-pre-wrap break-words rounded-xl border border-line bg-paper px-3 py-2 font-mono text-sm [overflow-wrap:anywhere]">{{ $announcement->whatsappText() }}</textarea>
        <x-btn variant="secondary" type="button" data-copy="#wa-text" class="mt-2">Salin teks</x-btn>
    </section>

    <section id="komentar" class="mt-8">
        <h2 class="text-lg font-semibold">Komentar</h2>

        <form method="POST" action="{{ route('announcements.comments.store', $announcement) }}" data-remote="comment" class="mt-4 rounded-2xl border border-line bg-card p-5">
            @csrf
            <div class="flex items-start gap-3">
                <x-avatar :user="auth()->user()" />
                <div class="min-w-0 flex-1">
                    <label class="block text-sm font-medium">Tulis komentar</label>
                    <textarea name="body" rows="3" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2 text-sm outline-none focus:border-navy">{{ old('body') }}</textarea>
                </div>
            </div>
            @error('body')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
            <x-btn type="submit" class="mt-3">Kirim</x-btn>
        </form>

        <div data-comment-list class="mt-4 space-y-3">
            @forelse ($announcement->comments as $comment)
                @include('announcements._comment')
            @empty
                <p data-comment-empty class="text-sm text-ink/55">Belum ada komentar.</p>
            @endforelse
        </div>
    </section>

    @if (auth()->user()->role->canPublishAnnouncements())
        <section class="mt-8">
            <h2 class="text-sm font-semibold">Sudah baca ({{ $announcement->reads->count() }})</h2>
            <ul class="mt-2 space-y-2 text-sm text-ink/65">
                @forelse ($announcement->reads as $read)
                    <li class="flex items-center gap-2">
                        @if ($read->user)
                            <a href="{{ route('profiles.show', $read->user) }}" class="flex items-center gap-2 hover:text-navy">
                                <x-avatar :user="$read->user" size="sm" />
                                <span>{{ $read->user->name }} · {{ $read->user->nim }}</span>
                            </a>
                        @else
                            <span>Anggota</span>
                        @endif
                    </li>
                @empty
                    <li>Belum ada.</li>
                @endforelse
            </ul>
        </section>
    @endif
@endsection
