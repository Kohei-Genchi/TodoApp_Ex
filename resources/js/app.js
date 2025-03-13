import "./bootstrap";

import { createApp } from "vue";
import router from "./router";
import AppLayout from "./layouts/AppLayout.vue";

// Import custom directive
import { vTodoApp } from "./directives/todoApp";

// Import existing components
import TodoApp from "./components/TodoApp.vue";
import TodoList from "./components/TodoList.vue";
import TodoCalendar from "./components/TodoCalendar.vue";
import TaskModal from "./components/TaskModal.vue";
import DateNavigation from "./components/DateNavigation.vue";
import MonthNavigation from "./components/MonthNavigation.vue";
import DeleteConfirmModal from "./components/DeleteConfirmModal.vue";
import EmptyState from "./components/EmptyState.vue";
import TaskItem from "./components/TaskItem.vue";
import TaskStats from "./components/TaskStats.vue";
import AppHeader from "./components/AppHeader.vue";
import LegacyTodoAppWrapper from "./components/LegacyTodoAppWrapper.vue";

// Import new navigation components
import SidebarNavigation from "./components/navigation/SidebarNavigation.vue";
import UserDropdown from "./components/navigation/UserDropdown.vue";
import QuickInputSection from "./components/navigation/QuickInputSection.vue";
import MemoList from "./components/navigation/MemoList.vue";

// Create Vue application
const app = createApp(AppLayout);

// Use Vue Router
app.use(router);

// Register custom directive
app.directive("todo-app", vTodoApp);

// Register components globally to ensure they're available throughout the app
// Todo components
app.component("TodoApp", TodoApp);
app.component("TodoList", TodoList);
app.component("TodoCalendar", TodoCalendar);
app.component("TaskModal", TaskModal);
app.component("DateNavigation", DateNavigation);
app.component("MonthNavigation", MonthNavigation);
app.component("DeleteConfirmModal", DeleteConfirmModal);
app.component("EmptyState", EmptyState);
app.component("TaskItem", TaskItem);
app.component("TaskStats", TaskStats);
app.component("AppHeader", AppHeader);
app.component("LegacyTodoAppWrapper", LegacyTodoAppWrapper);

// Navigation components
app.component("SidebarNavigation", SidebarNavigation);
app.component("UserDropdown", UserDropdown);
app.component("QuickInputSection", QuickInputSection);
app.component("MemoList", MemoList);

// Mount the application
app.mount("#app");

// Make the router available globally for components outside Vue instance
window.vueRouter = router;

// Save the original editTodo function if it exists
const originalEditTodo = window.editTodo;

// Provide enhanced editTodo function globally for backward compatibility
window.editTodo = function (taskIdOrData, todoData = null) {
    console.log("Global editTodo called:", taskIdOrData, todoData);

    // Try to use the original function first if it exists
    if (typeof originalEditTodo === "function") {
        try {
            return originalEditTodo(taskIdOrData, todoData);
        } catch (e) {
            console.error("Error in original editTodo:", e);
        }
    }

    // Fallback to event-based approach
    const event = new CustomEvent("edit-todo", {
        detail: { id: taskIdOrData, data: todoData },
    });
    document.dispatchEvent(event);
};

// Listen for popstate events to handle browser back/forward buttons with Vue Router
window.addEventListener("popstate", () => {
    router.go(0); // This forces router to re-evaluate the current URL
});
