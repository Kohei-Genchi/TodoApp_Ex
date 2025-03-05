<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\Api\CategoryApiController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Todo API Routes
Route::prefix('todos')->group(function () {
    Route::get('/', [TodoController::class, 'apiIndex']);
    Route::post('/', [TodoController::class, 'store']);
    Route::get('/trashed', [TodoController::class, 'trashedApi']);
    Route::delete('/trash/empty', [TodoController::class, 'emptyTrash']);
    Route::get('/{todo}', [TodoController::class, 'show']);
    Route::put('/{todo}', [TodoController::class, 'update']);
    Route::patch('/{todo}/toggle', [TodoController::class, 'toggle']);
    Route::patch('/{todo}/trash', [TodoController::class, 'trash']);
    Route::patch('/{todo}/restore', [TodoController::class, 'restore']);
    Route::delete('/{todo}', [TodoController::class, 'destroy']);
});

// Category API Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryApiController::class, 'index']);
    Route::post('/', [CategoryApiController::class, 'store']);
    Route::put('/{category}', [CategoryApiController::class, 'update']);
    Route::delete('/{category}', [CategoryApiController::class, 'destroy']);
});
