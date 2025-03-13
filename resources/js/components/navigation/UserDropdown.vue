<template>
    <div>
        <!-- User Section (Authenticated) -->
        <div v-if="user" class="mb-6">
            <!-- User Display with Dropdown Toggle -->
            <div
                class="flex justify-between items-center cursor-pointer p-2 rounded-md hover:bg-gray-700 transition-colors duration-200"
                @click="isDropdownOpen = !isDropdownOpen"
            >
                <div class="flex items-center">
                    <!-- User icon -->
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
                v-show="isDropdownOpen"
                class="mt-2 bg-gray-700 rounded-md shadow-lg z-10 border border-gray-600"
            >
                <div class="py-1">
                    <!-- Home -->
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

                    <!-- Profile -->
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

                    <!-- Subscription -->
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

                    <!-- Logout -->
                    <button
                        @click="confirmLogout"
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

        <!-- Guest Section (Not Authenticated) -->
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
    </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from "vue";

export default {
    name: "UserDropdown",

    props: {
        user: {
            type: Object,
            default: null,
        },
    },

    setup() {
        const isDropdownOpen = ref(false);

        // Method to confirm logout
        const confirmLogout = () => {
            if (confirm("ログアウトしてもよろしいですか？")) {
                document.getElementById("logout-form").submit();
            }
        };

        // Close dropdown when clicking outside
        const handleClickOutside = (event) => {
            const dropdown = document.querySelector(".user-dropdown");
            if (dropdown && !dropdown.contains(event.target)) {
                isDropdownOpen.value = false;
            }
        };

        // Add and remove event listeners
        onMounted(() => {
            document.addEventListener("click", handleClickOutside);
        });

        onBeforeUnmount(() => {
            document.removeEventListener("click", handleClickOutside);
        });

        return {
            isDropdownOpen,
            confirmLogout,
        };
    },
};
</script>
