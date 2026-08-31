<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_profile_pages(): void
    {
        $member = User::factory()->anggota()->create();

        $this->get(route('profiles.edit'))->assertRedirect(route('login'));
        $this->get(route('profiles.show', $member))->assertRedirect(route('login'));
    }

    public function test_a_member_can_open_and_update_their_profile(): void
    {
        $member = User::factory()->anggota()->create([
            'name' => 'Nama Lama',
        ]);

        $this->actingAs($member)
            ->get(route('profiles.edit'))
            ->assertOk()
            ->assertSee('Profil saya')
            ->assertSee($member->nim);

        $this->actingAs($member)
            ->from(route('profiles.edit'))
            ->patch(route('profiles.update'), [
                'name' => 'Yosua Hercules',
                'bio' => 'KM 01SISE001',
            ])
            ->assertRedirect(route('profiles.edit'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'nim' => $member->nim,
            'name' => 'Yosua Hercules',
            'bio' => 'KM 01SISE001',
        ]);
    }

    public function test_a_member_can_upload_and_remove_a_photo(): void
    {
        Storage::fake('local');

        $member = User::factory()->anggota()->create();
        $photo = UploadedFile::fake()->image('wajah.jpg', 120, 120);

        $this->actingAs($member)
            ->patch(route('profiles.update'), [
                'name' => $member->name,
                'photo' => $photo,
            ])
            ->assertRedirect(route('profiles.edit'));

        $member->refresh();

        $this->assertTrue($member->hasAvatar());
        Storage::disk('local')->assertExists($member->avatar_path);

        $this->actingAs($member)
            ->get(route('profiles.photo', $member))
            ->assertOk();

        $this->actingAs($member)
            ->patch(route('profiles.update'), [
                'name' => $member->name,
                'remove_photo' => '1',
            ])
            ->assertRedirect(route('profiles.edit'));

        $member->refresh();

        $this->assertFalse($member->hasAvatar());
        $this->assertNull($member->avatar_path);
    }

    public function test_classmates_can_see_a_profile_and_photo_but_guests_cannot(): void
    {
        Storage::fake('local');

        $owner = User::factory()->anggota()->create([
            'name' => 'Siti Aminah',
            'bio' => 'Bendahara kelompok 3',
            'avatar_path' => 'avatars/siti.jpg',
        ]);
        Storage::disk('local')->put('avatars/siti.jpg', 'foto');

        $classmate = User::factory()->anggota()->create();

        $this->actingAs($classmate)
            ->get(route('profiles.show', $owner))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertSee('Bendahara kelompok 3')
            ->assertSee($owner->nim);

        $this->actingAs($classmate)
            ->get(route('profiles.photo', $owner))
            ->assertOk();

        $this->post(route('logout'));

        $this->get(route('profiles.photo', $owner))->assertRedirect(route('login'));
    }

    public function test_a_name_is_required_and_a_photo_must_be_an_image(): void
    {
        Storage::fake('local');

        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->from(route('profiles.edit'))
            ->patch(route('profiles.update'), [
                'name' => '',
                'bio' => 'ok',
            ])
            ->assertRedirect(route('profiles.edit'))
            ->assertSessionHasErrors('name');

        $this->actingAs($member)
            ->from(route('profiles.edit'))
            ->patch(route('profiles.update'), [
                'name' => $member->name,
                'photo' => UploadedFile::fake()->create('catatan.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('profiles.edit'))
            ->assertSessionHasErrors('photo');
    }

    public function test_announcement_comments_and_likes_show_profile_details(): void
    {
        $author = User::factory()->anggota()->create([
            'name' => 'Budi Santoso',
            'bio' => 'Ketua kelompok 1',
        ]);
        $announcement = Announcement::factory()->create();
        $announcement->likes()->create(['user_id' => $author->id]);
        $announcement->comments()->create([
            'user_id' => $author->id,
            'body' => 'Siap, KM.',
        ]);

        $viewer = User::factory()->anggota()->create();

        $this->actingAs($viewer)
            ->get(route('announcements.show', $announcement))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Ketua kelompok 1')
            ->assertSee('menyukai')
            ->assertSee('Siap, KM.');

        $this->actingAs($viewer)
            ->postJson(route('announcements.likes.store', $announcement))
            ->assertOk()
            ->assertJsonPath('liked', true)
            ->assertJsonPath('likes_count', 2)
            ->assertSee($viewer->name, false);
    }
}
