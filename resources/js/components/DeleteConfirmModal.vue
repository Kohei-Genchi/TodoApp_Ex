<template>
    <div class="fixed inset-0 flex items-center justify-center z-50">
      <div class="absolute inset-0 bg-black bg-opacity-30" @click="cancel"></div>
      <div class="bg-white rounded-lg shadow-md w-full max-w-md relative z-10 p-6">
        <h3 class="text-lg font-medium mb-4">タスクの削除</h3>
        <div>
          <div class="flex items-center mb-4 text-red-600">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-gray-700 font-medium">「{{ todoTitle || 'このタスク' }}」を削除します。この操作は元に戻せません。</p>
          </div>

          <div v-if="isRecurring" class="mt-3 p-3 bg-yellow-50 border border-yellow-100 rounded-md">
            <p class="text-sm text-yellow-700">
              このタスクは繰り返し設定されています。すべての繰り返しタスクを削除しますか？
            </p>
            <div class="flex items-center mt-2">
              <input
                id="delete-all-recurring"
                type="checkbox"
                v-model="deleteAllRecurring"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="delete-all-recurring" class="ml-2 block text-sm text-gray-700">
                すべての繰り返しタスクを削除する
              </label>
            </div>
          </div>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" @click="cancel"
                  class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
            キャンセル
          </button>
          <button type="button" @click="confirm"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
            削除
          </button>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { ref } from 'vue';

  export default {
    props: {
      todoTitle: {
        type: String,
        default: 'このタスク'
      },
      isRecurring: {
        type: Boolean,
        default: false
      }
    },

    emits: ['confirm', 'cancel'],

    setup(props, { emit }) {
      console.log('DeleteConfirmModal setup with props:', props);
      const deleteAllRecurring = ref(false);

/*************  ✨ Codeium Command ⭐  *************/
      /**
       * Emit a confirmation event with the deleteAllRecurring flag
/******  81c55e35-f5c3-45c2-a40f-92db8231375b  *******/
      function confirm() {
        console.log('Deletion confirmed, deleteAllRecurring:', deleteAllRecurring.value);
        emit('confirm', true, deleteAllRecurring.value);
      }

      function cancel() {
        console.log('Deletion canceled');
        emit('cancel');
        emit('confirm', false); // Also emit confirm with false to clearly indicate cancellation
      }

      return {
        deleteAllRecurring,
        confirm,
        cancel
      };
    }
  };
  </script>
