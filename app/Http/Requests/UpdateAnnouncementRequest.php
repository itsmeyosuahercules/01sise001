<?php

namespace App\Http\Requests;

use App\Actions\AttachAnnouncementFiles;
use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $announcement = $this->route('announcement');

        return $announcement instanceof Announcement
            && ($this->user()?->can('update', $announcement) ?? false);
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
            'expires_at' => ['nullable', 'date'],
            ...AttachAnnouncementFiles::rules(),
        ];
    }
}
