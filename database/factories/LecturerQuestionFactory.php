<?php

namespace Database\Factories;

use App\Enums\LecturerQuestionKind;
use App\Enums\LecturerQuestionStatus;
use App\Models\LecturerQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LecturerQuestion>
 */
class LecturerQuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->anggota(),
            'kind' => fake()->randomElement(LecturerQuestionKind::cases()),
            'topic' => fake()->randomElement(['Basis Data', 'Pemrograman Web', 'Pak Andi']),
            'body' => fake()->sentence(12),
            'hide_name' => false,
            'status' => LecturerQuestionStatus::Baru,
        ];
    }

    public function selected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LecturerQuestionStatus::Dipilih,
            'curator_id' => User::factory()->km(),
            'curated_at' => now(),
        ]);
    }

    public function held(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LecturerQuestionStatus::Ditahan,
            'curator_id' => User::factory()->km(),
            'curated_at' => now(),
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LecturerQuestionStatus::Terkirim,
            'curator_id' => User::factory()->km(),
            'curated_at' => now(),
            'sent_at' => now(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes): array => [
            'hide_name' => true,
        ]);
    }
}
