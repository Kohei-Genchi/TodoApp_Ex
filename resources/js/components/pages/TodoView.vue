<template>
    <!-- Using Tailwind classes for layout and spacing -->
    <div class="w-full max-w-full">
        <!-- The LegacyTodoAppWrapper handles integration with your existing TodoApp component -->
        <legacy-todo-app-wrapper />
    </div>
</template>

<script>
import { useRoute } from "vue-router";
import { onMounted, watch } from "vue";
import LegacyTodoAppWrapper from "../LegacyTodoAppWrapper.vue";

export default {
    name: "TodoView",

    components: {
        LegacyTodoAppWrapper,
    },

    setup() {
        const route = useRoute();

        // Function to set the current view based on route
        const updateView = () => {
            if (window.todoAppInstance && route.meta.view) {
                window.todoAppInstance.currentView = route.meta.view;

                // If there's a specific date in the route, set it
                if (route.query.date) {
                    window.todoAppInstance.currentDate = route.query.date;
                }

                // Trigger data loading based on the view
                switch (route.meta.view) {
                    case "today":
                        window.todoAppInstance.loadTasks();
                        break;
                    case "calendar":
                        window.todoAppInstance.showCalendarView();
                        break;
                    case "trash":
                        window.todoAppInstance.showTrashView();
                        break;
                }
            }
        };

        // Update view when component is mounted
        onMounted(() => {
            // Small delay to ensure the todoAppInstance is available
            setTimeout(updateView, 100);
        });

        // Update view when route changes
        watch(
            () => route.path,
            () => {
                setTimeout(updateView, 100);
            },
        );

        return {};
    },
};
</script>
