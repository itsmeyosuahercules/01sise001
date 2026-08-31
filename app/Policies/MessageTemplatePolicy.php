<?php

namespace App\Policies;

use App\Models\MessageTemplate;
use App\Models\User;

class MessageTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role->canManageTemplates();
    }

    public function delete(User $user, MessageTemplate $messageTemplate): bool
    {
        return $user->role->canManageTemplates();
    }
}
