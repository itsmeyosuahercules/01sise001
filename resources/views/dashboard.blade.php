@extends('layouts.app')

@section('title', 'Dasbor')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Hari ini</h1>
    <p class="mt-1 text-sm text-ink/60">Koordinasi kelas. Jadwal, nilai, dan tugas dosen tetap di UNPAM / Mentari.</p>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-line bg-card p-5">
            <p class="text-xs tracking-wide text-ink/45 uppercase">Belum dibaca</p>
            <p class="mt-2 text-3xl font-semibold text-navy">{{ $unreadCount }}</p>
        </div>
        <a href="{{ route('attendances.index') }}" class="rounded-2xl border border-line bg-card p-5 transition hover:border-navy/25">
            <p class="text-xs tracking-wide text-ink/45 uppercase">Hadir Sabtu</p>
            @can('export', App\Models\SaturdayAttendance::class)
                <p class="mt-2 text-3xl font-semibold text-navy">{{ $saturdayPresent }}/{{ $memberCount }}</p>
            @else
                <p class="mt-2 text-3xl font-semibold text-navy">{{ $saturdayMine ? 'Hadir' : 'Belum' }}</p>
            @endcan
        </a>
        <a href="{{ route('lecturer-questions.index') }}" class="rounded-2xl border border-line bg-card p-5 transition hover:border-navy/25">
            <p class="text-xs tracking-wide text-ink/45 uppercase">Pertanyaan baru</p>
            <p class="mt-2 text-3xl font-semibold text-navy">{{ $pendingQuestions }}</p>
        </a>
        <div class="rounded-2xl border border-line bg-card p-5">
            <p class="text-xs tracking-wide text-ink/45 uppercase">Anggota</p>
            <p class="mt-2 text-3xl font-semibold text-navy">{{ $memberCount }}</p>
        </div>
    </div>

    @if ($memberCount === 0)
        <div class="mt-6 rounded-2xl border border-gold/40 bg-card p-5 text-sm">
            <p class="font-medium">Roster masih kosong.</p>
            <p class="mt-1 text-ink/65">Impor CSV <code>nim,nama</code>. NIM yang sudah ada dilewati.</p>
            @can('import', App\Models\User::class)
                <x-btn tag="a" href="{{ route('mahasiswa-imports.create') }}" class="mt-4">Impor mahasiswa</x-btn>
            @endcan
        </div>
    @endif

    <section class="mt-10">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Pengumuman</h2>
            <a href="{{ route('announcements.index') }}" class="text-sm text-navy hover:underline">Lihat semua</a>
        </div>
        <div class="mt-3 space-y-3">
            @forelse ($announcements as $announcement)
                <a href="{{ route('announcements.show', $announcement) }}" class="block rounded-2xl border border-line bg-card p-5 transition hover:border-navy/25">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-ink/50">
                        @if ($announcement->is_pinned)
                            <span class="rounded-full bg-gold/20 px-2 py-0.5 text-navy">Pin</span>
                        @endif
                        <span>{{ $announcement->category->label() }}</span>
                        @if ($announcement->isReadBy(auth()->user()))
                            <span>Sudah dibaca</span>
                        @else
                            <span class="text-accent-hot">Belum dibaca</span>
                        @endif
                        <span>{{ $announcement->likes_count }} suka</span>
                        <span>{{ $announcement->comments_count }} komentar</span>
                        @if ($announcement->attachments_count)
                            <span>{{ $announcement->attachments_count }} lampiran</span>
                        @endif
                    </div>
                    <p class="mt-2 font-medium">{{ $announcement->title }}</p>
                </a>
            @empty
                <p class="text-sm text-ink/55">Belum ada pengumuman.</p>
            @endforelse
        </div>
    </section>
@endsection
