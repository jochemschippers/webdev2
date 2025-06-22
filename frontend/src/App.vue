<template>
  <div id="app" class="min-h-screen bg-gray-100 font-inter flex flex-col">
    <AppNavbar :user="user" @logout="handleLogout" />

    <main class="p-4 flex-grow">
      <router-view
        :user="user"
        :auth-token="authToken"
        @login-success="handleLoginSuccess"
        @register-success="handleLoginSuccess"
        @add-to-cart="handleAddToCart"
        @remove-from-cart="handleRemoveFromCart"
        @update-cart-quantity="handleUpdateCartQuantity"
        :cart="cart"
        @order-completed="handlePlaceOrderSuccess"
        @navigate="handleNavigation"
      ></router-view>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, provide } from "vue";
import { useRouter } from "vue-router";
import AppNavbar from "./components/AppNavbar.vue";
import AppFooter from "./components/AppFooter.vue"; // NEW: Import AppFooter

const router = useRouter();

const user = ref(null);
const authToken = ref(null);
const cart = ref([]);

// Provide user and authToken to descendant components via provide/inject
provide("user", user);
provide("authToken", authToken);
provide("cart", cart); // Provide cart for CartSummary if it's not a direct child of router-view

// Define the main handleAddToCart function that will be provided
const handleAddToCart = (product, quantity = 1) => {
  const existingItem = cart.value.find((item) => item.id === product.id);
  if (existingItem) {
    // Corrected: Assign the new array back to cart.value
    cart.value = cart.value.map((item) =>
      item.id === product.id
        ? { ...item, quantity: item.quantity + quantity }
        : item
    );
  } else {
    // Corrected: Assign the new array back to cart.value
    // Also include stock information in cart item to validate quantity changes
    cart.value = [
      ...cart.value,
      { ...product, quantity: quantity, stock: product.stock },
    ];
  }
};

// Handle quantity update from CartSummary
const handleUpdateCartQuantity = (productId, newQuantity) => {
  cart.value = cart.value.map((item) =>
    item.id === productId ? { ...item, quantity: newQuantity } : item
  );
};

// Provide the handleAddToCart and handleUpdateCartQuantity functions for injection in other components
provide("handleAppAddToCart", handleAddToCart);
provide("handleAppUpdateCartQuantity", handleUpdateCartQuantity);

onMounted(() => {
  const storedUser = localStorage.getItem("user");
  const storedToken = localStorage.getItem("authToken");
  if (storedUser && storedToken) {
    user.value = JSON.parse(storedUser);
    authToken.value = storedToken;
  }
});

const handleLoginSuccess = (userData, token) => {
  user.value = userData;
  authToken.value = token;
  localStorage.setItem("user", JSON.stringify(userData));
  localStorage.setItem("authToken", token);
  router.push({ name: "products" });
};

const handleLogout = () => {
  user.value = null;
  authToken.value = null;
  localStorage.removeItem("user");
  localStorage.removeItem("authToken");
  cart.value = []; // Clear cart on logout
  router.push({ name: "home" });
};

const handleRemoveFromCart = (productId) => {
  cart.value = cart.value.filter((item) => item.id !== productId);
};

// This function is now called when the order is successfully finalized on the PaymentPage
const handlePlaceOrderSuccess = () => {
  cart.value = []; // Clear cart only after payment is confirmed/order is finalized
  // The redirection to 'orders' page is now handled within PaymentPage.vue
};

// NEW: Handler for custom navigation event from child components (like AdminPanel)
const handleNavigation = (routeName) => {
  router.push({ name: routeName });
};
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
body {
  font-family: "Inter", sans-serif;
}
.form-input,
.form-textarea,
.form-select {
  @apply shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500;
}
.form-textarea {
  @apply h-24 resize-y;
}
/* Custom styles for better aesthetics */
.shadow-md {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.shadow-lg {
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}
.shadow-xl {
  box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
}
.rounded-lg {
  border-radius: 0.5rem;
}
.rounded-md {
  border-radius: 0.375rem;
}
</style>
