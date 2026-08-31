<?php

namespace Tests\Feature;

use App\Actions\ImportMahasiswa;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImportMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_students_from_csv_text(): void
    {
        $result = app(ImportMahasiswa::class)->importText(
            "nim,nama\n2410112001,Siti Aminah\n2410112002,Budi Santoso\n"
        );

        $this->assertSame(2, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertDatabaseHas('users', [
            'nim' => '2410112001',
            'name' => 'Siti Aminah',
            'email' => '2410112001@01sise001.test',
        ]);
    }

    public function test_import_skips_an_existing_nim_without_changing_the_account(): void
    {
        $user = User::factory()->km()->create([
            'nim' => '2410112001',
            'name' => 'Siti Lama',
            'password' => 'password-lama',
        ]);

        $result = app(ImportMahasiswa::class)->importText(
            "nim,nama\n2410112001,Siti Aminah\n2410112003,Rina Kartika\n"
        );

        $user->refresh();

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame('Siti Lama', $user->name);
        $this->assertSame(UserRole::Km, $user->role);
        $this->assertTrue(Hash::check('password-lama', $user->getRawOriginal('password')));
        $this->assertDatabaseHas('users', [
            'nim' => '2410112003',
            'name' => 'Rina Kartika',
        ]);
    }

    public function test_km_can_import_pasted_rows(): void
    {
        $km = User::factory()->km()->create();

        $response = $this->actingAs($km)->post(route('mahasiswa-imports.store'), [
            'paste' => "2410112001,Siti Aminah\n",
        ]);

        $response->assertRedirect(route('roster.index'));
        $this->assertDatabaseHas('users', ['nim' => '2410112001', 'name' => 'Siti Aminah']);
    }

    public function test_km_can_import_an_uploaded_csv(): void
    {
        $km = User::factory()->km()->create();
        $file = UploadedFile::fake()->createWithContent(
            'mahasiswa.csv',
            "nim,nama\n2410112008,Rina Kartika\n"
        );

        $response = $this->actingAs($km)->post(route('mahasiswa-imports.store'), [
            'csv' => $file,
        ]);

        $response->assertRedirect(route('roster.index'));
        $this->assertDatabaseHas('users', ['nim' => '2410112008']);
    }

    public function test_members_cannot_import_students(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('mahasiswa-imports.store'), [
                'paste' => "2410112001,Siti Aminah\n",
            ])
            ->assertForbidden();
    }

    public function test_the_artisan_command_imports_a_csv_file(): void
    {
        $path = storage_path('framework/testing-mahasiswa.csv');
        file_put_contents($path, "nim,nama\n2410112010,Dedi Pratama\n");

        $this->artisan('kelas:import-mahasiswa', ['path' => $path])
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['nim' => '2410112010']);

        unlink($path);
    }
}
