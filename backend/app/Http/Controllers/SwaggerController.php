<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Task Tracker API",
 *     version="1.0.0",
 *     description="API untuk manajemen project dan task. Autentikasi menggunakan Laravel Sanctum Personal Access Token.",
 *     @OA\Contact(email="admin@tasktracker.com")
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Masukkan token yang didapat dari endpoint /api/auth/login"
 * )
 *
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Admin"),
 *     @OA\Property(property="email", type="string", format="email", example="admin@tasktracker.com")
 * )
 *
 * @OA\Schema(
 *     schema="CategoryResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Feature"),
 *     @OA\Property(property="color", type="string", example="#6366f1")
 * )
 *
 * @OA\Schema(
 *     schema="ProjectResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Website Redesign"),
 *     @OA\Property(property="description", type="string", example="Redesain tampilan website"),
 *     @OA\Property(property="status", type="string", enum={"active","archived"}, example="active"),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 *
 * @OA\Schema(
 *     schema="TaskResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="project_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Implementasi Login"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-12-31"),
 *     @OA\Property(property="status", type="string", enum={"todo","in_progress","done"}, example="todo"),
 *     @OA\Property(property="is_deleted", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 */
class SwaggerController extends Controller {}
