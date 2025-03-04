<template>
  <div class="fixed inset-0 overflow-y-auto z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                {{ mode === 'add' ? '新しいタスク' : 'タスクを編集' }}
              </h3>

              <!-- タスクフォーム -->
              <form @submit.prevent="submitForm" class="mt-4 space-y-4">
                <!-- タイトル -->
                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
                  <input type="text" id="title" v-model="form.title" required
                         class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <!-- 日付・時間 -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">期限日</label>
                    <input type="date" id="due_date" v-model="form.due_date"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  </div>
                  <div>
                    <label for="due_time" class="block text-sm font-medium text-gray-700">時間</label>
                    <input type="time" id="due_time" v-model="form.due_time"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  </div>
                </div>

                <!-- カテゴリー選択 -->
                <div>
                  <label for="category" class="block text-sm font-medium text-gray-700">カテゴリー</label>
                  <div class="flex space-x-2">
                    <select id="category" v-model="form.category_id"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                      <option value="">カテゴリーなし</option>
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                    <button type="button" @click="showCategoryInput = !showCategoryInput"
                            class="mt-1 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>
                  </div>
                </div>

                <!-- 新規カテゴリー入力 -->
                <div v-if="showCategoryInput" class="space-y-3 p-3 border border-gray-200 rounded-md bg-gray-50">
                  <div>
                    <label for="new_category_name" class="block text-sm font-medium text-gray-700">カテゴリー名</label>
                    <input type="text" id="new_category_name" v-model="newCategory.name" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  </div>
                  <div>
                    <label for="new_category_color" class="block text-sm font-medium text-gray-700">色</label>
                    <input type="color" id="new_category_color" v-model="newCategory.color"
                           class="mt-1 block border border-gray-300 rounded-md shadow-sm h-8 w-full">
                  </div>
                  <div class="flex justify-end space-x-2">
                    <button type="button" @click="showCategoryInput = false"
                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      キャンセル
                    </button>
                    <button type="button" @click="createCategory"
                           class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      追加
                    </button>
                  </div>
                </div>

                <!-- 繰り返し設定 -->
                <div>
                  <label for="recurrence_type" class="block text-sm font-medium text-gray-700">繰り返し</label>
                  <select id="recurrence_type" v-model="form.recurrence_type"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="none">繰り返しなし</option>
                    <option value="daily">毎日</option>
                    <option value="weekly">毎週</option>
                    <option value="monthly">毎月</option>
                  </select>
                </div>

                <!-- 繰り返し終了日（繰り返しが選択されている場合のみ表示） -->
                <div v-if="form.recurrence_type && form.recurrence_type !== 'none'">
                  <label for="recurrence_end_date" class="block text-sm font-medium text-gray-700">繰り返し終了日</label>
                  <input type="date" id="recurrence_end_date" v-model="form.recurrence_end_date"
                         class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  <p class="mt-1 text-xs text-gray-500">※指定しない場合は1ヶ月間繰り返されます</p>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- モーダルフッター（ボタン） -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button type="button" @click="submitForm"
                 class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
            {{ mode === 'add' ? '追加' : '保存' }}
          </button>
          <button type="button" @click="$emit('close')"
                 class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            キャンセル
          </button>
          <button v-if="mode === 'edit'" type="button" @click="deleteTask"
                 class="mt-3 w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:w-auto sm:text-sm">
            削除
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, watch } from 'vue';
import CategoryApi from '../api/category';

export default {
  props: {
    mode: {
      type: String,
      default: 'add', // 'add' or 'edit'
      validator: (value) => ['add', 'edit'].includes(value)
    },
    todoId: {
      type: Number,
      default: null
    },
    todoData: {
      type: Object,
      default: () => ({
        title: '',
        due_date: '',
        due_time: '',
        category_id: '',
        recurrence_type: 'none',
        recurrence_end_date: ''
      })
    },
    categories: {
      type: Array,
      default: () => []
    },
    currentDate: {
      type: String,
      default: ''
    },
    currentView: {
      type: String,
      default: 'today'
    }
  },

  emits: ['close', 'submit', 'delete', 'category-created'],

  setup(props, { emit }) {
    // フォームデータ
    const form = reactive({
      title: '',
      due_date: '',
      due_time: '',
      category_id: '',
      recurrence_type: 'none',
      recurrence_end_date: ''
    });

    // 新規カテゴリー用
    const showCategoryInput = ref(false);
    const newCategory = reactive({
      name: '',
      color: '#3b82f6' // デフォルト色
    });

    // propsの変更を監視してフォームを更新
    watch(() => props.todoData, (newData) => {
      if (newData) {
        form.title = newData.title || '';
        form.due_date = newData.due_date || '';
        form.due_time = newData.due_time || '';
        form.category_id = newData.category_id || '';
        form.recurrence_type = newData.recurrence_type || 'none';
        form.recurrence_end_date = newData.recurrence_end_date || '';
      }
    }, { immediate: true });

    // デフォルト日付設定
    watch(() => props.currentDate, (newDate) => {
      // 追加モードで、まだ日付が設定されていない場合のみデフォルト設定
      if (props.mode === 'add' && !form.due_date && newDate) {
        form.due_date = newDate;
      }
    }, { immediate: true });

    // フォーム送信
    function submitForm() {
      if (!form.title.trim()) {
        alert('タイトルを入力してください');
        return;
      }

      // フォームデータをコピー
      const formData = { ...form };

      // カテゴリIDが文字列の場合、数値に変換
      if (formData.category_id) {
        formData.category_id = Number(formData.category_id);
      }

      emit('submit', formData);
    }

    // 新しいカテゴリーを作成
    async function createCategory() {
      if (!newCategory.name.trim()) {
        alert('カテゴリー名を入力してください');
        return;
      }

      try {
        const response = await CategoryApi.createCategory(newCategory);
        const createdCategory = response.data;

        // フォームのカテゴリーを新しく作成したものに設定
        form.category_id = createdCategory.id;

        // カテゴリー入力フォームを非表示に
        showCategoryInput.value = false;

        // カテゴリーリストを再読み込み
        emit('category-created');

        // フォームをリセット
        newCategory.name = '';
        newCategory.color = '#3b82f6';

      } catch (error) {
        console.error('Error creating category:', error);
        alert('カテゴリーの作成に失敗しました');
      }
    }

    // タスクを削除
    function deleteTask() {
      const shouldDeleteAllRecurring = form.recurrence_type && form.recurrence_type !== 'none' ?
        confirm('このタスクは繰り返し設定されています。すべての繰り返しタスクを削除しますか？') : false;

      emit('delete', props.todoId, shouldDeleteAllRecurring);
    }

    return {
      form,
      showCategoryInput,
      newCategory,
      submitForm,
      createCategory,
      deleteTask
    };
  }
};
</script>
