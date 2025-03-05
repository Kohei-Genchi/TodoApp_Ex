<template>
    <div>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Calendar header - smaller to save space -->
            <div
                class="grid grid-cols-7 text-center text-xs text-gray-700 bg-gray-100"
            >
                <div class="py-1 text-red-600 font-medium">日</div>
                <div class="py-1 font-medium">月</div>
                <div class="py-1 font-medium">火</div>
                <div class="py-1 font-medium">水</div>
                <div class="py-1 font-medium">木</div>
                <div class="py-1 font-medium">金</div>
                <div class="py-1 text-blue-600 font-medium">土</div>
            </div>

            <!-- Calendar grid with optimized height -->
            <div class="grid grid-cols-7 border-t border-l border-gray-200">
                <div
                    v-for="(day, index) in calendarDays"
                    :key="index"
                    @click="selectDate(day.date)"
                    :class="[
                        'h-20 p-1 border-r border-b border-gray-200 relative',
                        day.isCurrentMonth ? 'bg-white' : 'bg-gray-50',
                        day.isToday ? 'bg-yellow-50' : '',
                        day.isSelected ? 'bg-blue-50' : '',
                    ]"
                >
                    <!-- Day number (smaller & in corner) -->
                    <div class="flex justify-between items-start">
                        <div
                            :class="[
                                'text-xs',
                                !day.isCurrentMonth ? 'text-gray-400' : '',
                                day.isToday
                                    ? 'font-bold bg-yellow-100 rounded-full w-4 h-4 flex items-center justify-center'
                                    : '',
                                day.date.split('-')[2] === '01'
                                    ? 'font-bold'
                                    : '',
                            ]"
                        >
                            {{ parseInt(day.date.split("-")[2]) }}
                        </div>

                        <!-- Task count badge -->
                        <div
                            v-if="day.todos.length > 0"
                            class="text-xs bg-blue-100 text-blue-800 px-1 rounded-full"
                        >
                            {{ day.todos.length }}
                        </div>
                    </div>

                    <!-- Tasks for this day (limited to 2) -->
                    <div class="mt-1 space-y-0.5">
                        <div
                            v-for="todo in day.todos.slice(0, 2)"
                            :key="todo.id"
                            :class="[
                                'flex items-center rounded text-xs hover:bg-gray-100 truncate',
                                todo.status === 'completed'
                                    ? 'text-gray-400 line-through'
                                    : '',
                            ]"
                            :style="{
                                borderLeft:
                                    '2px solid ' +
                                    (todo.category
                                        ? todo.category.color
                                        : '#ddd'),
                            }"
                            @click.stop="editTask(todo)"
                        >
                            <span class="truncate pl-0.5 text-xs">
                                {{ todo.title }}
                            </span>
                        </div>

                        <!-- Simplified "more" indicator -->
                        <div
                            v-if="day.todos.length > 2"
                            class="text-xs text-blue-500 cursor-pointer hover:underline"
                            @click.stop="viewAllTasks(day.date)"
                        >
                            +{{ day.todos.length - 2 }}
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
