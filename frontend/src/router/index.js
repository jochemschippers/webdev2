import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/components/HomePage.vue";
import ProductListPage from "@/components/ProductListPage.vue";
import LoginPage from "@/components/LoginPage.vue";
import RegisterPage from "@/components/RegisterPage.vue";
import AdminPanel from "@/components/AdminPanel.vue";
import ManufacturerManager from "@/components/ManufacturerManager.vue";
import BrandManager from "@/components/BrandManager.vue";
import OrderManager from "@/components/OrderManager.vue";
import CartPage from "@/components/CartPage.vue";
import PaymentPage from "@/components/PaymentPage.vue";
import GraphicCardDetailPage from "@/components/GraphicCardDetailPage.vue"; // NEW: Import GraphicCardDetailPage

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
      user: route.meta.user,
      authToken: route.meta.authToken,
      cart: route.meta.cart,
    }),
  },
  {
    // NEW ROUTE: Graphic Card Detail Page
    path: "/graphic-cards/:id",
    name: "graphic-card-detail",
    component: GraphicCardDetailPage,
    props: true, // This tells Vue Router to pass route.params.id as a prop to the component
  },
  {
    path: "/cart",
    name: "cart",
    component: CartPage,
    meta: { requiresAuth: true },
    props: (route) => ({
      user: route.meta.user,
      authToken: route.meta.authToken,
      cart: route.meta.cart,
    }),
  },
  {
    path: "/checkout/:orderId",
    name: "checkout",
    component: PaymentPage,
    meta: { requiresAuth: true },
    props: (route) => ({
      orderId: route.params.orderId,
      user: route.meta.user,
      authToken: route.meta.authToken,
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
    component: OrderManager,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  // Catch-all for 404
  {
    path: "/:catchAll(.*)",
    name: "NotFound",
    redirect: "/",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem("user"));
  const authToken = localStorage.getItem("authToken");

  to.meta.user = user;
  to.meta.authToken = authToken;

  if (to.meta.requiresAuth && !user) {
    next({ name: "login" });
  } else if (to.meta.requiresAdmin && (!user || user.role !== "admin")) {
    next({ name: "home" });
  } else {
    next();
  }
});

export default router;
