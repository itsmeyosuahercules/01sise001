<?php

namespace App\Http\Requests;

use App\Enums\AbsenceStatus;
use App\Models\AbsenceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $absenceRequest = $this->route('absence_request');

        return $absenceRequest instanceof AbsenceRequest
            && ($this->user()?->can('review', $absenceRequest) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(AbsenceStatus::class)->except(AbsenceStatus::Pending)],
        ];
    }
}
