<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponder;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Dashboard", description="Ringkasan statistik dan aktivitas")
 */
class DashboardController extends Controller
{
    use ApiResponder;

    /**
     * @OA\Get(
     *     path="/api/dashboard",
     *     tags={"Dashboard"},
     *     summary="Data ringkasan dashboard",
     *     description="Menampilkan total project aktif, total task belum selesai, dan task yang mendekati due date.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="upcoming_days", in="query", required=false, description="Jumlah hari ke depan untuk task mendekati due date (default: 7)", @OA\Schema(type="integer", default=7)),
     *     @OA\Response(
     *         response=200,
     *         description="Data dashboard berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_active_projects", type="integer", example=5),
     *                 @OA\Property(property="total_incomplete_tasks", type="integer", example=23),
     *                 @OA\Property(property="upcoming_tasks", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $days   = $request->integer('upcoming_days', 7);

        $userProjectIds = Project::where('created_by', $userId)->pluck('id');

        $totalActiveProjects = Project::where('created_by', $userId)
            ->where('status', 'active')
            ->count();

        $totalIncompleteTasks = Task::whereIn('project_id', $userProjectIds)
            ->whereNull('deleted_at')
            ->whereHas('category', fn ($q) => $q->where('name', '!=', 'Done'))
            ->count();

        $upcomingTasks = Task::with(['category', 'project'])
            ->whereIn('project_id', $userProjectIds)
            ->upcomingDue($days)
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(fn ($task) => [
                'id'       => $task->id,
                'title'    => $task->title,
                'due_date' => $task->due_date?->toDateString(),
                'project'  => ['id' => $task->project->id, 'name' => $task->project->name],
                'category' => ['id' => $task->category->id, 'name' => $task->category->name],
            ]);

        $tasksByCategory = Task::whereIn('project_id', $userProjectIds)
            ->whereNull('deleted_at')
            ->join('categories', 'tasks.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, COUNT(tasks.id) as count')
            ->groupBy('categories.name')
            ->pluck('count', 'category_name')
            ->map(fn ($count) => (int) $count);

        return $this->success([
            'total_active_projects'  => $totalActiveProjects,
            'total_incomplete_tasks' => $totalIncompleteTasks,
            'tasks_by_category'      => $tasksByCategory,
            'upcoming_tasks'         => $upcomingTasks,
        ], 'Data dashboard berhasil diambil.');
    }
}
