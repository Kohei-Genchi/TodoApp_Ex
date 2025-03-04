<template>
  <div class="bg-gray-100 min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <h1 class="text-xl font-semibold text-gray-900">Todo App</h1>

          <!-- ビューセレクター -->
          <div class="flex space-x-2">
            <button @click="setView('today')"
                   :class="[
                     'px-3 py-1 rounded-md text-sm font-medium',
                     currentView === 'today'
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                   ]">
              今日
            </button>
            <button @click="showCalendarView()"
                   :class="[
                     'px-3 py-1 rounded-md text-sm font-medium',
                     currentView === 'calendar'
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                   ]">
              カレンダー
            </button>
            <button @click="showTrashView()"
                   :class="[
                     'px-3 py-1 rounded-md text-sm font-medium',
                     currentView === 'trash'
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                   ]">
              ゴミ箱
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- 日付ナビゲーション（カレンダービュー以外で表示） -->
      <div v-if="currentView !== 'calendar' && currentView !== 'trash'" class="mb-6 flex justify-between items-center">
        <button @click="previousDay" class="text-gray-600 hover:text-gray-900">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <h2 class="text-lg font-medium text-gray-900">
          {{ formattedDate }}
        </h2>

        <button @click="nextDay" class="text-gray-600 hover:text-gray-900">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- カレンダー月ナビゲーション（カレンダービューで表示） -->
      <div v-if="currentView === 'calendar'" class="mb-6 flex justify-between items-center">
        <button @click="previousMonth" class="text-gray-600 hover:text-gray-900">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <h2 class="text-lg font-medium text-gray-900">
          {{ formattedMonth }}
        </h2>

        <button @click="nextMonth" class="text-gray-600 hover:text-gray-900">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- タスク追加ボタン（ゴミ箱ビュー以外で表示） -->
      <div v-if="currentView !== 'trash'" class="mb-6">
        <button @click="openAddTaskModal"
               class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          + 新しいタスク
        </button>
      </div>

      <!-- ゴミ箱を空にするボタン（ゴミ箱ビューで表示） -->
      <div v-if="currentView === 'trash' && trashedTodos.length > 0" class="mb-6">
        <button @click="confirmEmptyTrash"
               class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
          ゴミ箱を空にする
        </button>
      </div>

      <!-- カレンダー表示 -->
      <todo-calendar
        v-if="currentView === 'calendar'"
        :current-date="currentDate"
        :todos="todos"
        @date-selected="selectDate"
        @edit-task="openEditTaskModal"
      />

      <!-- タスク一覧（通常ビュー） -->
      <todo-list
        v-if="currentView !== 'calendar' && currentView !== 'trash'"
        :todos="filteredTodos"
        :categories="categories"
        @toggle-task="toggleTaskStatus"
        @edit-task="openEditTaskModal"
        @trash-task="trashTask"
      />

      <!-- タスク一覧（ゴミ箱ビュー） -->
      <todo-list
        v-if="currentView === 'trash'"
        :todos="trashedTodos"
        :categories="categories"
        :is-trash-view="true"
        @restore-task="restoreTask"
        @delete-task="confirmDeleteTask"
      />
    </main>

    <!-- タスク追加/編集モーダル -->
    <task-modal
      v-if="showTaskModal"
      :mode="taskModalMode"
      :todo-id="selectedTaskId"
      :todo-data="selectedTaskData"
      :categories="categories"
      :current-date="currentDate"
      :current-view="currentView"
      @close="closeTaskModal"
      @submit="submitTask"
      @delete="handleTaskDelete"
      @category-created="loadCategories"
    />

    <!-- 削除確認モーダル -->
    <delete-confirm-modal
      v-if="showDeleteConfirmModal"
      :todo-title="selectedTaskData?.title || ''"
      :is-recurring="selectedTaskData?.recurrence_type && selectedTaskData.recurrence_type !== 'none'"
      @confirm="confirmDelete"
      @cancel="showDeleteConfirmModal = false"
    />

    <!-- 通知コンポーネント -->
    <notification-component ref="notification" />
  </div>
</template>

<script>
import { ref, computed, onMounted, defineAsyncComponent } from 'vue';
import TodoApi from '../api/todo';
import CategoryApi from '../api/category';

// コンポーネントの非同期インポート
const TodoList = defineAsyncComponent(() => import('./TodoList.vue'));
const TodoCalendar = defineAsyncComponent(() => import('./TodoCalendar.vue'));
const TaskModal = defineAsyncComponent(() => import('./TaskModal.vue'));
const DeleteConfirmModal = defineAsyncComponent(() => import('./DeleteConfirmModal.vue'));
const NotificationComponent = defineAsyncComponent(() => import('./UI/NotificationComponent.vue'));

