// In resources/js/router/index.js
import { createRouter, createWebHistory } from "vue-router";

// Define route components
// Using dynamic imports for lazy loading
const Dashboard = () => import("../components/pages/Dashboard.vue");
const TodoView = () => import("../components/pages/TodoView.vue");

// Define routes - only include routes that are handled by Vue
const routes = [
    {
        path: "/",
        name: "home",
        redirect: "/todos",
    },
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
    },
    {
        path: "/todos",
        name: "todos",
        component: TodoView,
        meta: {
            title: "Todo List",
        },
    },
    {
        path: "/todos/today",
        name: "todos.today",
        component: TodoView,
        meta: {
            title: "Today's Tasks",
            view: "today",
        },
    },
    {
        path: "/todos/calendar",
        name: "todos.calendar",
        component: TodoView,
        meta: {
            title: "Task Calendar",
            view: "calendar",
        },
    },
];

// Create router instance with a custom scrollBehavior function
const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    },
});

// Update page title when route changes
router.beforeEach((to, from, next) => {
    // Set page title
    document.title = to.meta.title ? `${to.meta.title} - Todo App` : "Todo App";

    // Log navigation for debugging
    console.log(`Navigating from ${from.path} to ${to.path}`);

    next();
});

export default router;
