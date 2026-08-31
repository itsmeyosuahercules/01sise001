<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nim = fake()->unique()->numerify('2410######');

        return [
            'nim' => $nim,
            'name' => fake()->name(),
            'email' => User::emailFromNim($nim),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make((string) config('kelas.default_password')),
            'role' => UserRole::Anggota,
            'is_placeholder' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function km(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Km,
        ]);
    }

    public function anggota(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Anggota,
        ]);
    }

    public function placeholder(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_placeholder' => true,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
