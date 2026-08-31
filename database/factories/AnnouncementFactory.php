<?php

namespace Database\Factories;

use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->km(),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraphs(2, true),
            'category' => fake()->randomElement(AnnouncementCategory::cases()),
            'is_pinned' => false,
            'published_at' => now(),
            'expires_at' => null,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_pinned' => true,
        ]);
    }
}
