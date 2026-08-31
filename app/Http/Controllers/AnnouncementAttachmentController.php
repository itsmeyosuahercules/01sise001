<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementAttachmentController extends Controller
{
    public function show(Announcement $announcement, AnnouncementAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $announcement);

        return $attachment->isImage()
            ? Storage::disk($attachment->disk)->response($attachment->path, $attachment->original_name)
            : Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function destroy(Announcement $announcement, AnnouncementAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $attachment->delete();

        return back()->with('status', 'Lampiran dihapus.');
    }
}
