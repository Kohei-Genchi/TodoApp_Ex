// resources/js/api/todo.js
// Fix for API request headers and error handling

import axios from 'axios';

export default {
    // ビューと日付に基づいてタスクを取得
    getTasks(view, date) {
        console.log('API getTasks called with:', { view, date });
        return axios.get(`/api/todos`, {
            params: { view, date },
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    },

    // タスクをIDで取得
    getTaskById(id) {
        console.log('API getTaskById called with ID:', id);
        if (!id && id !== 0) {
            console.error('Error: getTaskById called without an ID');
            return Promise.reject(new Error('No task ID provided'));
        }
        return axios.get(`/api/todos/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    },

    // 新しいタスクを作成
    createTask(taskData) {
        console.log('API createTask called with data:', taskData);

        // Make sure we have the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.warn('CSRF token not found, attempting to create task anyway');
        }

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        console.log('Creating task with headers:', headers);

        return axios.post('/api/todos', taskData, { headers });
    },

    // 既存のタスクを更新
    // In resources/js/api/todo.js - update the updateTask function
updateTask(id, taskData) {
    console.log('API updateTask called with ID:', id, 'and data:', taskData);

    if (!id && id !== 0) {
        console.error('Error: updateTask called without an ID');
        return Promise.reject(new Error('No task ID provided'));
    }

    // Get CSRF token directly from the meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Add CSRF token and other required headers
    const headers = {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    // Log the full request for debugging
    console.log('Full update request:', {
        url: `/api/todos/${id}`,
        method: 'PUT',
        headers,
        data: taskData
    });

    // Use axios.post with _method instead of axios.put
    // Laravel works better with POST + _method for CSRF protection
    return axios.post(`/api/todos/${id}`, {
        ...taskData,
        _method: 'PUT'  // This tells Laravel to treat it as a PUT request
    }, { headers })
    .catch(error => {
        console.error('Error in updateTask:', error.response || error);
        throw error;
    });
},

    // タスクの完了状態をトグル
    toggleTask(id) {
        console.log('API toggleTask called with ID:', id);

        if (!id && id !== 0) {
            console.error('Error: toggleTask called without an ID');
            return Promise.reject(new Error('No task ID provided'));
        }

        // Make sure we have the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.warn('CSRF token not found, attempting to toggle task anyway');
        }

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        return axios.patch(`/api/todos/${id}/toggle`, {}, { headers });
    },

    // タスクをゴミ箱に移動
    trashTask(id) {
        console.log('API trashTask called with ID:', id);

        if (!id && id !== 0) {
            console.error('Error: trashTask called without an ID');
            return Promise.reject(new Error('No task ID provided'));
        }

        // Make sure we have the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.warn('CSRF token not found, attempting to trash task anyway');
        }

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        return axios.patch(`/api/todos/${id}/trash`, {}, { headers });
    },

    // タスクをゴミ箱から復元
    restoreTask(id) {
        console.log('API restoreTask called with ID:', id);

        if (!id && id !== 0) {
            console.error('Error: restoreTask called without an ID');
            return Promise.reject(new Error('No task ID provided'));
        }

        // Make sure we have the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.warn('CSRF token not found, attempting to restore task anyway');
        }

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        return axios.patch(`/api/todos/${id}/restore`, {}, { headers });
    },

    // タスクを完全に削除
    deleteTask(id, deleteRecurring = false) {
        console.log('API deleteTask called with ID:', id, 'deleteRecurring:', deleteRecurring);

        if (!id && id !== 0) {
            console.error('Error: deleteTask called without an ID');
            return Promise.reject(new Error('No task ID provided'));
        }

        // Make sure we have the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.warn('CSRF token not found, attempting to delete task anyway');
        }

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        return axios.delete(`/api/todos/${id}`, {
            headers,
            params: { delete_recurring: deleteRecurring ? 1 : 0 }
        });
    },

    // ゴミ箱を空にする
    emptyTrash() {
        console.log('API emptyTrash called');

        // Make sure we have the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.warn('CSRF token not found, attempting to empty trash anyway');
        }

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        return axios.delete('/api/todos/trash/empty', { headers });
    },

    // ゴミ箱内のタスクを取得
    getTrashedTasks() {
        console.log('API getTrashedTasks called');
        return axios.get('/api/todos/trashed', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }
};
