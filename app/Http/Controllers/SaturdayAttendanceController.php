<?php

namespace App\Http\Controllers;

use App\Actions\StoreSaturdayAttendance;
use App\Http\Requests\StoreSaturdayAttendanceRequest;
use App\Models\SaturdayAttendance;
use App\Models\User;
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

        if ($user->role->canReviewAttendances()) {
            $attendances = SaturdayAttendance::query()
                ->with('user')
                ->whereDate('attended_on', $date->toDateString())
                ->get()
                ->keyBy('user_id');

            $members = User::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get()
                ->map(function (User $member) use ($attendances): array {
                    return [
                        'user' => $member,
                        'attendance' => $attendances->get($member->id),
                    ];
                });

            $presentCount = $attendances->count();
            $whatsappSummary = $this->whatsappSummary($date, $members, $presentCount);
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
        $existing = SaturdayAttendance::query()
            ->whereBelongsTo($member)
            ->whereDate('attended_on', $date->toDateString())
            ->first();
        $present = $request->boolean('present');

        if ($present) {
            if (! $existing instanceof SaturdayAttendance) {
                $existing = SaturdayAttendance::query()->create([
                    'user_id' => $member->id,
                    'marked_by' => $request->user()->id,
                    'attended_on' => $date->toDateString(),
                    'note' => 'Ditandai hadir oleh '.$request->user()->name,
                ]);
            }
        } elseif ($existing instanceof SaturdayAttendance) {
            $existing->delete();
            $existing = null;
        }

        $message = 'Kehadiran '.$member->name.' diperbarui.';

        return $this->respond($request, [
            'present' => $present,
            'is_manual' => $existing?->isManual() ?? false,
            'time' => $existing?->captured_at?->timezone(config('app.timezone'))->format('H:i'),
            'message' => $message,
        ], redirect()
            ->route('attendances.index', ['tanggal' => $date->toDateString()])
            ->with('status', $message));
    }

    public function store(StoreSaturdayAttendanceRequest $request, StoreSaturdayAttendance $store): RedirectResponse
    {
        $store->handle($request->user(), $request->file('photo'), [
            'latitude' => (float) $request->input('latitude'),
            'longitude' => (float) $request->input('longitude'),
            'accuracy' => $request->filled('accuracy') ? (int) round((float) $request->input('accuracy')) : null,
        ]);

        return redirect()
            ->route('attendances.index')
            ->with('status', 'Hadir Sabtu tercatat. Ini rekap kelas ke dosen, bukan presensi UNPAM.');
    }

    public function photo(SaturdayAttendance $saturdayAttendance): StreamedResponse
    {
        $this->authorize('view', $saturdayAttendance);

        abort_unless(
            filled($saturdayAttendance->photo_path) && Storage::disk('local')->exists($saturdayAttendance->photo_path),
            404,
        );

        return Storage::disk('local')->response($saturdayAttendance->photo_path, $saturdayAttendance->user?->name ?? 'hadir');
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
            ->with('user')
            ->whereDate('attended_on', $date->toDateString())
            ->get()
            ->keyBy('user_id');

        $rows = User::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get()
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
            'Rekap Hadir Sabtu '.$date->translatedFormat('d M Y').' — '.config('kelas.name'),
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
        $lines[] = 'Rekap kelas untuk dosen, bukan presensi resmi UNPAM.';

        return implode("\n", $lines);
    }
}
