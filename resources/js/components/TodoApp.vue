<template>
    <div class="bg-gray-100 min-h-screen main-content">
        <!-- Content from the existing TodoApp.vue component remains the same,
         just fixing the imports and initialization -->
        <div v-if="!loaded" class="text-center py-10">
            <p class="text-gray-500">Loading...</p>
        </div>
        <div v-else>
            <!-- Rest of your TodoApp component here -->
            <app-header
                :current-view="currentView"
                @set-view="setView"
                @show-calendar="showCalendarView"
                @show-trash="showTrashView"
                @add-task="openAddTaskModal"
            />

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
                <!-- Date navigation -->
                <date-navigation
                    v-if="currentView !== 'calendar' && currentView !== 'trash'"
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

                <!-- Empty trash button -->
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
                    v-if="currentView !== 'calendar' && currentView !== 'trash'"
                    :todos="filteredTodos"
                    :categories="categories"
                    @toggle-task="toggleTaskStatus"
                    @edit-task="openEditTaskModal"
                    @trash-task="trashTask"
                />

                <!-- Task list (trash view) -->
                <todo-list
                    v-if="currentView === 'trash'"
                    :todos="trashedTodos"
                    :categories="categories"
                    :is-trash-view="true"
                    @restore-task="restoreTask"
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
        <notification-component ref="notification" />
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
        // Add a loaded state to show loading indicator
        const loaded = ref(false);

        // Rest of your setup code...
        // ...

        // Initialize data and set loaded state
        onMounted(async () => {
            try {
                console.log("TodoApp component mounted");
                await Promise.all([loadTasks(), loadCategories()]);

                // Set loaded state to true when data is ready
                loaded.value = true;

                // The rest of your onMounted code...
            } catch (error) {
                console.error("Error initializing app:", error);
                // Still set loaded to true to show any error states instead of infinite loading
                loaded.value = true;
            }
        });

        return {
            // Add loaded state to the return value
            loaded,
            // Rest of your return values...
        };
    },
};
</script>

<style>
/* Add component-specific styles here */
</style>
