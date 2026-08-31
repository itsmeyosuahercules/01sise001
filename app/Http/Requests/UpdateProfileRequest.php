<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateProfile', $this->user()) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $config = config('kelas.avatar');

        return [
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:280'],
            'photo' => ['nullable', 'image', 'max:'.$config['max_kilobytes'], 'mimes:'.implode(',', $config['mimes'])],
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
