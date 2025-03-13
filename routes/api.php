<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\MemoApiController;
use App\Http\Controllers\StripSubscriptionController;

/**
 * User info API
 */
Route::middleware("auth:sanctum")->get("/user", function (Request $request) {
    return $request->user();
});

/**
 * Stripe webhook
 */
Route::post("/stripe/subscription/webhook", [
    StripSubscriptionController::class,
    "Webhook",
]);

/**
 * Todo API routes
 */
Route::prefix("todos")
    ->middleware(["web"])
    ->group(function () {
        // Get todos
        Route::get("/", [TodoController::class, "apiIndex"]);

        // Create todo
        Route::post("/", [TodoController::class, "store"]);

        // Trash related
        Route::get("/trashed", [TodoController::class, "trashedApi"]);
        Route::delete("/trash/empty", [TodoController::class, "emptyTrash"]);

        // Individual todo operations
        Route::get("/{todo}", [TodoController::class, "show"]);

        // Accept both PUT and POST
        Route::match(["put", "post"], "/{todo}", [
            TodoController::class,
            "update",
        ]);

        // Status operations
        Route::patch("/{todo}/toggle", [TodoController::class, "toggle"]);
        Route::patch("/{todo}/trash", [TodoController::class, "trash"]);
        Route::patch("/{todo}/restore", [TodoController::class, "restore"]);
        Route::delete("/{todo}", [TodoController::class, "destroy"]);
    });

/**
 * Category API routes
 */
Route::prefix("categories")
    ->middleware(["web"])
    ->group(function () {
        // Get categories
        Route::get("/", [CategoryApiController::class, "index"]);

        // Create category
        Route::post("/", [CategoryApiController::class, "store"]);

        // Update category
        Route::put("/{category}", [CategoryApiController::class, "update"]);

        // Delete category
        Route::delete("/{category}", [CategoryApiController::class, "destroy"]);
    });

// Add this route for web-categories that TodoApp is trying to access
Route::get("/web-categories", [CategoryApiController::class, "index"]);

/**
 * Memo API routes
 */
Route::prefix("memos")
    ->middleware(["web"])
    ->group(function () {
        // Get memos
        Route::get("/", [MemoApiController::class, "index"]);

        // Create memo
        Route::post("/", [MemoApiController::class, "store"]);

        // Trash memo
        Route::patch("/{todo}/trash", [MemoApiController::class, "trash"]);
    });
