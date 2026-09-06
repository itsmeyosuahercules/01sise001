<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Rekap Hadir Sabtu {{ $date->translatedFormat('d F Y') }} · {{ config('kelas.name') }}</title>
        <style>
            :root { color-scheme: light; }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                background: #f6f3ec;
                color: #142033;
                font-family: "Segoe UI", "Instrument Sans", sans-serif;
            }
            .toolbar {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 16px 24px;
                background: #fffcf8;
                border-bottom: 1px solid #e4ddd0;
            }
            .sheet {
                width: 210mm;
                max-width: 100%;
                margin: 24px auto;
                background: #fff;
                padding: 18mm 16mm;
                box-shadow: 0 8px 30px rgba(20, 32, 51, 0.08);
            }
            h1 { margin: 0; font-size: 20px; }
            .meta, .note { color: #5b6573; font-size: 12px; line-height: 1.5; }
            .note { margin-top: 8px; }
            .stats { display: flex; gap: 16px; margin: 16px 0 20px; font-size: 13px; }
            table { width: 100%; border-collapse: collapse; font-size: 11px; }
            th, td { border: 1px solid #d7d0c4; padding: 7px 8px; vertical-align: middle; }
            th { background: #0b2a6b; color: #fff; text-align: left; font-weight: 600; }
            td.center { text-align: center; }
            .hadir { color: #0b2a6b; font-weight: 600; }
            .absen { color: #b42318; font-weight: 600; }
            .tag { display: inline-block; margin-left: 4px; padding: 1px 6px; border-radius: 999px; background: #eef2fb; color: #0b2a6b; font-size: 9px; font-weight: 600; }
            .sign { display: flex; justify-content: flex-end; margin-top: 28px; }
            .sign-box { width: 240px; text-align: center; font-size: 12px; color: #142033; }
            .sign-pad { width: 240px; height: 110px; border: 1px dashed #b7bdc8; border-radius: 10px; background: #fff; touch-action: none; }
            .sign-img { width: 240px; height: 110px; object-fit: contain; display: none; }
            .sign-tools { display: flex; gap: 8px; justify-content: center; margin-top: 8px; }
            .sign-tools button { padding: 6px 10px; font-size: 12px; }
            .sign-name { margin-top: 8px; font-weight: 600; }
            .sign-line { margin-top: 4px; color: #5b6573; }
            .face { width: 42px; height: 42px; object-fit: cover; border-radius: 6px; }
            .mono { font-family: ui-monospace, Consolas, monospace; font-size: 10px; }
            button {
                background: #0b2a6b;
                color: #fff;
                border: 0;
                border-radius: 10px;
                padding: 8px 14px;
                font-size: 14px;
                cursor: pointer;
            }
            a.back { color: #0b2a6b; font-size: 14px; }
            @media print {
                body { background: #fff; }
                .toolbar { display: none; }
                .sheet { margin: 0; width: auto; box-shadow: none; padding: 0; }
                .sign-tools, .sign-pad.is-empty { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="toolbar">
            <a class="back" href="{{ route('attendances.index', ['tanggal' => $date->toDateString()]) }}">Kembali ke hadir</a>
            <button type="button" onclick="window.print()">Unduh / cetak PDF</button>
        </div>

        <article class="sheet">
            <h1>Rekap Hadir Sabtu · {{ config('kelas.name') }}</h1>
            <p class="meta">
                {{ $date->translatedFormat('l, d F Y') }}
                · Dicetak {{ $generatedAt->timezone(config('app.timezone'))->translatedFormat('d M Y H:i') }}
                · Oleh {{ $officer->name }} ({{ $officer->role->label() }})
            </p>
            <p class="note">Rekap kehadiran kelas untuk dosen. Bukan presensi resmi UNPAM / SIAKAD.</p>

            <div class="stats">
                <span><strong>{{ $presentCount }}</strong> hadir</span>
                <span><strong>{{ $rows->count() - $presentCount }}</strong> tidak hadir</span>
                <span><strong>{{ $rows->count() }}</strong> anggota</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 28px">No</th>
                        <th style="width: 56px">Foto</th>
                        <th style="width: 110px">NIM</th>
                        <th>Nama</th>
                        <th style="width: 88px">Status</th>
                        <th style="width: 54px">Jam</th>
                        <th>Lokasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $index => $row)
                        @php
                            $member = $row['user'];
                            $attendance = $row['attendance'];
                        @endphp
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td class="center">
                                @if ($attendance?->photoDataUri())
                                    <img class="face" src="{{ $attendance->photoDataUri() }}" alt="">
                                @else
                                    —
                                @endif
                            </td>
                            <td class="mono">{{ $member->nim }}</td>
                            <td>{{ $member->name }}</td>
                            <td class="{{ $attendance ? 'hadir' : 'absen' }}">
                                {{ $attendance ? 'Hadir' : 'Tidak hadir' }}
                                @if ($attendance?->isManual())
                                    <span class="tag">dicatat KM</span>
                                @endif
                            </td>
                            <td>{{ $attendance?->captured_at?->timezone(config('app.timezone'))->format('H:i') ?? '—' }}</td>
                            <td class="mono">
                                @if ($attendance?->hasLocation())
                                    {{ $attendance->coordinateLabel() }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="sign">
                <div class="sign-box">
                    <p class="sign-line">{{ config('kelas.name') }}, {{ $generatedAt->timezone(config('app.timezone'))->translatedFormat('d F Y') }}</p>
                    <canvas class="sign-pad is-empty" data-sign-pad width="240" height="110"></canvas>
                    <img class="sign-img" data-sign-img alt="Tanda tangan">
                    <div class="sign-tools">
                        <button type="button" data-sign-clear>Hapus tanda tangan</button>
                    </div>
                    <p class="sign-name">{{ $officer->name }}</p>
                    <p class="sign-line">{{ $officer->role->label() }} · tanda tangan elektronik</p>
                </div>
            </div>
        </article>

        <script>
            (function () {
                var pad = document.querySelector('[data-sign-pad]');
                var img = document.querySelector('[data-sign-img]');
                var clearBtn = document.querySelector('[data-sign-clear]');
                if (! pad) { return; }

                var key = 'esign:{{ $officer->nim }}';
                var ctx = pad.getContext('2d');
                ctx.lineWidth = 2.2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#142033';
                var drawing = false;
                var dirty = false;

                function pos(event) {
                    var rect = pad.getBoundingClientRect();
                    var point = event.touches ? event.touches[0] : event;
                    return {
                        x: (point.clientX - rect.left) * (pad.width / rect.width),
                        y: (point.clientY - rect.top) * (pad.height / rect.height),
                    };
                }
                function start(event) { event.preventDefault(); drawing = true; var p = pos(event); ctx.beginPath(); ctx.moveTo(p.x, p.y); }
                function move(event) { if (! drawing) { return; } event.preventDefault(); var p = pos(event); ctx.lineTo(p.x, p.y); ctx.stroke(); dirty = true; pad.classList.remove('is-empty'); }
                function end() { if (drawing && dirty) { try { localStorage.setItem(key, pad.toDataURL('image/png')); } catch (e) {} } drawing = false; }

                pad.addEventListener('mousedown', start);
                pad.addEventListener('mousemove', move);
                window.addEventListener('mouseup', end);
                pad.addEventListener('touchstart', start, { passive: false });
                pad.addEventListener('touchmove', move, { passive: false });
                pad.addEventListener('touchend', end);

                var saved = null;
                try { saved = localStorage.getItem(key); } catch (e) {}
                if (saved) {
                    img.src = saved;
                    img.style.display = 'block';
                    pad.style.display = 'none';
                }

                clearBtn.addEventListener('click', function () {
                    ctx.clearRect(0, 0, pad.width, pad.height);
                    pad.classList.add('is-empty');
                    pad.style.display = 'block';
                    img.style.display = 'none';
                    dirty = false;
                    try { localStorage.removeItem(key); } catch (e) {}
                });
            })();
        </script>
    </body>
</html>
