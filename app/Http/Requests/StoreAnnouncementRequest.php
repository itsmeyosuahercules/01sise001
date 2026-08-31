<?php

namespace App\Http\Requests;

use App\Actions\AttachAnnouncementFiles;
use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Announcement::class) ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['required', Rule::enum(AnnouncementCategory::class)],
            'is_pinned' => ['sometimes', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            ...AttachAnnouncementFiles::rules(),
        ];
    }
}
