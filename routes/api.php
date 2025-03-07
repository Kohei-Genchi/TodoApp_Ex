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

/**
 * ユーザー情報取得API
 */
Route::middleware("auth:sanctum")->get("/user", function (Request $request) {
    return $request->user();
});

/**
 * Todo API ルート
 *
 * 現在は auth:sanctum ではなく web ミドルウェアを使用
 */
Route::prefix("todos")
    ->middleware(["web"])
    ->group(function () {
        // タスク一覧取得
        Route::get("/", [TodoController::class, "apiIndex"]);

        // タスク作成
        Route::post("/", [TodoController::class, "store"]);

        // ゴミ箱関連
        Route::get("/trashed", [TodoController::class, "trashedApi"]);
        Route::delete("/trash/empty", [TodoController::class, "emptyTrash"]);

        // 個別タスク操作
        Route::get("/{todo}", [TodoController::class, "show"]);

        // PUT と POST の両方を受け付ける
        Route::match(["put", "post"], "/{todo}", [
            TodoController::class,
            "update",
        ]);

        // タスクステータス操作
        Route::patch("/{todo}/toggle", [TodoController::class, "toggle"]);
        Route::patch("/{todo}/trash", [TodoController::class, "trash"]);
        Route::patch("/{todo}/restore", [TodoController::class, "restore"]);
        Route::delete("/{todo}", [TodoController::class, "destroy"]);
    });

/**
 * カテゴリー API ルート
 */
Route::prefix("categories")
    ->middleware(["web"])
    ->group(function () {
        // カテゴリー一覧取得
        Route::get("/", [CategoryApiController::class, "index"]);

        // カテゴリー作成
        Route::post("/", [CategoryApiController::class, "store"]);

        // カテゴリー更新
        Route::put("/{category}", [CategoryApiController::class, "update"]);

        // カテゴリー削除
        Route::delete("/{category}", [CategoryApiController::class, "destroy"]);
    });
