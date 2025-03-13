<template>
    <nav
        class="bg-gray-800 text-white h-full w-64 fixed left-0 top-0 overflow-y-auto"
    >
        <div class="p-4">
            <!-- User Dropdown -->
            <user-dropdown :user="user" />

            <template v-if="isAuthenticated">
                <!-- Only show these sections for authenticated users -->
                <template v-if="!isProfileOrSubscriptionRoute">
                    <!-- Quick Input Section -->
                    <quick-input-section @task-added="handleTaskAdded" />

                    <!-- Memo List -->
                    <memo-list
                        :memos="memos"
                        @edit-memo="handleEditMemo"
                        @trash-memo="handleTrashMemo"
                        @refresh="loadMemos"
                    />
                </template>
            </template>

            <template v-else>
                <!-- Guest Message -->
                <div class="mt-6 p-3 bg-gray-700 rounded text-xs text-gray-300">
                    <p>
                        タスク管理機能を使用するには、ログインまたは新規登録してください。
                    </p>
                </div>
            </template>
        </div>
    </nav>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
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
        const user = ref(window.Laravel?.user || null);
        const memos = ref([]);

        // Check if authenticated
        const isAuthenticated = computed(() => !!user.value);

        // Check if current route is profile or subscription
        const isProfileOrSubscriptionRoute = computed(() => {
            return (
                route.path.includes("/profile") ||
                route.path.includes("/subscription")
            );
        });

        const loadMemos = async () => {
            try {
                const response = await fetch("/api/memos-partial", {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "text/html",
                    },
                });

                if (response.ok) {
                    const html = await response.text();
                    const memoContainers = document.querySelectorAll(
                        ".memo-list-container",
                    );
                    memoContainers.forEach((container) => {
                        container.innerHTML = html;
                    });
                }
            } catch (error) {
                console.error("Failed to load memos:", error);
            }
        };

        // Event handlers
        const handleTaskAdded = () => {
            loadMemos(); // Reload memos after adding a task
        };

        const handleEditMemo = (memo) => {
            if (window.editTodo) {
                window.editTodo(memo.id, {
                    id: memo.id,
                    title: memo.title,
                    due_date: memo.due_date,
                    due_time: memo.due_time,
                    category_id: memo.category_id,
                    recurrence_type: memo.recurrence_type,
                    recurrence_end_date: memo.recurrence_end_date,
                });
            }
        };

        const handleTrashMemo = async (id) => {
            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");

                const response = await fetch(`/todos/${id}/trash`, {
                    method: "PATCH",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                if (response.ok) {
                    loadMemos(); // Reload memos after trashing
                }
            } catch (error) {
                console.error("Error trashing memo:", error);
            }
        };

        // Load memos on component mount
        onMounted(() => {
            loadMemos();
        });

        return {
            user,
            memos,
            isAuthenticated,
            isProfileOrSubscriptionRoute,
            handleTaskAdded,
            handleEditMemo,
            handleTrashMemo,
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
