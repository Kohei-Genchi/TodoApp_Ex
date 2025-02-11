import '../css/app.css'; // Import Tailwind CSS
import { createApp } from 'vue';
import './bootstrap.js';
import App from './layouts/app.vue';
import { createRouter, createWebHistory } from 'vue-router';


import axios from 'axios';
import TodoList from '@/components/TodoList.vue';

// Define routes (this was missing)
const routes = [
  {
    path: '/',
    component: TodoList
  }
];


const router = createRouter({
    history: createWebHistory(),
    routes,
  });

  const app = createApp(App)
    .use(router);

  app.config.globalProperties.$axios = axios;

  app.mount('#app');
