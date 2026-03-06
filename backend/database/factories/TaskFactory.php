<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'category_id' => Category::factory(),
            'created_by'  => User::factory(),
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'due_date'    => $this->faker->optional()->dateTimeBetween('now', '+3 months')?->format('Y-m-d'),
            'deleted_at'  => null,
            'deleted_by'  => null,
        ];
    }
}
