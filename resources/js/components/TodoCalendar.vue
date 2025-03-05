<template>
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <!-- Calendar grid -->
      <div class="grid grid-cols-7 text-center text-xs text-gray-700 bg-gray-100">
        <div class="py-2 text-red-600">日</div>
        <div class="py-2">月</div>
        <div class="py-2">火</div>
        <div class="py-2">水</div>
        <div class="py-2">木</div>
        <div class="py-2">金</div>
        <div class="py-2 text-blue-600">土</div>
      </div>

      <!-- Calendar days -->
      <div class="grid grid-cols-7 border-t border-l border-gray-200">
        <div v-for="(day, index) in calendarDays" :key="index"
             @click="selectDate(day.date)"
             :class="[
               'min-h-[100px] p-1 border-r border-b border-gray-200',
               day.isCurrentMonth ? 'bg-white' : 'bg-gray-50',
               day.isToday ? 'bg-yellow-50' : '',
               day.isSelected ? 'bg-blue-50' : ''
             ]">

          <!-- Day number -->
          <div :class="[
              'text-center py-1',
              !day.isCurrentMonth ? 'text-gray-400' : '',
              day.isToday ? 'font-bold bg-yellow-100 rounded-full w-6 h-6 mx-auto' : '',
              day.date.split('-')[2] === '01' ? 'font-bold' : '' // Bold 1st of month
            ]">
            {{ parseInt(day.date.split('-')[2]) }} <!-- Display just the day number -->
          </div>

          <!-- Tasks for this day -->
          <div class="mt-1 text-xs space-y-1 max-h-28 overflow-y-auto">
            <div v-for="todo in day.todos" :key="todo.id"
                 :class="[
                   'flex items-center p-1 rounded hover:bg-gray-100',
                   todo.status === 'completed' ? 'text-gray-400 line-through' : ''
                 ]"
                 :style="{ borderLeft: '2px solid ' + (todo.category ? todo.category.color : '#ddd') }"
                 @click.stop="editTask(todo)">
              <span class="truncate text-xs">
                {{ todo.title }}
                <span v-if="todo.due_time" class="text-gray-500">
                  {{ formatTime(todo.due_time) }}
                </span>
              </span>
            </div>

            <!-- Show count if too many -->
            <div v-if="day.todos.length > 3" class="text-xs text-blue-500 text-center">
              + {{ day.todos.length - 3 }} more
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
      // Selected date
      const selectedDate = ref(props.currentDate);

      // CRITICAL: Fixed date formatting that properly handles timezones
      function formatDateToLocalYMD(dateString) {
        if (!dateString) return '';

        // If already in YYYY-MM-DD format, just return it
        if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) return dateString;

        try {
          const date = new Date(dateString);
          return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        } catch (e) {
          console.error('Error formatting date:', e);
          return '';
        }
      }

      // Generate calendar days
      const calendarDays = computed(() => {
        console.log('Generating calendar days for date:', props.currentDate);
        const days = [];

        // Create a local date object from the current date
        const date = new Date(props.currentDate);
        const year = date.getFullYear();
        const month = date.getMonth();

        // Log to help debug
        console.log('Calendar year/month:', year, month + 1);

        // First day of month in local time
        const firstDayOfMonth = new Date(year, month, 1);

        // Last day of month in local time
        const lastDayOfMonth = new Date(year, month + 1, 0);

        // First day of week containing first day of month
        const startDay = new Date(firstDayOfMonth);
        startDay.setDate(firstDayOfMonth.getDate() - firstDayOfMonth.getDay());

        // Current date for today highlighting
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Generate 6 weeks of days
        for (let week = 0; week < 6; week++) {
          for (let weekday = 0; weekday < 7; weekday++) {
            const currentDay = new Date(startDay);
            currentDay.setDate(startDay.getDate() + (week * 7) + weekday);

            // Format date as YYYY-MM-DD
            const dateString = formatDateToLocalYMD(currentDay);

            // Get todos for this day
            const todosForDay = getTodosForDate(dateString);

            days.push({
              date: dateString,
              isCurrentMonth: currentDay.getMonth() === month,
              isToday: currentDay.getTime() === today.getTime(),
              isSelected: dateString === selectedDate.value,
              todos: todosForDay
            });
          }
        }

        return days;
      });

      // Watch for prop changes
      watch(() => props.currentDate, (newDate) => {
        selectedDate.value = newDate;
      });

      // Select a date
      function selectDate(date) {
        selectedDate.value = date;
        emit('date-selected', date);
      }

      // Get todos for a specific date
      function getTodosForDate(date) {
        console.log('Looking for todos on date:', date);

        return props.todos.filter(todo => {
          // Format todo date to local YYYY-MM-DD
          const todoDate = formatDateToLocalYMD(todo.due_date);

          // Debug log
          console.log(`Todo "${todo.title}" date: DB=${todo.due_date}, local=${todoDate}, matches=${todoDate === date}`);

          return todoDate === date;
        });
      }

      // Format time for display
      function formatTime(timeString) {
        if (!timeString) return '';

        try {
          // Handle different time formats
          if (typeof timeString === 'string') {
            if (timeString.includes('T')) {
              // If it's a full ISO datetime
              const date = new Date(timeString);
              return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else {
              // If it's just HH:MM:SS
              const parts = timeString.split(':');
              return `${parts[0]}:${parts[1]}`;
            }
          } else if (timeString instanceof Date) {
            return timeString.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          }
          return timeString;
        } catch (e) {
          console.error('Error formatting time:', e);
          return '';
        }
      }

      // Edit task
      function editTask(todo) {
        emit('edit-task', todo);
      }

      return {
        calendarDays,
        selectDate,
        formatTime,
        editTask
      };
    }
  };
  </script>
