<template>
    <div class="flex min-h-screen bg-gray-100">
        <!-- Mobile Menu Button -->
        <button
            @click="isSidebarOpen = !isSidebarOpen"
            class="mobile-menu-button"
            aria-label="Toggle Menu"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
        </button>

        <!-- Overlay for mobile when sidebar is open -->
        <div
            v-if="isSidebarOpen"
            class="sidebar-overlay md:hidden"
            @click="isSidebarOpen = false"
        ></div>

        <!-- Sidebar Navigation -->
        <aside
            :class="[
                'sidebar',
                isSidebarOpen ? 'sidebar-mobile-open' : 'sidebar-mobile-closed',
                'md:translate-x-0',
            ]"
        >
            <!-- SidebarNavigation component will be mounted here from app.js -->
            <div id="sidebar-content"></div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 main-content">
            <router-view v-slot="{ Component }">
                <keep-alive>
                    <component :is="Component" :key="$route.path" />
                </keep-alive>
            </router-view>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import { useRoute } from "vue-router";

export default {
    name: "AppLayout",

    setup() {
        const route = useRoute();
        const isSidebarOpen = ref(false);

        // Close sidebar on route change (mobile only)
        watch(
            () => route.path,
            () => {
                isSidebarOpen.value = false;
            },
        );

        // Close sidebar when ESC key is pressed
        const handleKeydown = (e) => {
            if (e.key === "Escape" && isSidebarOpen.value) {
                isSidebarOpen.value = false;
            }
        };

        // Add event listeners
        onMounted(() => {
            document.addEventListener("keydown", handleKeydown);

            // Set body class when sidebar is open to prevent scrolling
            watch(isSidebarOpen, (isOpen) => {
                if (isOpen) {
                    document.body.classList.add(
                        "overflow-hidden",
                        "md:overflow-auto",
                    );
                } else {
                    document.body.classList.remove(
                        "overflow-hidden",
                        "md:overflow-auto",
                    );
                }
            });
        });

        // Clean up event listeners
        onBeforeUnmount(() => {
            document.removeEventListener("keydown", handleKeydown);
            document.body.classList.remove(
                "overflow-hidden",
                "md:overflow-auto",
            );
        });

        return {
            isSidebarOpen,
        };
    },
};
</script>
