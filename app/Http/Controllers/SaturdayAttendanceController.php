<?php

namespace App\Http\Controllers;

use App\Actions\StoreSaturdayAttendance;
use App\Http\Requests\StoreSaturdayAttendanceRequest;
use App\Models\SaturdayAttendance;
use App\Models\User;
use App\Support\SaturdayAttendanceWindow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        }

        return view('attendances.index', [
            'date' => $date,
            'isOpen' => SaturdayAttendanceWindow::isOpen(),
            'mine' => $mine,
            'members' => $members,
            'presentCount' => $presentCount,
            'saturdays' => SaturdayAttendanceWindow::recentSaturdays(),
        ]);
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
}
