<?php

namespace App\Http\Requests;

use App\Enums\LecturerQuestionKind;
use App\Models\LecturerQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLecturerQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LecturerQuestion::class) ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'kind' => ['required', Rule::enum(LecturerQuestionKind::class)],
            'topic' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
            'hide_name' => ['sometimes', 'boolean'],
        ];
    }
}
