<?php

namespace App\Http\Controllers;

use App\Enums\AbsenceStatus;
use App\Enums\AbsenceType;
use App\Http\Requests\ReviewAbsenceRequest;
use App\Http\Requests\StoreAbsenceRequest;
use App\Models\AbsenceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsenceRequestController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AbsenceRequest::class);

        $user = request()->user();

        $absences = AbsenceRequest::query()
            ->with(['student', 'reviewer'])
            ->when(
                ! $user->role->canReviewAbsences(),
                fn ($query) => $query->whereBelongsTo($user, 'student'),
            )
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
            ->get();

        return view('absence-requests.index', [
            'absences' => $absences,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AbsenceRequest::class);

        return view('absence-requests.create', [
            'types' => AbsenceType::cases(),
        ]);
    }

    public function store(StoreAbsenceRequest $request): RedirectResponse
    {
        AbsenceRequest::query()->create([
            ...$request->safe()->only(['type', 'starts_on', 'ends_on', 'reason']),
            'user_id' => $request->user()->id,
            'status' => AbsenceStatus::Pending,
        ]);

        return redirect()
            ->route('absence-requests.index')
            ->with('status', 'Izin dikirim. KM akan merekap ke dosen.');
    }

    public function update(ReviewAbsenceRequest $request, AbsenceRequest $absenceRequest): JsonResponse|RedirectResponse
    {
        $absenceRequest->update([
            'status' => $request->enum('status', AbsenceStatus::class),
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return $this->respond($request, [
            'status' => $absenceRequest->status->value,
            'label' => $absenceRequest->status->label(),
            'message' => 'Status izin diperbarui.',
        ], back()->with('status', 'Status izin diperbarui.'));
    }

    public function export(): StreamedResponse
    {
        $this->authorize('export', AbsenceRequest::class);

        $filename = config('kelas.name').'-izin.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['nim', 'nama', 'jenis', 'mulai', 'selesai', 'alasan', 'status']);

            AbsenceRequest::query()
                ->with('student')
                ->orderByDesc('starts_on')
                ->orderByDesc('id')
                ->each(function (AbsenceRequest $absence) use ($handle): void {
                    fputcsv($handle, [
                        $absence->student?->nim,
                        $absence->student?->name,
                        $absence->type->label(),
                        $absence->starts_on->toDateString(),
                        $absence->ends_on->toDateString(),
                        $absence->reason,
                        $absence->status->label(),
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
