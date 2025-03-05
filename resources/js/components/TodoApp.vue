<template>
    <div class="bg-gray-100 min-h-screen main-content">
        <!-- ヘッダー（「+ 新しいタスク」ボタンを含む） -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
                <div class="flex flex-col space-y-1">
                    <!-- App Title and View Selector Row -->
                    <div class="flex justify-between items-center">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Todo App
                        </h1>

                        <!-- ビューセレクター -->
                        <div class="flex space-x-2">
                            <button
                                @click="setView('today')"
                                :class="[
                                    'px-3 py-1 rounded-md text-sm font-medium',
                                    currentView === 'today'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                                ]"
                            >
                                今日
                            </button>
                            <button
                                @click="showCalendarView()"
                                :class="[
                                    'px-3 py-1 rounded-md text-sm font-medium',
                                    currentView === 'calendar'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                                ]"
                            >
                                カレンダー
                            </button>
                            <button
                                @click="showTrashView()"
                                :class="[
                                    'px-3 py-1 rounded-md text-sm font-medium',
                                    currentView === 'trash'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                                ]"
                            >
                                ゴミ箱
                            </button>
                        </div>
                    </div>

                    <!-- Add Task Button (always visible for non-trash views) -->
                    <div v-if="currentView !== 'trash'">
                        <button
                            @click="openAddTaskModal"
                            class="w-full py-1 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            + 新しいタスク
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            <!-- 日付ナビゲーション -->
            <div
                v-if="currentView !== 'calendar' && currentView !== 'trash'"
                class="mb-2 flex justify-between items-center"
            >
                <button
                    @click="previousDay"
                    class="text-gray-600 hover:text-gray-900 px-3 py-1"
                >
                    前日
                </button>

                <h2 class="text-lg font-medium text-gray-900">
                    {{ formattedDate }}
                </h2>

                <button
                    @click="nextDay"
                    class="text-gray-600 hover:text-gray-900 px-3 py-1"
                >
                    翌日
                </button>
            </div>

            <!-- カレンダー月ナビゲーション -->
            <div
                v-if="currentView === 'calendar'"
                class="mb-2 flex justify-between items-center"
            >
                <button
                    @click="previousMonth"
                    class="text-gray-600 hover:text-gray-900 px-3 py-1"
                >
                    前月
                </button>

                <h2 class="text-lg font-medium text-gray-900">
                    {{ formattedMonth }}
                </h2>

                <button
                    @click="nextMonth"
                    class="text-gray-600 hover:text-gray-900 px-3 py-1"
                >
                    翌月
                </button>
            </div>

            <!-- ゴミ箱を空にするボタン -->
            <div
                v-if="currentView === 'trash' && trashedTodos.length > 0"
                class="mb-2"
            >
                <button
                    @click="confirmEmptyTrash"
                    class="w-full py-1 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    ゴミ箱を空にする
                </button>
            </div>

            <!-- カレンダー表示 -->
            <todo-calendar
                v-if="currentView === 'calendar'"
                :current-date="currentDate"
                :todos="todos"
                @date-selected="selectDate"
                @edit-task="openEditTaskModal"
            />

            <!-- タスク一覧（通常ビュー） -->
            <todo-list
                v-if="currentView !== 'calendar' && currentView !== 'trash'"
                :todos="filteredTodos"
                :categories="categories"
                @toggle-task="toggleTaskStatus"
                @edit-task="openEditTaskModal"
                @trash-task="trashTask"
            />

            <!-- タスク一覧（ゴミ箱ビュー） -->
            <todo-list
                v-if="currentView === 'trash'"
                :todos="trashedTodos"
                :categories="categories"
                :is-trash-view="true"
                @restore-task="restoreTask"
                @delete-task="confirmDeleteTask"
            />
        </main>

        <!-- タスク追加/編集モーダル -->
        <task-modal
            v-if="showTaskModal"
            :mode="taskModalMode"
            :todo-id="selectedTaskId"
            :todo-data="selectedTaskData"
            :categories="categories"
            :current-date="currentDate"
            :current-view="currentView"
            @close="closeTaskModal"
            @submit="submitTask"
            @delete="handleTaskDelete"
            @category-created="loadCategories"
        />

        <!-- 削除確認モーダル -->
        <delete-confirm-modal
            v-if="showDeleteConfirmModal"
            :todo-title="selectedTaskData?.title || ''"
            :is-recurring="
                selectedTaskData?.recurrence_type &&
                selectedTaskData.recurrence_type !== 'none'
            "
            @confirm="confirmDelete"
            @cancel="showDeleteConfirmModal = false"
        />

        <!-- 通知コンポーネント -->
        <notification-component ref="notification" />
    </div>
