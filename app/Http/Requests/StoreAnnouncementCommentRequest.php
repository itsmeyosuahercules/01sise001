<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $announcement = $this->route('announcement');

        return $announcement instanceof Announcement
            && ($this->user()?->can('create', [AnnouncementComment::class, $announcement]) ?? false);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
