// resources/js/app.js
// Fix for initialization and window.editTodo function

import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import TodoApp from './components/TodoApp.vue';

window.Alpine = Alpine;
Alpine.start();

// Initialize Vue app if the todo-app element exists
if (document.getElementById('todo-app')) {
    const app = createApp(TodoApp);
    const vm = app.mount('#todo-app');

    // Create a global function for editing todos from outside Vue components
    window.editTodo = function(taskIdOrData, todoData = null) {
        console.log('Global editTodo called with:', taskIdOrData, todoData);

        // Handle when todoData is passed as a second parameter (from HTML data attributes)
        if (todoData && typeof todoData === 'object') {
            console.log('Using todoData from second parameter:', todoData);

            // Try to directly call the openEditTaskModal function with the todoData object
            if (vm && typeof vm.openEditTaskModal === 'function') {
                if (!todoData.id && taskIdOrData) {
                    todoData.id = Number(taskIdOrData);
                }
                vm.openEditTaskModal(todoData);
                return;
            }

            // Fallback to event dispatch
            const event = new CustomEvent('edit-todo', {
                detail: { id: Number(taskIdOrData), data: todoData }
            });
            document.getElementById('todo-app').dispatchEvent(event);
            return;
        }

        if (!taskIdOrData) {
            console.error('No task ID or data provided to editTodo');
            return;
        }

        try {
            // Use a direct ID if one is provided
            if (typeof taskIdOrData === 'number' || (typeof taskIdOrData === 'string' && !isNaN(parseInt(taskIdOrData)))) {
                const id = Number(taskIdOrData);

                // Try to directly call the fetchAndEditTask function if available
                if (vm && typeof vm.fetchAndEditTask === 'function') {
                    console.log('Directly calling fetchAndEditTask with ID:', id);
                    vm.fetchAndEditTask(id);
                    return;
                }

                // Fallback to event dispatch
                console.log('Dispatching edit-todo event with ID:', id);
                const event = new CustomEvent('edit-todo', {
                    detail: { id, data: null }
                });
                document.getElementById('todo-app').dispatchEvent(event);
                return;
            }

            // Use object data if provided
            if (typeof taskIdOrData === 'object' && taskIdOrData !== null) {
                // Try to directly call the openEditTaskModal function if available
                if (vm && typeof vm.openEditTaskModal === 'function') {
                    console.log('Directly calling openEditTaskModal with object:', taskIdOrData);
                    vm.openEditTaskModal(taskIdOrData);
                    return;
                }

                // Fallback to event dispatch
                const detail = taskIdOrData.id ?
                    { id: Number(taskIdOrData.id), data: taskIdOrData } :
                    { id: null, data: taskIdOrData };

                console.log('Dispatching edit-todo event with detail:', detail);
                const event = new CustomEvent('edit-todo', { detail });
                document.getElementById('todo-app').dispatchEvent(event);
                return;
            }

            console.error('Invalid task data format:', taskIdOrData);
        } catch (error) {
            console.error('Error in editTodo function:', error);
            alert('タスクの編集中にエラーが発生しました');
        }
    };
}

// Legacy listeners for buttons outside Vue components
document.addEventListener('DOMContentLoaded', function() {
    // Add the click handlers for "edit" buttons within legacy HTML
    const editButtons = document.querySelectorAll('.edit-task-btn');

    if (editButtons.length > 0) {
        console.log('Found', editButtons.length, 'edit buttons to attach handlers to');

        editButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const taskId = this.getAttribute('data-task-id');
                if (taskId) {
                    console.log('Edit button clicked for task ID:', taskId);
                    window.editTodo(Number(taskId));
                } else {
                    console.error('No task ID found on button:', this);
                }
            });
        });
    } else {
        console.log('No edit buttons found on page');
    }
});
