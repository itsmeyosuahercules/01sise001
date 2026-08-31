<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementLikeController extends Controller
{
    public function store(Request $request, Announcement $announcement): JsonResponse|RedirectResponse
    {
        $this->authorize('view', $announcement);

        $like = $announcement->likes()->whereBelongsTo($request->user())->first();

        if ($like) {
            $like->delete();

            return $this->likeResponse($request, $announcement, false, 'Suka dibatalkan.');
        }

        $announcement->likes()->create([
            'user_id' => $request->user()->id,
        ]);

        return $this->likeResponse($request, $announcement, true, 'Pengumuman disukai.');
    }

    private function likeResponse(Request $request, Announcement $announcement, bool $liked, string $message): JsonResponse|RedirectResponse
    {
        $announcement->load(['likes' => fn ($query) => $query->with('user')->latest('id')]);

        return $this->respond($request, [
            'liked' => $liked,
            'likes_count' => $announcement->likes->count(),
            'likes_html' => view('announcements._likes', ['announcement' => $announcement])->render(),
            'message' => $message,
        ], back()->with('status', $message));
    }
}
