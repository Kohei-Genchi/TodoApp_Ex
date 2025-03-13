// In resources/js/app.js
import "./bootstrap";

// Import Alpine.js
import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

// Import Vue and Vue Router
import { createApp } from "vue";
import { createRouter, createWebHistory } from "vue-router";
import router from "./router"; // Import router configuration

// Import components
import TodoApp from "./components/TodoApp.vue";
import SidebarNavigation from "./components/navigation/SidebarNavigation.vue";

// Log initialization info for debugging
console.log("Starting application initialization");

// Wait for DOM to be ready
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM fully loaded");

    // Mount the Vue app if the target element exists
    const todoAppElement = document.getElementById("todo-app");
    if (todoAppElement) {
        console.log("Found #todo-app element, mounting Vue application");
        const app = createApp(TodoApp);

        // Use Vue Router with the app
        app.use(router);

        const vm = app.mount("#todo-app");
        console.log("Vue app mounted successfully");

        // Global function to edit a todo from outside Vue
        window.editTodo = function (taskIdOrData, todoData = null) {
            console.log("Global editTodo called with:", taskIdOrData, todoData);

            // If todoData is passed as an object (from data attributes)
            if (todoData && typeof todoData === "object") {
                console.log("Using todoData from data attributes:", todoData);

                // Try to call the component method directly if available
                if (vm && typeof vm.openEditTaskModal === "function") {
                    if (!todoData.id && taskIdOrData) {
                        todoData.id = Number(taskIdOrData);
                    }
                    vm.openEditTaskModal(todoData);
                    return;
                }

                // Fallback to dispatching an event
                const event = new CustomEvent("edit-todo", {
                    detail: { id: Number(taskIdOrData), data: todoData },
                });
                todoAppElement.dispatchEvent(event);
                return;
            }

            // If only ID is provided
            if (taskIdOrData !== undefined && taskIdOrData !== null) {
                try {
                    // Handle numeric or string IDs
                    if (
                        typeof taskIdOrData === "number" ||
                        (typeof taskIdOrData === "string" &&
                            !isNaN(parseInt(taskIdOrData)))
                    ) {
                        const id = Number(taskIdOrData);

                        // Try direct method call first
                        if (vm && typeof vm.fetchAndEditTask === "function") {
                            console.log(
                                "Calling fetchAndEditTask with ID:",
                                id,
                            );
                            vm.fetchAndEditTask(id);
                            return;
                        }

                        // Fallback to event
                        console.log("Dispatching edit-todo event with ID:", id);
                        const event = new CustomEvent("edit-todo", {
                            detail: { id, data: null },
                        });
                        todoAppElement.dispatchEvent(event);
                        return;
                    }

                    // Handle object data
                    if (
                        typeof taskIdOrData === "object" &&
                        taskIdOrData !== null
                    ) {
                        if (vm && typeof vm.openEditTaskModal === "function") {
                            vm.openEditTaskModal(taskIdOrData);
                            return;
                        }

                        const detail = taskIdOrData.id
                            ? {
                                  id: Number(taskIdOrData.id),
                                  data: taskIdOrData,
                              }
                            : { id: null, data: taskIdOrData };

                        const event = new CustomEvent("edit-todo", { detail });
                        todoAppElement.dispatchEvent(event);
                        return;
                    }

                    console.error("Invalid task data format:", taskIdOrData);
                } catch (error) {
                    console.error("Error in editTodo function:", error);
                    alert("タスクの編集中にエラーが発生しました");
                }
            } else {
                console.error("No task ID or data provided to editTodo");
            }
        };

        // Global function to trash a memo
        window.trashMemo = function (id) {
            if (!id) {
                console.error("No memo ID provided to trashMemo");
                return;
            }

            console.log("Trashing memo with ID:", id);

            // Create a form and submit it via POST
            const form = document.createElement("form");
            form.method = "POST";
            form.action = `/todos/${id}/trash`;

            // Add CSRF token
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");
            if (!csrfToken) {
                console.error("CSRF token not found!");
                return;
            }

            const csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "_token";
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            // Add method override for PATCH
            const methodInput = document.createElement("input");
            methodInput.type = "hidden";
            methodInput.name = "_method";
            methodInput.value = "PATCH";
            form.appendChild(methodInput);

            // Append form to document and submit
            document.body.appendChild(form);
            form.submit();
        };
    } else {
        console.warn("Could not find #todo-app element. Vue app not mounted.");
    }

    // Mount the sidebar navigation
    const navElement = document.getElementById("sidebar-nav");
    if (navElement) {
        console.log("Found #sidebar-nav element, mounting Vue sidebar");
        const sidebarApp = createApp(SidebarNavigation, {
            currentRoute: window.location.pathname,
        });

        // Use Vue Router with the sidebar app
        sidebarApp.use(router);

        sidebarApp.mount("#sidebar-nav");
        console.log("Vue sidebar mounted successfully");
    } else {
        console.warn(
            "Could not find #sidebar-nav element. Vue sidebar not mounted.",
        );
    }

    // Add a global object to check for script execution
    window.__vueAppLoaded = true;
    console.log("==== Application initialization complete ====");
});
