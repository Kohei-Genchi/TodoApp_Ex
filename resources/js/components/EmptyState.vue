<template>
  <div class="bg-white rounded-lg shadow-sm p-8 text-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
    </svg>
    <h3 class="text-lg font-medium text-gray-700 mb-2">タスクはありません</h3>
    <p v-if="!isTrashView" class="text-sm text-gray-500 mb-4">新しいタスクを追加しましょう</p>
    <p v-else class="text-sm text-gray-500 mb-4">ゴミ箱は空です</p>
  </div>
</template>

<script>
export default {
  name: 'EmptyState',
  props: {
    isTrashView: {
      type: Boolean,
      default: false
    }
  }
}
</script>