</template>

<script>
import { ref, computed, onMounted, defineAsyncComponent } from "vue";
import TodoApi from "../api/todo";
import CategoryApi from "../api/category";

// Components imports
const TodoList = defineAsyncComponent(() => import("./TodoList.vue"));
const TodoCalendar = defineAsyncComponent(() => import("./TodoCalendar.vue"));
const TaskModal = defineAsyncComponent(() => import("./TaskModal.vue"));
const DeleteConfirmModal = defineAsyncComponent(
    () => import("./DeleteConfirmModal.vue"),
);
const NotificationComponent = defineAsyncComponent(
    () => import("./UI/NotificationComponent.vue"),
);

export default {
    components: {
        TodoList,
        TodoCalendar,
        TaskModal,
        DeleteConfirmModal,
        NotificationComponent,
    },

    setup() {
        // State
        const todos = ref([]);
        const trashedTodos = ref([]);
        const categories = ref([]);
        const currentView = ref("today");
        const currentDate = ref(new Date().toISOString().split("T")[0]);

        // Modal state
        const showTaskModal = ref(false);
        const taskModalMode = ref("add");
        const selectedTaskId = ref(null);
        const selectedTaskData = ref(null);
        const showDeleteConfirmModal = ref(false);
        const deleteAllRecurring = ref(false);

        // Notification ref
        const notification = ref(null);

        // Date formatting helper - CRITICAL for comparing dates correctly
        function formatDateForComparison(dateString) {
            if (!dateString) return "";

            try {
                // Handle different date formats
                let date;
                if (typeof dateString === "string") {
                    // If it's already a YYYY-MM-DD string, return it
                    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                        return dateString;
                    }
                    date = new Date(dateString);
                } else if (dateString instanceof Date) {
                    date = dateString;
                } else {
                    console.error("Invalid date format:", dateString);
                    return "";
                }

                // Ensure it's a valid date
                if (isNaN(date.getTime())) {
                    console.error("Invalid date:", dateString);
                    return "";
                }

                // Format to YYYY-MM-DD using local timezone
                return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
            } catch (e) {
                console.error("Error formatting date:", e, dateString);
                return "";
            }
        }

        // Computed properties
        const formattedDate = computed(() => {
            const date = new Date(currentDate.value);
            const today = new Date();

            if (date.toDateString() === today.toDateString()) {
                return "今日";
            }

            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            if (date.toDateString() === tomorrow.toDateString()) {
                return "明日";
            }

            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            if (date.toDateString() === yesterday.toDateString()) {
                return "昨日";
            }

            // Japanese format
            const options = {
                year: "numeric",
                month: "long",
                day: "numeric",
                weekday: "long",
            };
            return date.toLocaleDateString("ja-JP", options);
        });

        const formattedMonth = computed(() => {
            const date = new Date(currentDate.value);
            const options = { year: "numeric", month: "long" };
            return date.toLocaleDateString("ja-JP", options);
        });

        const filteredTodos = computed(() => {
            const formattedCurrentDate = formatDateForComparison(
                currentDate.value,
            );
            console.log("Filtering todos for date:", formattedCurrentDate);
            console.log("Available todos:", todos.value.length);

            // Show more details about available todos to debug
            if (todos.value.length > 0) {
                console.log(
                    "Todo dates:",
                    todos.value.map((t) => ({
                        id: t.id,
                        title: t.title,
                        due_date: t.due_date,
                        formatted: formatDateForComparison(t.due_date),
                    })),
                );
            }

            const filtered = todos.value.filter((todo) => {
                // Format the todo's due_date in local timezone
                const formattedTodoDate = formatDateForComparison(
                    todo.due_date,
                );
                const match =
                    formattedTodoDate === formattedCurrentDate &&
                    todo.status !== "trashed";
                return match;
            });

            console.log("Filtered todos count:", filtered.length);
            return filtered;
        });

        // Methods
        async function loadTasks() {
            console.log(
                "Loading tasks for view:",
                currentView.value,
                "date:",
                currentDate.value,
            );
            try {
                const response = await TodoApi.getTasks(
                    currentView.value,
                    currentDate.value,
                );
                console.log("API response data:", response.data);

                if (!Array.isArray(response.data)) {
                    console.error(
                        "API response is not an array:",
                        response.data,
                    );
                    todos.value = [];
                    return;
                }

                // Process the response to ensure consistency
                todos.value = response.data.map((todo) => {
                    // Make a deep copy to avoid reference issues
                    const processedTodo = { ...todo };

                    // Ensure ID is a number
                    if (processedTodo.id !== undefined) {
                        processedTodo.id = Number(processedTodo.id);
                    }

                    // Ensure category_id is a number if present
                    if (
                        processedTodo.category_id !== null &&
                        processedTodo.category_id !== undefined
                    ) {
                        processedTodo.category_id = Number(
                            processedTodo.category_id,
                        );
                    }

                    // Add formatted date for easier comparison
                    if (processedTodo.due_date) {
                        processedTodo.formatted_due_date =
                            formatDateForComparison(processedTodo.due_date);
                    }

                    return processedTodo;
                });

                console.log("Todos after processing:", todos.value);
            } catch (error) {
                console.error("Error loading tasks:", error);
                notification.value?.show(
                    "タスクの読み込みに失敗しました",
                    "error",
                );
            }
        }

        async function loadTrashedTasks() {
            try {
                const response = await TodoApi.getTrashedTasks();
                trashedTodos.value = response.data;
            } catch (error) {
                console.error("Error loading trashed tasks:", error);
                notification.value?.show(
                    "ゴミ箱の読み込みに失敗しました",
                    "error",
                );
            }
        }

        async function loadCategories() {
            console.log("Loading categories...");
            try {
                // Add a timestamp to avoid cache issues
                const response = await CategoryApi.getCategories();
                console.log("Categories API response:", response);

                if (!response || !response.data) {
                    console.error(
                        "Invalid response from categories API:",
                        response,
                    );
                    categories.value = [];
                    return;
                }

                console.log("Categories loaded:", response.data);

                // Ensure the categories are properly formatted
                categories.value = Array.isArray(response.data)
                    ? response.data.map((cat) => {
                          return {
                              id: Number(cat.id),
                              name: cat.name || "Unnamed Category",
                              color: cat.color || "#cccccc",
                              user_id: cat.user_id,
                          };
                      })
                    : [];

                console.log("Categories processed:", categories.value);
            } catch (error) {
                console.error("Error loading categories:", error);
                notification.value?.show(
                    "カテゴリーの読み込みに失敗しました",
                    "error",
                );
            }
        }
        function setView(view) {
            currentView.value = view;
            if (view === "today") {
                currentDate.value = new Date().toISOString().split("T")[0];
            }
            loadTasks();
        }

        function showCalendarView() {
            currentView.value = "calendar";
            loadTasks();
        }

        function showTrashView() {
            currentView.value = "trash";
            loadTrashedTasks();
        }

        function previousDay() {
            const date = new Date(currentDate.value);
            date.setDate(date.getDate() - 1);
            currentDate.value = date.toISOString().split("T")[0];
            loadTasks();
        }

        function nextDay() {
            const date = new Date(currentDate.value);
            date.setDate(date.getDate() + 1);
            currentDate.value = date.toISOString().split("T")[0];
            loadTasks();
        }

        function previousMonth() {
            const date = new Date(currentDate.value);
            date.setMonth(date.getMonth() - 1);
            currentDate.value = date.toISOString().split("T")[0];
            loadTasks();
        }

        function nextMonth() {
            const date = new Date(currentDate.value);
            date.setMonth(date.getMonth() + 1);
            currentDate.value = date.toISOString().split("T")[0];
            loadTasks();
        }

        function selectDate(date) {
            currentDate.value = date;
            currentView.value = "date";
            loadTasks();
        }

        // Task modal functions
        function openAddTaskModal() {
            console.log("Opening add task modal");

            taskModalMode.value = "add";
            selectedTaskId.value = null;
            selectedTaskData.value = {
                title: "",
                description: "",
                due_date: currentDate.value,
                due_time: "",
                category_id: "",
                recurrence_type: "none",
                recurrence_end_date: "",
            };

            console.log("Set add task modal data:", selectedTaskData.value);
            showTaskModal.value = true;
        }

        async function openEditTaskModal(task) {
            try {
                console.log("OpenEditTaskModal called with:", task);

                // Handle direct ID input (as number or string)
                if (
                    (typeof task === "number" || typeof task === "string") &&
                    !isNaN(Number(task))
                ) {
                    const taskId = Number(task);
                    console.log("Task is a direct ID:", taskId);
                    await fetchAndEditTask(taskId);
                    return;
                }

                // Handle case where task might be empty array or undefined
                if (!task || (Array.isArray(task) && task.length === 0)) {
                    console.error("Task is empty or invalid:", task);
                    notification.value?.show(
                        "編集するタスクが見つかりません",
                        "error",
                    );
                    return;
                }

                // Handle case where task is an object with an ID property
                if (typeof task === "object" && task !== null) {
                    // Force reload categories before opening modal
                    await loadCategories();
                    console.log(
                        "Categories refreshed before opening modal:",
                        categories.value,
                    );

                    taskModalMode.value = "edit";

                    // Make sure task ID is properly set
                    if (task.id === undefined || task.id === null) {
                        console.error("Task object has no ID:", task);
                        notification.value?.show(
                            "タスクIDが見つかりません",
                            "error",
                        );
                        return;
                    }

                    selectedTaskId.value = Number(task.id);
                    console.log(
                        "Selected task ID set to:",
                        selectedTaskId.value,
                    );

                    // Make a deep copy of the task to avoid reference issues
                    selectedTaskData.value = JSON.parse(JSON.stringify(task));

                    // Log the data being passed to the modal
                    console.log("Data being passed to TaskModal:", {
                        mode: taskModalMode.value,
                        todoId: selectedTaskId.value,
                        todoData: selectedTaskData.value,
                        categories: categories.value,
                    });

                    showTaskModal.value = true;
                }
            } catch (error) {
                console.error("Error in openEditTaskModal:", error);
                notification.value?.show(
                    "タスク編集の準備中にエラーが発生しました",
                    "error",
                );
            }
        }

        // Helper function to fetch task data by ID and open edit modal
        async function fetchAndEditTask(taskId) {
            console.log("Fetching task data for ID:", taskId);

            try {
                // First check if we have this task already loaded
                const task =
                    todos.value.find((t) => t.id === taskId) ||
                    trashedTodos.value.find((t) => t.id === taskId);

                if (task) {
                    console.log("Found task in local data:", task);
                    // First, ensure categories are loaded
                    await loadCategories();

                    taskModalMode.value = "edit";
                    selectedTaskId.value = Number(taskId);
                    selectedTaskData.value = JSON.parse(JSON.stringify(task));
                    showTaskModal.value = true;
                    return;
                }

                // Fetch the task data from API
                console.log("Task not found locally, fetching from API...");
                const response = await TodoApi.getTaskById(taskId);
                console.log("Task data fetched from API:", response.data);

                // Ensure categories are loaded
                await loadCategories();

                taskModalMode.value = "edit";
                selectedTaskId.value = Number(taskId);
                selectedTaskData.value = response.data;
                showTaskModal.value = true;
            } catch (error) {
                console.error("Error fetching task data:", error);
                notification.value?.show(
                    "タスクデータの取得に失敗しました",
                    "error",
                );
            }
        }

        function closeTaskModal() {
            console.log("Closing task modal");
            showTaskModal.value = false;
        }

        function formatDateForAPI(dateStr) {
            // Ensure we send the date in the format the API expects
            // For API calls, we should ensure the date is sent correctly
            if (!dateStr) return null;

            try {
                const date = new Date(dateStr);
                // Format as YYYY-MM-DD and set time to midday to avoid timezone issues
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, "0");
                const day = String(date.getDate()).padStart(2, "0");

                // Return just the date part
                return `${year}-${month}-${day}`;
            } catch (e) {
                console.error("Error formatting date for API:", e);
                return null;
            }
        }

        // Add this to your TodoApp.vue setup function
        async function submitTask(taskData) {
            try {
                console.log("submitTask called with:", taskData);

                // Clone task data to avoid modifying the original
                const preparedData = { ...taskData };

                // Format dates correctly for the API
                if (preparedData.due_date) {
                    preparedData.due_date = formatDateForAPI(
                        preparedData.due_date,
                    );
                }

                if (preparedData.recurrence_end_date) {
                    preparedData.recurrence_end_date = formatDateForAPI(
                        preparedData.recurrence_end_date,
                    );
                }

                console.log(
                    "Submitting task with prepared data:",
                    preparedData,
                );
                console.log(
                    "Task mode:",
                    taskModalMode.value,
                    "Selected task ID:",
                    selectedTaskId.value,
                );

                let response;

                if (taskModalMode.value === "add") {
                    // New task addition
                    console.log("Creating new task with TodoApi.createTask()");
                    response = await TodoApi.createTask(preparedData);
                    console.log("Task created response:", response);
                } else {
                    // Ensure task ID is available before updating
                    const taskId =
                        selectedTaskId.value ||
                        (preparedData.id ? Number(preparedData.id) : null);

                    if (!taskId && taskId !== 0) {
                        console.error("No task ID available for update");
                        notification.value.show("error");
                        return;
                    }

                    // Check if we're adding a due date to a previously date-less task (メモ一覧のタスク)
                    const isAddingDueDateToMemo =
                        preparedData.due_date &&
                        (!selectedTaskData.value.due_date ||
                            selectedTaskData.value.due_date === null);

                    console.log(
                        "Is adding due date to memo task?",
                        isAddingDueDateToMemo,
                    );

                    // Update existing task
                    console.log(
                        `Updating task ID ${taskId} with TodoApi.updateTask()`,
                    );
                    response = await TodoApi.updateTask(taskId, preparedData);
                    console.log("Task updated response:", response);

                    // If we're adding a due date to a task from memo list, refresh memo list immediately
                    if (isAddingDueDateToMemo) {
                        console.log(
                            "Due date added to memo task - refreshing memo list",
                        );
                        await refreshMemoList();
                    }
                }

                closeTaskModal();

                // After closing modal, reload relevant task data based on current view
                if (currentView.value === "trash") {
                    await loadTrashedTasks();
                } else {
                    await loadTasks();
                }
            } catch (error) {
                console.error("Error submitting task:", error);
            }
        }
        async function refreshMemoList() {
            console.log("Refreshing memo list");
            try {
                // Fetch the updated memo list HTML
                const response = await fetch("/api/memos-partial", {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "text/html",
                        "Cache-Control": "no-cache, no-store, must-revalidate",
                    },
                    cache: "no-store", // Ensure no caching
                });

                if (!response.ok) {
                    throw new Error(`API returned status ${response.status}`);
                }

                const html = await response.text();
                console.log("Received memo list HTML, length:", html.length);

                // Find all memo list containers (might be multiple in different parts of the page)
                const memoContainers = document.querySelectorAll(
                    ".memo-list-container",
                );
                console.log(`Found ${memoContainers.length} memo containers`);

                if (memoContainers.length > 0) {
                    // Update all memo containers with the new HTML
                    memoContainers.forEach((container) => {
                        container.innerHTML = html;
                    });
                    console.log("Updated memo containers successfully");

                    // Re-attach any event listeners if needed
                    attachMemoListEvents();

                    return true;
                } else {
                    console.warn("No memo list containers found in the DOM");
                    return false;
                }
            } catch (error) {
                console.error("Error refreshing memo list:", error);
                return false;
            }
        }
        function attachMemoListEvents() {
            // Find all trash buttons in memo list and attach click handlers
            const trashButtons = document.querySelectorAll(
                '.memo-list-container button[onclick*="trashMemo"]',
            );
            console.log(
                `Re-attaching events to ${trashButtons.length} trash buttons`,
            );
            // Any other event listeners that need to be re-attached
        }

        async function toggleTaskStatus(task) {
            try {
                await TodoApi.toggleTask(task.id);

                // Optimistic update
                const taskIndex = todos.value.findIndex(
                    (t) => t.id === task.id,
                );
                if (taskIndex !== -1) {
                    todos.value[taskIndex].status =
                        todos.value[taskIndex].status === "completed"
                            ? "pending"
                            : "completed";
                }

                loadTasks();
            } catch (error) {
                console.error("Error toggling task status:", error);
                notification.value?.show(
                    "タスクのステータス変更に失敗しました",
                    "error",
                );
            }
        }

        async function trashTask(task) {
            try {
                await TodoApi.trashTask(task.id);
                notification.value?.show("タスクをゴミ箱に移動しました");
                loadTasks();
            } catch (error) {
                console.error("Error trashing task:", error);
                notification.value?.show("タスクの削除に失敗しました", "error");
            }
        }

        async function restoreTask(task) {
            try {
                await TodoApi.restoreTask(task.id);
                notification.value?.show("タスクを復元しました");
                loadTrashedTasks();
            } catch (error) {
                console.error("Error restoring task:", error);
                notification.value?.show("タスクの復元に失敗しました", "error");
            }
        }

        async function handleTaskDelete(id, deleteAllRecurringFlag) {
            console.log(
                "handleTaskDelete called with:",
                id,
                deleteAllRecurringFlag,
            );

            // First close the task modal
            closeTaskModal();

            // Then show the delete confirmation modal
            selectedTaskId.value = id;
            deleteAllRecurring.value = deleteAllRecurringFlag;

            // Find the task for showing in the confirmation
            const task =
                todos.value.find((t) => t.id === id) ||
                trashedTodos.value.find((t) => t.id === id);

            if (task) {
                selectedTaskData.value = task;
            }

            // Show the confirmation modal - don't delete until user confirms
            showDeleteConfirmModal.value = true;
        }

        function confirmDeleteTask(task) {
            selectedTaskId.value = task.id;
            selectedTaskData.value = task;
            showDeleteConfirmModal.value = true;
        }

        async function confirmDelete(confirmed = true) {
            console.log("confirmDelete called with confirmation:", confirmed);

            // Only proceed if user confirmed
            if (!confirmed) {
                showDeleteConfirmModal.value = false;
                return;
            }

            try {
                await TodoApi.deleteTask(
                    selectedTaskId.value,
                    deleteAllRecurring.value,
                );
                notification.value?.show("タスクを削除しました");
                showDeleteConfirmModal.value = false;

                // Aggressively refresh task lists
                if (currentView.value === "trash") {
                    await loadTrashedTasks();
                } else {
                    await loadTasks();
                }

                // Force a refresh of filtered todos
                todos.value = [...todos.value]; // Trigger reactivity
            } catch (error) {
                console.error("Error deleting task:", error);
                notification.value?.show("タスクの削除に失敗しました", "error");
            }
        }

        async function confirmEmptyTrash() {
            if (confirm("ゴミ箱を空にしますか？この操作は元に戻せません。")) {
                try {
                    await TodoApi.emptyTrash();
                    notification.value?.show("ゴミ箱を空にしました");
                    loadTrashedTasks();
                } catch (error) {
                    console.error("Error emptying trash:", error);
                    notification.value?.show(
                        "ゴミ箱を空にできませんでした",
                        "error",
                    );
                }
            }
        }

        // Initialize
        onMounted(() => {
            console.log("TodoApp component mounted");
            loadTasks();
            loadCategories();

            // Listen for edit-todo events from legacy code
            document
                .getElementById("todo-app")
                ?.addEventListener("edit-todo", async (event) => {
                    console.log("Received edit-todo event:", event.detail);

                    try {
                        const { id, data } = event.detail;

                        if (id !== undefined && id !== null) {
                            console.log("Edit-todo event contains ID:", id);
                            await fetchAndEditTask(Number(id));
                        } else if (data) {
                            console.log(
                                "Edit-todo event contains data object:",
                                data,
                            );
                            openEditTaskModal(data);
                        } else {
                            console.error(
                                "Invalid edit-todo event payload:",
                                event.detail,
                            );
                            notification.value?.show(
                                "タスク編集データが無効です",
                                "error",
                            );
                        }
                    } catch (error) {
                        console.error(
                            "Error processing edit-todo event:",
                            error,
                        );
                        notification.value?.show(
                            "タスク編集の処理中にエラーが発生しました",
                            "error",
                        );
                    }
                });
        });

        return {
            todos,
            trashedTodos,
            categories,
            currentView,
            currentDate,
            formattedDate,
            formattedMonth,
            filteredTodos,
            showTaskModal,
            taskModalMode,
            selectedTaskId,
            selectedTaskData,
            showDeleteConfirmModal,
            notification,
            setView,
            showCalendarView,
            showTrashView,
            previousDay,
            nextDay,
            previousMonth,
            nextMonth,
            selectDate,
            openAddTaskModal,
            openEditTaskModal,
            fetchAndEditTask,
            closeTaskModal,
            submitTask,
            toggleTaskStatus,
            trashTask,
            restoreTask,
            handleTaskDelete,
            confirmDeleteTask,
            confirmDelete,
            confirmEmptyTrash,
            loadCategories,
        };
    },
};
</script>
