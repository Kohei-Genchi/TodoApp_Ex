<template>
    <nav
        class="bg-gray-800 text-white h-full w-64 fixed left-0 top-0 overflow-y-auto"
    >
        <div class="p-4">
            <!-- User Profile Section -->
            <user-dropdown :user="user" @refresh-memos="loadMemos" />

            <!-- Main Content for Authenticated Users -->
            <div v-if="user && !isProfileOrSubscriptionPage">
                <!-- Quick Input Section -->
                <quick-input-section @task-added="loadMemos" />

                <!-- Memo List -->
                <memo-list
                    :memos="memos"
                    @edit-memo="editMemo"
                    @trash-memo="trashMemo"
                />
            </div>

            <!-- Message for non-authenticated users -->
            <div
                v-else-if="!user"
                class="mt-6 p-3 bg-gray-700 rounded text-xs text-gray-300"
            >
                <p>
                    タスク管理機能を使用するには、ログインまたは新規登録してください。
                </p>
            </div>
        </div>
    </nav>
</template>

<script>
import { ref, onMounted, computed, onBeforeUnmount } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import UserDropdown from "./UserDropdown.vue";
import QuickInputSection from "./QuickInputSection.vue";
import MemoList from "./MemoList.vue";

export default {
    name: "SidebarNavigation",
    components: {
        UserDropdown,
        QuickInputSection,
        MemoList,
    },

    setup() {
        const route = useRoute();
        // User state
        const user = ref(window.Laravel?.user || null);
        // Memo state
        const memos = ref([]);

        // Check if current page is profile or subscription
        const isProfileOrSubscriptionPage = computed(() => {
            const currentPath = route.path;
            return (
                currentPath.includes("profile") ||
                currentPath.includes("subscription")
            );
        });

        // Load memos
        const loadMemos = async () => {
            if (!user.value) return;

            try {
                const response = await axios.get("/api/todos", {
                    params: { view: "inbox" },
                });

                if (Array.isArray(response.data)) {
                    memos.value = response.data;
                }
            } catch (error) {
                console.error("Error loading memos:", error);
            }
        };

        // Edit memo
        const editMemo = (memo) => {
            // Call global editTodo function if available
            if (window.editTodo) {
                window.editTodo(memo.id, memo);
            } else {
                console.warn("editTodo function not available");
            }
        };

        // Trash memo
        const trashMemo = async (id) => {
            try {
                // Call the delete function directly
                if (
                    window.todoAppInstance &&
                    window.todoAppInstance.deleteTodo
                ) {
                    window.todoAppInstance.deleteTodo(id);
                } else {
                    // Fallback to direct API call
                    await axios.delete(`/api/todos/${id}`);
                }

                // Remove from local state
                memos.value = memos.value.filter((memo) => memo.id !== id);
            } catch (error) {
                console.error("Error deleting memo:", error);
            }
        };

        // Setup event listeners and load data
        onMounted(() => {
            loadMemos();

            // Listen for task updated events to refresh memo list
            window.addEventListener("task-updated", loadMemos);
        });

        // Clean up event listeners
        onBeforeUnmount(() => {
            window.removeEventListener("task-updated", loadMemos);
        });

        return {
            user,
            memos,
            isProfileOrSubscriptionPage,
            loadMemos,
            editMemo,
            trashMemo,
        };
    },
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(55, 65, 81, 0.5);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.8);
}
</style>
