<template>
    <div
        class="fixed inset-0 flex items-center justify-center z-10"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div
            class="absolute inset-0 bg-black bg-opacity-40"
            @click="$emit('close')"
        ></div>

        <!-- Modal Content - スクロール可能にする -->
        <div
            class="bg-white rounded-lg shadow-lg w-full max-w-xl relative z-10 flex flex-col max-h-[90vh]"
        >
            <!-- ヘッダー - 固定 -->
            <div
                class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center"
            >
                <h3 id="modal-title" class="text-lg font-medium text-gray-800">
                    {{ mode === "add" ? "新しいタスク" : "タスクを編集" }}
                </h3>
                <button
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-gray-500"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- 本体 - スクロール可能 -->
            <div class="p-4 overflow-y-auto">
                <!-- Task Form -->
                <form @submit.prevent="submitForm" class="space-y-2">
                    <!-- Title -->
                    <div>
                        <label
                            for="title"
                            class="block text-sm font-medium text-gray-700"
                        >
                            タイトル<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            v-model="form.title"
                            required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-3 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Description - 高さを小さく -->
                    <div>
                        <label
                            for="description"
                            class="block text-sm font-medium text-gray-700"
                            >説明</label
                        >
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="2"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-3 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>

                    <!-- Date/Time -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label
                                for="due_date"
                                class="block text-sm font-medium text-gray-700"
                                >期限日</label
                            >
                            <input
                                type="date"
                                id="due_date"
                                v-model="form.due_date"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-3 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                        <div>
                            <label
                                for="due_time"
                                class="block text-sm font-medium text-gray-700"
                                >時間</label
                            >
                            <input
                                type="time"
                                id="due_time"
                                v-model="form.due_time"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-3 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Category Selection - コンパクトに -->
                    <div>
                        <div class="flex justify-between items-center">
                            <label
                                for="category_id"
                                class="block text-sm font-medium text-gray-700"
                                >カテゴリー</label
                            >
                            <button
                                type="button"
                                @click="showCategoryInput = !showCategoryInput"
                                class="text-xs text-blue-600 hover:text-blue-800"
                            >
                                {{
                                    showCategoryInput
                                        ? "戻る"
                                        : "新規カテゴリー"
                                }}
                            </button>
                        </div>

                        <!-- カテゴリー選択 -->
                        <div v-if="!showCategoryInput" class="mt-1">
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-3 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">カテゴリーなし</option>
                                <option
                                    v-for="category in categoriesArray"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>

                            <!-- 選択されたカテゴリー表示 -->
                            <div
                                v-if="form.category_id && getSelectedCategory"
                                class="mt-1 p-1 bg-gray-50 rounded-md text-xs flex items-center"
                            >
                                <div
                                    class="w-3 h-3 rounded-full mr-1"
                                    :style="{
                                        backgroundColor:
                                            getSelectedCategory.color,
                                    }"
                                ></div>
                                <span>{{ getSelectedCategory.name }}</span>
                            </div>
                        </div>

                        <!-- カテゴリー作成フォーム - コンパクトに -->
                        <div
                            v-if="showCategoryInput"
                            class="mt-1 space-y-2 p-2 border border-gray-200 rounded-md bg-gray-50"
                        >
                            <div class="flex gap-2">
                                <div class="flex-grow">
                                    <input
                                        type="text"
                                        id="new_category_name"
                                        v-model="newCategory.name"
                                        placeholder="カテゴリー名"
                                        class="w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 text-sm"
                                    />
                                </div>
                                <div class="w-24">
                                    <input
                                        type="color"
                                        id="new_category_color"
                                        v-model="newCategory.color"
                                        class="w-full h-7 border border-gray-300 rounded-md p-0"
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    @click="createCategory"
                                    class="px-2 py-1 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    追加
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recurrence Type - コンパクトに -->
                    <div>
                        <label
                            for="recurrence_type"
                            class="block text-sm font-medium text-gray-700"
                            >繰り返し</label
                        >
                        <select
                            id="recurrence_type"
                            v-model="form.recurrence_type"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-3 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="none">繰り返しなし</option>
                            <option value="daily">毎日</option>
                            <option value="weekly">毎週</option>
                            <option value="monthly">毎月</option>
                        </select>
                    </div>

                    <!-- Recurrence End Date - 条件付き表示をそのままに -->
                    <div
                        v-if="
                            form.recurrence_type &&
                            form.recurrence_type !== 'none'
                        "
                        class="pb-1"
                    >
                        <label
                            for="recurrence_end_date"
                            class="block text-sm font-medium text-gray-700"
                            >繰り返し終了日</label
                        >
                        <input
                            type="date"
                            id="recurrence_end_date"
                            v-model="form.recurrence_end_date"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-3 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p class="mt-0.5 text-xs text-gray-500">
                            ※指定しない場合は1ヶ月間繰り返されます
                        </p>
                    </div>
                </form>
            </div>

            <!-- Footer - 固定、コンパクトに -->
            <div class="bg-gray-50 px-4 py-2 border-t border-gray-200 mt-auto">
                <div class="flex justify-between items-center">
                    <!-- 削除ボタン (編集時のみ) -->
                    <div v-if="mode === 'edit'">
                        <button
                            type="button"
                            @click="deleteTask"
                            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-red-300 text-red-700 bg-white hover:bg-red-50"
                        >
                            削除
                        </button>
                    </div>
                    <div v-else class="flex-1"></div>

                    <!-- キャンセル/保存ボタン -->
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
                        >
                            キャンセル
                        </button>
                        <button
                            type="button"
                            @click="submitForm"
                            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-transparent text-white bg-blue-600 hover:bg-blue-700"
                        >
                            {{ mode === "add" ? "追加" : "保存" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, watch, onMounted, nextTick, computed } from "vue";

export default {
    props: {
        mode: {
            type: String,
            default: "add",
            validator: (value) => ["add", "edit"].includes(value),
        },
        todoId: {
            type: Number,
            default: null,
        },
        todoData: {
            type: Object,
            default: () => ({
                title: "",
                description: "",
                due_date: "",
                due_time: "",
                category_id: "",
                recurrence_type: "none",
                recurrence_end_date: "",
            }),
        },
        categories: {
            type: Array,
            default: () => [],
        },
        currentDate: {
            type: String,
            default: "",
        },
        currentView: {
            type: String,
            default: "today",
        },
    },

    emits: ["close", "submit", "delete", "category-created"],

    setup(props, { emit }) {
        console.log("TaskModal setup - Mode:", props.mode);
        console.log("Categories received:", props.categories);
        console.log("todoData received:", props.todoData);
        console.log("todoId received:", props.todoId);

        // Helper function to format dates
        function formatDateString(dateStr) {
            if (!dateStr) return "";

            try {
                // Check if already in YYYY-MM-DD format
                if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                    return dateStr;
                }

                // Convert ISO date string to local date format
                const date = new Date(dateStr);
                return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
            } catch (e) {
                console.error("Error formatting date:", e, dateStr);
                return "";
            }
        }

        // Form data - initialize with default values
        const form = reactive({
            title: "",
            description: "",
            due_date: "",
            due_time: "",
            category_id: "",
            recurrence_type: "none",
            recurrence_end_date: "",
        });

        // New category form
        const showCategoryInput = ref(false);
        const newCategory = reactive({
            name: "",
            color: "#3b82f6",
        });

        // Ensure categories are properly handled
        const categoriesArray = computed(() => {
            if (!props.categories) return [];

            // If it's already an array, return it
            if (Array.isArray(props.categories)) {
                return props.categories.map((cat) => {
                    // Make sure ID is a string for comparison with form.category_id
                    return {
                        ...cat,
                        id: String(cat.id),
                    };
                });
            }

            // If it's not an array, return empty array
            console.error("Categories prop is not an array:", props.categories);
            return [];
        });

        // Get selected category
        const getSelectedCategory = computed(() => {
            if (!form.category_id) return null;

            return (
                categoriesArray.value.find(
                    (cat) => String(cat.id) === String(form.category_id),
                ) || null
            );
        });

        // Initialize form data based on todoData prop
        function initializeForm() {
            console.log("Initializing form with data:", props.todoData);

            if (!props.todoData) {
                console.warn("TodoData is null or undefined");

                // Set defaults for new task
                if (props.mode === "add") {
                    form.title = "";
                    form.description = "";
                    form.due_date = props.currentDate || "";
                    form.due_time = "";
                    form.category_id = "";
                    form.recurrence_type = "none";
                    form.recurrence_end_date = "";
                }
                return;
            }

            // Set the title and description
            form.title = props.todoData.title || "";
            form.description = props.todoData.description || "";

            // Format dates properly for form inputs
            form.due_date = props.todoData.due_date
                ? formatDateString(props.todoData.due_date)
                : "";

            // Handle time from ISO string
            if (props.todoData.due_time) {
                if (
                    typeof props.todoData.due_time === "string" &&
                    props.todoData.due_time.includes("T")
                ) {
                    const timeDate = new Date(props.todoData.due_time);
                    form.due_time = `${String(timeDate.getHours()).padStart(2, "0")}:${String(timeDate.getMinutes()).padStart(2, "0")}`;
                } else {
                    // Try to extract just time part if it's in HH:MM:SS format
                    const timeParts = props.todoData.due_time.split(":");
                    if (timeParts.length >= 2) {
                        form.due_time = `${timeParts[0]}:${timeParts[1]}`;
                    } else {
                        form.due_time = props.todoData.due_time;
                    }
                }
            } else {
                form.due_time = "";
            }

            // Make sure category_id is properly handled (as a string for select element)
            if (
                props.todoData.category_id !== null &&
                props.todoData.category_id !== undefined
            ) {
                form.category_id = String(props.todoData.category_id);
                console.log("Setting category_id to:", form.category_id);
            } else {
                form.category_id = "";
            }

            form.recurrence_type = props.todoData.recurrence_type || "none";
            form.recurrence_end_date = props.todoData.recurrence_end_date
                ? formatDateString(props.todoData.recurrence_end_date)
                : "";

            console.log("Form initialized with:", { ...form });
        }

        // Watch for prop changes and initialize form
        watch(
            () => props.todoData,
            (newData) => {
                console.log("TodoData changed:", newData);
                nextTick(() => {
                    initializeForm();
                });
            },
            { immediate: true, deep: true },
        );

        // Watch for mode changes
        watch(
            () => props.mode,
            (newMode) => {
                console.log("Mode changed to:", newMode);
                nextTick(() => {
                    initializeForm();
                });
            },
            { immediate: true },
        );

        // Set default date based on current view
        watch(
            () => props.currentDate,
            (newDate) => {
                if (props.mode === "add" && !form.due_date && newDate) {
                    form.due_date = formatDateString(newDate);
                }
            },
            { immediate: true },
        );

        // Watch for categories changes and log them
        watch(
            () => props.categories,
            (newCategories) => {
                console.log("Categories updated in TaskModal:", newCategories);

                // Log detailed info about categories for debugging
                if (newCategories) {
                    console.log("Categories type:", typeof newCategories);
                    console.log("Is array:", Array.isArray(newCategories));
                    console.log("Length:", newCategories.length);

                    if (
                        Array.isArray(newCategories) &&
                        newCategories.length > 0
                    ) {
                        console.log("First category:", newCategories[0]);
                    }
                }
            },
            { immediate: true, deep: true },
        );

        // Initialize form when component mounts
        onMounted(() => {
            console.log(
                "TaskModal mounted - TodoID:",
                props.todoId,
                "Mode:",
                props.mode,
            );
            console.log("Categories on mount:", props.categories);

            // Log detailed info about categories for debugging
            if (props.categories) {
                console.log(
                    "Categories type on mount:",
                    typeof props.categories,
                );
                console.log(
                    "Is array on mount:",
                    Array.isArray(props.categories),
                );
                console.log("Length on mount:", props.categories.length);

                if (
                    Array.isArray(props.categories) &&
                    props.categories.length > 0
                ) {
                    console.log(
                        "First category on mount:",
                        props.categories[0],
                    );
                }
            }

            // Initialize form data
            nextTick(() => {
                initializeForm();
            });
        });

        // Form submission
        function submitForm() {
            console.log("Submit button clicked");
            console.log("Submitting form with data:", { ...form });
            console.log("Task ID from props:", props.todoId);

            if (!form.title.trim()) {
                alert("タイトルを入力してください");
                return;
            }

            // Prepare data for submission
            const formData = { ...form };

            // Convert category_id to number if it's a string and not empty
            if (formData.category_id !== "" && formData.category_id !== null) {
                formData.category_id = Number(formData.category_id);
            } else {
                // Ensure it's null and not an empty string
                formData.category_id = null;
            }

            console.log("Processed form data for submission:", formData);

            // Include task ID in the submission for edit mode
            if (props.mode === "edit" && props.todoId) {
                console.log(
                    "Emitting submit event with task ID:",
                    props.todoId,
                );
                formData.id = props.todoId;
            }

            // Find the complete category object for this category_id
            const categoryObject =
                formData.category_id !== null
                    ? categoriesArray.value.find(
                          (c) => Number(c.id) === formData.category_id,
                      )
                    : null;

            // Update existing todo in the list with category data
            const updatedTodo = {
                ...formData,
                category: categoryObject,
            };

            console.log("Emitting task with category:", updatedTodo);

            // Emit only the necessary events
            emit("submit", updatedTodo);
            emit("close");
        }

        // Create new category
        async function createCategory() {
            if (!newCategory.name.trim()) {
                alert("カテゴリー名を入力してください");
                return;
            }

            console.log("Creating category:", newCategory);

            try {
                // Get CSRF token
                const csrf = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");

                // Make direct fetch request
                const response = await fetch("/api/categories", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({
                        name: newCategory.name.trim(),
                        color: newCategory.color,
                    }),
                    credentials: "include", // Important for authentication
                });

                // Check for errors
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(
                        errorData.message || "Error creating category",
                    );
                }

                // Parse response
                const data = await response.json();
                console.log("Category created successfully:", data);

                // Set the new category as selected
                form.category_id = String(data.id);

                // Hide the category form
                showCategoryInput.value = false;

                // Emit event to refresh categories
                emit("category-created");

                // Reset form
                newCategory.name = "";
                newCategory.color = "#3b82f6";

                // Show success message
                alert("カテゴリーが作成されました");
            } catch (error) {
                console.error("Error creating category:", error);
                alert("カテゴリーの作成に失敗しました: " + error.message);
            }
        }

        // Delete task
        function deleteTask() {
            console.log(
                "Delete task button clicked for task ID:",
                props.todoId,
            );

            if (!props.todoId) {
                console.error("No task ID found");
                return;
            }

            // Use confirm dialog if task has recurrence
            const shouldDeleteAllRecurring =
                form.recurrence_type && form.recurrence_type !== "none";

            console.log(
                "Emitting delete event with id:",
                props.todoId,
                "deleteAllRecurring:",
                shouldDeleteAllRecurring,
            );

            emit("delete", props.todoId, shouldDeleteAllRecurring);
        }

        return {
            form,
            showCategoryInput,
            newCategory,
            categoriesArray,
            getSelectedCategory,
            submitForm,
            createCategory,
            deleteTask,
            props, // Expose props for debugging
        };
    },
};
</script>
