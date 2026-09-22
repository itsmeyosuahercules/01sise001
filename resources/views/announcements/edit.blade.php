@extends('layouts.app')

@section('title', 'Ubah pengumuman')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Ubah pengumuman</h1>

    <form method="POST" action="{{ route('announcements.update', $announcement) }}" enctype="multipart/form-data" class="mt-6 min-w-0 max-w-xl space-y-4 overflow-hidden rounded-2xl border border-line bg-card p-4 sm:p-6">
        @csrf
        @method('PUT')
        @include('announcements._form')
        <x-btn type="submit">Simpan</x-btn>
    </form>

    @if ($announcement->attachments->isNotEmpty())
        <section class="mt-4 max-w-xl rounded-2xl border border-line bg-card p-6">
            <h2 class="text-sm font-semibold">File sekarang</h2>
            <p class="mt-1 text-xs text-ink/50">Hapus berkas di sini. Pengumumannya tetap ada.</p>
            <ul class="mt-3 space-y-2">
                @foreach ($announcement->attachments as $attachment)
                    <li class="flex min-w-0 items-center justify-between gap-3 rounded-xl border border-line bg-paper px-3 py-2 text-sm">
                        <a href="{{ route('announcements.attachments.show', [$announcement, $attachment]) }}" class="min-w-0 break-all text-navy hover:underline [overflow-wrap:anywhere]">
                            {{ $attachment->original_name }}
                            <span class="text-ink/45">· {{ $attachment->humanSize() }}</span>
                        </a>
                        <form method="POST" action="{{ route('announcements.attachments.destroy', [$announcement, $attachment]) }}" onsubmit="return confirm('Hapus file ini? Pengumuman tetap ada.')" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <x-btn variant="ghost" type="submit" class="!px-2 !py-1 text-xs">Hapus</x-btn>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
