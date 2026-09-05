<?php

namespace Tests\Feature;

use App\Models\SaturdayAttendance;
use App\Models\User;
use App\Support\SaturdayAttendanceWindow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SaturdayAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-05 10:15:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_members_can_check_in_on_saturday_with_a_face_photo_and_live_location(): void
    {
        Storage::fake('local');

        $member = User::factory()->anggota()->create();
        $photo = UploadedFile::fake()->image('wajah.jpg', 240, 240);

        $this->actingAs($member)
            ->get(route('attendances.index'))
            ->assertOk()
            ->assertSee('Jepret')
            ->assertDontSee('choose file', false);

        $this->actingAs($member)
            ->post(route('attendances.store'), [
                'photo' => $photo,
                'latitude' => -6.2615123,
                'longitude' => 106.6640456,
                'accuracy' => 17,
            ])
            ->assertRedirect(route('attendances.index'))
            ->assertSessionHas('status');

        $attendance = SaturdayAttendance::query()->first();

        $this->assertNotNull($attendance);
        $this->assertSame($member->id, $attendance->user_id);
        $this->assertSame('2026-09-05', $attendance->attended_on->toDateString());
        $this->assertEqualsWithDelta(-6.2615123, $attendance->latitude, 0.00001);
        Storage::disk('local')->assertExists($attendance->photo_path);
    }

    public function test_members_cannot_check_in_outside_saturday(): void
    {
        Storage::fake('local');
        Carbon::setTestNow(Carbon::parse('2026-09-04 10:15:00', 'Asia/Jakarta'));

        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->from(route('attendances.index'))
            ->post(route('attendances.store'), [
                'photo' => UploadedFile::fake()->image('wajah.jpg', 120, 120),
                'latitude' => -6.26,
                'longitude' => 106.66,
            ])
            ->assertRedirect(route('attendances.index'))
            ->assertSessionHasErrors('photo');

        $this->assertSame(0, SaturdayAttendance::query()->count());
        $this->assertFalse(SaturdayAttendanceWindow::isOpen());
    }

    public function test_a_check_in_requires_a_photo_and_coordinates(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->from(route('attendances.index'))
            ->post(route('attendances.store'), [])
            ->assertRedirect(route('attendances.index'))
            ->assertSessionHasErrors(['photo', 'latitude', 'longitude']);
    }

    public function test_a_member_can_replace_their_saturday_check_in(): void
    {
        Storage::fake('local');

        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('attendances.store'), [
                'photo' => UploadedFile::fake()->image('satu.jpg', 120, 120),
                'latitude' => -6.26,
                'longitude' => 106.66,
            ])
            ->assertRedirect();

        $firstPath = SaturdayAttendance::query()->first()?->photo_path;

        $this->actingAs($member)
            ->post(route('attendances.store'), [
                'photo' => UploadedFile::fake()->image('dua.jpg', 120, 120),
                'latitude' => -6.27,
                'longitude' => 106.67,
            ])
            ->assertRedirect();

        $this->assertSame(1, SaturdayAttendance::query()->count());
        $this->assertEqualsWithDelta(-6.27, SaturdayAttendance::query()->first()?->latitude, 0.00001);
        Storage::disk('local')->assertMissing($firstPath);
    }

    public function test_members_cannot_view_someone_elses_attendance_photo(): void
    {
        Storage::fake('local');

        $owner = User::factory()->anggota()->create();
        $other = User::factory()->anggota()->create();
        Storage::disk('local')->put('attendances/2026-09-05/wajah.jpg', 'foto');

        $attendance = SaturdayAttendance::factory()->create([
            'user_id' => $owner->id,
            'photo_path' => 'attendances/2026-09-05/wajah.jpg',
        ]);

        $this->actingAs($other)
            ->get(route('attendances.photo', $attendance))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('attendances.photo', $attendance))
            ->assertOk();
    }

    public function test_officers_can_open_the_recap_and_print_report(): void
    {
        Storage::fake('local');

        $km = User::factory()->km()->create(['name' => 'Yosua Hercules']);
        $present = User::factory()->anggota()->create([
            'nim' => '261091700033',
            'name' => 'Aida Kamila',
        ]);
        $absent = User::factory()->anggota()->create([
            'nim' => '261091700090',
            'name' => 'Nur Via Rela',
        ]);

        Storage::disk('local')->put('attendances/2026-09-05/aida.jpg', 'foto');
        SaturdayAttendance::factory()->create([
            'user_id' => $present->id,
            'photo_path' => 'attendances/2026-09-05/aida.jpg',
        ]);

        $this->actingAs($km)
            ->get(route('attendances.index'))
            ->assertOk()
            ->assertSee('Hadir')
            ->assertSee('Tidak hadir')
            ->assertSee('Aida Kamila')
            ->assertSee('Nur Via Rela')
            ->assertSee('Unduh PDF');

        $this->actingAs($km)
            ->get(route('attendances.report', ['tanggal' => '2026-09-05']))
            ->assertOk()
            ->assertSee('Rekap Hadir Sabtu')
            ->assertSee('261091700033')
            ->assertSee('Tidak hadir')
            ->assertSee('Bukan presensi resmi UNPAM');

        $this->actingAs($absent)
            ->get(route('attendances.report'))
            ->assertForbidden();
    }

    public function test_the_class_vice_can_open_the_report(): void
    {
        $wakil = User::factory()->wakil()->create();

        $this->actingAs($wakil)
            ->get(route('attendances.report'))
            ->assertOk();
    }

    public function test_the_dashboard_shows_saturday_attendance_instead_of_izin(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hadir Sabtu')
            ->assertDontSee('Izin menunggu');
    }
}
