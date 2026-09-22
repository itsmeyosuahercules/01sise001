<?php

namespace App\Http\Controllers;

use App\Actions\StoreSaturdayAttendance;
use App\Http\Requests\StoreSaturdayAttendanceRequest;
use App\Models\SaturdayAttendance;
use App\Models\User;
use App\Support\AttendanceLocationReview;
use App\Support\SaturdayAttendanceWindow;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaturdayAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SaturdayAttendance::class);

        try {
            $date = SaturdayAttendanceWindow::resolveDate($request->string('tanggal')->toString() ?: null);
        } catch (InvalidArgumentException) {
            $date = SaturdayAttendanceWindow::currentOrLatestSaturday();
        }

        $user = $request->user();
        $mine = SaturdayAttendance::query()
            ->whereBelongsTo($user)
            ->whereDate('attended_on', $date->toDateString())
            ->first();

        $members = collect();
        $presentCount = 0;
        $whatsappSummary = null;
        $whatsappAbsent = null;

        if ($user->role->canReviewAttendances()) {
            $attendances = SaturdayAttendance::query()
                ->whereDate('attended_on', $date->toDateString())
                ->get()
                ->keyBy('user_id');

            $members = User::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'nim', 'name'])
                ->map(function (User $member) use ($attendances): array {
                    $attendance = $attendances->get($member->id);

                    return [
                        'user' => $member,
                        'attendance' => $attendance,
                        'warnings' => $attendance instanceof SaturdayAttendance
                            ? AttendanceLocationReview::warnings($attendance, $attendances)
                            : [],
                    ];
                });

            $presentCount = $attendances->count();
            $whatsappSummary = $this->whatsappSummary($date, $members, $presentCount);
            $whatsappAbsent = $this->whatsappAbsent($date, $members);
        }

        return view('attendances.index', [
            'date' => $date,
            'checkInDate' => SaturdayAttendanceWindow::currentOrLatestSaturday(),
            'isOpen' => SaturdayAttendanceWindow::isOpen(),
            'openWindowLabel' => SaturdayAttendanceWindow::openWindowLabel(),
            'canManage' => $user->role->canReviewAttendances(),
            'mine' => $mine,
            'members' => $members,
            'presentCount' => $presentCount,
            'whatsappSummary' => $whatsappSummary,
            'whatsappAbsent' => $whatsappAbsent,
            'saturdays' => SaturdayAttendanceWindow::recentSaturdays(),
        ]);
    }

    public function mark(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('manage', SaturdayAttendance::class);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'present' => ['required', 'boolean'],
        ]);

        try {
            $date = SaturdayAttendanceWindow::resolveDate($validated['tanggal']);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['tanggal' => $exception->getMessage()]);
        }

        $member = User::query()->findOrFail($validated['user_id']);
        $present = $request->boolean('present');
        $existing = $this->applyMark($member, $date, $present, $request->user());

        $message = 'Kehadiran '.$member->name.' diperbarui.';

        return $this->respond($request, [
            'present' => $present,
            'is_manual' => $existing?->isManual() ?? false,
            'time' => $existing?->captured_at?->timezone(config('app.timezone'))->format('H:i'),
            'note' => $existing?->note ?? '',
            'message' => $message,
        ], redirect()
            ->route('attendances.index', ['tanggal' => $date->toDateString()])
            ->with('status', $message));
    }

    public function markBulk(Request $request): RedirectResponse
    {
        $this->authorize('manage', SaturdayAttendance::class);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'present' => ['required', 'boolean'],
        ], [
            'user_ids.required' => 'Pilih minimal satu mahasiswa.',
            'user_ids.min' => 'Pilih minimal satu mahasiswa.',
        ]);

        try {
            $date = SaturdayAttendanceWindow::resolveDate($validated['tanggal']);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['tanggal' => $exception->getMessage()]);
        }

        $present = $request->boolean('present');
        $members = User::query()->whereIn('id', $validated['user_ids'])->get();

        foreach ($members as $member) {
            $this->applyMark($member, $date, $present, $request->user());
        }

        $label = $present ? 'hadir' : 'tidak hadir';

        return redirect()
            ->route('attendances.index', ['tanggal' => $date->toDateString()])
            ->with('status', $members->count().' mahasiswa ditandai '.$label.'.');
    }

    public function note(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('manage', SaturdayAttendance::class);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:280'],
        ]);

        try {
            $date = SaturdayAttendanceWindow::resolveDate($validated['tanggal']);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['tanggal' => $exception->getMessage()]);
        }

        $member = User::query()->findOrFail($validated['user_id']);
        $attendance = SaturdayAttendance::query()
            ->whereBelongsTo($member)
            ->whereDate('attended_on', $date->toDateString())
            ->first();

        if (! $attendance instanceof SaturdayAttendance) {
            return back()->withErrors(['note' => 'Catatan hanya untuk yang sudah hadir.']);
        }

        $note = trim($validated['note'] ?? '');
        $attendance->update([
            'note' => $note === '' ? null : $note,
        ]);

        $message = 'Catatan '.$member->name.' disimpan.';

        return $this->respond($request, [
            'note' => $attendance->note ?? '',
            'message' => $message,
        ], redirect()
            ->route('attendances.index', ['tanggal' => $date->toDateString()])
            ->with('status', $message));
    }

    public function store(StoreSaturdayAttendanceRequest $request, StoreSaturdayAttendance $store): JsonResponse|RedirectResponse
    {
        $store->handle($request->user(), $request->file('photo'), [
            'latitude' => (float) $request->input('latitude'),
            'longitude' => (float) $request->input('longitude'),
            'accuracy' => $request->filled('accuracy') ? (int) round((float) $request->input('accuracy')) : null,
        ]);

        $message = 'Hadir Sabtu tercatat. Ini rekap kelas ke dosen, bukan presensi UNPAM.';

        return $this->respond($request, [
            'message' => $message,
            'redirect' => route('attendances.index'),
        ], redirect()
            ->route('attendances.index')
            ->with('status', $message));
    }

    public function photo(SaturdayAttendance $saturdayAttendance): StreamedResponse
    {
        $this->authorize('view', $saturdayAttendance);

        abort_unless(
            filled($saturdayAttendance->photo_path) && Storage::disk('local')->exists($saturdayAttendance->photo_path),
            404,
        );

        return Storage::disk('local')->response($saturdayAttendance->photo_path, 'hadir.jpg', [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function report(Request $request): View
    {
        $this->authorize('export', SaturdayAttendance::class);

        try {
            $date = SaturdayAttendanceWindow::resolveDate($request->string('tanggal')->toString() ?: null);
        } catch (InvalidArgumentException $exception) {
            abort(422, $exception->getMessage());
        }

        $attendances = SaturdayAttendance::query()
            ->whereDate('attended_on', $date->toDateString())
            ->get()
            ->keyBy('user_id');

        $rows = User::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'nim', 'name'])
            ->map(function (User $member) use ($attendances): array {
                return [
                    'user' => $member,
                    'attendance' => $attendances->get($member->id),
                ];
            });

        return view('attendances.report', [
            'date' => $date,
            'rows' => $rows,
            'presentCount' => $attendances->count(),
            'generatedAt' => now(),
            'officer' => $request->user(),
        ]);
    }

    /**
     * @param  Collection<int, array{user: User, attendance: ?SaturdayAttendance}>  $members
     */
    private function whatsappSummary(CarbonImmutable $date, Collection $members, int $presentCount): string
    {
        $present = $members->filter(fn (array $row): bool => $row['attendance'] !== null)->values();
        $absent = $members->filter(fn (array $row): bool => $row['attendance'] === null)->values();

        $lines = [
            'Daftar hadir Sabtu '.$date->translatedFormat('d M Y').' — '.config('kelas.name'),
            'Hadir '.$presentCount.'/'.$members->count(),
            '',
            'Hadir:',
        ];

        foreach ($present as $index => $row) {
            $lines[] = ($index + 1).'. '.$row['user']->name;
        }

        if ($present->isEmpty()) {
            $lines[] = '-';
        }

        $lines[] = '';
        $lines[] = 'Tidak hadir:';

        foreach ($absent as $index => $row) {
            $lines[] = ($index + 1).'. '.$row['user']->name;
        }

        if ($absent->isEmpty()) {
            $lines[] = '-';
        }

        $lines[] = '';
        $lines[] = 'Catatan kelas untuk dosen.';

        return implode("\n", $lines);
    }

    /**
     * @param  Collection<int, array{user: User, attendance: ?SaturdayAttendance}>  $members
     */
    private function whatsappAbsent(CarbonImmutable $date, Collection $members): string
    {
        $absent = $members->filter(fn (array $row): bool => $row['attendance'] === null)->values();
        $lines = [
            'Belum hadir Sabtu '.$date->translatedFormat('d M Y').' ('.$absent->count().' orang)',
            'Buka lewat browser HP, jangan dari dalam WhatsApp. Ketuk Izinkan kamera & lokasi.',
            '',
        ];

        foreach ($absent as $index => $row) {
            $lines[] = ($index + 1).'. '.$row['user']->name;
        }

        if ($absent->isEmpty()) {
            $lines[] = 'Semua sudah hadir.';
        }

        return implode("\n", $lines);
    }

    private function applyMark(User $member, CarbonImmutable $date, bool $present, User $officer): ?SaturdayAttendance
    {
        $existing = SaturdayAttendance::query()
            ->whereBelongsTo($member)
            ->whereDate('attended_on', $date->toDateString())
            ->first();

        if ($present) {
            if ($existing instanceof SaturdayAttendance) {
                return $existing;
            }

            return SaturdayAttendance::query()->create([
                'user_id' => $member->id,
                'marked_by' => $officer->id,
                'attended_on' => $date->toDateString(),
                'note' => 'Ditandai hadir oleh '.$officer->name,
            ]);
        }

        $existing?->delete();

        return null;
    }
}
