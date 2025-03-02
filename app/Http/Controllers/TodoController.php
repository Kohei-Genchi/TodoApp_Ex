<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoRequest;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TodoController extends Controller
{
    public function index(Request $request): View
    {
        // Default to 'today' view if none specified
        $view = $request->view ?? 'today';
        $date = $request->date ? now()->parse($request->date) : now();

        // Initialize empty collections
        $todos = collect();
        $categories = collect();

        if (Auth::check()) {
            // Get categories for the authenticated user
            $categories = Auth::user()->categories()->orderBy('name')->get();

            // Query builder with eager loading
            $query = Auth::user()->todos()
                ->with('category')
                ->where('status', '!=', 'trashed');

            switch ($view) {
                // We no longer need specific 'inbox' handling in the main view
                case 'today':
                    $query->whereDate('due_date', now()->format('Y-m-d'));
                    break;
                case 'date':
                    $query->whereDate('due_date', $date->format('Y-m-d'));
                    break;
                case 'calendar':
                    $startDate = $date->copy()->startOfMonth();
                    $endDate = $date->copy()->endOfMonth();
                    $query->whereBetween('due_date', [
                        $startDate->format('Y-m-d'),
                        $endDate->format('Y-m-d')
                    ]);
                    break;
            }

            $todos = $query->orderBy('due_time')->get();
        }

        return view('todos.index', compact('todos', 'view', 'date', 'categories'));
    }

    public function store(TodoRequest $request): RedirectResponse
    {
        if (Gate::denies('create', Todo::class)) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validated();
        $data['user_id'] = Auth::id();

        if (isset($data['due_date'])) {
            $data['location'] = ($data['due_date'] === now()->format('Y-m-d'))
                ? 'TODAY'
                : 'SCHEDULED';
        } else {
            $data['location'] = 'INBOX';
            $data['due_date'] = null;
            $data['due_time'] = null;
        }

        Todo::create($data);

        if (!empty($data['recurrence_type']) && $data['recurrence_type'] !== 'none') {
            $this->createRecurringTasks($data);
        }

        return back()->with('success', 'タスクを追加しました');
    }

    public function update(TodoRequest $request, Todo $todo): RedirectResponse
    {
        if (Gate::denies('update', $todo)) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validated();

        // Set location based on due date
        if (isset($data['due_date'])) {
            $data['location'] = ($data['due_date'] === now()->format('Y-m-d'))
                ? 'TODAY'
                : 'SCHEDULED';
        } else {
            $data['location'] = 'INBOX';
            $data['due_date'] = null;
            $data['due_time'] = null;
        }

        $todo->update($data);

        // Handle recurring tasks
        if (!empty($data['recurrence_type']) && $data['recurrence_type'] !== 'none') {
            // Add user_id to data for createRecurringTasks
            $data['user_id'] = $todo->user_id;
            $this->createRecurringTasks($data);
        }

        return back()->with('success', 'タスクを更新しました');
    }

    public function restore(Request $request, Todo $todo): RedirectResponse
    {
        if (Gate::denies('update', $todo)) {
            abort(403, 'Unauthorized action.');
        }

        if ($todo->status !== 'trashed') {
            return back()->with('error', 'ゴミ箱にあるタスクのみ復元できます');
        }

        // 期限がある場合は、その日付に復元（今日の場合はTODAY、それ以外はSCHEDULED）
        if ($todo->due_date) {
            if ($todo->due_date->isToday()) {
                $todo->location = 'TODAY';
            } else {
                $todo->location = 'SCHEDULED';
            }
        } else {
            // 期限がない場合はINBOXに復元
            $todo->location = 'INBOX';
        }

        $todo->status = 'pending';
        $todo->save();

        return back()->with('success', 'タスクを復元しました');
    }

    public function toggle(Todo $todo): RedirectResponse
    {
        if (Gate::denies('update', $todo)) {
            abort(403, 'Unauthorized action.');
        }

        $todo->status = $todo->status === 'completed' ? 'pending' : 'completed';
        $todo->save();
        return back();
    }

    public function trash(Todo $todo): RedirectResponse
    {
        if (Gate::denies('update', $todo)) {
            abort(403, 'Unauthorized action.');
        }

        $todo->status = 'trashed';
        $todo->save();
        return back()->with('success', 'タスクをゴミ箱に移動しました');
    }

    public function trashed(): View
    {
        $todos = Auth::user()->todos()
            ->with('category')
            ->where('status', 'trashed')
            ->get();

        $categories = Auth::user()->categories()->orderBy('name')->get();

        return view('todos.trash', compact('todos', 'categories'));
    }

    public function destroy(Todo $todo, Request $request): RedirectResponse
    {
        if (Gate::denies('delete', $todo)) {
            abort(403, 'Unauthorized action.');
        }

        // Check if we're deleting all recurring tasks too
        if ($request->has('delete_recurring') && $todo->recurrence_type && $todo->recurrence_type !== 'none') {
            // This could be expanded to delete all related recurring tasks based on some pattern
            // For now, we'll just delete this specific task
            $todo->delete();
            return back()->with('success', '繰り返しタスクを削除しました');
        }

        $todo->delete();
        return back()->with('success', 'タスクを完全に削除しました');
    }

    public function emptyTrash(): RedirectResponse
    {
        // 認証済みユーザーのtrashedステータスのタスクをすべて削除
        Auth::user()->todos()->where('status', 'trashed')->delete();

        return back()->with('success', 'ゴミ箱を空にしました');
    }

// TodoController.php の createRecurringTasks メソッドを修正

private function createRecurringTasks(array $data): void
{
    if (empty($data['due_date'])) return;

    // 開始日を取得
    $startDate = now()->parse($data['due_date']);

    // 終了日を設定（指定されていない場合は1ヶ月後）
    $endDate = !empty($data['recurrence_end_date'])
        ? now()->parse($data['recurrence_end_date'])
        : $startDate->copy()->addMonths(1);

    // 開始日が終了日と同じかそれ以降の場合は何もしない
    if ($startDate->greaterThanOrEqualTo($endDate)) return;

    // 繰り返し日付の生成
    $dates = [];

    // 開始日そのものからスタート（翌日からではなく）
    $currentDate = $startDate->copy();

    // 最初のタスクは別途作成されるので、初回分をスキップして2回目以降を生成
    switch ($data['recurrence_type']) {
        case 'daily':
            // 初回の日付から1日進める
            $currentDate->addDay();
            while ($currentDate->lessThanOrEqualTo($endDate)) {
                $dates[] = $currentDate->copy();
                $currentDate->addDay();
            }
            break;

        case 'weekly':
            // 初回の日付から1週間進める
            $currentDate->addWeek();
            while ($currentDate->lessThanOrEqualTo($endDate)) {
                $dates[] = $currentDate->copy();
                $currentDate->addWeek();
            }
            break;

        case 'monthly':
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
        $taskData['due_date'] = $date->format('Y-m-d');
        $taskData['location'] = $date->isToday() ? 'TODAY' : 'SCHEDULED';

        unset($taskData['recurrence_type'], $taskData['recurrence_end_date']);
        Todo::create($taskData);
    }
}
}
