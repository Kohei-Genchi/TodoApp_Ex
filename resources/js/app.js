import '../css/app.css'; // Import Tailwind CSS
import { createApp } from 'vue';
import './bootstrap.js';
import { createPinia } from 'pinia'
import App from './layouts/app.vue';
import { createRouter, createWebHistory } from 'vue-router';



import axios from 'axios';


const router = createRouter({
  history: createWebHistory(),
  routes,
});

const app = createApp(App)
  .use(router);


// Add axios to Vue instance
app.config.globalProperties.$axios = axios;

// Use Pinia for state management
app.use(createPinia());

// Mount the app
app.mount('#app');
