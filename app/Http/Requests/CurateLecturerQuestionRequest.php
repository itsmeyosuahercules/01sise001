<?php

namespace App\Http\Requests;

use App\Enums\LecturerQuestionStatus;
use App\Models\LecturerQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurateLecturerQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $question = $this->route('lecturer_question');

        return $question instanceof LecturerQuestion
            && ($this->user()?->can('curate', $question) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(LecturerQuestionStatus::class)->only([
                LecturerQuestionStatus::Dipilih,
                LecturerQuestionStatus::Ditahan,
            ])],
        ];
    }
}
