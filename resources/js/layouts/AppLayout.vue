<template>
    <div class="flex min-h-screen bg-gray-100">
        <!-- Sidebar Navigation -->
        <SidebarNavigation />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col ml-64">
            <!-- 64 = 16rem (w-64) -->
            <!-- Content -->
            <main class="flex-1 p-4 overflow-x-hidden">
                <router-view v-slot="{ Component }">
                    <keep-alive>
                        <component :is="Component" :key="$route.path" />
                    </keep-alive>
                </router-view>
            </main>
        </div>
    </div>
</template>

<script>
import { onMounted, onBeforeUnmount } from "vue";
import SidebarNavigation from "../components/navigation/SidebarNavigation.vue";

export default {
    name: "AppLayout",

    components: {
        SidebarNavigation,
    },

    setup() {
        // Listen for edit-todo events from outside Vue (legacy code)
        const handleEditTodo = (event) => {
            console.log("Edit todo event received:", event.detail);
            // This will be handled by the TodoApp component
        };

        onMounted(() => {
            document.addEventListener("edit-todo", handleEditTodo);

            // Add Tailwind classes to ensure proper integration
            // We need to override some TodoApp styles that might conflict with our layout
            document.body.classList.add("todo-app-integration");
        });

        onBeforeUnmount(() => {
            document.removeEventListener("edit-todo", handleEditTodo);
        });
    },
};
</script>

<style>
/* These minimal overrides can't be achieved with just Tailwind classes */
.todo-app-integration .main-content {
    @apply ml-0;
}

/* Ensure modals appear on top and properly positioned */
.todo-app-integration [x-data][x-show]:not(.hidden) {
    @apply z-50;
}
</style>
