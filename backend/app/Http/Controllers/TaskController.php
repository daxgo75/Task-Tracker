<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Traits\ApiResponder;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Tasks", description="Manajemen task dalam project")
 */
class TaskController extends Controller
{
    use ApiResponder;

    /**
     * @OA\Get(
     *     path="/api/projects/{projectId}/tasks",
     *     tags={"Tasks"},
     *     summary="Daftar task dalam satu project",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Daftar task berhasil diambil"),
     *     @OA\Response(response=404, description="Project tidak ditemukan"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function index(Request $request, int $projectId): JsonResponse
    {
        $project = Project::where('created_by', $request->user()->id)->find($projectId);

        if (! $project) {
            return $this->notFound('Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $query = Task::with(['category', 'creator'])
            ->where('project_id', $projectId)
            ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $tasks = $query->orderBy('due_date')->paginate($request->integer('per_page', 50));

        return $this->success(
            TaskResource::collection($tasks)->response()->getData(true),
            'Daftar task berhasil diambil.'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Daftar semua task lintas project (global)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Daftar task global berhasil diambil"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function globalIndex(Request $request): JsonResponse
    {
        $userProjectIds = Project::where('created_by', $request->user()->id)->pluck('id');

        $query = Task::with(['category', 'creator', 'project'])
            ->whereIn('project_id', $userProjectIds)
            ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        $tasks = $query->orderBy('due_date')->paginate($request->integer('per_page', 20));

        return $this->success(
            TaskResource::collection($tasks)->response()->getData(true),
            'Daftar task global berhasil diambil.'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/projects/{projectId}/tasks",
     *     tags={"Tasks"},
     *     summary="Buat task baru dalam project",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id","title"},
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Implementasi Login"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-12-31")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Task berhasil dibuat"),
     *     @OA\Response(response=404, description="Project tidak ditemukan"),
     *     @OA\Response(response=422, description="Validasi gagal"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function store(StoreTaskRequest $request, int $projectId): JsonResponse
    {
        $project = Project::where('created_by', $request->user()->id)->find($projectId);

        if (! $project) {
            return $this->notFound('Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $task = Task::create([
            'project_id'  => $projectId,
            'category_id' => $request->category_id,
            'created_by'  => $request->user()->id,
            'title'       => $request->title,
            'description' => $request->description,
            'due_date'    => $request->due_date,
        ]);

        $task->load(['category', 'creator', 'project']);

        return $this->created(new TaskResource($task), 'Task berhasil dibuat.');
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Detail task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail task berhasil diambil"),
     *     @OA\Response(response=404, description="Task tidak ditemukan"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userProjectIds = Project::where('created_by', $request->user()->id)->pluck('id');

        $task = Task::with(['category', 'creator', 'project'])
            ->whereIn('project_id', $userProjectIds)
            ->whereNull('deleted_at')
            ->find($id);

        if (! $task) {
            return $this->notFound('Task tidak ditemukan atau Anda tidak memiliki akses.');
        }

        return $this->success(new TaskResource($task), 'Detail task berhasil diambil.');
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Update task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="due_date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Task berhasil diperbarui"),
     *     @OA\Response(response=404, description="Task tidak ditemukan"),
     *     @OA\Response(response=422, description="Validasi gagal"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $userProjectIds = Project::where('created_by', $request->user()->id)->pluck('id');

        $task = Task::whereIn('project_id', $userProjectIds)
            ->whereNull('deleted_at')
            ->find($id);

        if (! $task) {
            return $this->notFound('Task tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $task->update($request->only([
            'category_id', 'title', 'description', 'due_date',
        ]));

        $task->load(['category', 'creator', 'project']);

        return $this->success(new TaskResource($task), 'Task berhasil diperbarui.');
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Hapus task (soft delete)",
     *     description="Menghapus task secara soft delete. Field deleted_at dan deleted_by akan terisi.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task berhasil dihapus"),
     *     @OA\Response(response=404, description="Task tidak ditemukan"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userProjectIds = Project::where('created_by', $request->user()->id)->pluck('id');

        $task = Task::whereIn('project_id', $userProjectIds)
            ->whereNull('deleted_at')
            ->find($id);

        if (! $task) {
            return $this->notFound('Task tidak ditemukan atau sudah dihapus sebelumnya.');
        }

        $task->update([
            'deleted_at' => now(),
            'deleted_by' => $request->user()->id,
        ]);

        return $this->success(null, "Task \"{$task->title}\" berhasil dihapus.");
    }
}
