<?php

namespace Database\Factories;

use App\Enums\AbsenceStatus;
use App\Enums\AbsenceType;
use App\Models\AbsenceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbsenceRequest>
 */
class AbsenceRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsOn = now()->toDateString();

        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(AbsenceType::cases()),
            'starts_on' => $startsOn,
            'ends_on' => $startsOn,
            'reason' => fake()->sentence(),
            'status' => AbsenceStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AbsenceStatus::Disetujui,
            'reviewer_id' => User::factory()->km(),
            'reviewed_at' => now(),
        ]);
    }
}
