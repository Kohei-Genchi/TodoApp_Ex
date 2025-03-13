<?php

use App\Http\Controllers\Auth\GuestLoginController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Models\Todo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\StripSubscriptionController;

/**
 * Authentication routes
 */
require __DIR__ . "/auth.php";

/**
 * Vue.js SPA Route - Catch all routes and let Vue Router handle them
 * This should be the LAST route in the web.php file
 */
Route::get("/{any?}", function () {
    return view("layouts.app");
})->where(
    "any",
    "^(?!api|logout|login|register|password|auth|guest-login|stripe/subscription/webhook|stripe/subscription/checkout|stripe/subscription/comp|stripe/subscription/customer_portal).*"
);

/**
 * Guest login functionality
 */
Route::get("/guest-login", [GuestLoginController::class, "login"])
    ->middleware("guest")
    ->name("guest.login");

// Google login
Route::get("/auth/google", [GoogleController::class, "redirectToGoogle"]);
Route::get("/auth/google/callback", [
    GoogleController::class,
    "handleGoogleCallback",
]);

# Stripe checkout related routes
Route::get("stripe/subscription/checkout", [
    StripSubscriptionController::class,
    "checkout",
])->name("stripe.subscription.checkout");

Route::post("/stripe/subscription/webhook", function (Request $request) {
    return response()->json(["message" => "Webhook received"]);
});

# Payment completion
Route::get("stripe/subscription/comp", [
    StripSubscriptionController::class,
    "comp",
])->name("stripe.subscription.comp");

# Customer portal
Route::get("stripe/subscription/customer_portal", [
    StripSubscriptionController::class,
    "customer_portal",
])->name("stripe.subscription.customer_portal");

/**
 * API Web routes for authentication-requiring features
 */
Route::middleware(["auth"])->group(function () {
    /**
     * Todo routes
     */
    // Trash related
    Route::get("/todos/trash", [TodoController::class, "trashed"])->name(
        "todos.trashed"
    );

    Route::delete("/todos/trash/empty", [
        TodoController::class,
        "emptyTrash",
    ])->name("todos.trash.empty");

    // Task operations
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
    ])->name("todos.restore");

    Route::delete("/todos/{todo}", [TodoController::class, "destroy"])->name(
        "todos.destroy"
    );

    /**
     * Category routes
     */
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

    /**
     * Profile routes
     */
    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit"
    );

    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update"
    );

    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy"
    );

    /**
     * API for memo listing
     */
    Route::get("/api/memos-partial", function () {
        $memos = Auth::user()
            ->todos()
            ->with("category")
            ->whereNull("due_date")
            ->where("status", "pending")
            ->orderBy("created_at", "desc")
            ->get();

        return response()->json($memos);
    });

    /**
     * Category API
     */
    Route::post("/api/categories", [
        CategoryApiController::class,
        "store",
    ])->name("api.categories.store");
});
