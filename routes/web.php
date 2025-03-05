<?php

use App\Http\Controllers\Auth\GuestLoginController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

require __DIR__ . "/auth.php";

Route::get("/", function () {
    return redirect()->route("todos.index");
})->name("home");

// 簡単ログイン
Route::get("/guest-login", [GuestLoginController::class, "login"])
    ->middleware("guest")
    ->name("guest.login");

// In routes/web.php
Route::get("/debug-categories", function () {
    try {
        if (!Auth::check()) {
            return "Not logged in";
        }

        $user = Auth::user();

        // Direct DB query
        $rawCategories = DB::select(
            "SELECT * FROM categories WHERE user_id = ?",
            [$user->id]
        );

        // Eloquent query
        $categories = $user->categories()->get();

        return [
            "user_id" => $user->id,
            "raw_categories_count" => count($rawCategories),
            "raw_categories" => $rawCategories,
            "eloquent_categories_count" => $categories->count(),
            "eloquent_categories" => $categories,
        ];
    } catch (\Exception $e) {
        return [
            "error" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine(),
        ];
    }
});
// In routes/web.php
Route::get("/api/web-categories", [
    App\Http\Controllers\Api\CategoryApiController::class,
    "index",
]);

Route::get("/todos", [TodoController::class, "index"])->name("todos.index");

Route::get("/dashboard", function () {
    return redirect()->route("todos.index", ["view" => "today"]);
})
    ->middleware(["auth"])
    ->name("dashboard");

Route::middleware(["auth"])->group(function () {
    Route::get("/todos/trash", [TodoController::class, "trashed"])->name(
        "todos.trashed"
    );
    Route::delete("/todos/trash/empty", [
        TodoController::class,
        "emptyTrash",
    ])->name("todos.trash.empty"); // 新規追加：ゴミ箱を空にする機能
    Route::post("/todos", [TodoController::class, "store"])->name(
        "todos.store"
    );
    Route::put("/todos/{todo}", [TodoController::class, "update"])->name(
        "todos.update"
    );
    Route::patch("/todos/{todo}/toggle", [
        TodoController::class,
        "toggle",
    ])->name("todos.toggle");
    Route::patch("/todos/{todo}/trash", [TodoController::class, "trash"])->name(
        "todos.trash"
    );
    Route::patch("/todos/{todo}/restore", [
        TodoController::class,
        "restore",
    ])->name("todos.restore"); // 新規追加：ゴミ箱からタスクを復元
    Route::delete("/todos/{todo}", [TodoController::class, "destroy"])->name(
        "todos.destroy"
    );

    Route::get("/categories", [CategoryController::class, "index"])->name(
        "categories.index"
    );
    Route::post("/categories", [CategoryController::class, "store"])->name(
        "categories.store"
    );
    Route::put("/categories/{category}", [
        CategoryController::class,
        "update",
    ])->name("categories.update");
    Route::delete("/categories/{category}", [
        CategoryController::class,
        "destroy",
    ])->name("categories.destroy");

    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit"
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update"
    );
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy"
    );

    Route::post("/api/categories", [
        App\Http\Controllers\Api\CategoryApiController::class,
        "store",
    ])->name("api.categories.store");
});
