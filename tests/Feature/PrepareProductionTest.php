<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrepareProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_prep_removes_dummy_accounts_and_imports_the_roster(): void
    {
        $dummy = User::factory()->km()->placeholder()->create([
            'nim' => '9900000001',
            'name' => 'KM Dummy',
        ]);
        Announcement::factory()->create([
            'user_id' => $dummy->id,
            'title' => 'Papan kelas 01SISE001 sudah hidup',
        ]);
        User::factory()->placeholder()->create([
            'nim' => '9900000002',
            'name' => 'Anggota Dummy',
        ]);

        $path = storage_path('framework/testing-produksi.csv');
        file_put_contents($path, "nim,nama\n2610112001,Siti Aminah\n2610112002,Budi Santoso\n");

        $this->artisan('kelas:siapkan-produksi', [
            '--csv' => $path,
            '--km' => '2610112001',
        ])->assertSuccessful();

        unlink($path);

        $this->assertDatabaseMissing('users', ['nim' => '9900000001']);
        $this->assertDatabaseMissing('users', ['nim' => '9900000002']);
        $this->assertDatabaseMissing('announcements', ['title' => 'Papan kelas 01SISE001 sudah hidup']);
        $this->assertDatabaseHas('users', [
            'nim' => '2610112001',
            'name' => 'Siti Aminah',
            'role' => UserRole::Km->value,
            'is_placeholder' => 0,
        ]);
        $this->assertDatabaseHas('users', [
            'nim' => '2610112002',
            'role' => UserRole::Anggota->value,
        ]);
    }

    public function test_seeding_does_not_create_dummy_accounts(): void
    {
        $this->seed();

        $this->assertDatabaseMissing('users', ['nim' => '9900000001']);
        $this->assertSame(0, User::query()->placeholders()->count());
    }
}
