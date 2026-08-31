<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RosterController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $members = User::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('roster.index', [
            'members' => $members,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdateUserRoleRequest $request, User $user): JsonResponse|RedirectResponse
    {
        $user->update($request->safe()->only(['role']));

        return $this->respond($request, [
            'role' => $user->role->value,
            'label' => $user->role->label(),
            'message' => "Peran {$user->name} diperbarui.",
        ], back()->with('status', "Peran {$user->name} diperbarui."));
    }

    public function export(): StreamedResponse
    {
        $this->authorize('export', User::class);

        $filename = config('kelas.name').'-roster.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['nim', 'nama', 'peran']);

            User::query()
                ->orderBy('name')
                ->orderBy('id')
                ->each(function (User $user) use ($handle): void {
                    fputcsv($handle, [
                        $user->nim,
                        $user->name,
                        $user->role->label(),
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
