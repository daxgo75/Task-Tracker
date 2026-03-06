<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->token    = $this->user->createToken('test')->plainTextToken;
        $this->category = Category::create(['name' => 'Feature']);
    }

    public function test_dashboard_returns_correct_active_project_count(): void
    {
        Project::factory()->count(3)->create(['created_by' => $this->user->id, 'status' => 'active']);
        Project::factory()->create(['created_by' => $this->user->id, 'status' => 'archived']);

        $response = $this->withToken($this->token)->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_active_projects', 3);
    }

    public function test_dashboard_returns_correct_incomplete_task_count(): void
    {
        $project     = Project::factory()->create(['created_by' => $this->user->id]);
        $doneCategory = Category::create(['name' => 'Done']);

        // Two incomplete tasks (not in "Done" category)
        Task::factory()->create(['project_id' => $project->id, 'category_id' => $this->category->id, 'created_by' => $this->user->id]);
        Task::factory()->create(['project_id' => $project->id, 'category_id' => $this->category->id, 'created_by' => $this->user->id]);
        // One "done" task (in "Done" category — not counted)
        Task::factory()->create(['project_id' => $project->id, 'category_id' => $doneCategory->id, 'created_by' => $this->user->id]);
        // Soft-deleted task should not count
        Task::factory()->create([
            'project_id'  => $project->id,
            'category_id' => $this->category->id,
            'created_by'  => $this->user->id,
            'deleted_at'  => now(),
            'deleted_by'  => $this->user->id,
        ]);

        $response = $this->withToken($this->token)->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_incomplete_tasks', 2);
    }

    public function test_dashboard_returns_upcoming_tasks(): void
    {
        $project      = Project::factory()->create(['created_by' => $this->user->id]);
        $doneCategory = Category::create(['name' => 'Done']);

        // Task due in 3 days (upcoming, not done)
        Task::factory()->create([
            'project_id'  => $project->id,
            'category_id' => $this->category->id,
            'created_by'  => $this->user->id,
            'due_date'    => now()->addDays(3)->toDateString(),
        ]);

        // Task due in 30 days (not upcoming within 7 days)
        Task::factory()->create([
            'project_id'  => $project->id,
            'category_id' => $this->category->id,
            'created_by'  => $this->user->id,
            'due_date'    => now()->addDays(30)->toDateString(),
        ]);

        // Task in "Done" category (should not appear even if upcoming)
        Task::factory()->create([
            'project_id'  => $project->id,
            'category_id' => $doneCategory->id,
            'created_by'  => $this->user->id,
            'due_date'    => now()->addDays(2)->toDateString(),
        ]);

        $response = $this->withToken($this->token)->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.upcoming_tasks'));
    }

    public function test_dashboard_structure_is_correct(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_active_projects',
                    'total_incomplete_tasks',
                    'tasks_by_category',
                    'upcoming_tasks',
                ],
            ]);
    }
}
