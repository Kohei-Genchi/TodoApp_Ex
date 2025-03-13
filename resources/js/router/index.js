import { createRouter, createWebHistory } from "vue-router";

// Import page components
const Dashboard = () => import("../components/pages/Dashboard.vue");
const Profile = () => import("../components/pages/Profile.vue");
const Subscription = () => import("../components/pages/Subscription.vue");
const TodoView = () => import("../components/pages/TodoView.vue");

// Define routes
const routes = [
    {
        path: "/",
        name: "home",
        redirect: "/todos",
        meta: {
            title: "Home",
        },
    },
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
        meta: {
            title: "Dashboard",
        },
    },
    {
        path: "/profile",
        name: "profile",
        component: Profile,
        meta: {
            title: "Profile",
        },
    },
    {
        path: "/subscription",
        name: "subscription",
        component: Subscription,
        meta: {
            title: "Subscription",
        },
    },
    {
        path: "/todos",
        name: "todos",
        component: TodoView,
        meta: {
            title: "Todo List",
        },
    },
    // Additional todo routes that should all use the TodoView component
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
    {
        path: "/todos/trash",
        name: "todos.trash",
        component: TodoView,
        meta: {
            title: "Trash",
            view: "trash",
        },
    },
];

// Create router instance
const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Title update hook
router.beforeEach((to, from, next) => {
    document.title = to.meta.title ? `${to.meta.title} - Todo App` : "Todo App";
    next();
});

export default router;
