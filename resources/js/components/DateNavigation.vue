<template>
  <div class="mb-2 flex justify-between items-center">
    <button
      @click="$emit('previous-day')"
      class="text-gray-600 hover:text-gray-900 px-3 py-1"
    >
      前日
    </button>

    <h2 class="text-lg font-medium text-gray-900">
      {{ formattedDate }}
    </h2>

    <button
      @click="$emit('next-day')"
      class="text-gray-600 hover:text-gray-900 px-3 py-1"
    >
      翌日
    </button>
  </div>
</template>

<script>
export default {
  name: 'DateNavigation',

  props: {
    /**
     * フォーマット済みの日付
     */
    formattedDate: {
      type: String,
      required: true
    }
  },

  emits: ['previous-day', 'next-day']
}
</script>
