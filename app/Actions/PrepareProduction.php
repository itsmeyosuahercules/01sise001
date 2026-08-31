<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use RuntimeException;

class PrepareProduction
{
    /**
     * @return array{purged: int, created: int, updated: int, skipped: int, km: ?string, errors: list<string>}
     */
    public function handle(?string $csvPath = null, ?string $kmNim = null): array
    {
        $purged = $this->purgeDummyAccounts();

        $import = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $path = $this->resolveCsvPath($csvPath);

        if ($path === null) {
            throw new RuntimeException(
                'CSV roster tidak ketemu. Taruh di database/data/mahasiswa.csv atau pakai --csv=path.',
            );
        }

        $import = app(ImportMahasiswa::class)->importPath($path);
        $assignedKm = $this->assignKm($kmNim ?: config('kelas.km_nim'));

        return [
            'purged' => $purged,
            'created' => $import['created'],
            'updated' => $import['updated'],
            'skipped' => $import['skipped'],
            'km' => $assignedKm?->nim,
            'errors' => $import['errors'],
        ];
    }

    public function purgeDummyAccounts(): int
    {
        $dummy = User::query()
            ->where(function ($query): void {
                $query->where('is_placeholder', true)
                    ->orWhere('nim', 'like', '990000%');
            })
            ->get();

        $dummy->each(function (User $user): void {
            $user->delete();
        });

        return $dummy->count();
    }

    public function resolveCsvPath(?string $csvPath): ?string
    {
        $candidates = array_values(array_filter([
            $csvPath,
            config('kelas.mahasiswa_csv'),
            database_path('data/mahasiswa.example.csv'),
        ], fn (mixed $path): bool => is_string($path) && $path !== ''));

        foreach ($candidates as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    public function assignKm(mixed $nim): ?User
    {
        if (! is_string($nim) || $nim === '') {
            return User::query()->where('role', UserRole::Km)->first();
        }

        $user = User::query()->where('nim', $nim)->first();

        if (! $user instanceof User) {
            throw new RuntimeException("NIM KM {$nim} tidak ada di roster. Impor CSV dulu.");
        }

        $user->update(['role' => UserRole::Km]);

        return $user->fresh();
    }
}
