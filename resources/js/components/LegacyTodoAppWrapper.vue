<template>
    <!-- Add Tailwind classes for proper integration -->
    <div id="todo-app-container" class="w-full">
        <todo-app ref="todoApp" class="todo-app-integrated" />
    </div>
</template>

<script>
// Import your existing TodoApp component
import TodoApp from "./TodoApp.vue";

export default {
    name: "LegacyTodoAppWrapper",

    components: {
        TodoApp,
    },

    mounted() {
        // Attach event listeners to support legacy edit-todo events
        document.addEventListener("edit-todo", this.handleEditTodo);

        // Make the todoApp instance globally accessible
        window.todoAppInstance = this.$refs.todoApp;

        // Apply integration fixes to the TodoApp component
        this.applyIntegrationFixes();

        // Wait for the component to be fully mounted
        this.$nextTick(() => {
            if (this.$refs.todoApp) {
                // Override the submitTask method to dispatch task-updated event
                if (this.$refs.todoApp.submitTask) {
                    const originalSubmitTask = this.$refs.todoApp.submitTask;
                    this.$refs.todoApp.submitTask = async function (...args) {
                        // Call the original method
                        const result = await originalSubmitTask.apply(
                            this,
                            args,
                        );

                        // Dispatch task updated event
                        window.dispatchEvent(new CustomEvent("task-updated"));

                        // Additional log for debugging
                        console.log("Task updated, dispatching refresh event");

                        return result;
                    };
                }

                // Also intercept the updateTask method if it exists
                if (this.$refs.todoApp.updateTask) {
                    const originalUpdateTask = this.$refs.todoApp.updateTask;
                    this.$refs.todoApp.updateTask = async function (...args) {
                        const result = await originalUpdateTask.apply(
                            this,
                            args,
                        );

                        // Ensure memo list refresh
                        window.dispatchEvent(new CustomEvent("task-updated"));
                        console.log(
                            "Task updated via updateTask, dispatching refresh event",
                        );

                        return result;
                    };
                }

                // Watch for modal close events which might indicate task edits
                const taskModal = document.getElementById("task-modal");
                if (taskModal) {
                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (
                                mutation.attributeName === "class" &&
                                !taskModal.classList.contains("hidden")
                            ) {
                                // Modal was closed, might need to refresh memos
                                setTimeout(() => {
                                    window.dispatchEvent(
                                        new CustomEvent("task-updated"),
                                    );
                                    console.log(
                                        "Task modal closed, dispatching refresh event",
                                    );
                                }, 300);
                            }
                        });
                    });

                    observer.observe(taskModal, { attributes: true });
                }
            }
        });
    },

    beforeUnmount() {
        document.removeEventListener("edit-todo", this.handleEditTodo);
        window.todoAppInstance = null;
    },

    methods: {
        handleEditTodo(event) {
            const { id, data } = event.detail;

            if (this.$refs.todoApp) {
                if (id && typeof id === "number") {
                    this.$refs.todoApp.fetchAndEditTask(id);
                } else if (data) {
                    this.$refs.todoApp.openEditTaskModal(data);
                }
            }
        },

        // Apply integration fixes after the component is mounted
        applyIntegrationFixes() {
            // Get the TodoApp DOM element
            const todoAppElement = document.querySelector("#todo-app");
            if (!todoAppElement) return;

            // Fix background color
            const bgElements = todoAppElement.querySelectorAll(".bg-gray-100");
            bgElements.forEach((el) => {
                el.classList.remove("bg-gray-100");
                el.classList.add("bg-transparent");
            });

            // Remove min-height: 100vh
            const minHElements =
                todoAppElement.querySelectorAll(".min-h-screen");
            minHElements.forEach((el) => {
                el.classList.remove("min-h-screen");
            });

            // Remove left margin
            const marginElements =
                todoAppElement.querySelectorAll('[class*="ml-"]');
            marginElements.forEach((el) => {
                // Remove any ml-* classes
                const classes = Array.from(el.classList);
                const mlClasses = classes.filter((c) => c.startsWith("ml-"));
                mlClasses.forEach((c) => el.classList.remove(c));
            });

            // Hide duplicate navigation elements
            const navElements = todoAppElement.querySelectorAll("nav");
            navElements.forEach((el) => {
                el.classList.add("hidden");
            });

            // Hide sidebar elements
            const sidebarElements =
                todoAppElement.querySelectorAll(".sidebar-wrapper");
            sidebarElements.forEach((el) => {
                el.classList.add("hidden");
            });
        },
    },
};
</script>

<style>
/* A few styles that can't be easily achieved with Tailwind classes */
.todo-app-integrated [x-data] {
    @apply z-50; /* Ensure modals appear on top */
}
</style>
