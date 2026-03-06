<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Project $project;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->token    = $this->user->createToken('test')->plainTextToken;
        $this->project  = Project::factory()->create(['created_by' => $this->user->id]);
        $this->category = Category::create(['name' => 'Feature']);
    }

    private function taskPayload(array $overrides = []): array
    {
        return array_merge([
            'category_id' => $this->category->id,
            'title'       => 'Test Task',
            'description' => 'A test task description',
            'due_date'    => now()->addDays(10)->toDateString(),
        ], $overrides);
    }

    public function test_user_can_create_task_in_project(): void
    {
        $response = $this->withToken($this->token)
            ->postJson("/api/projects/{$this->project->id}/tasks", $this->taskPayload());

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.title', 'Test Task');

        $this->assertDatabaseHas('tasks', [
            'project_id' => $this->project->id,
            'title'      => 'Test Task',
            'deleted_at' => null,
        ]);
    }

    public function test_task_creation_requires_title_and_category(): void
    {
        $response = $this->withToken($this->token)
            ->postJson("/api/projects/{$this->project->id}/tasks", []);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['title', 'category_id']]);
    }

    public function test_task_creation_fails_with_nonexistent_category(): void
    {
        $response = $this->withToken($this->token)
            ->postJson("/api/projects/{$this->project->id}/tasks", $this->taskPayload([
                'category_id' => 9999,
            ]));

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['category_id']]);
    }

    public function test_user_can_list_tasks_in_project(): void
    {
        Task::factory()->count(4)->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/projects/{$this->project->id}/tasks");

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data.data'));
    }

    public function test_user_can_update_task(): void
    {
        $task = Task::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'created_by'  => $this->user->id,
            'title'       => 'Old Title',
        ]);

        $newCategory = Category::create(['name' => 'InProgress']);

        $response = $this->withToken($this->token)
            ->putJson("/api/tasks/{$task->id}", [
                'title'       => 'Updated Title',
                'category_id' => $newCategory->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.category.name', 'InProgress');
    }

    public function test_user_can_soft_delete_task(): void
    {
        $task = Task::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify soft delete fields are set
        $task->refresh();
        $this->assertNotNull($task->deleted_at);
        $this->assertEquals($this->user->id, $task->deleted_by);
    }

    public function test_deleted_task_does_not_appear_in_list(): void
    {
        Task::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'deleted_at'  => now(),
            'deleted_by'  => $this->user->id,
        ]);
        Task::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/projects/{$this->project->id}/tasks");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_user_can_search_tasks_by_title(): void
    {
        Task::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'title'       => 'Implement Login Feature',
        ]);
        Task::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'title'       => 'Fix Database Bug',
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/projects/{$this->project->id}/tasks?search=Login");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_user_can_view_global_task_list(): void
    {
        $project2 = Project::factory()->create(['created_by' => $this->user->id]);

        Task::factory()->create(['project_id' => $this->project->id, 'category_id' => $this->category->id, 'created_by' => $this->user->id]);
        Task::factory()->create(['project_id' => $project2->id, 'category_id' => $this->category->id, 'created_by' => $this->user->id]);

        $otherUser    = User::factory()->create();
        $otherProject = Project::factory()->create(['created_by' => $otherUser->id]);
        Task::factory()->create(['project_id' => $otherProject->id, 'category_id' => $this->category->id, 'created_by' => $otherUser->id]);

        $response = $this->withToken($this->token)->getJson('/api/tasks');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_delete_task_again_returns_404(): void
    {
        $task = Task::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'deleted_at'  => now(),
            'deleted_by'  => $this->user->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(404);
    }
}
