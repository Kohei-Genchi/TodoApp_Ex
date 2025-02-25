<?php

use App\Http\Controllers\TodoController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return redirect()->route('todos.index');
})->name('home');


Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');


require __DIR__.'/auth.php';


Route::get('/dashboard', function () {
    return redirect()->route('todos.index', ['view' => 'today']);
})->middleware(['auth'])->name('dashboard');


Route::middleware(['auth'])->group(function () {

    Route::get('/todos/trash', [TodoController::class, 'trashed'])->name('todos.trashed');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::put('/todos/{todo}', [TodoController::class, 'update'])->name('todos.update');
    Route::patch('/todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');
    Route::patch('/todos/{todo}/trash', [TodoController::class, 'trash'])->name('todos.trash');
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');


    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
