// src/api/category.js
import axios from 'axios';

export default {
    /**
     * Get all categories for the authenticated user
     */
    getCategories() {
        console.log('API getCategories called');
        return axios.get('/api/categories');
    },

    /**
     * Create a new category
     */
    createCategory(categoryData) {
        console.log('API createCategory called with data:', categoryData);

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };

        return axios.post('/api/categories', categoryData, { headers });
    },

    /**
     * Update an existing category
     */
    updateCategory(id, categoryData) {
        console.log('API updateCategory called with ID:', id, 'and data:', categoryData);

        if (!id) {
            console.error('Error: updateCategory called without an ID');
            return Promise.reject(new Error('No category ID provided'));
        }

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };

        return axios.put(`/api/categories/${id}`, categoryData, { headers });
    },

    /**
     * Delete a category
     */
    deleteCategory(id) {
        console.log('API deleteCategory called with ID:', id);

        if (!id) {
            console.error('Error: deleteCategory called without an ID');
            return Promise.reject(new Error('No category ID provided'));
        }

        // Add CSRF token to headers
        const headers = {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };

        return axios.delete(`/api/categories/${id}`, { headers });
    }
};
