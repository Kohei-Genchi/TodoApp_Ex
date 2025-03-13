<template>
    <nav
        class="bg-gray-800 text-white h-full w-64 fixed left-0 top-0 overflow-y-auto"
    >
        <div class="p-4">
            <!-- User Profile Section -->
            <div v-if="user" class="relative mb-6">
                <div
                    class="flex justify-between items-center cursor-pointer p-2 rounded-md hover:bg-gray-700 transition-colors duration-200"
                    @click="toggleUserDropdown"
                >
                    <div class="flex items-center">
                        <!-- User icon before username -->
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-400 mr-2"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>
                        <span class="font-bold">{{ user.name }}</span>
                    </div>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </div>

                <!-- Dropdown Menu -->
                <div
                    v-if="showUserDropdown"
                    class="absolute right-0 mt-2 w-48 bg-gray-700 rounded-md shadow-lg z-10 border border-gray-600"
                >
                    <div class="py-1">
                        <router-link
                            to="/"
                            class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                            Home
                        </router-link>
                        <router-link
                            to="/profile"
                            class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            Profile
                        </router-link>
                        <router-link
                            to="/subscription"
                            class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 9a2 2 0 10-4 0v5a2 2 0 104 0V9z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 9h6m-6 6h6"
                                />
                            </svg>
                            Subscription
                        </router-link>
                        <button
                            @click="logout"
                            class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-200 hover:bg-gray-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                />
                            </svg>
                            Logout
                        </button>
                    </div>
                </div>
            </div>

            <!-- Guest View -->
            <div v-else class="flex justify-between items-center mb-6">
                <span class="font-bold">ゲスト</span>
                <div class="flex space-x-3">
                    <a
                        href="/login"
                        class="text-sm text-gray-400 hover:text-white"
                        title="ログイン"
                    >
                        🔑
                    </a>
                    <a
                        href="/register"
                        class="text-sm text-gray-400 hover:text-white"
                        title="新規登録"
                    >
                        📝
                    </a>
                </div>
            </div>

            <!-- Main Content for Authenticated Users -->
            <div v-if="user && !isProfileOrSubscriptionPage">
                <!-- Quick Input Section -->
                <div class="mb-4">
                    <div
                        class="text-xs text-gray-400 uppercase tracking-wider mb-2"
                    >
                        クイック入力
                    </div>
                    <form
                        @submit.prevent="addQuickMemo"
                        class="flex items-center bg-gray-700 rounded overflow-hidden"
                    >
                        <input
                            type="text"
                            v-model="newMemoTitle"
                            required
                            placeholder="新しいメモを入力"
                            class="w-full bg-gray-700 px-3 py-2 text-sm focus:outline-none text-white"
                        />
                        <button
                            type="submit"
                            class="px-2 py-2 text-gray-400 hover:text-white"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Memo List -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div
                            class="text-xs text-gray-400 uppercase tracking-wider"
                        >
                            メモ一覧
                        </div>
                        <div
                            class="text-xs bg-gray-600 px-1.5 py-0.5 rounded-full"
                        >
                            {{ memos.length }}
                        </div>
                    </div>

                    <div
                        class="sidebar-memo-list space-y-1 max-h-96 overflow-y-auto pr-1 custom-scrollbar"
                    >
                        <div
                            v-if="memos.length === 0"
                            class="text-xs text-gray-500 text-center py-2"
                        >
                            メモはありません
                        </div>
                        <div
                            v-for="memo in memos"
                            :key="memo.id"
                            class="group bg-gray-700 hover:bg-gray-600 rounded py-1.5 px-2 cursor-pointer transition-colors"
                            @click="editMemo(memo)"
                            :style="{
                                'border-left':
                                    '3px solid ' +
                                    (memo.category
                                        ? memo.category.color
                                        : '#6B7280'),
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <div class="text-sm truncate pr-1">
                                    {{ memo.title }}
                                </div>
                                <div
                                    class="opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                                >
                                    <button
                                        type="button"
                                        @click.stop="trashMemo(memo.id)"
                                        class="text-gray-400 hover:text-gray-200"
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div
                                v-if="memo.category"
                                class="text-xs text-gray-400 mt-0.5 flex items-center"
                            >
                                <span
                                    class="w-2 h-2 rounded-full mr-1"
                                    :style="{
                                        'background-color': memo.category.color,
                                    }"
                                ></span>
                                {{ memo.category.name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message for not authenticated users -->
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
import { ref, onMounted, computed } from "vue";
import axios from "axios";

export default {
    name: "SidebarNavigation",

    props: {
        currentRoute: {
            type: String,
            default: "",
        },
    },

    setup(props, { emit }) {
        // User state
        const user = ref(window.Laravel?.user || null);
        const showUserDropdown = ref(false);

        // Memo state
        const memos = ref([]);
        const newMemoTitle = ref("");

        // Check if current page is profile or subscription
        const isProfileOrSubscriptionPage = computed(() => {
            const route = props.currentRoute;
            return route.includes("profile") || route.includes("subscription");
        });

        // Toggle user dropdown
        const toggleUserDropdown = () => {
            showUserDropdown.value = !showUserDropdown.value;
        };

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

        // Add new memo
        const addQuickMemo = async () => {
            if (!newMemoTitle.value.trim()) return;

            try {
                const response = await axios.post("/api/todos", {
                    title: newMemoTitle.value,
                    due_date: null,
                    due_time: null,
                    category_id: null,
                    recurrence_type: "none",
                });

                // Add the new memo to the list
                if (response.data && response.data.todo) {
                    memos.value.unshift(response.data.todo);
                }

                // Clear input
                newMemoTitle.value = "";

                // Reload memos to ensure synced data
                loadMemos();
            } catch (error) {
                console.error("Error adding memo:", error);
            }
        };

        // Edit memo
        const editMemo = (memo) => {
            emit("edit-memo", memo);
        };

        // Trash memo
        const trashMemo = async (id) => {
            try {
                await axios.patch(`/api/todos/${id}/trash`, {});

                // Remove from local state
                memos.value = memos.value.filter((memo) => memo.id !== id);
            } catch (error) {
                console.error("Error trashing memo:", error);
            }
        };

        // Logout
        const logout = async () => {
            if (confirm("ログアウトしてもよろしいですか？")) {
                try {
                    // Get CSRF token
                    const csrfToken = document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content");

                    await axios.post(
                        "/logout",
                        {},
                        {
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                            },
                        },
                    );

                    // Redirect to home
                    window.location.href = "/";
                } catch (error) {
                    console.error("Error during logout:", error);
                }
            }
        };

        // Close dropdown when clicking outside
        const handleClickOutside = (event) => {
            if (showUserDropdown.value) {
                const dropdown = document.querySelector(".relative");
                if (dropdown && !dropdown.contains(event.target)) {
                    showUserDropdown.value = false;
                }
            }
        };

        // Setup event listeners and load data
        onMounted(() => {
            document.addEventListener("click", handleClickOutside);
            loadMemos();
        });

        return {
            user,
            showUserDropdown,
            memos,
            newMemoTitle,
            isProfileOrSubscriptionPage,
            toggleUserDropdown,
            addQuickMemo,
            editMemo,
            trashMemo,
            logout,
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
