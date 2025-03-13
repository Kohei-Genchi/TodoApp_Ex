// In resources/js/api/todo.js
import axios from "axios";

/**
 * Common headers for API requests
 * @returns {Object} Headers object
 */
const getCommonHeaders = () => ({
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
});

/**
 * CSRF protected request headers
 * @returns {Object} Headers object with CSRF token
 */
const getCsrfHeaders = () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (!csrfToken) {
        console.warn("CSRF token not found");
    }

    return {
        ...getCommonHeaders(),
        "X-CSRF-TOKEN": csrfToken,
        "Content-Type": "application/json",
    };
};

/**
 * Validate task ID
 * @param {number} id Task ID
 * @param {string} methodName Method name for error message
 * @returns {boolean} True if valid
 */
const validateId = (id, methodName) => {
    if (id === undefined || id === null) {
        console.error(`Error: ${methodName} called without an ID`);
        throw new Error("タスクIDが指定されていません");
    }
    return true;
};

export default {
    /**
     * Get tasks based on view and date
     * @param {string} view View type (today, inbox, calendar, etc)
     * @param {string} date Date in YYYY-MM-DD format
     * @returns {Promise} API response
     */
    getTasks(view, date) {
        console.log(`API: Getting tasks for view=${view}, date=${date}`);
        return axios.get("/api/todos", {
            params: { view, date },
            headers: getCommonHeaders(),
        });
    },

    /**
     * Get trashed tasks
     * @returns {Promise} API response
     */
    getTrashedTodos() {
        console.log("API: Getting trashed todos");
        return axios.get("/api/todos", {
            params: { view: "trash" },
            headers: getCommonHeaders(),
        });
    },

    /**
     * Empty trash
     * @returns {Promise} API response
     */
    emptyTrash() {
        console.log("API: Emptying trash");
        return axios.delete("/api/todos/trash/empty", {
            headers: getCsrfHeaders(),
        });
    },

    /**
     * Get task by ID
     * @param {number} id Task ID
     * @returns {Promise} API response
     */
    getTaskById(id) {
        validateId(id, "getTaskById");
        console.log(`API: Getting task with ID ${id}`);
        return axios.get(`/api/todos/${id}`, {
            headers: getCommonHeaders(),
        });
    },

    /**
     * Create a new task
     * @param {Object} taskData Task data
     * @returns {Promise} API response
     */
    createTask(taskData) {
        console.log("API: Creating new task", taskData);
        return axios.post("/api/todos", taskData, {
            headers: getCsrfHeaders(),
        });
    },

    /**
     * Update an existing task
     * @param {number} id Task ID
     * @param {Object} taskData Updated task data
     * @returns {Promise} API response
     */
    updateTask(id, taskData) {
        validateId(id, "updateTask");
        console.log(`API: Updating task ${id}`, taskData);

        // Use PUT for RESTful update
        return axios.put(`/api/todos/${id}`, taskData, {
            headers: getCsrfHeaders(),
        });
    },

    /**
     * Toggle task status
     * @param {number} id Task ID
     * @returns {Promise} API response
     */
    toggleTask(id) {
        validateId(id, "toggleTask");
        console.log(`API: Toggling task ${id}`);
        return axios.patch(
            `/api/todos/${id}/toggle`,
            {},
            {
                headers: getCsrfHeaders(),
            },
        );
    },

    /**
     * Trash a task
     * @param {number} id Task ID
     * @returns {Promise} API response
     */
    trashTask(id) {
        validateId(id, "trashTask");
        console.log(`API: Trashing task ${id}`);
        return axios.patch(
            `/api/todos/${id}/trash`,
            {},
            {
                headers: getCsrfHeaders(),
            },
        );
    },

    /**
     * Restore a trashed task
     * @param {number} id Task ID
     * @returns {Promise} API response
     */
    restoreTask(id) {
        validateId(id, "restoreTask");
        console.log(`API: Restoring task ${id}`);
        return axios.patch(
            `/api/todos/${id}/restore`,
            {},
            {
                headers: getCsrfHeaders(),
            },
        );
    },

    /**
     * Delete a task permanently
     * @param {number} id Task ID
     * @param {boolean} deleteRecurring Whether to delete recurring instances
     * @returns {Promise} API response
     */
    deleteTask(id, deleteRecurring = false) {
        validateId(id, "deleteTask");
        console.log(`API: Deleting task ${id}, recurring=${deleteRecurring}`);
        return axios.delete(`/api/todos/${id}`, {
            headers: getCsrfHeaders(),
            params: { delete_recurring: deleteRecurring ? 1 : 0 },
        });
    },
};
