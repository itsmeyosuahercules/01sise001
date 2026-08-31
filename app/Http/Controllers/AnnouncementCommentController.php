<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementCommentRequest;
use App\Models\Announcement;
use App\Models\AnnouncementComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementCommentController extends Controller
{
    public function store(StoreAnnouncementCommentRequest $request, Announcement $announcement): JsonResponse|RedirectResponse
    {
        $comment = $announcement->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->toString(),
        ]);

        $comment->setRelation('announcement', $announcement);
        $comment->load('author');

        return $this->respond($request, [
            'html' => view('announcements._comment', ['comment' => $comment])->render(),
            'comments_count' => $announcement->comments()->count(),
            'message' => 'Komentar dikirim.',
        ], redirect()
            ->route('announcements.show', $announcement)
            ->withFragment('komentar')
            ->with('status', 'Komentar dikirim.'));
    }

    public function destroy(Request $request, Announcement $announcement, AnnouncementComment $comment): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return $this->respond($request, [
            'id' => $comment->id,
            'comments_count' => $announcement->comments()->count(),
            'message' => 'Komentar dihapus.',
        ], redirect()
            ->route('announcements.show', $announcement)
            ->withFragment('komentar')
            ->with('status', 'Komentar dihapus.'));
    }
}
