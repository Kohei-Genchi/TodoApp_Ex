<template>
    <div>
        <!-- Memo List Header -->
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-gray-400 uppercase tracking-wider">
                メモ一覧
            </div>
            <div
                v-if="memos.length > 0"
                class="text-xs bg-gray-600 px-1.5 py-0.5 rounded-full"
            >
                {{ memos.length }}
            </div>
        </div>

        <!-- Memo List Container -->
        <div
            class="space-y-1 max-h-96 overflow-y-auto pr-1 custom-scrollbar memo-list-container"
        >
            <!-- Empty State -->
            <div
                v-if="memos.length === 0"
                class="text-xs text-gray-500 text-center py-2"
            >
                メモはありません
            </div>

            <!-- Memo Items -->
            <div
                v-for="memo in memos"
                :key="memo.id"
                class="group bg-gray-700 hover:bg-gray-600 rounded py-1.5 px-2 cursor-pointer transition-colors"
                :style="{
                    'border-left': `3px solid ${memo.category ? memo.category.color : '#6B7280'}`,
                }"
                v-todo-app:openEditTaskModal="memo"
                @click="$emit('edit-memo', memo)"
            >
                <div class="flex items-center justify-between">
                    <!-- Memo Title -->
                    <div class="text-sm truncate pr-1">{{ memo.title }}</div>

                    <!-- Trash Button (Visible on Hover) -->
                    <div
                        class="opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                    >
                        <button
                            type="button"
                            @click.stop="$emit('trash-memo', memo.id)"
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

                <!-- Category Display (if any) -->
                <div
                    v-if="memo.category"
                    class="text-xs text-gray-400 mt-0.5 flex items-center"
                >
                    <span
                        class="w-2 h-2 rounded-full mr-1"
                        :style="{ 'background-color': memo.category.color }"
                    ></span>
                    {{ memo.category.name }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "MemoList",

    props: {
        memos: {
            type: Array,
            default: () => [],
        },
    },

    emits: ["edit-memo", "trash-memo"],
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
