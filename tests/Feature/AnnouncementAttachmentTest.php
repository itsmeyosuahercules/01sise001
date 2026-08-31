<?php

namespace Tests\Feature;

use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_class_rep_can_attach_images_and_files_when_publishing(): void
    {
        Storage::fake('local');

        $km = User::factory()->km()->create();
        $image = UploadedFile::fake()->image('poster.jpg', 200, 120);
        $pdf = UploadedFile::fake()->create('jadwal.pdf', 40, 'application/pdf');

        $this->actingAs($km)
            ->post(route('announcements.store'), [
                'title' => 'Poster UTS',
                'body' => 'Cek lampiran.',
                'category' => AnnouncementCategory::Ujian->value,
                'attachments' => [$image, $pdf],
            ])
            ->assertRedirect('/announcements/poster-uts');

        $announcement = Announcement::query()->first();

        $this->assertNotNull($announcement);
        $this->assertSame(2, $announcement->attachments()->count());
        $this->assertTrue($announcement->attachments->contains(fn (AnnouncementAttachment $attachment): bool => $attachment->isImage()));
        Storage::disk('local')->assertExists($announcement->attachments->first()->path);
        $this->assertStringContainsString('Lampiran: poster.jpg, jadwal.pdf', $announcement->whatsappText());
    }

    public function test_members_can_view_images_and_download_files(): void
    {
        Storage::fake('local');

        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create(['title' => 'Ada lampiran']);
        $path = 'announcements/'.$announcement->id.'/jadwal.pdf';
        Storage::disk('local')->put($path, 'isi-pdf');

        $attachment = AnnouncementAttachment::factory()->create([
            'announcement_id' => $announcement->id,
            'path' => $path,
            'original_name' => 'jadwal.pdf',
            'mime' => 'application/pdf',
        ]);

        $this->actingAs($member)
            ->get(route('announcements.show', $announcement))
            ->assertOk()
            ->assertSee('jadwal.pdf');

        $this->actingAs($member)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('1 lampiran');

        $this->actingAs($member)
            ->get(route('announcements.attachments.show', [$announcement, $attachment]))
            ->assertOk();
    }

    public function test_members_cannot_delete_attachments(): void
    {
        Storage::fake('local');

        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create();
        $attachment = AnnouncementAttachment::factory()->create([
            'announcement_id' => $announcement->id,
        ]);

        $this->actingAs($member)
            ->delete(route('announcements.attachments.destroy', [$announcement, $attachment]))
            ->assertForbidden();
    }

    public function test_the_class_rep_can_remove_an_attachment(): void
    {
        Storage::fake('local');

        $km = User::factory()->km()->create();
        $announcement = Announcement::factory()->create();
        $path = 'announcements/'.$announcement->id.'/poster.jpg';
        Storage::disk('local')->put($path, 'img');

        $attachment = AnnouncementAttachment::factory()->image()->create([
            'announcement_id' => $announcement->id,
            'path' => $path,
        ]);

        $this->actingAs($km)
            ->delete(route('announcements.attachments.destroy', [$announcement, $attachment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('announcement_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_deleting_an_announcement_removes_its_files(): void
    {
        Storage::fake('local');

        $km = User::factory()->km()->create();
        $announcement = Announcement::factory()->create();
        $path = 'announcements/'.$announcement->id.'/berkas.pdf';
        Storage::disk('local')->put($path, 'pdf');

        AnnouncementAttachment::factory()->create([
            'announcement_id' => $announcement->id,
            'path' => $path,
        ]);

        $this->actingAs($km)
            ->delete(route('announcements.destroy', $announcement))
            ->assertRedirect(route('announcements.index'));

        Storage::disk('local')->assertMissing($path);
    }

    public function test_executable_uploads_are_rejected(): void
    {
        Storage::fake('local');

        $km = User::factory()->km()->create();
        $exe = UploadedFile::fake()->create('virus.exe', 20, 'application/x-msdownload');

        $this->actingAs($km)
            ->from(route('announcements.create'))
            ->post(route('announcements.store'), [
                'title' => 'Jangan',
                'body' => 'Isi',
                'category' => AnnouncementCategory::Umum->value,
                'attachments' => [$exe],
            ])
            ->assertRedirect(route('announcements.create'))
            ->assertSessionHasErrors('attachments.0');
    }
}
