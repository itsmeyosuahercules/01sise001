<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ImportMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import', User::class) ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'csv' => ['nullable', 'file', 'mimes:csv,txt', 'max:512'],
            'paste' => ['nullable', 'string', 'max:20000'],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->hasFile('csv') || filled($this->input('paste'))) {
                    return;
                }

                $validator->errors()->add('paste', 'Unggah CSV atau tempel daftar NIM,nama.');
            },
        ];
    }
}
