@extends('layouts.app')

@section('title', 'Izin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Izin & sakit</h1>
            <p class="mt-1 text-sm text-ink/60">Anggota mengajukan. KM yang setujui atau tolak, lalu merekap ke dosen.</p>
        </div>
        <div class="flex gap-2">
            <x-btn tag="a" href="{{ route('absence-requests.create') }}">Ajukan izin</x-btn>
            @can('export', App\Models\AbsenceRequest::class)
                <x-btn tag="a" variant="secondary" href="{{ route('absence-requests.export') }}">Unduh rekap</x-btn>
            @endcan
        </div>
    </div>

    <div data-filter-root class="mt-6">
        <div class="mb-4 flex flex-wrap gap-2">
            <input
                type="search"
                data-filter-q
                placeholder="Cari nama, NIM, atau alasan…"
                class="min-w-[16rem] flex-1 rounded-xl border border-line bg-card px-3 py-2 text-sm outline-none focus:border-navy"
            >
            <select data-filter-key="type" class="rounded-xl border border-line bg-card px-3 py-2 text-sm">
                <option value="">Semua jenis</option>
                @foreach (App\Enums\AbsenceType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </select>
            <select data-filter-key="status" class="rounded-xl border border-line bg-card px-3 py-2 text-sm">
                <option value="">Semua status</option>
                @foreach (App\Enums\AbsenceStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-line bg-card">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="border-b border-line text-xs tracking-wide text-ink/45 uppercase">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Jenis</th>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Alasan</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absences as $absence)
                        <tr
                            data-filter-row
                            data-search="{{ $absence->student?->name }} {{ $absence->student?->nim }} {{ $absence->reason }}"
                            data-type="{{ $absence->type->value }}"
                            data-status="{{ $absence->status->value }}"
                            class="border-b border-line/70 last:border-0"
                        >
                            <td class="px-4 py-3">
                                {{ $absence->student?->name }}
                                <div class="font-mono text-xs text-ink/45">{{ $absence->student?->nim }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $absence->type->label() }}</td>
                            <td class="px-4 py-3">{{ $absence->starts_on->format('d M') }} – {{ $absence->ends_on->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $absence->reason }}</td>
                            <td class="px-4 py-3" data-status-cell>
                                <span data-status-label>{{ $absence->status->label() }}</span>
                                @can('review', $absence)
                                    @if ($absence->status === App\Enums\AbsenceStatus::Pending)
                                        <form method="POST" action="{{ route('absence-requests.update', $absence) }}" data-remote="review" data-review-actions class="mt-2 flex gap-1">
                                            @csrf
                                            @method('PUT')
                                            <x-btn type="submit" name="status" value="disetujui" class="!px-2 !py-1 text-xs">Setujui</x-btn>
                                            <x-btn variant="secondary" type="submit" name="status" value="ditolak" class="!px-2 !py-1 text-xs">Tolak</x-btn>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-ink/50">Belum ada izin.</td>
                        </tr>
                    @endforelse
                    <tr data-filter-empty class="hidden">
                        <td colspan="5" class="px-4 py-8 text-ink/50">Tidak ada izin yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
