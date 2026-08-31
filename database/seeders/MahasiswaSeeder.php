<?php

namespace Database\Seeders;

use App\Actions\ImportMahasiswa;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = config('kelas.mahasiswa_csv');

        if (! is_string($csvPath) || ! is_readable($csvPath)) {
            return;
        }

        try {
            app(ImportMahasiswa::class)->importPath($csvPath);
        } catch (RuntimeException) {
            return;
        }

        $kmNim = config('kelas.km_nim');

        if (! is_string($kmNim) || $kmNim === '') {
            return;
        }

        User::query()->where('nim', $kmNim)->update([
            'role' => UserRole::Km,
        ]);
    }
}
