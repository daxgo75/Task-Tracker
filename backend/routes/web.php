<?php

use App\Http\Controllers\DocumentationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Swagger API Documentation
Route::get('/api/documentation', [DocumentationController::class, 'index'])->name('l5-swagger.docs');

Route::get('/api/docs.json', function () {
    return response()->file(public_path('api-docs.json'), [
        'Content-Type' => 'application/json',
    ]);
})->name('l5-swagger.json');
