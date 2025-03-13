<template>
    <div class="mb-4">
        <div class="text-xs text-gray-400 uppercase tracking-wider mb-2">
            クイック入力
        </div>

        <form
            @submit.prevent="submitQuickTask"
            class="flex items-center bg-gray-700 rounded overflow-hidden"
        >
            <input
                type="text"
                v-model="taskTitle"
                required
                placeholder="新しいメモを入力"
                class="w-full bg-gray-700 px-3 py-2 text-sm focus:outline-none text-white"
            />

            <button
                type="submit"
                class="px-2 py-2 text-gray-400 hover:text-white transition-colors"
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
        </form>

        <!-- Add Task Button -->
        <div class="mt-2">
            <button
                v-todo-app:openAddTaskModal
                class="w-full py-1 text-xs text-center bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors"
            >
                + 新しいタスク追加
            </button>
        </div>
    </div>
</template>

<script>
import { ref } from "vue";

export default {
    name: "QuickInputSection",

    emits: ["task-added"],

    setup(props, { emit }) {
        const taskTitle = ref("");
        const isSubmitting = ref(false);

        const submitQuickTask = async () => {
            if (!taskTitle.value.trim() || isSubmitting.value) return;

            isSubmitting.value = true;

            try {
                // Get CSRF token
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");

                // Create form data
                const formData = new FormData();
                formData.append("title", taskTitle.value);
                formData.append("_token", csrfToken);

                // Send request
                const response = await fetch("/todos", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                if (response.ok) {
                    // Clear input and emit event
                    taskTitle.value = "";
                    emit("task-added");

                    // Dispatch task-updated event for MemoList refresh
                    window.dispatchEvent(new CustomEvent("task-updated"));
                } else {
                    console.error(
                        "Failed to create task:",
                        await response.text(),
                    );
                }
            } catch (error) {
                console.error("Error creating task:", error);
            } finally {
                isSubmitting.value = false;
            }
        };

        return {
            taskTitle,
            isSubmitting,
            submitQuickTask,
        };
    },
};
</script>
