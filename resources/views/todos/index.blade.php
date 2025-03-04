<x-app-layout>
    <div class="max-w-6xl mx-auto p-4">
        <!-- ページヘッダー -->
        <div class="flex justify-between items-center mb-4">
            <div>
                @if($view === 'calendar')
                    <div class="flex items-center">
                        <a href="{{ route('todos.index', ['view' => 'calendar', 'date' => $date->copy()->subMonth()->format('Y-m-d')]) }}"
                           class="p-1.5 rounded-full text-gray-600 hover:bg-gray-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <h1 class="text-xl font-semibold px-2">{{ $date->format('Y年n月') }}</h1>
                        <a href="{{ route('todos.index', ['view' => 'calendar', 'date' => $date->copy()->addMonth()->format('Y-m-d')]) }}"
                           class="p-1.5 rounded-full text-gray-600 hover:bg-gray-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                @elseif($view === 'date' || $view === 'today')
                    <div class="flex items-center">
                    <a href="{{ route('todos.index', ['view' => ($view === 'today' ? 'date' : $view), 'date' => $date->copy()->subDay()->format('Y-m-d')]) }}"
                           class="p-1.5 rounded-full text-gray-600 hover:bg-gray-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <div class="px-2">
                            <h1 class="text-xl font-semibold">{{ $date->format('m月d日') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }})</h1>
                            @if($view === 'today')
                                <p class="text-sm text-gray-500">本日のタスク</p>
                            @endif
                        </div>
                        <a href="{{ route('todos.index', ['view' => ($view === 'today' ? 'date' : $view), 'date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
                           class="p-1.5 rounded-full text-gray-600 hover:bg-gray-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                @endif
            </div>

            @auth
                <button type="button" onclick="openTaskModal('add')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 transition-colors flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    タスクを追加
                </button>
            @endauth
        </div>

        <!-- メインコンテンツ -->
        <div>
            @if($view === 'calendar')
                <!-- カレンダービュー（固定の高さで6週常に表示） -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <!-- カレンダーヘッダー -->
                    <div class="grid grid-cols-7 text-center text-sm bg-gray-50 border-b border-gray-200">
                        <div class="py-2 text-red-600 font-medium">日</div>
                        <div class="py-2 font-medium">月</div>
                        <div class="py-2 font-medium">火</div>
                        <div class="py-2 font-medium">水</div>
                        <div class="py-2 font-medium">木</div>
                        <div class="py-2 font-medium">金</div>
                        <div class="py-2 text-blue-600 font-medium">土</div>
                    </div>

                    <!-- カレンダーグリッド（常に6週分表示） -->
                    <div class="grid grid-cols-7">
                        @php
                            // 現在の月の初日
                            $firstOfMonth = $date->copy()->startOfMonth();
                            // 現在の月の末日
                            $lastOfMonth = $date->copy()->endOfMonth();

                            // カレンダー表示の最初の日（月の初日を含む週の日曜日）
                            $firstDayOfCalendar = $firstOfMonth->copy()->startOfWeek();

                            // 常に6週間表示するための計算
                            $weeksToShow = 6;
                            $lastDayOfCalendar = $firstDayOfCalendar->copy()->addWeeks($weeksToShow)->subDay();

                            $today = now()->startOfDay();
                        @endphp

                        @for ($day = $firstDayOfCalendar->copy(); $day->lte($lastDayOfCalendar); $day->addDay())
                            @php
                                $isCurrentMonth = $day->month === $date->month;
                                $isToday = $day->isSameDay($today);
                                $dayTodos = $todos->filter(function($todo) use ($day) {
                                    return $todo->due_date && $todo->due_date->isSameDay($day);
                                });
                            @endphp

                            <div class="h-20 p-1 border-r border-b border-gray-200 transition-colors
                                    {{ $isCurrentMonth ? 'bg-white' : 'bg-gray-50' }}
                                    {{ $isToday ? 'bg-blue-50' : '' }}">

                                <!-- 日付表示 -->
                                <div class="flex justify-between items-center">
                                    <a href="{{ route('todos.index', ['view' => 'date', 'date' => $day->format('Y-m-d')]) }}"
                                       class="inline-block rounded-full w-6 h-6 text-center leading-6
                                              {{ !$isCurrentMonth ? 'text-gray-400' : '' }}
                                              {{ $day->dayOfWeek === 0 ? 'text-red-600' : '' }}
                                              {{ $day->dayOfWeek === 6 ? 'text-blue-600' : '' }}
                                              {{ $isToday ? 'bg-blue-600 text-white font-medium' : '' }}">
                                        {{ $day->day }}
                                    </a>

                                    @if($dayTodos->count() > 0)
                                        <span class="text-xs text-blue-600 font-medium">
                                            {{ $dayTodos->count() }}
                                        </span>
                                    @endif
                                </div>

                                <!-- タスク表示（最大2件まで） -->
                                <div class="mt-1 overflow-hidden space-y-1">
                                    @foreach ($dayTodos->take(2) as $todo)
                                        <div class="text-xs truncate px-1 rounded-sm
                                                  {{ $todo->status === 'completed' ? 'line-through text-gray-400' : '' }}"
                                             style="border-left: 2px solid {{ $todo->category ? $todo->category->color : '#9CA3AF' }}">
                                            <a href="#" onclick="event.preventDefault(); editTodo({{ $todo->id }}, {{ json_encode([
                                                'title' => $todo->title,
                                                'due_date' => $todo->due_date ? $todo->due_date->format('Y-m-d') : null,
                                                'due_time' => $todo->due_time ? $todo->due_time->format('H:i') : null,
                                                'category_id' => $todo->category_id,
                                                'recurrence_type' => $todo->recurrence_type,
                                                'recurrence_end_date' => $todo->recurrence_end_date ? $todo->recurrence_end_date->format('Y-m-d') : null,
                                            ]) }})">
                                                {{ $todo->title }}
                                            </a>
                                        </div>
                                    @endforeach

                                    @if ($dayTodos->count() > 2)
                                        <a href="{{ route('todos.index', ['view' => 'date', 'date' => $day->format('Y-m-d')]) }}"
                                           class="text-xs text-blue-600 hover:text-blue-800">
                                            +{{ $dayTodos->count() - 2 }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            @elseif($view === 'today' || $view === 'date')
                <!-- 本日/特定日付のタスクビュー -->
                <div class="bg-white rounded-lg shadow-sm">
                    <!-- シンプルなステータスバー -->
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            @php
                                $totalTasks = $todos->count();
                                $completedTasks = $todos->where('status', 'completed')->count();
                                $pendingTasks = $totalTasks - $completedTasks;
                            @endphp
                            <span>全 {{ $totalTasks }} タスク</span>
                            <span class="mx-2 text-gray-400">|</span>
                            <span class="text-green-600">完了: {{ $completedTasks }}</span>
                            <span class="mx-2 text-gray-400">|</span>
                            <span class="text-blue-600">未完了: {{ $pendingTasks }}</span>
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ $date->format('Y年m月d日') }}
                        </div>
                    </div>

                    @if($todos->isEmpty())
                        <!-- タスクがない場合 -->
                        <div class="p-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">タスクはありません</h3>
                            <p class="text-sm text-gray-500 mb-4">新しいタスクを追加しましょう</p>
                            <button type="button" onclick="openTaskModal('add')"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 transition-colors inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                タスクを追加
                            </button>
                        </div>
                    @else
                        <!-- タスク一覧（シンプル化） -->
                        <ul class="divide-y divide-gray-100">
                            @foreach($todos as $todo)
                                <li class="hover:bg-gray-50 transition-colors">
                                    <div class="p-3 sm:px-4 flex items-center">
                                        <form action="{{ route('todos.toggle', $todo) }}" method="POST" class="mr-3 flex-shrink-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="checkbox" onChange="this.form.submit()"
                                                   {{ $todo->status === 'completed' ? 'checked' : '' }}
                                                   class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                                        </form>

                                        <div class="flex-1 min-w-0" onclick="editTodo({{ $todo->id }}, {{ json_encode([
                                            'title' => $todo->title,
                                            'due_date' => $todo->due_date ? $todo->due_date->format('Y-m-d') : null,
                                            'due_time' => $todo->due_time ? $todo->due_time->format('H:i') : null,
                                            'category_id' => $todo->category_id,
                                            'recurrence_type' => $todo->recurrence_type,
                                            'recurrence_end_date' => $todo->recurrence_end_date ? $todo->recurrence_end_date->format('Y-m-d') : null,
                                        ]) }})" style="cursor: pointer">
                                            <div class="flex items-center">
                                                <p class="font-medium {{ $todo->status === 'completed' ? 'line-through text-gray-500' : 'text-gray-900' }}">
                                                    {{ $todo->title }}
                                                </p>

                                                @if($todo->category)
                                                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium"
                                                          style="background-color: {{ hexToRgba($todo->category->color, 0.15) }}; color: {{ $todo->category->color }}">
                                                        {{ $todo->category->name }}
                                                    </span>
                                                @endif

                                                @if($todo->isRecurring())
                                                    <span class="ml-2 text-xs text-gray-500">
                                                        <span class="inline-flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                            </svg>
                                                            {{ $todo->recurrence_type === 'daily' ? '毎日' :
                                                               ($todo->recurrence_type === 'weekly' ? '毎週' : '毎月') }}
                                                        </span>
                                                    </span>
                                                @endif
                                            </div>

                                            @if($todo->due_time)
                                                <div class="text-sm text-gray-500 mt-0.5">
                                                    <span class="inline-flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $todo->due_time->format('H:i') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-shrink-0 ml-3">
                                            <form action="{{ route('todos.trash', $todo) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-gray-400 hover:text-gray-600 transition-colors">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>

        @auth
        <!-- タスク編集/追加モーダル -->
        @include('todos.partials.task-modal')
        @endauth
    </div>

        <script>
            // Main task modal functionality
            function openTaskModal(mode, todoId = null, todoData = null) {
                console.log('Opening modal:', mode, todoId, todoData);

                const modal = document.getElementById('task-modal');
                const form = document.getElementById('task-form');
                const title = document.getElementById('modal-title');
                const submitLabel = document.getElementById('submit-label');
                const deleteOptions = document.getElementById('delete-options');
                const deleteSingleBtn = document.getElementById('delete-single-btn');
                const deleteRecurringBtn = document.getElementById('delete-recurring-btn');
                const methodContainer = document.getElementById('method-field-container');

                // フォームをリセット
                form.reset();
                methodContainer.innerHTML = '';

                if (mode === 'add') {
                    // 新規タスク追加モード
                    title.textContent = '新しいタスク';
                    form.action = "{{ route('todos.store') }}";
                    submitLabel.textContent = '追加';
                    deleteOptions.classList.add('hidden');

                    // デフォルトの日付を設定
                    @if($view === 'date' || $view === 'calendar')
                    document.getElementById('due_date').value = "{{ $date->format('Y-m-d') }}";
                    @endif
                }
                else if (mode === 'edit' && todoId) {
                    // タスク編集モード
                    title.textContent = 'タスクの編集';
                    form.action = `/todos/${todoId}`;
                    submitLabel.textContent = '更新';

                    // Blade の @method('PUT') と同等の HTML を追加
                    methodContainer.innerHTML = `
                        <input type="hidden" name="_method" value="PUT">
                    `;

                    console.log('Added method field for PUT');

                    // 削除ボタンの設定
                    deleteOptions.classList.remove('hidden');

                    // 通常の削除ボタン
                    deleteSingleBtn.onclick = function() {
                        confirmDelete(todoId, todoData.title, false);
                    };

                    // 繰り返し削除ボタン
                    if (todoData && todoData.recurrence_type && todoData.recurrence_type !== 'none') {
                        deleteRecurringBtn.classList.remove('hidden');
                        deleteRecurringBtn.onclick = function() {
                            confirmDelete(todoId, todoData.title, true);
                        };
                    } else {
                        deleteRecurringBtn.classList.add('hidden');
                    }

                    // タスクデータをフォームに設定
                    if (todoData) {
                        document.getElementById('title').value = todoData.title || '';
                        document.getElementById('due_date').value = todoData.due_date || '';
                        document.getElementById('due_time').value = todoData.due_time || '';
                        document.getElementById('category_id').value = todoData.category_id || '';
                        document.getElementById('recurrence_type').value = todoData.recurrence_type || 'none';

                        // 繰り返し終了日の表示制御
                        toggleRecurrenceEndDate();
                        if (todoData.recurrence_type && todoData.recurrence_type !== 'none') {
                            document.getElementById('recurrence_end_date').value = todoData.recurrence_end_date || '';
                        }

                        // カテゴリーの選択状態を更新
                        updateCategoryUI();
                    }
                }

                // モーダルを表示
                modal.classList.remove('hidden');
            }

            // タスクモーダルを閉じる
            function closeTaskModal() {
                document.getElementById('task-modal').classList.add('hidden');
            }

            // 繰り返し終了日フィールドの表示/非表示切替
            function toggleRecurrenceEndDate() {
                const recurrenceType = document.getElementById('recurrence_type').value;
                const container = document.getElementById('recurrence-end-container');

                if (recurrenceType !== 'none') {
                    container.classList.remove('hidden');

                    // デフォルトで1ヶ月後の日付を設定
                    if (!document.getElementById('recurrence_end_date').value) {
                        const today = new Date();
                        today.setMonth(today.getMonth() + 1);
                        document.getElementById('recurrence_end_date').value = today.toISOString().split('T')[0];
                    }
                } else {
                    container.classList.add('hidden');
                }
            }

            // カテゴリーのUI更新（選択時やロード時）
            function updateCategoryUI() {
                const categorySelect = document.getElementById('category_id');
                const selectedCategoryChip = document.getElementById('selected-category-chip');
                const selectedCategoryName = document.getElementById('selected-category-name');

                if (categorySelect && categorySelect.value) {
                    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        const color = selectedOption.dataset.color;

                        // Border styling
                        categorySelect.style.borderLeft = `4px solid ${color}`;

                        // Show category chip
                        selectedCategoryName.textContent = selectedOption.textContent;
                        selectedCategoryChip.style.backgroundColor = hexToRgba(color, 0.1);
                        selectedCategoryChip.style.color = color;
                        selectedCategoryChip.style.borderColor = hexToRgba(color, 0.2);
                        selectedCategoryChip.classList.remove('hidden');
                        return;
                    }
                }

                // If no category is selected
                categorySelect.style.borderLeft = '';
                selectedCategoryChip.classList.add('hidden');
            }

            // Hex to RGBA conversion helper
            function hexToRgba(hex, alpha = 1) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            // タスクフォーム送信
            function submitTaskForm() {
                console.log('Submitting form:', document.getElementById('task-form'));
                document.getElementById('task-form').submit();
            }

            // 削除確認
            function confirmDelete(todoId, taskTitle, isRecurring) {
                console.log('Confirming delete:', todoId, taskTitle, isRecurring);

                const modal = document.getElementById('delete-confirm-modal');
                const message = document.getElementById('delete-message');
                const form = document.getElementById('delete-form');

                // フォームのアクションを設定
                form.action = `/todos/${todoId}`;

                // 既存のdelete_recurringフィールドを削除
                const existingField = form.querySelector('input[name="delete_recurring"]');
                if (existingField) {
                    existingField.remove();
                }

                // メッセージと繰り返し削除パラメータを設定
                if (isRecurring) {
                    message.textContent = `「${taskTitle}」とそれに関連するすべての繰り返しタスクを削除しますか？`;

                    // 繰り返し削除用のパラメータを追加
                    const recurringField = document.createElement('input');
                    recurringField.type = 'hidden';
                    recurringField.name = 'delete_recurring';
                    recurringField.value = '1';
                    form.appendChild(recurringField);

                    console.log('Added delete_recurring field:', recurringField);
                } else {
                    message.textContent = `「${taskTitle}」を削除しますか？`;
                }

                // 確認モーダルを表示
                modal.classList.remove('hidden');

                // タスク編集モーダルを閉じる
                closeTaskModal();
            }

            // 削除確認モーダルを閉じる
            function closeDeleteModal() {
                document.getElementById('delete-confirm-modal').classList.add('hidden');
            }

            // ESCキーでモーダルを閉じる
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeTaskModal();
                    closeDeleteModal();
                }
            });

            // タスク編集
            function editTodo(todoId, todoData) {
                openTaskModal('edit', todoId, todoData);
            }

            // DOMロード時の初期設定
            document.addEventListener('DOMContentLoaded', function() {
                // Category Management functionality
                const newCategoryButton = document.getElementById('new-category-button');
                const categorySelector = document.getElementById('category-selector');
                const newCategoryForm = document.getElementById('new-category-form');
                const saveCategoryButton = document.getElementById('save-category-button');
                const cancelCategoryButton = document.getElementById('cancel-category-button');
                const categorySelect = document.getElementById('category_id');
                const newCategoryName = document.getElementById('new-category-name');
                const newCategoryColor = document.getElementById('new-category-color');
                const colorPreview = document.getElementById('color-preview');
                const selectedCategoryChip = document.getElementById('selected-category-chip');
                const clearCategoryButton = document.getElementById('clear-category-button');

                // Show the new category form
                if (newCategoryButton) {
                    newCategoryButton.addEventListener('click', function() {
                        categorySelector.classList.add('hidden');
                        newCategoryForm.classList.remove('hidden');
                        newCategoryName.focus();
                    });
                }

                // Cancel creating a new category
                if (cancelCategoryButton) {
                    cancelCategoryButton.addEventListener('click', function() {
                        resetCategoryForm();
                    });
                }

                // Update color preview when color is changed
                if (newCategoryColor) {
                    newCategoryColor.addEventListener('input', function() {
                        colorPreview.style.backgroundColor = this.value;
                    });
                    // Initialize color preview
                    if (colorPreview) {
                        colorPreview.style.backgroundColor = newCategoryColor.value;
                    }
                }

                // Save a new category
                if (saveCategoryButton) {
                    saveCategoryButton.addEventListener('click', function() {
                        const name = newCategoryName.value.trim();
                        const color = newCategoryColor.value;

                        if (!name) {
                            alert('カテゴリー名を入力してください');
                            return;
                        }

                        // Show loading state
                        saveCategoryButton.disabled = true;
                        saveCategoryButton.innerHTML = '保存中...';

                        // Send AJAX request to create category
                        fetch('{{ route('api.categories.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                name: name,
                                color: color
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('サーバーエラーが発生しました');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Add the new category to the dropdown
                            const option = document.createElement('option');
                            option.value = data.id;
                            option.textContent = data.name;
                            option.dataset.color = data.color;
                            option.selected = true;
                            categorySelect.appendChild(option);

                            // Reset and hide the form
                            resetCategoryForm();

                            // Update the category UI
                            updateCategoryUI();

                            // Show success message
                            showNotification('カテゴリーを作成しました', 'success');
                        })
                        .catch(error => {
                            console.error('Error creating category:', error);
                            showNotification('カテゴリーの作成に失敗しました', 'error');
                        })
                        .finally(() => {
                            saveCategoryButton.disabled = false;
                            saveCategoryButton.innerHTML = '保存';
                        });
                    });
                }

                // Clear selected category
                if (clearCategoryButton) {
                    clearCategoryButton.addEventListener('click', function() {
                        categorySelect.value = '';
                        selectedCategoryChip.classList.add('hidden');
                        categorySelect.style.borderLeft = '';
                    });
                }

                // Handle category selection change
                if (categorySelect) {
                    categorySelect.addEventListener('change', updateCategoryUI);
                }

                // Existing functionality for recurrenceType
                const recurrenceTypeSelect = document.getElementById('recurrence_type');
                if (recurrenceTypeSelect) {
                    recurrenceTypeSelect.addEventListener('change', toggleRecurrenceEndDate);
                }

                // Helper functions
                function resetCategoryForm() {
                    newCategoryForm.classList.add('hidden');
                    categorySelector.classList.remove('hidden');
                    newCategoryName.value = '';
                    newCategoryColor.value = '#3B82F6';
                    colorPreview.style.backgroundColor = '#3B82F6';
                }

                function showNotification(message, type) {
                    // Create notification element
                    const notification = document.createElement('div');
                    notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg text-white text-sm z-50 transition-opacity duration-300 transform translate-y-0 opacity-100 ${type === 'error' ? 'bg-red-500' : 'bg-green-500'}`;
                    notification.textContent = message;

                    // Add to DOM
                    document.body.appendChild(notification);

                    // Fade out and remove after 3 seconds
                    setTimeout(() => {
                        notification.classList.add('opacity-0', 'translate-y-2');
                        setTimeout(() => {
                            document.body.removeChild(notification);
                        }, 300);
                    }, 3000);
                }
            });
        </script>
        {{-- @endauth --}}
    </div>
</x-app-layout>
