// In resources/js/components/TodoApp.vue
<template>
    <div class="bg-gray-100 min-h-screen main-content">
        <!-- Loading indicator -->
        <div v-if="!loaded" class="text-center py-10">
            <p class="text-gray-500">Loading...</p>
        </div>
        <div v-else>
            <!-- App header -->
            <app-header
                :current-view="currentView"
                @set-view="setView"
                @show-calendar="showCalendarView"
                @add-task="openAddTaskModal"
            />

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
                <!-- Date navigation -->
                <date-navigation
                    v-if="currentView !== 'calendar'"
                    :formatted-date="formattedDate"
                    @previous-day="previousDay"
                    @next-day="nextDay"
                />

                <!-- Calendar month navigation -->
                <month-navigation
                    v-if="currentView === 'calendar'"
                    :formatted-month="formattedMonth"
                    @previous-month="previousMonth"
                    @next-month="nextMonth"
                />

                <!-- Calendar view -->
                <todo-calendar
                    v-if="currentView === 'calendar'"
                    :current-date="currentDate"
                    :todos="todos"
                    @date-selected="selectDate"
                    @edit-task="openEditTaskModal"
                />

                <!-- Task list (standard view) -->
                <todo-list
                    v-if="currentView !== 'calendar'"
                    :todos="filteredTodos"
                    :categories="categories"
                    @toggle-task="toggleTaskStatus"
                    @edit-task="openEditTaskModal"
                    @delete-task="confirmDeleteTask"
                />
            </main>
        </div>

        <!-- Task add/edit modal -->
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

        <!-- Delete confirmation modal -->
        <delete-confirm-modal
            v-if="showDeleteConfirmModal"
            :todo-title="selectedTaskData?.title || ''"
            :is-recurring="isRecurringTask(selectedTaskData)"
            @confirm="confirmDelete"
            @cancel="showDeleteConfirmModal = false"
        />

        <!-- Notification component -->
        <notification-component ref="notificationRef" />
    </div>
</template>

<script>
import { ref, computed, onMounted, defineAsyncComponent } from "vue";
import TodoApi from "../api/todo";
import CategoryApi from "../api/category";

