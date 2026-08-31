<?php

namespace App\Policies;

use App\Enums\LecturerQuestionStatus;
use App\Models\LecturerQuestion;
use App\Models\User;

class LecturerQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LecturerQuestion $lecturerQuestion): bool
    {
        return $user->is($lecturerQuestion->author) || $user->role->canCurateLecturerQuestions();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function curate(User $user, LecturerQuestion $lecturerQuestion): bool
    {
        return $user->role->canCurateLecturerQuestions()
            && $lecturerQuestion->status !== LecturerQuestionStatus::Terkirim;
    }

    public function sendPackage(User $user): bool
    {
        return $user->role->canCurateLecturerQuestions();
    }
}
