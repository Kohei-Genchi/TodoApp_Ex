import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8080', // Remove /api since it's already in the URL
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

export default api;
