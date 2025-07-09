<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserEmailFactory extends Factory
{
    protected $model = UserEmail::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fake()->unique()->safeEmail(),
            'is_primary' => false,
            'verified_at' => fake()->boolean(70) ? now() : null, // 70% chance to be verified
        ];
    }

    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => true,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'verified_at' => now(),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'verified_at' => null,
        ]);
    }
}
