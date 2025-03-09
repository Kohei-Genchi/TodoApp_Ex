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

/**
 * 認証ルートの読み込み
 */
require __DIR__ . "/auth.php";

/**
 * ホームページリダイレクト
 */
Route::get("/", function () {
    return redirect()->route("todos.index");
})->name("home");

/**
 * ゲストログイン機能
 */
Route::get("/guest-login", [GuestLoginController::class, "login"])
    ->middleware("guest")
    ->name("guest.login");


    // @Todo delete debug code
/**
 * カテゴリーデバッグ用エンドポイント
 */
Route::get("/debug-categories", function () {
    try {
        if (!Auth::check()) {
            return "Not logged in";
        }

        $user = Auth::user();

        // 直接DBクエリ
        $rawCategories = DB::select(
            "SELECT * FROM categories WHERE user_id = ?",
            [$user->id]
        );

        // Eloquentクエリ
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
// @Todo web.php内で良い?
/**
 * Web用カテゴリーAPI
 */
Route::get("/api/web-categories", [CategoryApiController::class, "index"]);

/**
 * Todoアプリメインページ
 */
Route::get("/todos", [TodoController::class, "index"])->name("todos.index");

/**
 * ダッシュボードリダイレクト
 */
Route::get("/dashboard", function () {
    return redirect()->route("todos.index", ["view" => "today"]);
})
    ->middleware(["auth"])
    ->name("dashboard");

/**
 * 認証が必要なルートグループ
 */
Route::middleware(["auth"])->group(function () {
    /**
     * Todoルート
     */
    // ゴミ箱関連
    Route::get("/todos/trash", [TodoController::class, "trashed"])
        ->name("todos.trashed");

    Route::delete("/todos/trash/empty", [TodoController::class, "emptyTrash"])
        ->name("todos.trash.empty");

    // タスク操作
    Route::post("/todos", [TodoController::class, "store"])
        ->name("todos.store");

    Route::put("/todos/{todo}", [TodoController::class, "update"])
        ->name("todos.update");

    Route::patch("/todos/{todo}/toggle", [TodoController::class, "toggle"])
        ->name("todos.toggle");

    Route::patch("/todos/{todo}/trash", [TodoController::class, "trash"])
        ->name("todos.trash");

    Route::patch("/todos/{todo}/restore", [TodoController::class, "restore"])
        ->name("todos.restore");

    Route::delete("/todos/{todo}", [TodoController::class, "destroy"])
        ->name("todos.destroy");

    /**
     * カテゴリールート
     */
    Route::get("/categories", [CategoryController::class, "index"])
        ->name("categories.index");

    Route::post("/categories", [CategoryController::class, "store"])
        ->name("categories.store");

    Route::put("/categories/{category}", [CategoryController::class, "update"])
        ->name("categories.update");

    Route::delete("/categories/{category}", [CategoryController::class, "destroy"])
        ->name("categories.destroy");

        // @Todo Profileへのリンクを作る。→UserNameにリンクをつける。ログアウト機能はProfileへ
    /**
     * プロフィールルート
     */
    Route::get("/profile", [ProfileController::class, "edit"])
        ->name("profile.edit");

    Route::patch("/profile", [ProfileController::class, "update"])
        ->name("profile.update");

    Route::delete("/profile", [ProfileController::class, "destroy"])
        ->name("profile.destroy");


        // @Todo apiなのにweb.php内で良い?
    /**
     * メモリスト部分ビュー取得API
     */
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


    /**
     * カテゴリーAPI
     */
    Route::post("/api/categories", [CategoryApiController::class, "store"])
        ->name("api.categories.store");
});
