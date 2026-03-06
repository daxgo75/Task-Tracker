<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Traits\ApiResponder;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Categories", description="Daftar kategori task")
 */
class CategoryController extends Controller
{
    use ApiResponder;

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Daftar semua kategori task",
     *     description="Mengembalikan semua kategori yang tersedia untuk digunakan sebagai pilihan saat membuat/update task.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kategori berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CategoryResource"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return $this->success(
            CategoryResource::collection($categories),
            'Daftar kategori berhasil diambil.'
        );
    }
}
