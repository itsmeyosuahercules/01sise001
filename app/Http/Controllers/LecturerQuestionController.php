<?php

namespace App\Http\Controllers;

use App\Enums\LecturerQuestionKind;
use App\Enums\LecturerQuestionStatus;
use App\Http\Requests\CurateLecturerQuestionRequest;
use App\Http\Requests\StoreLecturerQuestionRequest;
use App\Models\LecturerQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LecturerQuestionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', LecturerQuestion::class);

        $user = request()->user();
        $canCurate = $user->role->canCurateLecturerQuestions();

        $questions = LecturerQuestion::query()
            ->with('author')
            ->when(
                ! $canCurate,
                fn ($query) => $query->whereBelongsTo($user, 'author'),
            )
            ->orderByDesc('id')
            ->get()
            ->sortBy(fn (LecturerQuestion $question): int => match ($question->status) {
                LecturerQuestionStatus::Baru => 0,
                LecturerQuestionStatus::Dipilih => 1,
                LecturerQuestionStatus::Ditahan => 2,
                LecturerQuestionStatus::Terkirim => 3,
            })
            ->values();

        return view('lecturer-questions.index', [
            'questions' => $questions,
            'selected' => $canCurate ? $this->selectedQuestions() : collect(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', LecturerQuestion::class);

        return view('lecturer-questions.create', [
            'kinds' => LecturerQuestionKind::cases(),
        ]);
    }

    public function store(StoreLecturerQuestionRequest $request): RedirectResponse
    {
        LecturerQuestion::query()->create([
            'user_id' => $request->user()->id,
            'kind' => $request->enum('kind', LecturerQuestionKind::class),
            'topic' => $request->string('topic')->toString(),
            'body' => $request->string('body')->toString(),
            'hide_name' => $request->boolean('hide_name'),
            'status' => LecturerQuestionStatus::Baru,
        ]);

        return redirect()
            ->route('lecturer-questions.index')
            ->with('status', 'Pertanyaan masuk. KM yang meneruskan ke dosen — bukan lewat Mentari.');
    }

    public function update(CurateLecturerQuestionRequest $request, LecturerQuestion $lecturerQuestion): JsonResponse|RedirectResponse
    {
        $lecturerQuestion->update([
            'status' => $request->enum('status', LecturerQuestionStatus::class),
            'curator_id' => $request->user()->id,
            'curated_at' => now(),
        ]);

        $lecturerQuestion->load('author');
        $package = $this->packagePayload();

        return $this->respond($request, [
            ...$package,
            'status' => $lecturerQuestion->status->value,
            'label' => $lecturerQuestion->status->label(),
            'html' => view('lecturer-questions._status', ['question' => $lecturerQuestion])->render(),
            'message' => 'Status pertanyaan diperbarui.',
        ], back()->with('status', 'Status pertanyaan diperbarui.'));
    }

    public function package(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('sendPackage', LecturerQuestion::class);

        $selected = $this->selectedQuestions();

        if ($selected->isEmpty()) {
            return $this->respond($request, [
                ...$this->packagePayload(),
                'message' => 'Belum ada pertanyaan yang dipilih.',
            ], back()->with('status', 'Belum ada pertanyaan yang dipilih.'));
        }

        LecturerQuestion::query()
            ->where('status', LecturerQuestionStatus::Dipilih)
            ->update([
                'status' => LecturerQuestionStatus::Terkirim,
                'curator_id' => $request->user()->id,
                'curated_at' => now(),
                'sent_at' => now(),
            ]);

        return $this->respond($request, [
            ...$this->packagePayload(),
            'message' => 'Paket ditandai sudah dikirim ke dosen.',
        ], redirect()
            ->route('lecturer-questions.index')
            ->with('status', 'Paket ditandai sudah dikirim ke dosen.'));
    }

    /**
     * @return Collection<int, LecturerQuestion>
     */
    private function selectedQuestions(): Collection
    {
        return LecturerQuestion::query()
            ->with('author')
            ->where('status', LecturerQuestionStatus::Dipilih)
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array{package_text: string, selected_count: int}
     */
    private function packagePayload(): array
    {
        $selected = $this->selectedQuestions();

        return [
            'package_text' => LecturerQuestion::whatsappPackage($selected),
            'selected_count' => $selected->count(),
        ];
    }
}
