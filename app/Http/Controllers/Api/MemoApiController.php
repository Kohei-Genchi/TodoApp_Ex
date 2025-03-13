<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MemoApiController extends Controller
{
    /**
     * Get all memos for the authenticated user.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return response()->json([], 401);
            }

            $memos = Auth::user()
                ->todos()
                ->with("category")
                ->whereNull("due_date")
                ->where("status", "pending")
                ->orderBy("created_at", "desc")
                ->get();

            return response()->json($memos);
        } catch (\Exception $e) {
            Log::error(
                "Error in MemoApiController->index: " . $e->getMessage()
            );
            return response()->json(
                [
                    "error" => "Failed to retrieve memos",
                ],
                500
            );
        }
    }

    /**
     * Store a new memo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return response()->json(
                    [
                        "error" => "Unauthorized",
                    ],
                    401
                );
            }

            $validated = $request->validate([
                "title" => "required|string|max:255",
                "category_id" => "nullable|exists:categories,id",
            ]);

            $todo = Todo::create([
                "title" => $validated["title"],
                "category_id" => $validated["category_id"] ?? null,
                "status" => "pending",
                "location" => "INBOX",
                "user_id" => Auth::id(),
            ]);

            return response()->json(
                [
                    "message" => "Memo created successfully",
                    "memo" => $todo->load("category"),
                ],
                201
            );
        } catch (\Exception $e) {
            Log::error(
                "Error in MemoApiController->store: " . $e->getMessage()
            );
            return response()->json(
                [
                    "error" => "Failed to create memo",
                ],
                500
            );
        }
    }

    /**
     * Move a memo to trash.
     *
     * @param Todo $todo
     * @return JsonResponse
     */
    public function trash(Todo $todo): JsonResponse
    {
        try {
            if (!Auth::check() || $todo->user_id !== Auth::id()) {
                return response()->json(
                    [
                        "error" => "Unauthorized",
                    ],
                    401
                );
            }

            $todo->status = "trashed";
            $todo->save();

            return response()->json([
                "message" => "Memo moved to trash",
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Error in MemoApiController->trash: " . $e->getMessage()
            );
            return response()->json(
                [
                    "error" => "Failed to trash memo",
                ],
                500
            );
        }
    }
}
