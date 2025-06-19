import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/components/HomePage.vue";
import ProductListPage from "@/components/ProductListPage.vue";
import LoginPage from "@/components/LoginPage.vue";
import RegisterPage from "@/components/RegisterPage.vue";
import AdminPanel from "@/components/AdminPanel.vue";
import ManufacturerManager from "@/components/ManufacturerManager.vue";
import BrandManager from "@/components/BrandManager.vue";
import OrderManager from "@/components/OrderManager.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
  },
  {
    path: "/products",
    name: "products",
    component: ProductListPage,
    props: (route) => ({
      user: route.meta.user, // Will pass user from auth meta if available
      authToken: route.meta.authToken, // Will pass token from auth meta if available
    }),
  },
  {
    path: "/login",
    name: "login",
    component: LoginPage,
  },
  {
    path: "/register",
    name: "register",
    component: RegisterPage,
  },
  {
    path: "/admin",
    name: "admin",
    component: AdminPanel,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/manufacturers",
    name: "admin-manufacturers",
    component: ManufacturerManager,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/brands",
    name: "admin-brands",
    component: BrandManager,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/orders",
    name: "orders",
    component: OrderManager,
    meta: { requiresAuth: true },
  },
  {
    path: "/admin/orders",
    name: "admin-orders",
    component: OrderManager, // Re-use OrderManager for admin view
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  // Catch-all for 404
  {
    path: "/:catchAll(.*)",
    name: "NotFound",
    redirect: "/", // Redirect to home for now, or display a 404 component
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Basic Navigation Guard (Middleware) for authentication and authorization
router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem("user"));
  const authToken = localStorage.getItem("authToken");

  // Attach user and authToken to route meta for components to access
  to.meta.user = user;
  to.meta.authToken = authToken;

  if (to.meta.requiresAuth && !user) {
    // If route requires auth and user is not logged in, redirect to login
    next({ name: "login" });
  } else if (to.meta.requiresAdmin && (!user || user.role !== "admin")) {
    // If route requires admin and user is not admin, redirect to home or show access denied
    next({ name: "home" }); // Or a dedicated access-denied page
  } else {
    next(); // Proceed to route
  }
});

export default router;
