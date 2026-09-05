<?php

namespace App\Http\Controllers;

use App\Enums\LecturerQuestionStatus;
use App\Models\Announcement;
use App\Models\LecturerQuestion;
use App\Models\SaturdayAttendance;
use App\Models\User;
use App\Support\SaturdayAttendanceWindow;
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

        $saturday = SaturdayAttendanceWindow::currentOrLatestSaturday();
        $saturdayPresent = SaturdayAttendance::query()
            ->whereDate('attended_on', $saturday->toDateString())
            ->count();
        $saturdayMine = SaturdayAttendance::query()
            ->whereBelongsTo($user)
            ->whereDate('attended_on', $saturday->toDateString())
            ->exists();

        $pendingQuestions = $user->role->canCurateLecturerQuestions()
            ? LecturerQuestion::query()->where('status', LecturerQuestionStatus::Baru)->count()
            : LecturerQuestion::query()->whereBelongsTo($user, 'author')->where('status', LecturerQuestionStatus::Baru)->count();

        return view('dashboard', [
            'announcements' => $announcements,
            'unreadCount' => $unreadCount,
            'saturdayPresent' => $saturdayPresent,
            'saturdayMine' => $saturdayMine,
            'pendingQuestions' => $pendingQuestions,
            'memberCount' => User::query()->count(),
        ]);
    }
}
