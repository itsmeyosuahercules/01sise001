<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnnouncementAttachment>
 */
class AnnouncementAttachmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'announcement_id' => Announcement::factory(),
            'disk' => 'local',
            'path' => 'announcements/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime' => 'application/pdf',
            'size' => 2048,
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes): array => [
            'path' => 'announcements/'.fake()->uuid().'.jpg',
            'original_name' => fake()->word().'.jpg',
            'mime' => 'image/jpeg',
        ]);
    }
}
