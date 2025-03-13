import { createRouter, createWebHistory } from "vue-router";

// Define route components using dynamic imports for lazy loading
const Dashboard = () => import("../components/pages/Dashboard.vue");
const TodoView = () => import("../components/pages/TodoView.vue");
const Profile = () => import("../components/pages/Profile.vue");
const Subscription = () => import("../components/pages/Subscription.vue");

// Define routes
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
        meta: {
            title: "Dashboard",
            requiresAuth: true,
        },
    },
    {
        path: "/todos",
        name: "todos",
        component: TodoView,
        meta: {
            title: "Todo List",
            requiresAuth: true,
        },
    },
    {
        path: "/todos/today",
        name: "todos.today",
        component: TodoView,
        meta: {
            title: "Today's Tasks",
            view: "today",
            requiresAuth: true,
        },
    },
    {
        path: "/todos/calendar",
        name: "todos.calendar",
        component: TodoView,
        meta: {
            title: "Task Calendar",
            view: "calendar",
            requiresAuth: true,
        },
    },
    {
        path: "/profile",
        name: "profile",
        component: Profile,
        meta: {
            title: "User Profile",
            requiresAuth: true,
        },
    },
    {
        path: "/subscription",
        name: "subscription",
        component: Subscription,
        meta: {
            title: "Subscription Management",
            requiresAuth: true,
        },
    },
];

// Create router instance
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

// Navigation guard for auth-required routes
router.beforeEach((to, from, next) => {
    // Check if route requires authentication
    if (to.matched.some((record) => record.meta.requiresAuth)) {
        // Check if user is logged in from the Laravel window object
        const user = window.Laravel?.user;
        if (!user) {
            // User is not logged in, redirect to login page
            return next({
                path: "/login",
                query: { redirect: to.fullPath },
            });
        }
    }

    // Update page title
    document.title = to.meta.title ? `${to.meta.title} - Todo App` : "Todo App";

    // Continue navigation
    next();
});

export default router;