export default {
  components: {
    TodoList,
    TodoCalendar,
    TaskModal,
    DeleteConfirmModal,
    NotificationComponent
  },

  setup() {
    // 状態管理
    const todos = ref([]);
    const trashedTodos = ref([]);
    const categories = ref([]);
    const currentView = ref('today');
    const currentDate = ref(new Date().toISOString().split('T')[0]);

    // モーダル管理
    const showTaskModal = ref(false);
    const taskModalMode = ref('add');
    const selectedTaskId = ref(null);
    const selectedTaskData = ref(null);
    const showDeleteConfirmModal = ref(false);
    const deleteAllRecurring = ref(false);

    // 通知用ref
    const notification = ref(null);

    // 計算プロパティ
    const formattedDate = computed(() => {
      const date = new Date(currentDate.value);
      const today = new Date();

      if (date.toDateString() === today.toDateString()) {
        return '今日';
      }

      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      if (date.toDateString() === tomorrow.toDateString()) {
        return '明日';
      }

      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      if (date.toDateString() === yesterday.toDateString()) {
        return '昨日';
      }

      // 日本語表記に変換
      const options = { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' };
      return date.toLocaleDateString('ja-JP', options);
    });

    const formattedMonth = computed(() => {
      const date = new Date(currentDate.value);
      // 日本語表記に変換
      const options = { year: 'numeric', month: 'long' };
      return date.toLocaleDateString('ja-JP', options);
    });

    const filteredTodos = computed(() => {
      if (currentView.value === 'today') {
        // 今日のタスク
        return todos.value.filter(todo =>
          todo.due_date === currentDate.value && todo.status !== 'trashed'
        );
      } else {
        // 指定日のタスク
        return todos.value.filter(todo =>
          todo.due_date === currentDate.value && todo.status !== 'trashed'
        );
      }
    });

    // メソッド
    async function loadTasks() {
      try {
        const response = await TodoApi.getTasks(currentView.value, currentDate.value);
        todos.value = response.data;
      } catch (error) {
        console.error('Error loading tasks:', error);
        notification.value.show('タスクの読み込みに失敗しました', 'error');
      }
    }

    async function loadTrashedTasks() {
      try {
        const response = await TodoApi.getTrashedTasks();
        trashedTodos.value = response.data;
      } catch (error) {
        console.error('Error loading trashed tasks:', error);
        notification.value.show('ゴミ箱の読み込みに失敗しました', 'error');
      }
    }

    async function loadCategories() {
      try {
        const response = await CategoryApi.getCategories();
        categories.value = response.data;
      } catch (error) {
        console.error('Error loading categories:', error);
        notification.value.show('カテゴリーの読み込みに失敗しました', 'error');
      }
    }

    function setView(view) {
      currentView.value = view;
      if (view === 'today') {
        currentDate.value = new Date().toISOString().split('T')[0];
      }
      loadTasks();
    }

    function showCalendarView() {
      currentView.value = 'calendar';
      loadTasks();
    }

    function showTrashView() {
      currentView.value = 'trash';
      loadTrashedTasks();
    }

    function previousDay() {
      const date = new Date(currentDate.value);
      date.setDate(date.getDate() - 1);
      currentDate.value = date.toISOString().split('T')[0];
      loadTasks();
    }

    function nextDay() {
      const date = new Date(currentDate.value);
      date.setDate(date.getDate() + 1);
      currentDate.value = date.toISOString().split('T')[0];
      loadTasks();
    }

    function previousMonth() {
      const date = new Date(currentDate.value);
      date.setMonth(date.getMonth() - 1);
      currentDate.value = date.toISOString().split('T')[0];
      loadTasks();
    }

    function nextMonth() {
      const date = new Date(currentDate.value);
      date.setMonth(date.getMonth() + 1);
      currentDate.value = date.toISOString().split('T')[0];
      loadTasks();
    }

    function selectDate(date) {
      currentDate.value = date;
      currentView.value = 'date';
      loadTasks();
    }

    // タスクモーダル関連
    function openAddTaskModal() {
      taskModalMode.value = 'add';
      selectedTaskId.value = null;
      selectedTaskData.value = {
        title: '',
        due_date: currentDate.value,
        due_time: '',
        category_id: '',
        recurrence_type: 'none',
        recurrence_end_date: ''
      };
      showTaskModal.value = true;
    }

    function openEditTaskModal(task) {
      taskModalMode.value = 'edit';
      selectedTaskId.value = task.id;
      selectedTaskData.value = { ...task };
      showTaskModal.value = true;
    }

    function closeTaskModal() {
      showTaskModal.value = false;
    }

    async function submitTask(taskData) {
      try {
        if (taskModalMode.value === 'add') {
          // 新規タスク追加
          await TodoApi.createTask(taskData);
          notification.value.show('タスクを追加しました');
        } else {
          // 既存タスク更新
          await TodoApi.updateTask(selectedTaskId.value, taskData);
          notification.value.show('タスクを更新しました');
        }

        closeTaskModal();
        // ビューに応じてタスクリストを更新
        if (currentView.value === 'trash') {
          loadTrashedTasks();
        } else {
          loadTasks();
        }
      } catch (error) {
        console.error('Error submitting task:', error);
        notification.value.show('タスクの保存に失敗しました', 'error');
      }
    }

    // タスク操作関連
    async function toggleTaskStatus(task) {
      try {
        await TodoApi.toggleTask(task.id);

        // 状態を即時更新（最適化）
        const taskIndex = todos.value.findIndex(t => t.id === task.id);
        if (taskIndex !== -1) {
          todos.value[taskIndex].status =
            todos.value[taskIndex].status === 'completed' ? 'pending' : 'completed';
        }

        // 全体を更新
        loadTasks();
      } catch (error) {
        console.error('Error toggling task status:', error);
        notification.value.show('タスクのステータス変更に失敗しました', 'error');
      }
    }

    async function trashTask(task) {
      try {
        await TodoApi.trashTask(task.id);
        notification.value.show('タスクをゴミ箱に移動しました');
        loadTasks();
      } catch (error) {
        console.error('Error trashing task:', error);
        notification.value.show('タスクの削除に失敗しました', 'error');
      }
    }

    async function restoreTask(task) {
      try {
        await TodoApi.restoreTask(task.id);
        notification.value.show('タスクを復元しました');
        loadTrashedTasks();
      } catch (error) {
        console.error('Error restoring task:', error);
        notification.value.show('タスクの復元に失敗しました', 'error');
      }
    }

    function handleTaskDelete(id, deleteAllRecurringFlag) {
      selectedTaskId.value = id;
      deleteAllRecurring.value = deleteAllRecurringFlag;

      // 選択したタスクの情報を取得
      const task = todos.value.find(t => t.id === id);
      if (task) {
        selectedTaskData.value = task;
      }

      showDeleteConfirmModal.value = true;
    }

    function confirmDeleteTask(task) {
      selectedTaskId.value = task.id;
      selectedTaskData.value = task;
      showDeleteConfirmModal.value = true;
    }

    async function confirmDelete() {
      try {
        await TodoApi.deleteTask(selectedTaskId.value, deleteAllRecurring.value);
        notification.value.show('タスクを削除しました');
        showDeleteConfirmModal.value = false;

        // ビューに応じてタスクリストを更新
        if (currentView.value === 'trash') {
          loadTrashedTasks();
        } else {
          loadTasks();
        }

        closeTaskModal();
      } catch (error) {
        console.error('Error deleting task:', error);
        notification.value.show('タスクの削除に失敗しました', 'error');
      }
    }

    async function confirmEmptyTrash() {
      if (confirm('ゴミ箱を空にしますか？この操作は元に戻せません。')) {
        try {
          await TodoApi.emptyTrash();
          notification.value.show('ゴミ箱を空にしました');
          loadTrashedTasks();
        } catch (error) {
          console.error('Error emptying trash:', error);
          notification.value.show('ゴミ箱を空にできませんでした', 'error');
        }
      }
    }

    // 初期化
    onMounted(() => {
      loadTasks();
      loadCategories();
    });

    return {
      todos,
      trashedTodos,
      categories,
      currentView,
      currentDate,
      formattedDate,
      formattedMonth,
      filteredTodos,
      showTaskModal,
      taskModalMode,
      selectedTaskId,
      selectedTaskData,
      showDeleteConfirmModal,
      notification,
      setView,
      showCalendarView,
      showTrashView,
      previousDay,
      nextDay,
      previousMonth,
      nextMonth,
      selectDate,
      openAddTaskModal,
      openEditTaskModal,
      closeTaskModal,
      submitTask,
      toggleTaskStatus,
      trashTask,
      restoreTask,
      handleTaskDelete,
      confirmDeleteTask,
      confirmDelete,
      confirmEmptyTrash,
      loadCategories
    };
  }
};
</script>
