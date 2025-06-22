// src/router/index.js

import { createRouter, createWebHistory } from "vue-router";
// Updated imports based on new folder structure
import HomePage from "@/pages/HomePage.vue";
import ProductListPage from "@/pages/ProductListPage.vue";
import LoginPage from "@/pages/LoginPage.vue";
import RegisterPage from "@/pages/RegisterPage.vue";
import AdminPanel from "@/components/AdminPanel.vue"; // This remains in components
import ManufacturerManager from "@/components/ManufacturerManager.vue"; // This remains in components
import BrandManager from "@/components/BrandManager.vue"; // This remains in components
import OrderManager from "@/components/OrderManager.vue"; // This remains in components
import UserManager from "@/components/UserManager.vue"; // This remains in components
import GraphicCardDetailPage from "@/pages/GraphicCardDetailPage.vue";
import CartPage from "@/pages/CartPage.vue";
import PaymentPage from "@/pages/PaymentPage.vue";

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
  },
  {
    path: "/graphic-cards/:id",
    name: "graphic-card-detail",
    component: GraphicCardDetailPage,
    props: true,
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
    path: "/cart",
    name: "cart",
    component: CartPage,
  },
  {
    path: "/payment",
    name: "payment",
    component: PaymentPage,
    meta: { requiresAuth: true },
  },
  {
    path: "/orders",
    name: "orders",
    component: OrderManager,
    meta: { requiresAuth: true },
  },
  {
    path: "/orders/:id",
    name: "order-detail",
    props: true,
    meta: { requiresAuth: true },
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
    path: "/admin/orders",
    name: "admin-orders",
    component: OrderManager,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/users",
    name: "admin-users",
    component: UserManager,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
];

const router = createRouter({
  history: createWebHistory("/"),
  routes,
});

// Navigation guard to check authentication and roles
router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem("user") || "null");

  if (to.meta.requiresAuth && !user) {
    next({ name: "login" });
  } else if (to.meta.requiresAdmin && (!user || user.role !== "admin")) {
    next({ name: "home" });
  } else {
    next();
  }
});

export default router;
