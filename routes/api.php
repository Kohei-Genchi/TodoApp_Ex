<?php

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// User routes that require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Todo API Routes - temporarily without auth for debugging
Route::get('/todos', [TodoController::class, 'apiIndex']);
Route::post('/todos', [TodoController::class, 'store']);

// These routes need to be before the {todo} parameter routes to avoid conflicts
Route::get('/todos/trash', [TodoController::class, 'trashedApi']);
Route::delete('/todos/trash/empty', [TodoController::class, 'emptyTrash']);

// Routes with {todo} parameter
Route::get('/todos/{todo}', [TodoController::class, 'show']);
Route::put('/todos/{todo}', [TodoController::class, 'update']);
Route::delete('/todos/{todo}', [TodoController::class, 'destroy']);
Route::patch('/todos/{todo}/toggle', [TodoController::class, 'toggle']);
Route::patch('/todos/{todo}/trash', [TodoController::class, 'trash']);  // Updated to use trash method
Route::patch('/todos/{todo}/restore', [TodoController::class, 'restore']);

// Category API Routes - temporarily without auth for debugging
Route::apiResource('categories', CategoryApiController::class);

// Public API routes
Route::post('/categories', [CategoryApiController::class, 'store'])->name('api.categories.store');
