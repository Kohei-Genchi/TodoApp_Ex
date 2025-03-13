/**
 * Custom directive to integrate with TodoApp component
 *
 * Usage:
 * v-todo-app:method="parameters"
 *
 * Examples:
 * v-todo-app:editTask="taskId"
 * v-todo-app:openAddTaskModal
 */
export const vTodoApp = {
    beforeMount(el, binding, vnode) {
        if (!binding.arg) {
            console.warn("v-todo-app directive requires a method argument");
            return;
        }

        el._todoAppHandler = function () {
            // Check if TodoApp instance is available
            const todoApp = window.todoAppInstance;
            if (!todoApp) {
                console.warn("TodoApp instance not found");
                return;
            }

            // Call the specified method with parameters
            const method = binding.arg;
            const params = binding.value;

            if (typeof todoApp[method] === "function") {
                if (params !== undefined) {
                    todoApp[method](params);
                } else {
                    todoApp[method]();
                }
            } else {
                console.warn(`Method ${method} not found on TodoApp instance`);
            }
        };

        el.addEventListener("click", el._todoAppHandler);
    },

    unmounted(el) {
        if (el._todoAppHandler) {
            el.removeEventListener("click", el._todoAppHandler);
            delete el._todoAppHandler;
        }
    },
};

export default vTodoApp;
