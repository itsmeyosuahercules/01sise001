<?php

namespace Database\Factories;

use App\Models\SaturdayAttendance;
use App\Models\User;
use App\Support\SaturdayAttendanceWindow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaturdayAttendance>
 */
class SaturdayAttendanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'attended_on' => SaturdayAttendanceWindow::currentOrLatestSaturday()->toDateString(),
            'photo_path' => 'attendances/demo.jpg',
            'latitude' => -6.2615000,
            'longitude' => 106.6640000,
            'accuracy' => 18,
            'captured_at' => now(),
        ];
    }

    /**
     * A record marked manually by an officer, without photo or location.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes): array => [
            'marked_by' => User::factory(),
            'photo_path' => null,
            'latitude' => null,
            'longitude' => null,
            'accuracy' => null,
            'captured_at' => null,
            'note' => 'Ditandai hadir oleh KM',
        ]);
    }
}
