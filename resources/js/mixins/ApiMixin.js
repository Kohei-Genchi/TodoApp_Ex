/**
 * API helper mixin for Vue components
 */
export default {
    methods: {
        /**
         * Get CSRF token from meta tag
         * @returns {string} CSRF token
         */
        getCsrfToken() {
            return (
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || ""
            );
        },

        /**
         * Build common headers for API requests
         * @param {boolean} includeContentType Whether to include Content-Type
         * @returns {Object} Headers object
         */
        getApiHeaders(includeContentType = true) {
            const headers = {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": this.getCsrfToken(),
                Accept: "application/json",
            };

            if (includeContentType) {
                headers["Content-Type"] = "application/json";
            }

            return headers;
        },

        /**
         * Make a GET request to the API
         * @param {string} url API endpoint URL
         * @param {Object} params Query parameters
         * @returns {Promise} Fetch promise
         */
        async apiGet(url, params = {}) {
            // Build query string
            const queryString = Object.keys(params).length
                ? `?${new URLSearchParams(params).toString()}`
                : "";

            const response = await fetch(`${url}${queryString}`, {
                method: "GET",
                headers: this.getApiHeaders(),
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            return response.json();
        },

        /**
         * Make a POST request to the API
         * @param {string} url API endpoint URL
         * @param {Object} data Request body data
         * @returns {Promise} Fetch promise
         */
        async apiPost(url, data = {}) {
            const response = await fetch(url, {
                method: "POST",
                headers: this.getApiHeaders(),
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            return response.json();
        },

        /**
         * Make a PUT request to the API
         * @param {string} url API endpoint URL
         * @param {Object} data Request body data
         * @returns {Promise} Fetch promise
         */
        async apiPut(url, data = {}) {
            const response = await fetch(url, {
                method: "PUT",
                headers: this.getApiHeaders(),
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            return response.json();
        },

        /**
         * Make a PATCH request to the API
         * @param {string} url API endpoint URL
         * @param {Object} data Request body data
         * @returns {Promise} Fetch promise
         */
        async apiPatch(url, data = {}) {
            const response = await fetch(url, {
                method: "PATCH",
                headers: this.getApiHeaders(),
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            return response.json();
        },

        /**
         * Make a DELETE request to the API
         * @param {string} url API endpoint URL
         * @returns {Promise} Fetch promise
         */
        async apiDelete(url) {
            const response = await fetch(url, {
                method: "DELETE",
                headers: this.getApiHeaders(),
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            return response.json();
        },
    },
};
