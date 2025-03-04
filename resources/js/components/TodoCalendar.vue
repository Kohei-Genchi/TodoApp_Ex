<template>
  <div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <!-- カレンダーグリッド -->
    <div class="grid grid-cols-7 text-center">
      <!-- 曜日ヘッダー -->
      <div v-for="(day, index) in weekDays" :key="index" class="py-2 bg-gray-50 font-medium text-gray-700">
        {{ day }}
      </div>

      <!-- 日付セル -->
      <div v-for="(day, index) in calendarDays" :key="index"
           @click="selectDate(day.date)"
           :class="[
             'min-h-[100px] p-2 border border-gray-100',
             day.isCurrentMonth ? 'bg-white' : 'bg-gray-50 text-gray-400',
             day.isToday ? 'border-blue-300' : '',
             day.isSelected ? 'bg-blue-50' : ''
           ]">
        <!-- 日付表示 -->
        <div :class="[
            'text-sm font-medium rounded-full w-7 h-7 flex items-center justify-center',
            day.isToday ? 'bg-blue-600 text-white' : ''
          ]">
          {{ new Date(day.date).getDate() }}
        </div>

        <!-- タスク表示 -->
        <div class="mt-1 space-y-1 overflow-y-auto max-h-[80px]">
          <div v-for="todo in day.todos" :key="todo.id"
               :class="[
                 'text-xs p-1 rounded truncate cursor-pointer',
                 todo.status === 'completed' ? 'line-through text-gray-400 bg-gray-100' : 'text-gray-700 bg-blue-50'
               ]"
               :title="todo.title"
               @click.stop="editTask(todo)">
            <span v-if="todo.due_time">{{ formatTime(todo.due_time) }} </span>
            {{ todo.title }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, watch } from 'vue';

export default {
  props: {
    currentDate: {
      type: String,
      required: true
    },
    todos: {
      type: Array,
      default: () => []
    }
  },

  emits: ['date-selected', 'edit-task'],

  setup(props, { emit }) {
    // Add global function for legacy HTML onclick handlers
    window.editTodo = function(todoId) {
      const todo = props.todos.find(t => t.id === todoId);
      if (todo) {
        emit('edit-task', todo);
      }
    };
    // 選択された日付
    const selectedDate = ref(props.currentDate);

    // 曜日配列（日本語）
    const weekDays = ['日', '月', '火', '水', '木', '金', '土'];

    // カレンダーデータ生成
    const calendarDays = computed(() => {
      const days = [];
      const date = new Date(props.currentDate);
      const year = date.getFullYear();
      const month = date.getMonth();

      // 月の最初の日を取得
      const firstDayOfMonth = new Date(year, month, 1);
      // 月の最後の日を取得
      const lastDayOfMonth = new Date(year, month + 1, 0);

      // 前月の表示日数を計算（日曜始まり）
      const daysFromPrevMonth = firstDayOfMonth.getDay();

      // 前月の日を追加
      for (let i = daysFromPrevMonth - 1; i >= 0; i--) {
        const prevDate = new Date(year, month, -i);
        days.push({
          date: formatDate(prevDate),
          isCurrentMonth: false,
          isToday: isToday(prevDate),
          isSelected: isSameDate(prevDate, selectedDate.value),
          todos: getTodosForDate(formatDate(prevDate))
        });
      }

      // 当月の日を追加
      for (let i = 1; i <= lastDayOfMonth.getDate(); i++) {
        const currentDate = new Date(year, month, i);
        days.push({
          date: formatDate(currentDate),
          isCurrentMonth: true,
          isToday: isToday(currentDate),
          isSelected: isSameDate(currentDate, selectedDate.value),
          todos: getTodosForDate(formatDate(currentDate))
        });
      }

      // 翌月の日を追加（6週間分になるように）
      const totalDays = 42; // 6週間分
      const remainingDays = totalDays - days.length;

      for (let i = 1; i <= remainingDays; i++) {
        const nextDate = new Date(year, month + 1, i);
        days.push({
          date: formatDate(nextDate),
          isCurrentMonth: false,
          isToday: isToday(nextDate),
          isSelected: isSameDate(nextDate, selectedDate.value),
          todos: getTodosForDate(formatDate(nextDate))
        });
      }

      return days;
    });

    // 選択日付が変更されたら更新
    watch(() => props.currentDate, (newDate) => {
      selectedDate.value = newDate;
    });

    // 日付を選択
    function selectDate(date) {
      selectedDate.value = date;
      emit('date-selected', date);
    }

    // 特定の日付のタスクを取得
    function getTodosForDate(date) {
      return props.todos.filter(todo => todo.due_date === date);
    }

    // 日付のフォーマット関数
    function formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    // 今日かどうかチェック
    function isToday(date) {
      const today = new Date();
      return date.getDate() === today.getDate() &&
             date.getMonth() === today.getMonth() &&
             date.getFullYear() === today.getFullYear();
    }

    // 同じ日付かチェック
    function isSameDate(date, dateString) {
      if (!dateString) return false;
      const compareDate = new Date(dateString);
      return date.getDate() === compareDate.getDate() &&
             date.getMonth() === compareDate.getMonth() &&
             date.getFullYear() === compareDate.getFullYear();
    }

    // 時間表示のフォーマット
    function formatTime(timeString) {
      if (!timeString) return '';

      const parts = timeString.split(':');
      if (parts.length >= 2) {
        return `${parts[0]}:${parts[1]}`;
      }
      return timeString;
    }

    // Add editTask method
    function editTask(todo) {
      emit('edit-task', todo);
    }

    return {
      weekDays,
      calendarDays,
      selectDate,
      formatTime,
      editTask
    };
  }
};
</script>
