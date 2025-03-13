<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Dashboard</h1>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="text-gray-700">Welcome back, {{ userName }}!</p>

                    <div
                        class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <!-- Task Summary Card -->
                        <div
                            class="bg-blue-50 p-5 rounded-lg border border-blue-100"
                        >
                            <h3 class="text-lg font-medium text-blue-800 mb-2">
                                Task Summary
                            </h3>
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-blue-700">
                                    <p>
                                        Today's Tasks:
                                        <span class="font-semibold">{{
                                            todayTasks
                                        }}</span>
                                    </p>
                                    <p>
                                        Completed:
                                        <span class="font-semibold">{{
                                            completedTasks
                                        }}</span>
                                    </p>
                                    <p>
                                        Pending:
                                        <span class="font-semibold">{{
                                            pendingTasks
                                        }}</span>
                                    </p>
                                </div>
                                <div class="text-blue-400">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-10 w-10"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4">
                                <router-link
                                    to="/todos/today"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                                >
                                    View today's tasks
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 ml-1"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </router-link>
                            </div>
                        </div>

                        <!-- Calendar Card -->
                        <div
                            class="bg-green-50 p-5 rounded-lg border border-green-100"
                        >
                            <h3 class="text-lg font-medium text-green-800 mb-2">
                                Calendar
                            </h3>
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-green-700">
                                    <p>
                                        This Month:
                                        <span class="font-semibold">{{
                                            monthlyTasks
                                        }}</span>
                                        tasks
                                    </p>
                                    <p>
                                        Upcoming:
                                        <span class="font-semibold">{{
                                            upcomingTasks
                                        }}</span>
                                        tasks
                                    </p>
                                </div>
                                <div class="text-green-400">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-10 w-10"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4">
                                <router-link
                                    to="/todos/calendar"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200"
                                >
                                    View calendar
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 ml-1"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </router-link>
                            </div>
                        </div>

                        <!-- Categories Card -->
                        <div
                            class="bg-purple-50 p-5 rounded-lg border border-purple-100"
                        >
                            <h3
                                class="text-lg font-medium text-purple-800 mb-2"
                            >
                                Categories
                            </h3>
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-purple-700">
                                    <p>
                                        Total:
                                        <span class="font-semibold">{{
                                            categoriesCount
                                        }}</span>
                                        categories
                                    </p>
                                    <p>
                                        Most used:
                                        <span class="font-semibold">{{
                                            mostUsedCategory
                                        }}</span>
                                    </p>
                                </div>
                                <div class="text-purple-400">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-10 w-10"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a
                                    href="/categories"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200"
                                >
                                    Manage categories
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 ml-1"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button
                            @click="createNewTask"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                            Create New Task
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

export default {
    name: "Dashboard",

    setup() {
        const router = useRouter();

        // User data
        const userName = ref(window.Laravel?.user?.name || "User");

        // Task statistics
        const todayTasks = ref(0);
        const completedTasks = ref(0);
        const pendingTasks = ref(0);
        const monthlyTasks = ref(0);
        const upcomingTasks = ref(0);

        // Category data
        const categoriesCount = ref(0);
        const mostUsedCategory = ref("None");

        // Load dashboard data
        const loadDashboardData = async () => {
            try {
                // Get today's tasks
                const todayResponse = await axios.get("/api/todos", {
                    params: { view: "today" },
                });

                if (Array.isArray(todayResponse.data)) {
                    const todayData = todayResponse.data;
                    todayTasks.value = todayData.length;
                    completedTasks.value = todayData.filter(
                        (task) => task.status === "completed",
                    ).length;
                    pendingTasks.value = todayData.filter(
                        (task) => task.status === "pending",
                    ).length;
                }

                // Get monthly tasks
                const monthlyResponse = await axios.get("/api/todos", {
                    params: { view: "calendar" },
                });

                if (Array.isArray(monthlyResponse.data)) {
                    monthlyTasks.value = monthlyResponse.data.length;

                    // Calculate upcoming tasks (future tasks that are pending)
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    upcomingTasks.value = monthlyResponse.data.filter(
                        (task) => {
                            if (!task.due_date || task.status !== "pending")
                                return false;
                            const taskDate = new Date(task.due_date);
                            return taskDate > today;
                        },
                    ).length;
                }

                // Get categories
                const categoriesResponse = await axios.get(
                    "/api/web-categories",
                );

                if (Array.isArray(categoriesResponse.data)) {
                    categoriesCount.value = categoriesResponse.data.length;

                    if (categoriesResponse.data.length > 0) {
                        // Find the category with the most tasks
                        const categoryWithMostTasks =
                            categoriesResponse.data.reduce(
                                (prev, current) =>
                                    prev.todos_count > current.todos_count
                                        ? prev
                                        : current,
                                { name: "None", todos_count: 0 },
                            );

                        mostUsedCategory.value =
                            categoryWithMostTasks.name || "None";
                    }
                }
            } catch (error) {
                console.error("Error loading dashboard data:", error);
            }
        };

        // Create new task function
        const createNewTask = () => {
            // Check if the Todo app instance is available
            if (
                window.todoAppInstance &&
                window.todoAppInstance.openAddTaskModal
            ) {
                window.todoAppInstance.openAddTaskModal();
            } else {
                // Fallback to navigation
                router.push("/todos/today");
            }
        };

        // Load data on component mount
        onMounted(() => {
            loadDashboardData();
        });

        return {
            userName,
            todayTasks,
            completedTasks,
            pendingTasks,
            monthlyTasks,
            upcomingTasks,
            categoriesCount,
            mostUsedCategory,
            createNewTask,
        };
    },
};
</script>
