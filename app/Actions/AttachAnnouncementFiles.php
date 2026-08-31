<?php

namespace App\Actions;

use App\Models\Announcement;
use Illuminate\Http\UploadedFile;

class AttachAnnouncementFiles
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function rules(): array
    {
        $config = config('kelas.announcement_attachments');

        return [
            'attachments' => ['sometimes', 'array', 'max:'.$config['max_files']],
            'attachments.*' => ['file', 'max:'.$config['max_kilobytes'], 'mimes:'.implode(',', $config['mimes'])],
        ];
    }

    /**
     * @param  list<UploadedFile|null>  $files
     */
    public function attach(Announcement $announcement, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('announcements/'.$announcement->id, 'local');

            if (! is_string($path) || $path === '') {
                continue;
            }

            $announcement->attachments()->create([
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }
    }
}
