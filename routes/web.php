<?php

use App\Http\Controllers\AbsenceRequestController;
use App\Http\Controllers\AnnouncementAttachmentController;
use App\Http\Controllers\AnnouncementCommentController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementLikeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LecturerQuestionController;
use App\Http\Controllers\MahasiswaImportController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RosterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('profil', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profiles.update');
    Route::get('profil/{user:nim}', [ProfileController::class, 'show'])->name('profiles.show');
    Route::get('profil/{user:nim}/foto', [ProfileController::class, 'photo'])->name('profiles.photo');

    Route::get('roster', [RosterController::class, 'index'])->name('roster.index');
    Route::get('roster/export', [RosterController::class, 'export'])->name('roster.export');
    Route::patch('roster/{user}', [RosterController::class, 'update'])->name('roster.update');

    Route::get('mahasiswa/impor', [MahasiswaImportController::class, 'create'])->name('mahasiswa-imports.create');
    Route::post('mahasiswa/impor', [MahasiswaImportController::class, 'store'])->name('mahasiswa-imports.store');

    Route::resource('announcements', AnnouncementController::class);
    Route::get('announcements/{announcement}/attachments/{attachment}', [AnnouncementAttachmentController::class, 'show'])
        ->scopeBindings()
        ->name('announcements.attachments.show');
    Route::delete('announcements/{announcement}/attachments/{attachment}', [AnnouncementAttachmentController::class, 'destroy'])
        ->scopeBindings()
        ->name('announcements.attachments.destroy');
    Route::post('announcements/{announcement}/likes', [AnnouncementLikeController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('announcements.likes.store');
    Route::post('announcements/{announcement}/comments', [AnnouncementCommentController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('announcements.comments.store');
    Route::delete('announcements/{announcement}/comments/{comment}', [AnnouncementCommentController::class, 'destroy'])
        ->middleware('throttle:20,1')
        ->scopeBindings()
        ->name('announcements.comments.destroy');

    Route::post('pertanyaan/paket', [LecturerQuestionController::class, 'package'])
        ->name('lecturer-questions.package');
    Route::resource('pertanyaan', LecturerQuestionController::class)
        ->parameters(['pertanyaan' => 'lecturer_question'])
        ->names('lecturer-questions')
        ->only(['index', 'create', 'store', 'update']);

    Route::get('izin/export', [AbsenceRequestController::class, 'export'])->name('absence-requests.export');
    Route::resource('izin', AbsenceRequestController::class)
        ->parameters(['izin' => 'absence_request'])
        ->names('absence-requests')
        ->only(['index', 'create', 'store', 'update']);

    Route::resource('templates', MessageTemplateController::class)
        ->parameters(['templates' => 'message_template'])
        ->names('message-templates')
        ->only(['index', 'create', 'store', 'destroy']);
});
