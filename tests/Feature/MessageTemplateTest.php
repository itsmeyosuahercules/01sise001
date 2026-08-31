<?php

namespace Tests\Feature;

use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_view_templates(): void
    {
        $member = User::factory()->anggota()->create();
        MessageTemplate::factory()->create([
            'title' => 'Izin kolektif ke dosen',
        ]);

        $this->actingAs($member)
            ->get(route('message-templates.index'))
            ->assertOk()
            ->assertSee('Izin kolektif ke dosen');
    }

    public function test_members_cannot_create_templates(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('message-templates.store'), [
                'title' => 'Tidak boleh',
                'body' => 'Isi',
            ])
            ->assertForbidden();
    }

    public function test_officers_can_create_templates(): void
    {
        $km = User::factory()->km()->create();

        $this->actingAs($km)
            ->post(route('message-templates.store'), [
                'title' => 'Ucapan seminar',
                'body' => 'Halo {{nama}} dari {{kelas}}',
            ])
            ->assertRedirect(route('message-templates.index'));

        $this->assertDatabaseHas('message_templates', [
            'title' => 'Ucapan seminar',
        ]);
    }
}
