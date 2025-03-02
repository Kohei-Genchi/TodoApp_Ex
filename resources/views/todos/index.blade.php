<x-app-layout>
    <div class="max-w-6xl mx-auto p-4">
        @include('todos.partials.nav-tabs')

        <div class="mb-2 flex justify-between items-center">
            <div>
                @if($view === 'calendar')
                    <div class="flex items-center">
                        <a href="{{ route('todos.index', ['view' => 'calendar', 'date' => $date->copy()->subMonth()->format('Y-m-d')]) }}" class="mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <h1 class="text-xl font-semibold">{{ $date->format('Y年n月') }}</h1>
                        <a href="{{ route('todos.index', ['view' => 'calendar', 'date' => $date->copy()->addMonth()->format('Y-m-d')]) }}" class="ml-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                @elseif($view === 'date' || $view === 'today')
                    <div class="flex items-center">
                        <a href="{{ route('todos.index', ['view' => $view, 'date' => $date->copy()->subDay()->format('Y-m-d')]) }}" class="mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <h1 class="text-xl font-semibold">{{ $date->format('m月d日') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }})</h1>
                        <a href="{{ route('todos.index', ['view' => $view, 'date' => $date->copy()->addDay()->format('Y-m-d')]) }}" class="ml-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                @endif
            </div>

            @auth
                <button type="button" onclick="openTaskModal('add')"
                        class="bg-blue-500 text-white px-3 py-1.5 text-sm rounded hover:bg-blue-600 transition-colors">
                    タスクを追加
                </button>
            @endauth
        </div>

        <div>
            @if($view === 'calendar')
                @include('todos.partials.calendar')
            @elseif($view === 'today' || $view === 'date')
                @include('todos.partials.task-list')
            @endif
        </div>

        @auth
        <!-- タスク編集/追加モーダル -->
        <div id="task-modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeTaskModal()"></div>

            <!-- モーダルコンテンツ -->
            <div class="bg-white rounded-lg shadow-lg w-full max-w-xl relative z-10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 id="modal-title" class="text-lg font-medium text-gray-800">新しいタスク</h3>
                </div>

                <div class="p-6">
                    <!-- タスクフォーム -->
                    <form id="task-form" method="POST" action="{{ route('todos.store') }}">
                        @csrf

                        <!-- PUT メソッドは JavaScript で制御 -->
                        <div id="method-field-container">
                            <!-- 編集モードの場合、ここに @method('PUT') が挿入される -->
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">タスク名<span class="text-red-500">*</span></label>
                                <input type="text" id="title" name="title" required value=""
                                       class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">日付</label>
                                <input type="date" id="due_date" name="due_date" value="{{ now()->format('Y-m-d') }}"
                                       class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            </div>

                            <div>
                                <label for="due_time" class="block text-sm font-medium text-gray-700 mb-1">時間</label>
                                <input type="time" id="due_time" name="due_time" value="{{ now()->format('H:i') }}"
                                       class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">カテゴリー</label>
                                <select id="category_id" name="category_id"
                                        class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">カテゴリーなし</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                                data-color="{{ $category->color }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="recurrence_type" class="block text-sm font-medium text-gray-700 mb-1">繰り返し</label>
                                <select id="recurrence_type" name="recurrence_type"
                                        class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                        onchange="toggleRecurrenceEndDate()">
                                    <option value="none">繰り返しなし</option>
                                    <option value="daily">毎日</option>
                                    <option value="weekly">毎週</option>
                                    <option value="monthly">毎月</option>
                                </select>
                            </div>

                            <div id="recurrence-end-container" class="hidden">
                                <label for="recurrence_end_date" class="block text-sm font-medium text-gray-700 mb-1">繰り返し終了日</label>
                                <input type="date" id="recurrence_end_date" name="recurrence_end_date"
                                       class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            </div>

                            <input type="hidden" id="task-id" value="">
                        </div>
                    </form>
                </div>

                <!-- フッター部分 -->
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg border-t border-gray-200">
                    <!-- 操作ボタン -->
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        <!-- 左側: 削除オプション（初期状態では非表示） -->
                        <div id="delete-options" class="hidden flex flex-wrap gap-2">
                            <button type="button" id="delete-single-btn"
                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                このタスクを削除
                            </button>
                            <button type="button" id="delete-recurring-btn"
                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-red-700 text-white rounded hover:bg-red-800 transition-colors hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                繰り返しタスクも削除
                            </button>
                        </div>

                        <!-- 右側: 保存/キャンセルボタン -->
                        <div class="flex items-center ml-auto gap-2">
                            <button type="button" onclick="closeTaskModal()"
                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors">
                                キャンセル
                            </button>
                            <button type="button" onclick="submitTaskForm()"
                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span id="submit-label">追加</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 閉じるボタン -->
                <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition-colors" onclick="closeTaskModal()">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- 削除確認モーダル -->
        <div id="delete-confirm-modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeDeleteModal()"></div>

            <div class="bg-white rounded-lg shadow-lg w-full max-w-md relative z-10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                    <h3 class="text-lg font-medium text-red-600">削除の確認</h3>
                </div>

                <div class="p-6">
                    <div class="flex items-center mb-4 text-red-600">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p id="delete-message" class="text-gray-700 font-medium"></p>
                    </div>

                    <p class="text-sm text-gray-500 mb-6">この操作は元に戻すことができません。</p>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDeleteModal()"
                                class="px-3 py-1.5 text-sm bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors">
                            キャンセル
                        </button>
                        <form id="delete-form" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                                削除する
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // タスクモーダルを開く
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

                        // カテゴリーの色表示更新
                        updateCategoryColor();
                    }
                }

                // モーダルを表示
                modal.classList.remove('hidden');
            }

            // タスクモーダルを閉じる
            function closeTaskModal() {
                document.getElementById('task-modal').classList.add('hidden');
            }

            // カテゴリーの色表示を更新
            function updateCategoryColor() {
                const categorySelect = document.getElementById('category_id');
                if (categorySelect) {
                    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                    categorySelect.style.borderLeft = selectedOption && selectedOption.value
                        ? `4px solid ${selectedOption.dataset.color}`
                        : '';
                }
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
                // カテゴリー選択の色変更イベント
                const categorySelect = document.getElementById('category_id');
                if (categorySelect) {
                    categorySelect.addEventListener('change', updateCategoryColor);
                }

                // 繰り返しタイプ変更イベント
                const recurrenceTypeSelect = document.getElementById('recurrence_type');
                if (recurrenceTypeSelect) {
                    recurrenceTypeSelect.addEventListener('change', toggleRecurrenceEndDate);
                }
            });
        </script>
        @endauth
    </div>
</x-app-layout>
