<?php

namespace App\Policies;

use App\Models\SaturdayAttendance;
use App\Models\User;

class SaturdayAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SaturdayAttendance $saturdayAttendance): bool
    {
        return $user->is($saturdayAttendance->user) || $user->role->canReviewAttendances();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function export(User $user): bool
    {
        return $user->role->canReviewAttendances();
    }

    public function manage(User $user): bool
    {
        return $user->role->canReviewAttendances();
    }
}
