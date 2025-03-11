resources/views/todos/partials/task-modal.blade.php
<div id="task-modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeTaskModal()"></div>

    <!-- Modal Content -->
    <div class="bg-white rounded-lg shadow-lg w-full max-w-xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 id="modal-title" class="text-lg font-medium text-gray-800">新しいタスク</h3>
        </div>

        <div class="p-6">
            <!-- Task Form -->
            <form id="task-form" method="POST" action="{{ route('todos.store') }}">
                @csrf
                <div id="method-field-container">
                    <!-- Method field will be injected here for PUT requests -->
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

                    <!-- Enhanced Category Selection with Inline Creation -->
                    <div id="category-section">

                        <div class="flex justify-between items-center mb-1">
                            <label for="new-category-button" class="block text-sm font-medium text-gray-700">カテゴリー</label>
                            <button type="button" id="new-category-button"
                                    class="text-xs text-blue-600 hover:text-blue-800 focus:outline-none">
                                新しいカテゴリー
                            </button>
                        </div>

                        <!-- Category Selector -->
                        <div id="category-selector" class="mb-3">
                            <select id="category_id" name="category_id"
                                    class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">カテゴリーなし</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" data-color="{{ $category->color }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Inline Category Creation Form (Hidden by Default) -->
                        <div id="new-category-form" class="hidden space-y-3 p-3 bg-gray-50 rounded-md mb-3">
                            <div>
                                <label for="new-category-name" class="block text-sm font-medium text-gray-700 mb-1">カテゴリー名</label>
                                <input type="text" id="new-category-name"
                                       class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            </div>

                            <div>
                                <label for="new-category-color" class="block text-sm font-medium text-gray-700 mb-1">カラー</label>
                                <div class="flex items-center">
                                    <input type="color" id="new-category-color" value="#3B82F6"
                                           class="h-8 w-12 border border-gray-300 rounded">
                                    <div class="ml-2 flex-1 h-8 rounded-md transition-colors" id="color-preview"></div>
                                </div>
                            </div>

                            <div class="flex space-x-2">
                                <button type="button" id="save-category-button"
                                        class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors">
                                    保存
                                </button>
                                <button type="button" id="cancel-category-button"
                                        class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 transition-colors">
                                    キャンセル
                                </button>
                            </div>
                        </div>

                        <!-- Category Chip (Visual Display of Selected Category) -->
                        <div id="selected-category-chip" class="hidden">
                            <div class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <span id="selected-category-name">カテゴリー名</span>
                                <button type="button" id="clear-category-button" class="ml-1 text-blue-600 hover:text-blue-800">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
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

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg border-t border-gray-200">
            <!-- Action Buttons -->
            <div class="flex flex-wrap justify-between items-center gap-2">
                <!-- Delete Options (Hidden by Default) -->
                <div id="delete-options" class="hidden flex flex-wrap gap-2">
                    <button type="button" id="delete-single-btn"
                            class="inline-flex items-center px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        このタスクを削除
                    </button>
                    <button type="button" id="delete-recurring-btn"
                            class="inline-flex items-center px-3 py-1.5 text-sm bg-red-700 text-white rounded-md hover:bg-red-800 transition-colors hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        繰り返しタスクも削除
                    </button>
                </div>

                <!-- Save/Cancel Buttons -->
                <div class="flex items-center ml-auto gap-2">
                    <button type="button" onclick="closeTaskModal()"
                            class="inline-flex items-center px-3 py-1.5 text-sm bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        キャンセル
                    </button>
                    <button type="button" onclick="submitTaskForm()"
                            class="inline-flex items-center px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span id="submit-label">追加</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Close Button -->
        <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition-colors"
                onclick="closeTaskModal()">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

