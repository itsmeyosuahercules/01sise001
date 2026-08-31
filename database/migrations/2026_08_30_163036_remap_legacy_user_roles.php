<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotIn('role', ['km', 'anggota'])
            ->update(['role' => 'anggota']);

        $placeholderNames = [
            '9900000002' => 'Anggota Dummy',
            '9900000003' => 'Anggota Dummy',
            '9900000004' => 'Anggota Dummy',
            '9900000005' => 'Anggota Dummy',
        ];

        foreach ($placeholderNames as $nim => $name) {
            DB::table('users')
                ->where('nim', $nim)
                ->where('is_placeholder', true)
                ->update([
                    'name' => $name,
                    'role' => 'anggota',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Role lama tidak dipulihkan: hanya KM dan anggota yang dipakai.
    }
};
