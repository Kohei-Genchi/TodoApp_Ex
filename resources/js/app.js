import './bootstrap';
import { createApp } from 'vue';
import TodoApp from './components/TodoApp.vue';

// CSRFトークンをすべてのリクエストヘッダーに設定
import axios from 'axios';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Vueアプリケーションをマウント
const app = createApp(TodoApp);

// グローバルプロパティの設定
app.config.globalProperties.$axios = axios;

// アプリケーションのマウント
app.mount('#todo-app');
