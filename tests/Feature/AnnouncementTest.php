<?php

namespace Tests\Feature;

use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_view_announcements(): void
    {
        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create([
            'title' => 'Kuliah pindah ruangan',
        ]);

        $this->actingAs($member)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('Kuliah pindah ruangan');

        $this->actingAs($member)
            ->get(route('announcements.show', $announcement))
            ->assertOk();

        $this->assertSame('kuliah-pindah-ruangan', $announcement->slug);
        $this->assertStringContainsString('/announcements/kuliah-pindah-ruangan', route('announcements.show', $announcement));
        $this->assertStringNotContainsString('/announcements/'.$announcement->id, route('announcements.show', $announcement));
    }

    public function test_an_announcement_is_reached_by_slug_not_id(): void
    {
        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create([
            'title' => 'Jadwal UTS',
        ]);

        $this->actingAs($member)
            ->get('/announcements/'.$announcement->id)
            ->assertNotFound();

        $this->actingAs($member)
            ->get('/announcements/jadwal-uts')
            ->assertOk()
            ->assertSee('Jadwal UTS');
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        $first = Announcement::factory()->create(['title' => 'Info lab']);
        $second = Announcement::factory()->create(['title' => 'Info lab']);

        $this->assertSame('info-lab', $first->slug);
        $this->assertSame('info-lab-2', $second->slug);
    }

    public function test_members_cannot_create_announcements(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('announcements.store'), [
                'title' => 'Tidak boleh',
                'body' => 'Isi',
                'category' => AnnouncementCategory::Umum->value,
            ])
            ->assertForbidden();
    }

    public function test_class_officers_can_publish_announcements(): void
    {
        $km = User::factory()->km()->create();

        $response = $this->actingAs($km)->post(route('announcements.store'), [
            'title' => 'UTS Basis Data',
            'body' => 'Hari Senin, ruang B201.',
            'category' => AnnouncementCategory::Ujian->value,
            'is_pinned' => '1',
        ]);

        $announcement = Announcement::query()->first();

        $this->assertNotNull($announcement);
        $response->assertRedirect(route('announcements.show', $announcement));
        $this->assertTrue($announcement->is_pinned);
        $this->assertSame(AnnouncementCategory::Ujian, $announcement->category);
        $this->assertSame('uts-basis-data', $announcement->slug);
        $response->assertRedirect('/announcements/uts-basis-data');
    }

    public function test_visiting_an_announcement_marks_it_as_read(): void
    {
        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($member)
            ->get(route('announcements.show', $announcement))
            ->assertOk()
            ->assertDontSee('>Sudah baca<', false);

        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($member)
            ->get(route('announcements.show', $announcement))
            ->assertOk();

        $this->assertSame(1, AnnouncementRead::query()->count());
    }
}
