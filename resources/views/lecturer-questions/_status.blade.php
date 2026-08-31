<span data-status-label>{{ $question->status->label() }}</span>
@can('curate', $question)
    <form method="POST" action="{{ route('lecturer-questions.update', $question) }}" data-remote="question-status" data-curate-actions class="mt-2 flex flex-wrap gap-1">
        @csrf
        @method('PATCH')
        @if ($question->status !== App\Enums\LecturerQuestionStatus::Dipilih)
            <x-btn type="submit" name="status" value="{{ App\Enums\LecturerQuestionStatus::Dipilih->value }}" class="!px-2 !py-1 text-xs">Masuk paket</x-btn>
        @endif
        @if ($question->status !== App\Enums\LecturerQuestionStatus::Ditahan)
            <x-btn variant="secondary" type="submit" name="status" value="{{ App\Enums\LecturerQuestionStatus::Ditahan->value }}" class="!px-2 !py-1 text-xs">Tahan</x-btn>
        @endif
    </form>
@endcan
