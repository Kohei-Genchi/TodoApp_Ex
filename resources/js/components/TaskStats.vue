<template>
  <div class="bg-white rounded-lg shadow mb-6 px-4 py-3 flex justify-between items-center">
    <div class="text-sm text-gray-700">
      <span>全 {{ totalCount }} タスク</span>
      <span class="mx-2 text-gray-400">|</span>
      <span class="text-green-600">完了: {{ completedCount }}</span>
      <span class="mx-2 text-gray-400">|</span>
      <span class="text-blue-600">未完了: {{ pendingCount }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskStats',
  props: {
    totalCount: {
      type: Number,
      required: true
    },
    completedCount: {
      type: Number,
      required: true
    },
    pendingCount: {
      type: Number,
      required: true
    }
  }
}
</script>
