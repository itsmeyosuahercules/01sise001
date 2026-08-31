@extends('layouts.app')

@section('title', 'Pertanyaan ke dosen')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Pertanyaan ke dosen</h1>
            <p class="mt-1 text-sm text-ink/60">KM kurasi jadi satu paket. Bukan forum Mentari.</p>
        </div>
        <x-btn tag="a" href="{{ route('lecturer-questions.create') }}">Tulis pertanyaan</x-btn>
    </div>

    @can('sendPackage', App\Models\LecturerQuestion::class)
        <section class="mt-6 rounded-2xl border border-line bg-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">Paket siap tempel WA</h2>
                <p class="text-xs text-ink/50"><span data-selected-count>{{ $selected->count() }}</span> dipilih</p>
            </div>
            <textarea id="wa-package" data-package-text readonly rows="8" class="mt-2 w-full rounded-xl border border-line bg-paper px-3 py-2 font-mono text-sm">{{ App\Models\LecturerQuestion::whatsappPackage($selected) }}</textarea>
            <div class="mt-2 flex flex-wrap gap-2">
                <x-btn variant="secondary" type="button" data-copy="#wa-package">Salin teks</x-btn>
                <form method="POST" action="{{ route('lecturer-questions.package') }}" data-remote="question-package" onsubmit="return confirm('Tandai paket ini sudah dikirim ke dosen? Salin dulu jika belum.')">
                    @csrf
                    <x-btn type="submit">Tandai sudah dikirim</x-btn>
                </form>
            </div>
        </section>
    @endcan

    <div data-filter-root class="mt-6">
        <div class="mb-4 flex flex-wrap gap-2">
            <input
                type="search"
                data-filter-q
                placeholder="Cari topik, nama, atau isi…"
                class="min-w-[16rem] flex-1 rounded-xl border border-line bg-card px-3 py-2 text-sm outline-none focus:border-navy"
            >
            <select data-filter-key="kind" class="rounded-xl border border-line bg-card px-3 py-2 text-sm">
                <option value="">Semua jenis</option>
                @foreach (App\Enums\LecturerQuestionKind::cases() as $kind)
                    <option value="{{ $kind->value }}">{{ $kind->label() }}</option>
                @endforeach
            </select>
            <select data-filter-key="status" class="rounded-xl border border-line bg-card px-3 py-2 text-sm">
                <option value="">Semua status</option>
                @foreach (App\Enums\LecturerQuestionStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-line bg-card">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-line text-xs tracking-wide text-ink/45 uppercase">
                    <tr>
                        <th class="px-4 py-3 font-medium">Dari</th>
                        <th class="px-4 py-3 font-medium">Jenis / untuk</th>
                        <th class="px-4 py-3 font-medium">Isi</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($questions as $question)
                        <tr
                            data-filter-row
                            data-search="{{ $question->author?->name }} {{ $question->author?->nim }} {{ $question->topic }} {{ $question->body }}"
                            data-kind="{{ $question->kind->value }}"
                            data-status="{{ $question->status->value }}"
                            class="border-b border-line/70 last:border-0 align-top"
                        >
                            <td class="px-4 py-3">
                                {{ $question->author?->name }}
                                <div class="font-mono text-xs text-ink/45">{{ $question->author?->nim }}</div>
                                @if ($question->hide_name)
                                    <div class="mt-1 text-xs text-ink/45">Nama disembunyikan di paket</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $question->kind->label() }}</div>
                                <div class="text-ink/55">{{ $question->topic }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-pre-wrap">{{ $question->body }}</td>
                            <td class="px-4 py-3" data-status-cell>
                                @include('lecturer-questions._status')
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-ink/50">Belum ada pertanyaan.</td>
                        </tr>
                    @endforelse
                    <tr data-filter-empty class="hidden">
                        <td colspan="4" class="px-4 py-8 text-ink/50">Tidak ada pertanyaan yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
