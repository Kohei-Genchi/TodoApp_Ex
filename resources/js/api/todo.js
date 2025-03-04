import axios from 'axios';

export default {
    // ビューと日付に基づいてタスクを取得
    getTasks(view, date) {
        return axios.get(`/api/todos`, { params: { view, date } });
    },

    // 新しいタスクを作成
    createTask(taskData) {
        return axios.post('/api/todos', taskData);
    },

    // 既存のタスクを更新
    updateTask(id, taskData) {
        return axios.put(`/api/todos/${id}`, taskData);
    },

    // タスクの完了状態をトグル
    toggleTask(id) {
        return axios.patch(`/api/todos/${id}/toggle`);
    },

    // タスクをゴミ箱に移動
    trashTask(id) {
        return axios.patch(`/api/todos/${id}/trash`);
    },

    // タスクをゴミ箱から復元
    restoreTask(id) {
        return axios.patch(`/api/todos/${id}/restore`);
    },

    // タスクを完全に削除
    deleteTask(id, deleteRecurring = false) {
        return axios.delete(`/api/todos/${id}`, {
            params: { delete_recurring: deleteRecurring ? 1 : 0 }
        });
    },

    // ゴミ箱を空にする
    emptyTrash() {
        return axios.delete('/api/todos/trash/empty');
    },

    // ゴミ箱内のタスクを取得
    getTrashedTasks() {
        return axios.get('/api/todos/trash');
    }
};
