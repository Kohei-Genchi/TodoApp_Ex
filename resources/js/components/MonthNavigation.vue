<template>
  <div class="mb-2 flex justify-between items-center">
    <button
      @click="$emit('previous-month')"
      class="text-gray-600 hover:text-gray-900 px-3 py-1"
    >
      前月
    </button>

    <h2 class="text-lg font-medium text-gray-900">
      {{ formattedMonth }}
    </h2>

    <button
      @click="$emit('next-month')"
      class="text-gray-600 hover:text-gray-900 px-3 py-1"
    >
      翌月
    </button>
  </div>
</template>

<script>
export default {
  name: 'MonthNavigation',

  props: {
    /**
     * フォーマット済みの月
     */
    formattedMonth: {
      type: String,
      required: true
    }
  },

  emits: ['previous-month', 'next-month']
}
</script>
