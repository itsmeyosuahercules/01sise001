<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreProfilePhoto
{
    public function handle(User $user, ?UploadedFile $photo, bool $remove = false): void
    {
        if ($photo instanceof UploadedFile && $photo->isValid()) {
            $this->deleteCurrent($user);

            $path = $photo->store('avatars', 'local');

            if (is_string($path) && $path !== '') {
                $user->forceFill(['avatar_path' => $path])->save();
            }

            return;
        }

        if ($remove) {
            $this->deleteCurrent($user);
            $user->forceFill(['avatar_path' => null])->save();
        }
    }

    private function deleteCurrent(User $user): void
    {
        if (! filled($user->avatar_path)) {
            return;
        }

        Storage::disk('local')->delete($user->avatar_path);
    }
}
