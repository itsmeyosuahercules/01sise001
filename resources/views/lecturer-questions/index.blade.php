@extends('layouts.app')

@section('title', 'Pertanyaan ke dosen')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Pertanyaan ke dosen</h1>
            <p class="mt-1 text-sm text-ink/60">Ketua mengumpulkan pertanyaan, lalu mengirimkannya ke dosen.</p>
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
                class="w-full min-w-0 flex-1 rounded-xl border border-line bg-card px-3 py-2.5 text-base outline-none focus:border-navy sm:text-sm"
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

        <div class="space-y-3 md:hidden">
            @forelse ($questions as $question)
                <article
                    data-filter-row
                    data-search="{{ $question->author?->name }} {{ $question->author?->nim }} {{ $question->topic }} {{ \Illuminate\Support\Str::limit($question->body, 120, '') }}"
                    data-kind="{{ $question->kind->value }}"
                    data-status="{{ $question->status->value }}"
                    class="rounded-2xl border border-line bg-card p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $question->topic }}</p>
                            <p class="text-xs text-ink/50">{{ $question->kind->label() }} · {{ $question->author?->name }}</p>
                        </div>
                        <div class="shrink-0 text-sm text-navy" data-status-cell>
                            @include('lecturer-questions._status')
                        </div>
                    </div>
                    <p class="mt-3 text-sm whitespace-pre-wrap">{{ $question->body }}</p>
                    @if ($question->hide_name)
                        <p class="mt-2 text-xs text-ink/45">Nama tidak disebut ke dosen</p>
                    @endif
                </article>
            @empty
                <p class="text-sm text-ink/50">Belum ada pertanyaan.</p>
            @endforelse
        </div>

        <div class="mt-4 hidden overflow-x-auto rounded-2xl border border-line bg-card md:block">
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
                            data-search="{{ $question->author?->name }} {{ $question->author?->nim }} {{ $question->topic }} {{ \Illuminate\Support\Str::limit($question->body, 120, '') }}"
                            data-kind="{{ $question->kind->value }}"
                            data-status="{{ $question->status->value }}"
                            class="border-b border-line/70 last:border-0 align-top"
                        >
                            <td class="px-4 py-3">
                                {{ $question->author?->name }}
                                <div class="font-mono text-xs text-ink/45">{{ $question->author?->nim }}</div>
                                @if ($question->hide_name)
                                    <div class="mt-1 text-xs text-ink/45">Nama tidak disebut ke dosen</div>
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
                </tbody>
            </table>
        </div>
        <p data-filter-empty class="hidden px-1 py-6 text-sm text-ink/50">Tidak ada pertanyaan yang cocok.</p>
    </div>
@endsection
