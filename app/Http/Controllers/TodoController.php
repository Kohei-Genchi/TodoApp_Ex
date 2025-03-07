<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoRequest;
use App\Models\Todo;
use App\Models\Category;
use App\Traits\HandlesApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TodoController extends Controller
{
    use HandlesApiResponses;

    /**
     * コンストラクタ - 認証の設定
     */
    public function __construct()
    {
        // 認証はルートミドルウェアで処理
        // routes/api.phpで設定されるべき
    }

    /**
     * メインエンドポイント - Web/API両方のハンドリング
     *
     * @return JsonResponse|View レスポンスタイプに応じたレスポンス
     */
    public function index()
    {
        // APIリクエストの場合
        if (request()->expectsJson()) {
            try {
                // 未認証の場合は空配列を返す
                if (!Auth::check()) {
                    Log::info('未認証アクセス - 空データを返します');
                    // Return direct array for frontend compatibility
                    return response()->json([]);
                }

                // ユーザーのカテゴリを取得
                $user = Auth::user();
                $categories = Category::where('user_id', $user->id)->orderBy('name')->get();
                // Return direct array for frontend compatibility
                return response()->json($categories);
            } catch (\Exception $e) {
                Log::error("カテゴリ取得エラー: {$e->getMessage()}");
                return $this->errorResponse("データ取得エラー: {$e->getMessage()}");
            }
        }

        // Webリクエストの場合はビューを返す
        return view('todos.index');
    }

    /**
     * 新しいタスクを作成
     *
     * @param TodoRequest $request バリデーション済みリクエスト
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    public function store(TodoRequest $request)
    {
        try {
            // 未認証の場合
            if (!$this->isAuthenticated($request)) {
                return $this->handleUnauthenticated($request);
            }

            // リクエストデータの準備
            $taskData = $this->prepareTaskData($request->validated());

            // タスク作成
            $todo = Todo::create($taskData);

            // 繰り返しタスクの処理
            if ($this->shouldCreateRecurringTasks($taskData)) {
                $this->createRecurringTasks($taskData);
            }

            // レスポンス
            return $this->handleResponse($request, $todo, 'タスクを追加しました', 201);
        } catch (\Exception $e) {
            Log::error("タスク作成エラー: {$e->getMessage()}");
            return $this->handleError($request, "タスク作成エラー: {$e->getMessage()}");
        }
    }

    /**
     * ゴミ箱から特定のタスクを復元
     *
     * @param Request $request リクエスト
     * @param Todo $todo 対象タスク
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    public function restore(Request $request, Todo $todo)
    {
        try {
            // 未認証の場合
            if (!$this->isAuthenticated($request)) {
                return $this->handleUnauthenticated($request);
            }

            // ゴミ箱にないタスクは復元できない
            if ($todo->status !== 'trashed') {
                return $this->handleError($request, 'ゴミ箱にあるタスクのみ復元できます', 400);
            }

            // タスクの復元先を決定
            $this->determineTaskLocation($todo);
            $todo->status = 'pending';
            $todo->save();

            return $this->handleResponse($request, $todo->fresh(), 'タスクを復元しました');
        } catch (\Exception $e) {
            Log::error("タスク復元エラー: {$e->getMessage()}");
            return $this->handleError($request, "タスク復元エラー: {$e->getMessage()}");
        }
    }

    /**
     * タスクの完了状態を切り替え
     *
     * @param Todo $todo 対象タスク
     * @param Request $request リクエスト
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    public function toggle(Todo $todo, Request $request)
    {
        try {
            // 未認証の場合
            if (!$this->isAuthenticated($request)) {
                return $this->handleUnauthenticated($request);
            }

            // 完了状態の切り替え
            $todo->status = $todo->status === 'completed' ? 'pending' : 'completed';
            $todo->save();

            return $this->handleResponse($request, $todo->fresh(), 'タスクのステータスを更新しました');
        } catch (\Exception $e) {
            Log::error("タスク状態切替エラー: {$e->getMessage()}");
            return $this->handleError($request, "タスク状態切替エラー: {$e->getMessage()}");
        }
    }

    /**
     * タスクをゴミ箱に移動
     *
     * @param Todo $todo 対象タスク
     * @param Request $request リクエスト
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    public function trash(Todo $todo, Request $request)
    {
        try {
            // 未認証の場合
            if (!$this->isAuthenticated($request)) {
                return $this->handleUnauthenticated($request);
            }

            $todo->status = 'trashed';
            $todo->save();

            return $this->handleResponse($request, null, 'タスクをゴミ箱に移動しました');
        } catch (\Exception $e) {
            Log::error("ゴミ箱移動エラー: {$e->getMessage()}");
            return $this->handleError($request, "ゴミ箱移動エラー: {$e->getMessage()}");
        }
    }

    /**
     * ゴミ箱ビュー（非推奨 - APIエンドポイントを使用）
     *
     * @return RedirectResponse メインページへのリダイレクト
     */
    public function trashed(): RedirectResponse
    {
        // Vue.jsがAPIを使用するため不要
        return redirect()->route('todos.index');
    }

    /**
     * タスクの完全削除
     *
     * @param Todo $todo 対象タスク
     * @param Request $request リクエスト
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    public function destroy(Todo $todo, Request $request)
    {
        try {
            // 繰り返しタスクの削除
            if ($request->has('delete_recurring')) {
                return $this->deleteRecurringTasks($todo, $request);
            }

            // 単一タスクの削除
            $todo->delete();

            return $this->handleResponse($request, null, 'タスクを完全に削除しました');
        } catch (\Exception $e) {
            Log::error("タスク削除エラー: {$e->getMessage()}");
            return $this->handleError($request, "タスク削除エラー: {$e->getMessage()}");
        }
    }

    /**
     * ゴミ箱を空にする
     *
     * @param Request $request リクエスト
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    public function emptyTrash(Request $request)
    {
        try {
            // 未認証の場合
            if (!$this->isAuthenticated($request)) {
                return $this->handleUnauthenticated($request);
            }

            // ゴミ箱内のタスクをすべて削除
            Todo::where('user_id', Auth::id())->where('status', 'trashed')->delete();

            return $this->handleResponse($request, null, 'ゴミ箱を空にしました');
        } catch (\Exception $e) {
            Log::error("ゴミ箱を空にする処理エラー: {$e->getMessage()}");
            return $this->handleError($request, "ゴミ箱を空にできませんでした: {$e->getMessage()}");
        }
    }

    /**
     * API用のタスク一覧取得
     *
     * @param Request $request リクエスト
     * @return JsonResponse タスク一覧を含むJSONレスポンス
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            // 未認証の場合は空配列を返す
            if (!Auth::check()) {
                return response()->json([]);
            }

            // クエリパラメータの取得
            $view = $request->view ?? 'today';
            $date = $request->date ? now()->parse($request->date) : now();

            // ベースクエリの構築
            $query = $this->buildBaseTaskQuery();

            // ビュータイプに応じたフィルタリング
            $this->applyViewFilters($query, $view, $date, $request);

            // タスク取得
            $todos = $query->orderBy('created_at', 'desc')->get();

            // Return direct array for frontend compatibility
            return response()->json($todos);
        } catch (\Exception $e) {
            Log::error("タスク一覧取得エラー: {$e->getMessage()}");
            return response()->json(['error' => "タスク一覧取得エラー: {$e->getMessage()}"], 500);
        }
    }

    /**
     * タスクの更新
     *
     * @param Request $request リクエスト
     * @param Todo $todo 対象タスク
     * @return JsonResponse 更新結果を含むJSONレスポンス
     */
    public function update(Request $request, Todo $todo): JsonResponse
    {
        try {
            // 未認証の場合
            if (!Auth::check()) {
                return response()->json(['error' => '認証が必要です。再度ログインしてください。'], 401);
            }

            // タスクの所有権確認
            if ($todo->user_id !== Auth::id()) {
                return response()->json(['error' => 'このタスクを編集する権限がありません'], 403);
            }

            // リクエストの検証
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
                'due_date' => 'nullable|date',
                'due_time' => 'nullable|string',
                'recurrence_type' => 'nullable|in:none,daily,weekly,monthly',
                'recurrence_end_date' => 'nullable|date|after_or_equal:due_date',
            ]);

            // タスクの更新
            $todo->update($validated);
            $todo->refresh();
            $todo->load('category');

            // Return direct array for frontend compatibility
            return response()->json([
                'message' => 'タスクを更新しました',
                'todo' => $todo
            ]);
        } catch (\Exception $e) {
            Log::error("タスク更新エラー: {$e->getMessage()}");
            return response()->json(['error' => "更新に失敗しました: {$e->getMessage()}"], 500);
        }
    }

    /**
     * 特定のタスクを表示
     *
     * @param Todo $todo 対象タスク
     * @return JsonResponse|View レスポンスタイプに応じたレスポンス
     */
    public function show(Todo $todo)
    {
        try {
            // 未認証の場合
            if (!Auth::check()) {
                if (request()->expectsJson()) {
                    // Return direct array for frontend compatibility
                    return response()->json([]);
                }
                return redirect()->route('login');
            }

            // タスクの所有権確認
            if ($todo->user_id !== Auth::id()) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => '権限がありません'], 403);
                }
                return redirect()->route('todos.index')->with('error', '権限がありません');
            }

            // APIリクエストの場合
            if (request()->expectsJson()) {
                $todo->load('category');
                // Return direct array for frontend compatibility
                return response()->json($todo);
            }

            // Webリクエストの場合
            return view('todos.show', compact('todo'));
        } catch (\Exception $e) {
            Log::error("タスク表示エラー: {$e->getMessage()}");
            if (request()->expectsJson()) {
                return response()->json(['error' => "タスク表示エラー: {$e->getMessage()}"], 500);
            }
            return back()->with('error', "タスク表示エラー: {$e->getMessage()}");
        }
    }

    /**
     * ゴミ箱内のタスク一覧（API用）
     *
     * @return JsonResponse タスク一覧を含むJSONレスポンス
     */
    public function trashedApi(): JsonResponse
    {
        try {
            // 未認証の場合は空配列を返す
            if (!Auth::check()) {
                // Return direct array for frontend compatibility
                return response()->json([]);
            }

            // ゴミ箱内のタスクを取得
            $trashedTasks = Todo::where('user_id', Auth::id())
                ->with('category')
                ->where('status', 'trashed')
                ->get();

            // Return direct array for frontend compatibility
            return response()->json($trashedTasks);
        } catch (\Exception $e) {
            Log::error("ゴミ箱内タスク取得エラー: {$e->getMessage()}");
            return response()->json(['error' => "ゴミ箱内タスク取得エラー: {$e->getMessage()}"], 500);
        }
    }

    /**
     * 繰り返しタスクを作成
     *
     * @param array $taskData タスクデータ
     * @return void
     */
    private function createRecurringTasks(array $taskData): void
    {
        // 期限がない場合は繰り返しタスクを作成しない
        if (empty($taskData['due_date'])) {
            return;
        }

        // 日付の準備
        $startDate = now()->parse($taskData['due_date']);
        $endDate = !empty($taskData['recurrence_end_date'])
            ? now()->parse($taskData['recurrence_end_date'])
            : $startDate->copy()->addMonths(1);

        // 開始日が終了日以降の場合は何もしない
        if ($startDate->greaterThanOrEqualTo($endDate)) {
            return;
        }

        // 繰り返し日付の生成
        $dates = $this->generateRecurringDates($startDate, $endDate, $taskData['recurrence_type']);

        // 各日付に対してタスクを作成
        foreach ($dates as $date) {
            $newTaskData = $taskData;
            $newTaskData['due_date'] = $date->format('Y-m-d');
            $newTaskData['location'] = $date->isToday() ? 'TODAY' : 'SCHEDULED';

            // 繰り返し情報は不要なので削除
            unset($newTaskData['recurrence_type'], $newTaskData['recurrence_end_date']);

            Todo::create($newTaskData);
        }
    }

    /**
     * 繰り返し日付を生成
     *
     * @param \Carbon\Carbon $startDate 開始日
     * @param \Carbon\Carbon $endDate 終了日
     * @param string $recurrenceType 繰り返しタイプ
     * @return array 日付の配列
     */
    private function generateRecurringDates($startDate, $endDate, $recurrenceType): array
    {
        $dates = [];
        $currentDate = $startDate->copy();

        // 繰り返しタイプに応じた日付生成
        switch ($recurrenceType) {
            case 'daily':
                $currentDate->addDay();
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addDay();
                }
                break;

            case 'weekly':
                $currentDate->addWeek();
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addWeek();
                }
                break;

            case 'monthly':
                $currentDate->addMonth();
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addMonth();
                }
                break;
        }

        return $dates;
    }

    /**
     * 繰り返しタスクを削除
     *
     * @param Todo $todo 対象タスク
     * @param Request $request リクエスト
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    private function deleteRecurringTasks(Todo $todo, Request $request)
    {
        // 同じタイトルと作成時刻を持つタスクを検索
        $relatedTasks = Todo::where('user_id', $todo->user_id)
            ->where('title', $todo->title)
            ->where('created_at', $todo->created_at)
            ->get();

        // 見つかったすべてのタスクを削除
        foreach ($relatedTasks as $task) {
            $task->delete();
        }

        return $this->handleResponse($request, null, '繰り返しタスクを削除しました');
    }

    /**
     * タスクデータを準備
     *
     * @param array $validated バリデーション済みデータ
     * @return array 準備されたタスクデータ
     */
    private function prepareTaskData(array $validated): array
    {
        $taskData = $validated;
        $taskData['user_id'] = Auth::id();

        // タスクの場所（location）を設定
        if (isset($taskData['due_date'])) {
            $taskData['location'] = $taskData['due_date'] === now()->format('Y-m-d')
                ? 'TODAY'
                : 'SCHEDULED';
        } else {
            $taskData['location'] = 'INBOX';
            $taskData['due_date'] = null;
            $taskData['due_time'] = null;
        }

        return $taskData;
    }

    /**
     * 繰り返しタスクを作成すべきか判定
     *
     * @param array $taskData タスクデータ
     * @return bool 繰り返しタスクを作成すべきならtrue
     */
    private function shouldCreateRecurringTasks(array $taskData): bool
    {
        return !empty($taskData['recurrence_type']) && $taskData['recurrence_type'] !== 'none';
    }

    /**
     * タスクの復元先を決定
     *
     * @param Todo $todo 対象タスク
     * @return void
     */
    private function determineTaskLocation(Todo $todo): void
    {
        if ($todo->due_date) {
            $todo->location = $todo->due_date->isToday() ? 'TODAY' : 'SCHEDULED';
        } else {
            $todo->location = 'INBOX';
        }
    }

    /**
     * ベースタスククエリを構築
     *
     * @return \Illuminate\Database\Eloquent\Builder クエリビルダ
     */
    private function buildBaseTaskQuery()
    {
        return Todo::where('user_id', Auth::id())
            ->with('category')
            ->where('status', '!=', 'trashed');
    }

    /**
     * ビュータイプに応じたフィルタを適用
     *
     * @param \Illuminate\Database\Eloquent\Builder $query クエリビルダ
     * @param string $view ビュータイプ
     * @param \Carbon\Carbon $date 日付
     * @param Request $request リクエスト
     * @return void
     */
    private function applyViewFilters($query, $view, $date, $request): void
    {
        switch ($view) {
            case 'today':
                $query->whereDate('due_date', $date->format('Y-m-d'));
                break;
            case 'scheduled':
                $query->whereNotNull('due_date')
                    ->whereDate('due_date', '>', now()->format('Y-m-d'))
                    ->where('status', 'pending');
                break;
            case 'inbox':
                $query->whereNull('due_date')->where('status', 'pending');
                break;
            case 'calendar':
                $query->whereBetween('due_date', [
                    $request->start_date ?? $date->copy()->startOfMonth()->format('Y-m-d'),
                    $request->end_date ?? $date->copy()->endOfMonth()->format('Y-m-d'),
                ]);
                break;
            case 'date':
                $query->whereDate('due_date', $date->format('Y-m-d'));
                break;
        }
    }

    /**
     * 認証済みかどうかを確認
     *
     * @param Request $request リクエスト
     * @return bool 認証済みならtrue
     */
    private function isAuthenticated(Request $request): bool
    {
        return Auth::check();
    }

    /**
     * 未認証時の処理
     *
     * @param Request $request リクエスト
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    private function handleUnauthenticated(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([], 200);
        }

        return redirect()->route('login');
    }

    /**
     * レスポンスを処理
     *
     * @param Request $request リクエスト
     * @param mixed $data レスポンスデータ
     * @param string $message メッセージ
     * @param int $code ステータスコード
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    private function handleResponse(Request $request, $data, string $message, int $code = 200)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'todo' => $data
            ], $code);
        }

        return back()->with('success', $message);
    }

    /**
     * エラーを処理
     *
     * @param Request $request リクエスト
     * @param string $message エラーメッセージ
     * @param int $code ステータスコード
     * @return JsonResponse|RedirectResponse レスポンスタイプに応じたレスポンス
     */
    private function handleError(Request $request, string $message, int $code = 500)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], $code);
        }

        return back()->with('error', $message);
    }
}
