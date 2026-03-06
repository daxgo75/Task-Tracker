<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Traits\ApiResponder;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Projects", description="Manajemen project")
 */
class ProjectController extends Controller
{
    use ApiResponder;

    /**
     * @OA\Get(
     *     path="/api/projects",
     *     tags={"Projects"},
     *     summary="Daftar semua project",
     *     description="Menampilkan semua project milik user yang sedang login. Mendukung filter status dan pencarian judul.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, description="Cari project berdasarkan nama", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter berdasarkan status", @OA\Schema(type="string", enum={"active","archived"})),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Jumlah data per halaman", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Daftar project berhasil diambil"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['creator', 'activeTasks.category'])
            ->where('created_by', $request->user()->id);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'archived'])) {
            $query->where('status', $request->status);
        }

        $projects = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->success(
            ProjectResource::collection($projects)->response()->getData(true),
            'Daftar project berhasil diambil.'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/projects",
     *     tags={"Projects"},
     *     summary="Buat project baru",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Website Redesign"),
     *             @OA\Property(property="description", type="string", example="Redesain tampilan website utama"),
     *             @OA\Property(property="status", type="string", enum={"active","archived"}, example="active")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Project berhasil dibuat"),
     *     @OA\Response(response=422, description="Validasi gagal"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            'created_by'  => $request->user()->id,
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => $request->input('status', 'active'),
        ]);

        $project->load(['creator', 'activeTasks']);

        return $this->created(new ProjectResource($project), 'Project berhasil dibuat.');
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{id}",
     *     tags={"Projects"},
     *     summary="Detail project beserta daftar task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail project berhasil diambil"),
     *     @OA\Response(response=404, description="Project tidak ditemukan"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $project = Project::with(['creator', 'activeTasks.category', 'activeTasks.creator'])
            ->where('created_by', $request->user()->id)
            ->find($id);

        if (! $project) {
            return $this->notFound('Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        return $this->success(new ProjectResource($project), 'Detail project berhasil diambil.');
    }

    /**
     * @OA\Put(
     *     path="/api/projects/{id}",
     *     tags={"Projects"},
     *     summary="Update informasi project",
     *     description="Perbarui nama, deskripsi, atau status project. Gunakan endpoint ini juga untuk archive/restore project.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Website Redesign v2"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"active","archived"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Project berhasil diperbarui"),
     *     @OA\Response(response=404, description="Project tidak ditemukan"),
     *     @OA\Response(response=422, description="Validasi gagal"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        $project = Project::where('created_by', $request->user()->id)->find($id);

        if (! $project) {
            return $this->notFound('Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $project->update($request->only(['name', 'description', 'status']));
        $project->load(['creator', 'activeTasks.category']);

        return $this->success(new ProjectResource($project), 'Project berhasil diperbarui.');
    }

    /**
     * @OA\Patch(
     *     path="/api/projects/{id}/toggle-status",
     *     tags={"Projects"},
     *     summary="Toggle status project (active ↔ archived)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Status project berhasil diubah"),
     *     @OA\Response(response=404, description="Project tidak ditemukan"),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $project = Project::where('created_by', $request->user()->id)->find($id);

        if (! $project) {
            return $this->notFound('Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $project->status = $project->status === 'active' ? 'archived' : 'active';
        $project->save();

        $statusLabel = $project->status === 'active' ? 'aktif' : 'diarsipkan';

        return $this->success(
            new ProjectResource($project),
            "Status project berhasil diubah menjadi {$statusLabel}."
        );
    }
}
