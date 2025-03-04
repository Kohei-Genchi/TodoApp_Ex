import axios from 'axios';

export default {
    // すべてのカテゴリーを取得
    getCategories() {
        return axios.get('/api/categories');
    },

    // 新しいカテゴリーを作成
    createCategory(categoryData) {
        return axios.post('/api/categories', categoryData);
    },

    // カテゴリーを更新
    updateCategory(id, categoryData) {
        return axios.put(`/api/categories/${id}`, categoryData);
    },

    // カテゴリーを削除
    deleteCategory(id) {
        return axios.delete(`/api/categories/${id}`);
    }
};
