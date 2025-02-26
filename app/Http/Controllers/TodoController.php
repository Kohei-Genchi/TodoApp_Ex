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
        // dd($request->view);
        $view = $request->view ?? 'today';
        $date = $request->date ? now()->parse($request->date) : now();
        // dd($date);


        // Collectionとはリスト形式でデータを格納できるラッパー
        // https://qiita.com/fcafe_goto/items/9795417752793c989a03
        $todos = collect();
        $categories = collect();

        if (Auth::check()) {
            // Eagerロードとは、N+1問題を解決するための技法(一度のクエリでまとめてデータを取得)
            // https://qiita.com/teshimaaaaa1101/items/e4a45780a2e230b97558
            $query = Auth::user()->todos()
                ->with('category')
                ->where('status', '!=', 'trashed');

            switch ($view) {
                case 'inbox':
                    $query->where('location', 'INBOX');
                    break;
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
            // dd($todos[0]['title']);
    // orderBy('name'):カテゴリ選択ドロップダウンなどで、常に同じ順序でカテゴリを表示させるため
            $categories = Auth::user()->categories()->orderBy('name')->get();
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

        $todo->update($request->validated());
        return back()->with('success', 'タスクを更新しました');
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

    public function destroy(Todo $todo): RedirectResponse
    {
        if (Gate::denies('delete', $todo)) {
            abort(403, 'Unauthorized action.');
        }

        $todo->delete();
        return back()->with('success', 'タスクを完全に削除しました');
    }

    private function createRecurringTasks(array $data): void
    {
        if (empty($data['due_date'])) return;
        //now()->parce():文字列をDateTimeオブジェクトに変換
        $startDate = now()->parse($data['due_date']);
        $endDate = !empty($data['recurrence_end_date'])
            ? now()->parse($data['recurrence_end_date'])
            : $startDate->copy()->addMonths(1);

        //開始日が終了日と同じかそれより後の場合、繰り返しタスクを作成できないため、関数を終了。
        if ($startDate->greaterThanOrEqualTo($endDate)) return;

        //-----繰り返し日付の生成------
        $dates = [];
        //.copy()：現在の日付をコピー(Carbonオブジェクトを変更せずに新しいオブジェクトを作成)
        //https://qiita.com/monji586/items/bf769be57bca546e8049
        $currentDate = $startDate->copy()->addDay();
        switch ($data['recurrence_type']) {
            case 'daily':
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addDay();
                }
                break;
            case 'weekly':
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addWeek();
                }
                break;
            case 'monthly':
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $dates[] = $currentDate->copy();
                    $currentDate->addMonth();
                }
                break;
        }

        foreach ($dates as $date) {
            $taskData = $data;
            $taskData['due_date'] = $date->format('Y-m-d');
            $taskData['location'] = $date->isToday() ? 'TODAY' : 'SCHEDULED';

            unset($taskData['recurrence_type'], $taskData['recurrence_end_date']);
            Todo::create($taskData);
        }
    }
}
