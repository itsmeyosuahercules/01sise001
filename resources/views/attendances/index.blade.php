@extends('layouts.app')

@section('title', 'Hadir Sabtu')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-tight">Hadir Sabtu</h1>
            <p class="mt-1 text-sm text-ink/60">Foto muka + lokasi hidup, hanya hari Sabtu. Rekap kelas ke dosen — bukan presensi resmi UNPAM.</p>
        </div>
        @can('export', App\Models\SaturdayAttendance::class)
            <x-btn tag="a" href="{{ route('attendances.report', ['tanggal' => $date->toDateString()]) }}">Unduh PDF</x-btn>
        @endcan
    </div>

    <p class="mt-4 text-sm text-ink/65">
        Sabtu {{ $date->translatedFormat('d F Y') }}
        @if ($isOpen)
            · <span class="text-navy">Pengiriman dibuka hari ini</span>
        @else
            · Pengiriman tertutup sampai Sabtu berikutnya
        @endif
    </p>

    @if ($isOpen)
        <form method="POST" action="{{ route('attendances.store') }}" enctype="multipart/form-data" data-attendance-form class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-5">
            @csrf
            <input type="hidden" name="latitude" data-attendance-lat>
            <input type="hidden" name="longitude" data-attendance-lng>
            <input type="hidden" name="accuracy" data-attendance-accuracy>

            <div>
                <label class="block text-sm font-medium">Foto muka</label>
                <input
                    type="file"
                    name="photo"
                    accept="image/*"
                    capture="user"
                    required
                    data-attendance-photo
                    class="mt-1.5 w-full text-sm file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-navy file:px-3 file:py-2 file:text-white"
                >
                <p class="mt-1 text-xs text-ink/50">Pakai kamera depan. Foto dan lokasi diambil bersamaan. Maksimal 4 MB.</p>
                @error('photo')
                    <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
                @enderror
                <img data-attendance-preview alt="" class="mt-3 hidden max-h-56 w-full rounded-xl object-contain bg-paper">
            </div>

            <p data-attendance-geo class="text-sm text-ink/60">Mengambil lokasi hidup… Izinkan akses lokasi di browser.</p>
            @error('latitude')
                <p class="text-sm text-accent-hot">{{ $message }}</p>
            @enderror

            @if ($mine)
                <p class="text-sm text-navy">Sudah tercatat {{ $mine->captured_at?->timezone(config('app.timezone'))->format('H:i') }}. Kirim lagi untuk mengganti foto/lokasi.</p>
            @endif

            <x-btn type="submit" data-attendance-submit disabled>Kirim hadir</x-btn>
        </form>
    @elseif ($mine)
        <div class="mt-6 max-w-xl rounded-2xl border border-line bg-card p-5">
            <p class="text-sm font-medium">Hadir kamu Sabtu ini</p>
            <img src="{{ route('attendances.photo', $mine) }}" alt="" class="mt-3 max-h-56 w-full rounded-xl object-contain bg-paper">
            <p class="mt-3 text-sm text-ink/65">{{ $mine->captured_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
            <a href="{{ $mine->mapsUrl() }}" class="mt-1 inline-block text-sm text-navy hover:underline" target="_blank" rel="noreferrer">{{ $mine->coordinateLabel() }}</a>
        </div>
    @else
        <p class="mt-6 text-sm text-ink/55">Belum ada hadir untuk Sabtu {{ $date->translatedFormat('d F Y') }}.</p>
    @endif

    @can('export', App\Models\SaturdayAttendance::class)
        <section class="mt-10">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 class="text-lg font-semibold">Rekap {{ $date->translatedFormat('d F Y') }}</h2>
                <form method="GET" action="{{ route('attendances.index') }}" class="flex flex-wrap gap-2">
                    <select name="tanggal" class="rounded-xl border border-line bg-card px-3 py-2 text-sm" onchange="this.form.requestSubmit()">
                        @foreach ($saturdays as $saturday)
                            <option value="{{ $saturday->toDateString() }}" @selected($saturday->isSameDay($date))>
                                {{ $saturday->translatedFormat('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <p class="mt-1 text-sm text-ink/55">{{ $presentCount }} hadir · {{ $members->count() - $presentCount }} tidak hadir · {{ $members->count() }} anggota</p>

            <div class="mt-4 overflow-x-auto rounded-2xl border border-line bg-card">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-line text-xs tracking-wide text-ink/45 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-medium">Foto</th>
                            <th class="px-4 py-3 font-medium">NIM</th>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Waktu</th>
                            <th class="px-4 py-3 font-medium">Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $row)
                            @php
                                $member = $row['user'];
                                $attendance = $row['attendance'];
                            @endphp
                            <tr class="border-b border-line/70 last:border-0">
                                <td class="px-4 py-3">
                                    @if ($attendance)
                                        <a href="{{ route('attendances.photo', $attendance) }}">
                                            <img src="{{ route('attendances.photo', $attendance) }}" alt="" class="size-12 rounded-lg object-cover">
                                        </a>
                                    @else
                                        <span class="text-ink/40">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $member->nim }}</td>
                                <td class="px-4 py-3">{{ $member->name }}</td>
                                <td class="px-4 py-3">
                                    @if ($attendance)
                                        <span class="text-navy">Hadir</span>
                                    @else
                                        <span class="text-accent-hot">Tidak hadir</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-ink/65">{{ $attendance?->captured_at?->timezone(config('app.timezone'))->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($attendance)
                                        <a href="{{ $attendance->mapsUrl() }}" class="text-navy hover:underline" target="_blank" rel="noreferrer">{{ $attendance->coordinateLabel() }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endcan
@endsection
