<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_by'  => User::factory(),
            'name'        => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'status'      => $this->faker->randomElement(['active', 'archived']),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function archived(): static
    {
        return $this->state(['status' => 'archived']);
    }
}
