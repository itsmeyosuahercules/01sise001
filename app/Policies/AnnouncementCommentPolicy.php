<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\User;

class AnnouncementCommentPolicy
{
    public function create(User $user, Announcement $announcement): bool
    {
        return $user->can('view', $announcement);
    }

    public function delete(User $user, AnnouncementComment $comment): bool
    {
        return $user->is($comment->author) || $user->isPengurus();
    }
}
