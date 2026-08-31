<?php

namespace App\Http\Controllers;

use App\Enums\AbsenceStatus;
use App\Enums\LecturerQuestionStatus;
use App\Models\AbsenceRequest;
use App\Models\Announcement;
use App\Models\LecturerQuestion;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();

        $announcements = Announcement::query()
            ->visible()
            ->with([
                'author',
                'reads' => fn ($query) => $query->whereBelongsTo($user),
                'likes' => fn ($query) => $query->whereBelongsTo($user),
            ])
            ->withCount(['likes', 'comments', 'attachments'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $unreadCount = Announcement::query()
            ->visible()
            ->whereDoesntHave('reads', fn ($query) => $query->whereBelongsTo($user))
            ->count();

        $pendingAbsences = $user->role->canReviewAbsences()
            ? AbsenceRequest::query()->where('status', AbsenceStatus::Pending)->count()
            : AbsenceRequest::query()->whereBelongsTo($user, 'student')->where('status', AbsenceStatus::Pending)->count();

        $pendingQuestions = $user->role->canCurateLecturerQuestions()
            ? LecturerQuestion::query()->where('status', LecturerQuestionStatus::Baru)->count()
            : LecturerQuestion::query()->whereBelongsTo($user, 'author')->where('status', LecturerQuestionStatus::Baru)->count();

        return view('dashboard', [
            'announcements' => $announcements,
            'unreadCount' => $unreadCount,
            'pendingAbsences' => $pendingAbsences,
            'pendingQuestions' => $pendingQuestions,
            'memberCount' => User::query()->count(),
        ]);
    }
}
