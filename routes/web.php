<?php
// In routes/web.php - Add these routes

use App\Http\Controllers\Auth\GuestLoginController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\CategoryApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\StripSubscriptionController;

// Auth routes
require __DIR__ . "/auth.php";

// Home page redirect
Route::get("/", function () {
    return redirect()->route("todos.index");
});

// Google login
Route::get("/auth/google", [GoogleController::class, "redirectToGoogle"]);
Route::get("/auth/google/callback", [
    GoogleController::class,
    "handleGoogleCallback",
]);

// Subscription routes
Route::get("stripe/subscription", [
    StripSubscriptionController::class,
    "index",
])->name("stripe.subscription");
Route::get("stripe/subscription/checkout", [
    StripSubscriptionController::class,
    "checkout",
])->name("stripe.subscription.checkout");
Route::get("stripe/subscription/comp", [
    StripSubscriptionController::class,
    "comp",
])->name("stripe.subscription.comp");
Route::get("stripe/subscription/customer_portal", [
    StripSubscriptionController::class,
    "customer_portal",
])->name("stripe.subscription.customer_portal");

// Guest login
Route::get("/guest-login", [GuestLoginController::class, "login"])
    ->middleware("guest")
    ->name("guest.login");

// API routes for categories
Route::get("/api/web-categories", [CategoryApiController::class, "index"]);

// Main todo routes
Route::get("/todos", [TodoController::class, "index"])->name("todos.index");

// IMPORTANT: Add these route for Vue Router to work
Route::get("/todos/today", [TodoController::class, "index"])->name(
    "todos.today"
);
Route::get("/todos/calendar", [TodoController::class, "index"])->name(
    "todos.calendar"
);

// Dashboard redirect
Route::get("/dashboard", function () {
    return redirect()->route("todos.index", ["view" => "today"]);
})
    ->middleware(["auth"])
    ->name("dashboard");

// Routes that require authentication
Route::middleware(["auth"])->group(function () {
    // Todo actions
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
    Route::delete("/todos/{todo}", [TodoController::class, "destroy"])->name(
        "todos.destroy"
    );

    // Category routes
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

    // Profile routes
    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit"
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update"
    );
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy"
    );

    // API routes for memos
    Route::get("/api/memos-partial", function () {
        $memos = Auth::user()
            ->todos()
            ->with("category")
            ->whereNull("due_date")
            ->where("status", "pending")
            ->orderBy("created_at", "desc")
            ->get();
        return view("layouts.partials.memo-list", compact("memos"));
    });

    // API routes for categories
    Route::post("/api/categories", [
        CategoryApiController::class,
        "store",
    ])->name("api.categories.store");
});
