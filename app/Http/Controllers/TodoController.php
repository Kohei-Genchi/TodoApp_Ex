<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoRequest;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class TodoController extends Controller
{
    public function index()
    {
        // Debug what's happening
        Log::info(
            "TodoController index accessed. Is authenticated: " .
                (Auth::check() ? "Yes" : "No")
        );

        // This is the main entry point, needs to handle both web and API requests
        // For web requests, it should show the main todos page
        // For API requests, it should handle authentication appropriately

        if (request()->expectsJson()) {
            try {
                // For API requests, return empty data for unauthenticated users
                if (!Auth::check()) {
                    Log::info(
                        "Unauthenticated access to todos API - returning empty data"
                    );
                    return response()->json([]);
                }

                $user = Auth::user();
                Log::info("Fetching categories for user: " . $user->id);

                // Return user's categories for API requests
                $categories = $user->categories()->orderBy("name")->get();
                return response()->json($categories);
            } catch (\Exception $e) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Error fetching data: " . $e->getMessage(),
                    ],
                    500
                );
            }
        } else {
            // For web requests, render the main todos view
            // If not authenticated, the view should handle login prompts
            return view("todos.index");
        }
    }

    public function store(Request $request)
    {
        // Handle authentication - return empty array for API and redirect for web
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                // Return 200 for API with empty data to prevent frontend errors
                return response()->json([], 200);
            } else {
                return redirect()->route("login");
            }
        }

        $user = Auth::user();

        // Validate the request
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "category_id" => "nullable|exists:categories,id",
            "due_date" => "nullable|date",
            "due_time" => "nullable|string",
            "priority" => "nullable|in:low,medium,high",
            "recurrence_type" => "nullable|in:none,daily,weekly,monthly",
            "recurrence_end_date" => "nullable|date|after_or_equal:due_date",
        ]);

        $data = $validated;
        $data["user_id"] = $user->id;

        if (isset($data["due_date"])) {
            $data["location"] =
                $data["due_date"] === now()->format("Y-m-d")
                    ? "TODAY"
                    : "SCHEDULED";
        } else {
            $data["location"] = "INBOX";
            $data["due_date"] = null;
            $data["due_time"] = null;
        }

        $todo = Todo::create($data);

        if (
            !empty($data["recurrence_type"]) &&
            $data["recurrence_type"] !== "none"
        ) {
            $this->createRecurringTasks($data);
        }

        if ($request->expectsJson()) {
            return response()->json(
                [
                    "message" => "タスクを追加しました",
                    "todo" => $todo,
                ],
                201
            );
        }

        return back()->with("success", "タスクを追加しました");
    }

    public function restore(Request $request, Todo $todo)
    {
        // Handle authentication - return empty array for API and redirect for web
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                // Return 200 for API with empty data to prevent frontend errors
                return response()->json([], 200);
            } else {
                return redirect()->route("login");
            }
        }

        if ($todo->status !== "trashed") {
            return $request->expectsJson()
                ? response()->json(
                    ["error" => "ゴミ箱にあるタスクのみ復元できます"],
                    400
                )
                : back()->with("error", "ゴミ箱にあるタスクのみ復元できます");
        }

        // 期限がある場合は、その日付に復元（今日の場合はTODAY、それ以外はSCHEDULED）
        if ($todo->due_date) {
            if ($todo->due_date->isToday()) {
                $todo->location = "TODAY";
            } else {
                $todo->location = "SCHEDULED";
            }
        } else {
            // 期限がない場合はINBOXに復元
            $todo->location = "INBOX";
        }

        $todo->status = "pending";
        $todo->save();

        if ($request->expectsJson()) {
            return response()->json([
                "message" => "タスクを復元しました",
                "todo" => $todo->fresh(),
            ]);
        }

        return back()->with("success", "タスクを復元しました");
    }

    public function toggle(Todo $todo, Request $request)
    {
        // Handle authentication - return empty array for API and redirect for web
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                // Return 200 for API with empty data to prevent frontend errors
                return response()->json([], 200);
            } else {
                return redirect()->route("login");
            }
        }

        $todo->status = $todo->status === "completed" ? "pending" : "completed";
        $todo->save();

        if ($request->expectsJson()) {
            return response()->json([
                "message" => "タスクのステータスを更新しました",
                "todo" => $todo->fresh(),
            ]);
        }

        return back();
    }

    public function trash(Todo $todo, Request $request)
    {
        // Handle authentication - return empty array for API and redirect for web
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                // Return 200 for API with empty data to prevent frontend errors
                return response()->json([], 200);
            } else {
                return redirect()->route("login");
            }
        }

        $todo->status = "trashed";
        $todo->save();

        if ($request->expectsJson()) {
            return response()->json([
                "message" => "タスクをゴミ箱に移動しました",
            ]);
        }

        return back();
    }

    public function trashed(): RedirectResponse
    {
        // This method is no longer needed as we're using the API endpoint trashedApi instead
        // Vue.js will handle the trash view using the API
        return redirect()->route("todos.index");
    }

    public function destroy(Todo $todo, Request $request)
    {
        // 繰り返しタスクを削除するかどうかのチェック
        if ($request->has("delete_recurring")) {
            // 同じタイトルと作成時刻を持つタスクを検索（繰り返しタスクの全インスタンス）
            $relatedTasks = Todo::where("user_id", $todo->user_id)
                ->where("title", $todo->title)
                ->where("created_at", $todo->created_at)
                ->get();

            // 見つかったすべてのタスクを削除
            foreach ($relatedTasks as $task) {
                $task->delete();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    "message" => "繰り返しタスクを削除しました",
                ]);
            }

            return back()->with("success", "繰り返しタスクを削除しました");
        }

        // 単一タスクの削除（既存のコード）
        $todo->delete();

        if ($request->expectsJson()) {
            return response()->json([
                "message" => "タスクを完全に削除しました",
            ]);
        }

        return back()->with("success", "タスクを完全に削除しました");
    }

    public function emptyTrash(Request $request)
    {
        try {
            // Handle authentication - return empty array for API and redirect for web
            if (!Auth::check()) {
                if ($request->expectsJson()) {
                    // Return 200 for API with empty data to prevent frontend errors
                    return response()->json([], 200);
                } else {
                    return redirect()->route("login");
                }
            }

            $user = Auth::user();
            $user->todos()->where("status", "trashed")->delete();

            if ($request->expectsJson()) {
                return response()->json(["message" => "ゴミ箱を空にしました"]);
            }

            return back()->with("success", "ゴミ箱を空にしました");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(
                    [
                        "error" => "Error emptying trash: " . $e->getMessage(),
                    ],
                    500
                );
            }

            return back()->with(
                "error",
                "ゴミ箱を空にできませんでした: " . $e->getMessage()
            );
        }
    }

    /**
     * API用のメソッド
     */

    // API用のタスク一覧取得
    // app/Http/Controllers/TodoController.php
    // Fix for the apiIndex method

    public function apiIndex(Request $request): JsonResponse
    {
        // Default to 'today' view if none specified
        $view = $request->view ?? "today";
        $date = $request->date ? now()->parse($request->date) : now();

        try {
            // Handle authentication - return empty array for API
            if (!Auth::check()) {
                Log::info(
                    "Unauthenticated access to todos API - returning empty array"
                );
                return response()->json([]);
            }

            $user = Auth::user();
            Log::info("Fetching todos for user: " . $user->id);

            // Start with a base query for todos
            $query = $user
                ->todos()
                ->with("category")
                ->where("status", "!=", "trashed");

            // Add view-specific filtering
            switch ($view) {
                case "today":
                    // Simply filter by today's date, regardless of location
                    $query->whereDate("due_date", $date->format("Y-m-d"));
                    break;
                case "scheduled":
                    $query
                        ->whereNotNull("due_date")
                        ->whereDate("due_date", ">", now()->format("Y-m-d"))
                        ->where("status", "pending");
                    break;
                case "inbox":
                    $query->whereNull("due_date")->where("status", "pending");
                    break;
                case "calendar":
                    $query->whereBetween("due_date", [
                        $request->start_date ??
                        $date->copy()->startOfMonth()->format("Y-m-d"),
                        $request->end_date ??
                        $date->copy()->endOfMonth()->format("Y-m-d"),
                    ]);
                    break;
                case "date":
                    // Filter by specific date
                    $query->whereDate("due_date", $date->format("Y-m-d"));
                    break;
            }

            $todos = $query->orderBy("created_at", "desc")->get();

            // Log the query results for debugging
            Log::info("API todos query returned " . count($todos) . " results");
            if (count($todos) > 0) {
                Log::info("First todo: " . json_encode($todos[0]));
            }

            return response()->json($todos);
        } catch (\Exception $e) {
            Log::error("Error loading tasks: " . $e->getMessage());
            return response()->json(
                [
                    "error" => "Error loading tasks: " . $e->getMessage(),
                ],
                500
            );
        }
    }

    // Fix for the update method
    // In TodoController.php
    // In app/Http/Controllers/TodoController.php - update the update method
    public function update(Request $request, Todo $todo)
    {
        Log::info("Update method called for todo ID: " . $todo->id);
        Log::info("Request is authenticated? " . Auth::check());

        // Important: Print headers to debug CSRF and authentication
        Log::info("Request headers: " . json_encode($request->headers->all()));

        // Handle authentication - but don't check for expectsJson since API request might not have this
        if (!Auth::check()) {
            Log::warning(
                "Unauthenticated access attempt to update todo " . $todo->id
            );
            return response()->json(
                ["error" => "Unauthorized. Please log in again."],
                401
            );
        }

        // Check if user owns the todo
        if ($todo->user_id !== Auth::id()) {
            Log::warning(
                "User " .
                    Auth::id() .
                    " attempted to update todo " .
                    $todo->id .
                    " belonging to user " .
                    $todo->user_id
            );
            return response()->json(
                ["error" => "You do not have permission to edit this task"],
                403
            );
        }

        // Validate the request
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "category_id" => "nullable|exists:categories,id",
            "due_date" => "nullable|date",
            "due_time" => "nullable|string",
            "recurrence_type" => "nullable|in:none,daily,weekly,monthly",
            "recurrence_end_date" => "nullable|date|after_or_equal:due_date",
        ]);

        Log::info("Validated data: " . json_encode($validated));

        try {
            // Update the todo
            $todo->update($validated);

            // Reload the model to get fresh data
            $todo->refresh();
            $todo->load("category");

            Log::info("Todo updated successfully: " . json_encode($todo));

            return response()->json([
                "message" => "タスクを更新しました",
                "todo" => $todo,
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating todo: " . $e->getMessage());
            return response()->json(
                [
                    "error" => "Update failed: " . $e->getMessage(),
                ],
                500
            );
        }
    }
    // Fix for the show method
    public function show(Todo $todo)
    {
        // Handle authentication - return empty array for API and redirect for web
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                // Return 200 for API with empty data to prevent frontend errors
                return response()->json([]);
            } else {
                return redirect()->route("login");
            }
        }

        // Check if user owns the todo
        if ($todo->user_id !== Auth::id()) {
            if (request()->expectsJson()) {
                return response()->json(["error" => "Unauthorized"], 403);
            } else {
                return redirect()
                    ->route("todos.index")
                    ->with("error", "Unauthorized");
            }
        }

        if (request()->expectsJson()) {
            // Include the category relationship
            $todo->load("category");
            Log::info(
                "Returning todo " .
                    $todo->id .
                    " with category: " .
                    ($todo->category ? $todo->category->id : "null")
            );
            return response()->json($todo);
        }

        return view("todos.show", compact("todo"));
    }

    // タスクをゴミ箱に移動（API用）
    public function moveToTrash(Todo $todo): JsonResponse
    {
        // Handle authentication - return empty array for API
        if (!Auth::check()) {
            Log::info(
                "Unauthenticated access to moveToTrash API - returning empty array"
            );
            return response()->json([]);
        }

        $todo->status = "trashed";
        $todo->save();

        return response()->json(["message" => "タスクをゴミ箱に移動しました"]);
    }

    // ゴミ箱内のタスク一覧（API用）
    public function trashedApi(): JsonResponse
    {
        try {
            // Handle authentication - return empty array for API
            if (!Auth::check()) {
                Log::info(
                    "Unauthenticated access to trashedApi - returning empty array"
                );
                return response()->json([]);
            }

            $user = Auth::user();
            $todos = $user
                ->todos()
                ->with("category")
                ->where("status", "trashed")
                ->get();

            return response()->json($todos);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "error" =>
                        "Error loading trashed tasks: " . $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Display the specified todo.
     */

    private function createRecurringTasks(array $data): void
    {
        if (empty($data["due_date"])) {
            return;
        }

        // 開始日を取得
        $startDate = now()->parse($data["due_date"]);

        // 終了日を設定（指定されていない場合は1ヶ月後）
        $endDate = !empty($data["recurrence_end_date"])
            ? now()->parse($data["recurrence_end_date"])
            : $startDate->copy()->addMonths(1);

        // 開始日が終了日と同じかそれ以降の場合は何もしない
        if ($startDate->greaterThanOrEqualTo($endDate)) {
            return;
        }

        // 繰り返し日付の生成
        $dates = [];

        // 開始日そのものからスタート（翌日からではなく）
        $currentDate = $startDate->copy();

        // 最初のタスクは別途作成されるので、初回分をスキップして2回目以降を生成
        switch ($data["recurrence_type"]) {
            case "daily":
                // 初回の日付から1日進める
                $currentDate->addDay();
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addDay();
                }
                break;

            case "weekly":
                // 初回の日付から1週間進める
                $currentDate->addWeek();
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addWeek();
                }
                break;

            case "monthly":
                // 初回の日付から1ヶ月進める
                $currentDate->addMonth();
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addMonth();
                }
                break;
        }

        // 各日付に対してタスクを作成
        foreach ($dates as $date) {
            $taskData = $data;
            $taskData["due_date"] = $date->format("Y-m-d");
            $taskData["location"] = $date->isToday() ? "TODAY" : "SCHEDULED";

            unset(
                $taskData["recurrence_type"],
                $taskData["recurrence_end_date"]
            );
            Todo::create($taskData);
        }
    }
}
