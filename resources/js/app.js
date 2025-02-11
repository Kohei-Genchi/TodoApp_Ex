import '../css/app.css'; // Import Tailwind CSS
import { createApp } from 'vue';
import './bootstrap.js';
import { createPinia } from 'pinia'
import App from './layouts/app.vue';
import { createRouter, createWebHistory } from 'vue-router';
import { createPinia } from 'pinia'

import Top from '@/components/Top.vue';
import axios from 'axios';

const pinia = createPinia()

const routes = [

    { path: '/', component: Top },

//   { path: '/Files/:id', component: FileShow },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

const app = createApp(App)
  .use(router);

app.config.globalProperties.$axios = axios;
app.use(pinja)
app.mount('#app');
