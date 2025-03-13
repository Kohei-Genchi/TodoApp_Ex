<template>
    <div class="mb-4">
        <div class="text-xs text-gray-400 uppercase tracking-wider mb-2">
            クイック入力
        </div>
        <form @submit.prevent="addTask">
            <div class="flex items-center bg-gray-700 rounded overflow-hidden">
                <input
                    type="text"
                    v-model="newTaskTitle"
                    required
                    placeholder="新しいメモを入力"
                    class="w-full bg-gray-700 px-3 py-2 text-sm focus:outline-none text-white"
                />
                <button
                    type="submit"
                    class="px-2 py-2 text-gray-400 hover:text-white"
                    :disabled="isSubmitting"
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
            </div>
        </form>
    </div>
</template>

<script>
import { ref } from "vue";
import axios from "axios";

export default {
    name: "QuickInputSection",

    emits: ["task-added"],

    setup(props, { emit }) {
        const newTaskTitle = ref("");
        const isSubmitting = ref(false);

        // Add a new task/memo
        const addTask = async () => {
            if (!newTaskTitle.value.trim() || isSubmitting.value) return;

            try {
                isSubmitting.value = true;

                // Get CSRF token
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");

                // Create new task via API
                await axios.post(
                    "/api/todos",
                    {
                        title: newTaskTitle.value.trim(),
                    },
                    {
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                    },
                );

                // Clear input and emit event
                newTaskTitle.value = "";
                emit("task-added");

                isSubmitting.value = false;
            } catch (error) {
                console.error("Error adding task:", error);
                isSubmitting.value = false;
            }
        };

        return {
            newTaskTitle,
            addTask,
            isSubmitting,
        };
    },
};
</script>
