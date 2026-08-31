<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function viewProfile(User $user, User $mahasiswa): bool
    {
        return true;
    }

    public function updateProfile(User $user, User $mahasiswa): bool
    {
        return $user->is($mahasiswa);
    }

    public function export(User $user): bool
    {
        return $user->role->isPengurus();
    }

    public function import(User $user): bool
    {
        return $user->role->canImportMahasiswa();
    }

    public function updateRole(User $user, User $mahasiswa): bool
    {
        return $user->role->canManageRoles();
    }
}
