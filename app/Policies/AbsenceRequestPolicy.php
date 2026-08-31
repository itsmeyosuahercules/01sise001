<?php

namespace App\Policies;

use App\Models\AbsenceRequest;
use App\Models\User;

class AbsenceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AbsenceRequest $absenceRequest): bool
    {
        return $user->is($absenceRequest->student) || $user->role->canReviewAbsences();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function review(User $user, AbsenceRequest $absenceRequest): bool
    {
        return $user->role->canReviewAbsences();
    }

    public function export(User $user): bool
    {
        return $user->role->canReviewAbsences();
    }
}
