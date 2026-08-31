<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\AnnouncementLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_like_and_unlike_an_announcement(): void
    {
        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($member)
            ->post(route('announcements.likes.store', $announcement))
            ->assertRedirect();

        $this->assertDatabaseHas('announcement_likes', [
            'announcement_id' => $announcement->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($member)
            ->post(route('announcements.likes.store', $announcement))
            ->assertRedirect();

        $this->assertSame(0, AnnouncementLike::query()->count());
    }

    public function test_liking_an_announcement_can_return_json_without_a_redirect(): void
    {
        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($member)
            ->postJson(route('announcements.likes.store', $announcement))
            ->assertOk()
            ->assertJson([
                'liked' => true,
                'likes_count' => 1,
            ]);
    }

    public function test_members_can_comment_on_an_announcement(): void
    {
        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($member)
            ->post(route('announcements.comments.store', $announcement), [
                'body' => 'Siap, KM.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('announcement_comments', [
            'announcement_id' => $announcement->id,
            'user_id' => $member->id,
            'body' => 'Siap, KM.',
        ]);
    }

    public function test_commenting_can_return_html_json_without_a_redirect(): void
    {
        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($member)
            ->postJson(route('announcements.comments.store', $announcement), [
                'body' => 'Catatan tanpa reload.',
            ])
            ->assertOk()
            ->assertJsonPath('comments_count', 1)
            ->assertSee('Catatan tanpa reload.', false);
    }

    public function test_a_comment_requires_a_body(): void
    {
        $member = User::factory()->anggota()->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($member)
            ->from(route('announcements.show', $announcement))
            ->post(route('announcements.comments.store', $announcement), [
                'body' => '',
            ])
            ->assertRedirect(route('announcements.show', $announcement))
            ->assertSessionHasErrors('body');
    }

    public function test_a_member_can_delete_their_own_comment(): void
    {
        $member = User::factory()->anggota()->create();
        $comment = AnnouncementComment::factory()->create([
            'user_id' => $member->id,
        ]);

        $this->actingAs($member)
            ->delete(route('announcements.comments.destroy', [$comment->announcement, $comment]))
            ->assertRedirect();

        $this->assertModelMissing($comment);
    }

    public function test_a_member_cannot_delete_someone_elses_comment(): void
    {
        $member = User::factory()->anggota()->create();
        $comment = AnnouncementComment::factory()->create();

        $this->actingAs($member)
            ->delete(route('announcements.comments.destroy', [$comment->announcement, $comment]))
            ->assertForbidden();
    }

    public function test_an_officer_can_delete_any_comment(): void
    {
        $km = User::factory()->km()->create();
        $comment = AnnouncementComment::factory()->create();

        $this->actingAs($km)
            ->delete(route('announcements.comments.destroy', [$comment->announcement, $comment]))
            ->assertRedirect();

        $this->assertModelMissing($comment);
    }
}