<!-- Delete Confirmation Modal -->
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
                        class="px-3 py-1.5 text-sm bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                    キャンセル
                </button>
                <form id="delete-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        削除する
                    </button>
                </form>
            </div>
        </div>
    </div>
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

        // Reset form
        form.reset();
        methodContainer.innerHTML = '';

        if (mode === 'add') {
            // New task mode
            title.textContent = '新しいタスク';
            form.action = "{{ route('todos.store') }}";
            submitLabel.textContent = '追加';
            deleteOptions.classList.add('hidden');

            // Set default date based on current view
            @if(isset($view) && ($view === 'date' || $view === 'calendar') && isset($date))
            document.getElementById('due_date').value = "{{ $date->format('Y-m-d') }}";
            @endif
        }
        else if (mode === 'edit' && todoId) {
            // Edit task mode
            title.textContent = 'タスクの編集';
            form.action = `/todos/${todoId}`;
            submitLabel.textContent = '更新';

            // Add method field for PUT
            methodContainer.innerHTML = `<input type="hidden" name="_method" value="PUT">`;
            console.log('Added method field for PUT');

            // Set up delete options
            deleteOptions.classList.remove('hidden');

            // Regular delete button
            deleteSingleBtn.onclick = function() {
                confirmDelete(todoId, todoData.title, false);
            };

            // Recurring delete button
            if (todoData && todoData.recurrence_type && todoData.recurrence_type !== 'none') {
                deleteRecurringBtn.classList.remove('hidden');
                deleteRecurringBtn.onclick = function() {
                    confirmDelete(todoId, todoData.title, true);
                };
            } else {
                deleteRecurringBtn.classList.add('hidden');
            }

            // Set task data in form
            if (todoData) {
                document.getElementById('title').value = todoData.title || '';
                document.getElementById('due_date').value = todoData.due_date || '';
                document.getElementById('due_time').value = todoData.due_time || '';
                document.getElementById('category_id').value = todoData.category_id || '';
                document.getElementById('recurrence_type').value = todoData.recurrence_type || 'none';

                // Show/hide recurrence end date field
                toggleRecurrenceEndDate();
                if (todoData.recurrence_type && todoData.recurrence_type !== 'none') {
                    document.getElementById('recurrence_end_date').value = todoData.recurrence_end_date || '';
                }

                // Update UI for selected category
                updateCategoryUI();
            }
        }

        // Show modal
        modal.classList.remove('hidden');
    }

    // Close task modal
    function closeTaskModal() {
        document.getElementById('task-modal').classList.add('hidden');
    }

    // Toggle recurrence end date field
    function toggleRecurrenceEndDate() {
        const recurrenceType = document.getElementById('recurrence_type').value;
        const container = document.getElementById('recurrence-end-container');

        if (recurrenceType !== 'none') {
            container.classList.remove('hidden');

            // Set default end date (1 month from now if not set)
            if (!document.getElementById('recurrence_end_date').value) {
                const today = new Date();
                today.setMonth(today.getMonth() + 1);
                document.getElementById('recurrence_end_date').value = today.toISOString().split('T')[0];
            }
        } else {
            container.classList.add('hidden');
        }
    }

    // Update category UI
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
        if (categorySelect) categorySelect.style.borderLeft = '';
        if (selectedCategoryChip) selectedCategoryChip.classList.add('hidden');
    }

    // Convert hex color to rgba
    function hexToRgba(hex, alpha = 1) {
        if (!hex) return `rgba(156, 163, 175, ${alpha})`;

        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    // Submit task form
    function submitTaskForm() {
        document.getElementById('task-form').submit();
    }

    // Confirm delete
    function confirmDelete(todoId, taskTitle, isRecurring) {
        const modal = document.getElementById('delete-confirm-modal');
        const message = document.getElementById('delete-message');
        const form = document.getElementById('delete-form');

        // Set form action
        form.action = `/todos/${todoId}`;

        // Remove existing delete_recurring field
        const existingField = form.querySelector('input[name="delete_recurring"]');
        if (existingField) {
            existingField.remove();
        }

        // Set message and add parameter for recurring deletion
        if (isRecurring) {
            message.textContent = `「${taskTitle}」とそれに関連するすべての繰り返しタスクを削除しますか？`;

            // Add parameter for recurring deletion
            const recurringField = document.createElement('input');
            recurringField.type = 'hidden';
            recurringField.name = 'delete_recurring';
            recurringField.value = '1';
            form.appendChild(recurringField);
        } else {
            message.textContent = `「${taskTitle}」を削除しますか？`;
        }

        // Show confirmation modal
        modal.classList.remove('hidden');

        // Close task edit modal
        closeTaskModal();
    }

    // Close delete confirmation modal
    function closeDeleteModal() {
        document.getElementById('delete-confirm-modal').classList.add('hidden');
    }

    // Initialize when DOM is loaded
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

        // Show new category form
        if (newCategoryButton) {
            newCategoryButton.addEventListener('click', function() {
                categorySelector.classList.add('hidden');
                newCategoryForm.classList.remove('hidden');
                newCategoryName.focus();
            });
        }

        // Cancel creating new category
        if (cancelCategoryButton) {
            cancelCategoryButton.addEventListener('click', function() {
                resetCategoryForm();
            });
        }

        // Update color preview
        if (newCategoryColor && colorPreview) {
            newCategoryColor.addEventListener('input', function() {
                colorPreview.style.backgroundColor = this.value;
            });

            // Initialize color preview
            colorPreview.style.backgroundColor = newCategoryColor.value;
        }

        // Save new category
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

                // Send AJAX request
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
                    // Add category to dropdown
                    const option = document.createElement('option');
                    option.value = data.id;
                    option.textContent = data.name;
                    option.dataset.color = data.color;
                    option.selected = true;
                    categorySelect.appendChild(option);

                    // Reset form
                    resetCategoryForm();

                    // Update UI
                    updateCategoryUI();

                    // Show success notification
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

        // Handle recurrence type change
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
            notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg text-white text-sm z-50 shadow-lg transition-all duration-300 transform translate-y-0 opacity-100 ${type === 'error' ? 'bg-red-600' : 'bg-green-600'}`;
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

        // ESC key to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTaskModal();
                closeDeleteModal();
            }
        });
    });
</script>
