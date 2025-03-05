<template>
    <div>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Calendar header -->
            <div
                class="grid grid-cols-7 text-center text-xs text-gray-700 bg-gray-100"
            >
                <div class="py-2 text-red-600 font-medium">日</div>
                <div class="py-2 font-medium">月</div>
                <div class="py-2 font-medium">火</div>
                <div class="py-2 font-medium">水</div>
                <div class="py-2 font-medium">木</div>
                <div class="py-2 font-medium">金</div>
                <div class="py-2 text-blue-600 font-medium">土</div>
            </div>

            <!-- Calendar grid with smaller fixed height -->
            <div class="grid grid-cols-7 border-t border-l border-gray-200">
                <div
                    v-for="(day, index) in calendarDays"
                    :key="index"
                    @click="selectDate(day.date)"
                    :class="[
                        'h-16 p-1 border-r border-b border-gray-200 relative',
                        day.isCurrentMonth ? 'bg-white' : 'bg-gray-50',
                        day.isToday ? 'bg-yellow-50' : '',
                        day.isSelected ? 'bg-blue-50' : '',
                    ]"
                >
                    <!-- Day number -->
                    <div
                        :class="[
                            'text-center text-xs py-0.5',
                            !day.isCurrentMonth ? 'text-gray-400' : '',
                            day.isToday
                                ? 'font-bold bg-yellow-100 rounded-full w-5 h-5 mx-auto flex items-center justify-center'
                                : '',
                            day.date.split('-')[2] === '01' ? 'font-bold' : '', // Bold 1st of month
                        ]"
                    >
                        {{ parseInt(day.date.split("-")[2]) }}
                    </div>

                    <!-- Tasks for this day (limited to 1 for even more compact view) -->
                    <div class="mt-0.5">
                        <div
                            v-if="day.todos.length > 0"
                            :class="[
                                'flex items-center px-0.5 rounded text-xs hover:bg-gray-100 mb-0.5 truncate',
                                day.todos[0].status === 'completed'
                                    ? 'text-gray-400 line-through'
                                    : '',
                            ]"
                            :style="{
                                borderLeft:
                                    '2px solid ' +
                                    (day.todos[0].category
                                        ? day.todos[0].category.color
                                        : '#ddd'),
                            }"
                            @click.stop="editTask(day.todos[0])"
                        >
                            <span class="truncate pl-0.5 text-xs">
                                {{ day.todos[0].title }}
                            </span>
                        </div>

                        <!-- Show count if more than 1 task -->
                        <div
                            v-if="day.todos.length > 1"
                            class="text-xs text-blue-500 text-center bg-blue-50 py-0 rounded cursor-pointer hover:bg-blue-100"
                            @click.stop="viewAllTasks(day.date)"
                        >
                            +{{ day.todos.length - 1 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref, watch } from "vue";

export default {
    props: {
        currentDate: {
            type: String,
            required: true,
        },
        todos: {
            type: Array,
            default: () => [],
        },
    },

    emits: ["date-selected", "edit-task", "view-all-tasks", "add-task"],

    setup(props, { emit }) {
        // Selected date
        const selectedDate = ref(props.currentDate);

        // Format date to YYYY-MM-DD consistently
        function formatDateToLocalYMD(dateString) {
            if (!dateString) return "";

            // If already in YYYY-MM-DD format, just return it
            if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) return dateString;

            try {
                const date = new Date(dateString);
                return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
            } catch (e) {
                console.error("Error formatting date:", e);
                return "";
            }
        }

        // Generate calendar days
        const calendarDays = computed(() => {
            const days = [];

            // Create a local date object from the current date
            const date = new Date(props.currentDate);
            const year = date.getFullYear();
            const month = date.getMonth();

            // First day of month
            const firstDayOfMonth = new Date(year, month, 1);

            // First day of week containing first day of month
            const startDay = new Date(firstDayOfMonth);
            startDay.setDate(
                firstDayOfMonth.getDate() - firstDayOfMonth.getDay(),
            );

            // Current date for today highlighting
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Generate 6 weeks of days
            for (let week = 0; week < 6; week++) {
                for (let weekday = 0; weekday < 7; weekday++) {
                    const currentDay = new Date(startDay);
                    currentDay.setDate(startDay.getDate() + week * 7 + weekday);

                    // Format date as YYYY-MM-DD
                    const dateString = formatDateToLocalYMD(currentDay);

                    // Get todos for this day
                    const todosForDay = getTodosForDate(dateString);

                    days.push({
                        date: dateString,
                        isCurrentMonth: currentDay.getMonth() === month,
                        isToday: currentDay.getTime() === today.getTime(),
                        isSelected: dateString === selectedDate.value,
                        todos: todosForDay,
                    });
                }
            }

            return days;
        });

        // Watch for prop changes
        watch(
            () => props.currentDate,
            (newDate) => {
                selectedDate.value = newDate;
            },
        );

        // Select a date
        function selectDate(date) {
            selectedDate.value = date;
            emit("date-selected", date);
        }

        // View all tasks for a specific date
        function viewAllTasks(date) {
            emit("date-selected", date);
        }

        // Get todos for a specific date
        function getTodosForDate(date) {
            return props.todos.filter((todo) => {
                // Format todo date to local YYYY-MM-DD
                const todoDate = formatDateToLocalYMD(todo.due_date);
                return todoDate === date;
            });
        }

        // Format time for display
        function formatTime(timeString) {
            if (!timeString) return "";

            try {
                // Handle different time formats
                if (typeof timeString === "string") {
                    if (timeString.includes("T")) {
                        // If it's a full ISO datetime
                        const date = new Date(timeString);
                        return date.toLocaleTimeString([], {
                            hour: "2-digit",
                            minute: "2-digit",
                        });
                    } else {
                        // If it's just HH:MM:SS
                        const parts = timeString.split(":");
                        return `${parts[0]}:${parts[1]}`;
                    }
                } else if (timeString instanceof Date) {
                    return timeString.toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                    });
                }
                return timeString;
            } catch (e) {
                console.error("Error formatting time:", e);
                return "";
            }
        }

        // Edit task
        function editTask(todo) {
            emit("edit-task", todo);
        }

        return {
            calendarDays,
            selectDate,
            formatTime,
            editTask,
            viewAllTasks,
        };
    },
};
</script>
