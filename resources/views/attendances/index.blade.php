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
        Sabtu {{ $checkInDate->translatedFormat('d F Y') }} · jam buka {{ $openWindowLabel }}
        @if ($isOpen)
            · <span class="text-navy">Pengiriman dibuka sekarang</span>
        @else
            · Pengiriman tertutup, tunggu Sabtu jam buka
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
                <div class="mt-1.5 overflow-hidden rounded-2xl bg-navy/5">
                    <video data-attendance-video autoplay muted playsinline class="aspect-[3/4] w-full -scale-x-100 object-cover"></video>
                    <img data-attendance-preview alt="" class="hidden aspect-[3/4] w-full object-cover">
                </div>
                <canvas data-attendance-canvas class="hidden"></canvas>
                <input type="file" name="photo" accept="image/jpeg" data-attendance-photo class="sr-only" tabindex="-1">
                <div class="mt-3 flex flex-wrap gap-2">
                    <x-btn type="button" data-attendance-snap>Jepret</x-btn>
                    <x-btn type="button" variant="secondary" data-attendance-resnap class="hidden">Jepret ulang</x-btn>
                </div>
                <p data-attendance-cam class="mt-2 text-sm text-ink/60">Menghidupkan kamera depan… Izinkan akses kamera.</p>
                <p class="mt-1 text-xs text-ink/50">Kamera depan langsung. Jepret, cek hasilnya, lalu kirim bersama lokasi hidup.</p>
                @error('photo')
                    <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
                @enderror
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
            <p class="mt-1 text-xs text-ink/45">Ubah Hadir/Tidak hadir di kolom Aksi untuk mencatat Sabtu yang lewat. Yang tanpa foto ditandai KM.</p>

            <div class="mt-4 overflow-x-auto rounded-2xl border border-line bg-card">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead class="border-b border-line text-xs tracking-wide text-ink/45 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-medium">Foto</th>
                            <th class="px-4 py-3 font-medium">NIM</th>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Waktu</th>
                            <th class="px-4 py-3 font-medium">Lokasi</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $row)
                            @php
                                $member = $row['user'];
                                $attendance = $row['attendance'];
                            @endphp
                            <tr class="border-b border-line/70 last:border-0" data-attendance-row>
                                <td class="px-4 py-3" data-attendance-photo-cell>
                                    @if ($attendance && $attendance->hasPhoto())
                                        <a href="{{ route('attendances.photo', $attendance) }}">
                                            <img src="{{ route('attendances.photo', $attendance) }}" alt="" class="size-12 rounded-lg object-cover">
                                        </a>
                                    @else
                                        <span class="text-ink/40">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $member->nim }}</td>
                                <td class="px-4 py-3">{{ $member->name }}</td>
                                <td class="px-4 py-3" data-attendance-status-cell>
                                    @if ($attendance)
                                        <span class="text-navy" data-attendance-status-label>Hadir</span>
                                        @if ($attendance->isManual())
                                            <span class="ml-1 rounded-full bg-navy/10 px-2 py-0.5 text-[10px] text-navy" data-attendance-manual-tag>dicatat KM</span>
                                        @endif
                                    @else
                                        <span class="text-accent-hot" data-attendance-status-label>Tidak hadir</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-ink/65" data-attendance-time-cell>{{ $attendance?->captured_at?->timezone(config('app.timezone'))->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-3" data-attendance-location-cell>
                                    @if ($attendance && $attendance->hasLocation())
                                        <a href="{{ $attendance->mapsUrl() }}" class="text-navy hover:underline" target="_blank" rel="noreferrer">{{ $attendance->coordinateLabel() }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('attendances.mark') }}" data-remote="attendance-mark">
                                        @csrf
                                        <input type="hidden" name="tanggal" value="{{ $date->toDateString() }}">
                                        <input type="hidden" name="user_id" value="{{ $member->id }}">
                                        <select name="present" data-previous="{{ $attendance ? '1' : '0' }}" onchange="this.form.requestSubmit()" class="rounded-lg border border-line bg-card px-2 py-1.5 text-xs">
                                            <option value="1" @selected($attendance)>Hadir</option>
                                            <option value="0" @selected(! $attendance)>Tidak hadir</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($whatsappSummary)
                <div class="mt-4 rounded-2xl border border-line bg-card p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium">Salin rekap ke WhatsApp</p>
                        <x-btn type="button" variant="secondary" data-copy="#wa-summary">Salin</x-btn>
                    </div>
                    <textarea id="wa-summary" readonly rows="6" class="mt-2 w-full rounded-xl border border-line bg-paper px-3 py-2 font-mono text-xs">{{ $whatsappSummary }}</textarea>
                </div>
            @endif
        </section>
    @endcan
@endsection