// Import components - using defineAsyncComponent for code splitting
const AppHeader = defineAsyncComponent(() => import("./AppHeader.vue"));
const DateNavigation = defineAsyncComponent(
    () => import("./DateNavigation.vue"),
);
const MonthNavigation = defineAsyncComponent(
    () => import("./MonthNavigation.vue"),
);
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
    name: "TodoApp",

    components: {
        AppHeader,
        DateNavigation,
        MonthNavigation,
        TodoList,
        TodoCalendar,
        TaskModal,
        DeleteConfirmModal,
        NotificationComponent,
    },

    setup() {
        // Application state
        const loaded = ref(false);
        const todos = ref([]);
        const categories = ref([]);
        const currentView = ref("today");
        const currentDate = ref(new Date().toISOString().split("T")[0]); // YYYY-MM-DD format

        // Modal state
        const showTaskModal = ref(false);
        const taskModalMode = ref("add");
        const selectedTaskId = ref(null);
        const selectedTaskData = ref(null);
        const showDeleteConfirmModal = ref(false);

        // Create a ref for the notification component
        const notificationRef = ref(null);

        // Computed properties
        const formattedDate = computed(() => {
            if (!currentDate.value) return "";
            const date = new Date(currentDate.value);
            return date.toLocaleDateString("ja-JP", {
                year: "numeric",
                month: "long",
                day: "numeric",
                weekday: "long",
            });
        });

        const formattedMonth = computed(() => {
            if (!currentDate.value) return "";
            const date = new Date(currentDate.value);
            return date.toLocaleDateString("ja-JP", {
                year: "numeric",
                month: "long",
            });
        });

        const filteredTodos = computed(() => {
            return todos.value.filter((todo) => todo.status !== "trashed");
        });

        // API methods
        const loadTasks = async () => {
            try {
                console.log(
                    `Loading tasks for view: ${currentView.value}, date: ${currentDate.value}`,
                );
                const response = await TodoApi.getTasks(
                    currentView.value,
                    currentDate.value,
                );
                if (Array.isArray(response.data)) {
                    todos.value = response.data;
                    console.log(`Loaded ${todos.value.length} tasks`);
                } else {
                    console.warn(
                        "Unexpected response format from API:",
                        response.data,
                    );
                    todos.value = [];
                }
            } catch (error) {
                console.error("Error loading tasks:", error);
                todos.value = [];
            }
        };

        const loadCategories = async () => {
            try {
                console.log("Loading categories");
                const response = await CategoryApi.getCategories();
                if (Array.isArray(response.data)) {
                    categories.value = response.data;
                    console.log(`Loaded ${categories.value.length} categories`);
                } else {
                    console.warn(
                        "Unexpected response format from API:",
                        response.data,
                    );
                    categories.value = [];
                }
            } catch (error) {
                console.error("Error loading categories:", error);
                categories.value = [];
            }
        };

        // View methods
        const setView = (view) => {
            currentView.value = view;
            loadTasks();
        };

        const showCalendarView = () => {
            currentView.value = "calendar";
            loadTasks();
        };

        // Date navigation methods
        const previousDay = () => {
            const date = new Date(currentDate.value);
            date.setDate(date.getDate() - 1);
            currentDate.value = date.toISOString().split("T")[0];
            loadTasks();
        };

        const nextDay = () => {
            const date = new Date(currentDate.value);
            date.setDate(date.getDate() + 1);
            currentDate.value = date.toISOString().split("T")[0];
            loadTasks();
        };

        const previousMonth = () => {
            const date = new Date(currentDate.value);
            date.setMonth(date.getMonth() - 1);
            currentDate.value = date.toISOString().split("T")[0];
            loadTasks();
        };

        const nextMonth = () => {
            const date = new Date(currentDate.value);
            date.setMonth(date.getMonth() + 1);
            currentDate.value = date.toISOString().split("T")[0];
            loadTasks();
        };

        const selectDate = (date) => {
            currentDate.value = date;
            currentView.value = "date";
            loadTasks();
        };

        // Task methods
        const toggleTaskStatus = async (todo) => {
            try {
                await TodoApi.toggleTask(todo.id);
                todo.status =
                    todo.status === "completed" ? "pending" : "completed";

                // Show notification
                if (notificationRef.value) {
                    notificationRef.value.show(
                        todo.status === "completed"
                            ? "タスクを完了しました"
                            : "タスクを未完了に戻しました",
                        "success",
                    );
                }
            } catch (error) {
                console.error("Error toggling task status:", error);

                // Show error notification
                if (notificationRef.value) {
                    notificationRef.value.show(
                        "タスクの状態を変更できませんでした",
                        "error",
                    );
                }
            }
        };

        const openAddTaskModal = () => {
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
            showTaskModal.value = true;
        };

        const openEditTaskModal = (todo) => {
            taskModalMode.value = "edit";
            selectedTaskId.value = todo.id;
            selectedTaskData.value = { ...todo };
            showTaskModal.value = true;
        };

        const closeTaskModal = () => {
            showTaskModal.value = false;
            selectedTaskId.value = null;
            selectedTaskData.value = null;
        };

        const submitTask = async (taskData) => {
            try {
                if (taskModalMode.value === "add") {
                    await TodoApi.createTask(taskData);
                } else {
                    await TodoApi.updateTask(selectedTaskId.value, taskData);
                }

                // Close modal and reload tasks
                closeTaskModal();
                loadTasks();

                // Show notification
                if (notificationRef.value) {
                    notificationRef.value.show(
                        taskModalMode.value === "add"
                            ? "タスクを追加しました"
                            : "タスクを更新しました",
                        "success",
                    );
                }
            } catch (error) {
                console.error("Error submitting task:", error);

                // Show error notification
                if (notificationRef.value) {
                    notificationRef.value.show(
                        "タスクの保存に失敗しました",
                        "error",
                    );
                }
            }
        };

        const confirmDeleteTask = (todo) => {
            selectedTaskId.value = todo.id;
            selectedTaskData.value = todo;
            showDeleteConfirmModal.value = true;
        };

        const confirmDelete = async (confirmed, deleteRecurring = false) => {
            if (!confirmed) {
                showDeleteConfirmModal.value = false;
                return;
            }

            try {
                await TodoApi.deleteTask(selectedTaskId.value, deleteRecurring);

                // Close modal and reload tasks
                showDeleteConfirmModal.value = false;
                loadTasks();

                // Show notification - Using the ref instead of this.$refs
                if (notificationRef.value) {
                    notificationRef.value.show(
                        "タスクを削除しました",
                        "success",
                    );
                }
            } catch (error) {
                console.error("Error deleting task:", error);

                // Show error notification
                if (notificationRef.value) {
                    notificationRef.value.show(
                        "タスクの削除に失敗しました",
                        "error",
                    );
                }
            }
        };

        const handleTaskDelete = async (todoId, deleteAllRecurring = false) => {
            try {
                await TodoApi.deleteTask(todoId, deleteAllRecurring);

                // Close modal and reload tasks
                closeTaskModal();
                loadTasks();

                // Show notification
                if (notificationRef.value) {
                    notificationRef.value.show(
                        "タスクを削除しました",
                        "success",
                    );
                }
            } catch (error) {
                console.error("Error deleting task:", error);

                // Show error notification
                if (notificationRef.value) {
                    notificationRef.value.show(
                        "タスクの削除に失敗しました",
                        "error",
                    );
                }
            }
        };

        // Helper functions
        const isRecurringTask = (task) => {
            return (
                task && task.recurrence_type && task.recurrence_type !== "none"
            );
        };

        // Initialize data
        onMounted(async () => {
            try {
                console.log("TodoApp component mounted");

                // Load tasks and categories
                await Promise.all([loadTasks(), loadCategories()]);

                // Set the loaded state to true to hide loading indicator
                loaded.value = true;

                // Expose methods to window for global access
                if (window) {
                    window.todoAppInstance = {
                        openEditTaskModal,
                        openAddTaskModal,
                        fetchAndEditTask,
                        currentView: currentView.value,
                        setView,
                        loadTasks,
                    };
                }
            } catch (error) {
                console.error("Error initializing app:", error);
                loaded.value = true; // Set loaded to true anyway to avoid infinite loading
            }
        });

        // Public API methods for external use
        const fetchAndEditTask = async (taskId) => {
            try {
                const response = await TodoApi.getTaskById(taskId);
                openEditTaskModal(response.data);
            } catch (error) {
                console.error("Error fetching task for edit:", error);
            }
        };

        return {
            // State
            loaded,
            todos,
            categories,
            currentView,
            currentDate,
            showTaskModal,
            taskModalMode,
            selectedTaskId,
            selectedTaskData,
            showDeleteConfirmModal,
            notificationRef,

            // Computed
            formattedDate,
            formattedMonth,
            filteredTodos,

            // Methods
            loadTasks,
            loadCategories,
            setView,
            showCalendarView,
            previousDay,
            nextDay,
            previousMonth,
            nextMonth,
            selectDate,
            toggleTaskStatus,
            openAddTaskModal,
            openEditTaskModal,
            closeTaskModal,
            submitTask,
            confirmDeleteTask,
            confirmDelete,
            handleTaskDelete,
            isRecurringTask,
            fetchAndEditTask,
        };
    },
};
</script>

<style>
/* Add component-specific styles here */
</style>
