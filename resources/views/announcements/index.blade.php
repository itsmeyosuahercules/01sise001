@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Pengumuman</h1>
            <p class="mt-1 text-sm text-ink/60">Info dari KM, bukan Mentari. Buka detail = sudah dibaca.</p>
        </div>
        @can('create', App\Models\Announcement::class)
            <x-btn tag="a" href="{{ route('announcements.create') }}">Tulis pengumuman</x-btn>
        @endcan
    </div>

    <div data-filter-root class="mt-6">
        <div class="mb-4 flex flex-wrap gap-2">
            <input
                type="search"
                data-filter-q
                placeholder="Cari judul atau isi…"
                class="min-w-[16rem] flex-1 rounded-xl border border-line bg-card px-3 py-2 text-sm outline-none focus:border-navy"
            >
            <select data-filter-key="category" class="rounded-xl border border-line bg-card px-3 py-2 text-sm">
                <option value="">Semua kategori</option>
                @foreach (App\Enums\AnnouncementCategory::cases() as $category)
                    <option value="{{ $category->value }}">{{ $category->label() }}</option>
                @endforeach
            </select>
            <select data-filter-key="read" class="rounded-xl border border-line bg-card px-3 py-2 text-sm">
                <option value="">Semua status baca</option>
                <option value="0">Belum dibaca</option>
                <option value="1">Sudah dibaca</option>
            </select>
        </div>

        <div class="space-y-3">
            @forelse ($announcements as $announcement)
                <a
                    href="{{ route('announcements.show', $announcement) }}"
                    data-filter-row
                    data-search="{{ $announcement->title }} {{ $announcement->body }}"
                    data-category="{{ $announcement->category->value }}"
                    data-read="{{ $announcement->isReadBy(auth()->user()) ? '1' : '0' }}"
                    class="block rounded-2xl border border-line bg-card p-5 transition hover:border-navy/25"
                >
                    <div class="flex flex-wrap items-center gap-2 text-xs text-ink/50">
                        @if ($announcement->is_pinned)
                            <span class="rounded-full bg-gold/20 px-2 py-0.5 text-navy">Pin</span>
                        @endif
                        <span>{{ $announcement->category->label() }}</span>
                        <span>{{ $announcement->published_at?->format('d M Y') }}</span>
                        <span>{{ $announcement->reads_count }} baca</span>
                        <span>{{ $announcement->likes_count }} suka</span>
                        <span>{{ $announcement->comments_count }} komentar</span>
                        @if ($announcement->attachments_count)
                            <span>{{ $announcement->attachments_count }} lampiran</span>
                        @endif
                        @unless ($announcement->isReadBy(auth()->user()))
                            <span class="text-accent-hot">Belum dibaca</span>
                        @endunless
                    </div>
                    <p class="mt-2 text-lg font-medium">{{ $announcement->title }}</p>
                    <p class="mt-1 line-clamp-2 text-sm leading-6 text-ink/65">{{ $announcement->body }}</p>
                </a>
            @empty
                <p class="text-sm text-ink/55">Belum ada pengumuman.</p>
            @endforelse
            <p data-filter-empty class="hidden text-sm text-ink/55">Tidak ada pengumuman yang cocok.</p>
        </div>
    </div>
@endsection
