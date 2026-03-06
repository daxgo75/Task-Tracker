<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user  = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_user_can_create_a_project(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/projects', [
            'name'        => 'My First Project',
            'description' => 'A test project description',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'My First Project')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('projects', [
            'name'       => 'My First Project',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_project_creation_requires_name(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/projects', [
            'description' => 'No name provided',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_user_can_list_own_projects(): void
    {
        Project::factory()->count(3)->create(['created_by' => $this->user->id]);

        $otherUser = User::factory()->create();
        Project::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->withToken($this->token)->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_user_can_search_projects_by_name(): void
    {
        Project::factory()->create(['created_by' => $this->user->id, 'name' => 'Alpha Project']);
        Project::factory()->create(['created_by' => $this->user->id, 'name' => 'Beta Project']);
        Project::factory()->create(['created_by' => $this->user->id, 'name' => 'Gamma Task']);

        $response = $this->withToken($this->token)->getJson('/api/projects?search=Project');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_user_can_update_project(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->user->id,
            'name'       => 'Old Name',
        ]);

        $response = $this->withToken($this->token)->putJson("/api/projects/{$project->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'New Name']);
    }

    public function test_user_can_toggle_project_status(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->user->id,
            'status'     => 'active',
        ]);

        $response = $this->withToken($this->token)->patchJson("/api/projects/{$project->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'archived');

        // Toggle back
        $this->withToken($this->token)->patchJson("/api/projects/{$project->id}/toggle-status");
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'active']);
    }

    public function test_user_cannot_access_other_users_project(): void
    {
        $otherUser    = User::factory()->create();
        $otherProject = Project::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->withToken($this->token)->getJson("/api/projects/{$otherProject->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_filter_projects_by_status(): void
    {
        Project::factory()->create(['created_by' => $this->user->id, 'status' => 'active']);
        Project::factory()->create(['created_by' => $this->user->id, 'status' => 'active']);
        Project::factory()->create(['created_by' => $this->user->id, 'status' => 'archived']);

        $response = $this->withToken($this->token)->getJson('/api/projects?status=active');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }
}
