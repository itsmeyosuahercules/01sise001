<?php

namespace App\Http\Controllers;

use App\Actions\StoreProfilePhoto;
use App\Http\Requests\ResetUserPasswordRequest;
use App\Http\Requests\UpdateOwnPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profiles.edit', [
            'user' => $request->user(),
        ]);
    }

    public function show(User $user): View
    {
        $this->authorize('viewProfile', $user);

        return view('profiles.show', [
            'user' => $user,
        ]);
    }

    public function update(UpdateProfileRequest $request, StoreProfilePhoto $storePhoto): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'name' => $request->string('name')->toString(),
            'bio' => $request->filled('bio') ? $request->string('bio')->toString() : null,
        ]);

        $storePhoto->handle($user, $request->file('photo'), $request->boolean('remove_photo'));

        return redirect()
            ->route('profiles.edit')
            ->with('status', 'Profil diperbarui.');
    }

    public function updatePassword(UpdateOwnPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->string('password')->toString(),
        ]);

        $request->session()->regenerate();

        return redirect()
            ->route('profiles.edit')
            ->with('status', 'Kata sandi diganti.');
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'password' => $request->string('password')->toString(),
        ]);

        return redirect()
            ->route('profiles.show', $user)
            ->withFragment('sandi')
            ->with('status', "Kata sandi {$user->name} diperbarui.");
    }

    public function photo(User $user): StreamedResponse
    {
        $this->authorize('viewProfile', $user);

        abort_unless(
            filled($user->avatar_path) && Storage::disk('local')->exists($user->avatar_path),
            404,
        );

        return Storage::disk('local')->response($user->avatar_path, $user->name);
    }
}
