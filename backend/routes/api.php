<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication (public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Categories (dropdown data)
    Route::get('/categories', [CategoryController::class, 'index']);

    // Projects
    Route::apiResource('projects', ProjectController::class)->except(['destroy']);
    Route::patch('/projects/{id}/toggle-status', [ProjectController::class, 'toggleStatus']);

    // Tasks (scoped inside project)
    Route::get('/projects/{projectId}/tasks', [TaskController::class, 'index']);
    Route::post('/projects/{projectId}/tasks', [TaskController::class, 'store']);

    // Tasks (global / standalone)
    Route::get('/tasks', [TaskController::class, 'globalIndex']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});
