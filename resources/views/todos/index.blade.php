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
                @else
                    <h1 class="text-xl font-semibold">
                        @if($view === 'inbox')
                            MEMO一覧
                        @endif
                    </h1>
                @endif
            </div>

            @auth
                <button type="button" onclick="openTaskModal('add')"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    タスクを追加
                </button>
            @endauth
        </div>


        <div>
            @if($view === 'calendar')
                @include('todos.partials.calendar')
            @else
                @include('todos.partials.task-list')
            @endif
        </div>

        @auth
        {{-- モーダルウィンドウについて --}}
            <div id="task-modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                <div class="absolute inset-0 bg-black bg-opacity-30" onclick="closeTaskModal()"></div>

                <div class="bg-white rounded-lg shadow-md w-full max-w-md relative z-10">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 id="modal-title" class="text-lg font-medium">新しいタスク</h3>
                    </div>

                    <div class="p-6">
                        <x-task-form :categories="$categories" id="task-form">
                            <input type="hidden" id="task-id" value="">
                        </x-task-form>
                    </div>

                    <div class="flex justify-end px-6 py-3 bg-gray-50 rounded-b-lg border-t border-gray-200">
                        <button type="button" onclick="closeTaskModal()"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded mr-2 hover:bg-gray-300">
                            キャンセル
                        </button>
                        <button type="button" onclick="document.getElementById('task-form').submit()"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            <span id="submit-label">追加</span>
                        </button>
                    </div>

                    <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-500" onclick="closeTaskModal()">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <script>
                // タスクの追加/編集用のモーダルウィンドウを開く機能
                function openTaskModal(mode, todoId = null, todoData = null) {
                    const modal = document.getElementById('task-modal');
                    const form = document.getElementById('task-form');
                    const modalTitle = document.getElementById('modal-title');
                    const submitLabel = document.getElementById('submit-label');
                    const taskIdField = document.getElementById('task-id');

                    if (mode === 'add') {
                        modalTitle.textContent = '新しいタスク';
                        form.action = "{{ route('todos.store') }}";
                        form.method = 'POST';
                        form.reset();
                        submitLabel.textContent = '追加';

                        // Set default date based on current view
                        @if($view === 'date' || $view === 'calendar')
                        document.getElementById('due_date').value = "{{ $date->format('Y-m-d') }}";
                        @endif
                    } else if (mode === 'edit' && todoId) {
                        modalTitle.textContent = 'タスクの編集';
                        form.action = `/todos/${todoId}`;
                        form.method = 'POST';


                        let methodField = form.querySelector('input[name="_method"]');
                        if (!methodField) {
                            methodField = document.createElement('input');
                            methodField.type = 'hidden';
                            methodField.name = '_method';
                            form.appendChild(methodField);
                        }
                        methodField.value = 'PUT';

                        submitLabel.textContent = '更新';


                        if (todoData) {
                            document.getElementById('title').value = todoData.title;
                            document.getElementById('due_date').value = todoData.due_date;
                            document.getElementById('due_time').value = todoData.due_time;
                            document.getElementById('category_id').value = todoData.category_id || '';
                            document.getElementById('recurrence_type').value = todoData.recurrence_type || 'none';

                            const recurrenceEndContainer = document.getElementById('recurrence-end-container');
                            if (todoData.recurrence_type && todoData.recurrence_type !== 'none') {
                                recurrenceEndContainer.classList.remove('hidden');
                                document.getElementById('recurrence_end_date').value = todoData.recurrence_end_date || '';
                            } else {
                                recurrenceEndContainer.classList.add('hidden');
                            }

                            // カテゴリに基づいて選択ボーダーを更新
                            const categorySelect = document.getElementById('category_id');
                            if (todoData.category_id) {
                                const selectedOption = Array.from(categorySelect.options).find(
                                    option => option.value == todoData.category_id
                                );
                                if (selectedOption) {
                                    categorySelect.style.borderLeft = `4px solid ${selectedOption.dataset.color}`;
                                }
                            } else {
                                categorySelect.style.borderLeft = '';
                            }
                        }
                    }
                    //モーダルウィンドウの表示
                    modal.classList.remove('hidden');
                }

                function closeTaskModal() {
                    document.getElementById('task-modal').classList.add('hidden');
                }


                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') closeTaskModal();
                });


                function editTodo(todoId, todoData) {
                    openTaskModal('edit', todoId, todoData);
                }

                function toggleTodo(todoId) {
                    document.getElementById(`toggle-form-${todoId}`).submit();
                }
            </script>
        @endauth
    </div>
</x-app-layout>
