<template>
  <div id="app" class="min-h-screen bg-gray-100 font-inter">
    <!-- AppNavbar will now use router-link internally and not need currentPage -->
    <AppNavbar :user="user" @logout="handleLogout" />

    <main class="p-4">
      <!-- Vue Router will render the matched component here -->
      <router-view
        :user="user"
        :auth-token="authToken"
        @login-success="handleLoginSuccess"
        @register-success="handleLoginSuccess"
        @add-to-cart="handleAddToCart"
        @remove-from-cart="handleRemoveFromCart"
        @place-order="handlePlaceOrderSuccess"
        :cart="cart"
      ></router-view>
      <!-- The 'Access Denied' message will be handled by router guards or within components -->
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, provide } from "vue";
import { useRouter } from "vue-router"; // useRoute is no longer needed here
import AppNavbar from "./components/AppNavbar.vue";

const router = useRouter(); // Initialize router
// const route = useRoute(); // Removed as it was assigned but never used

// Global state for the application
const user = ref(null);
const authToken = ref(null);
const cart = ref([]);

// Provide user and authToken to descendant components via provide/inject
// This is an alternative to passing them as props through router-view
provide("user", user);
provide("authToken", authToken);
provide("cart", cart); // Provide cart for CartSummary if it's not a direct child of router-view

// Load user/token from localStorage on initial load
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
  router.push({ name: "products" }); // Use router.push for navigation
};

const handleLogout = () => {
  user.value = null;
  authToken.value = null;
  localStorage.removeItem("user");
  localStorage.removeItem("authToken");
  cart.value = [];
  router.push({ name: "home" }); // Use router.push for navigation
};

const handleAddToCart = (product) => {
  const existingItem = cart.value.find((item) => item.id === product.id);
  if (existingItem) {
    cart.value = cart.value.map((item) =>
      item.id === product.id ? { ...item, quantity: item.quantity + 1 } : item
    );
  } else {
    cart.value = [...cart.value, { ...product, quantity: 1 }];
  }
};

const handleRemoveFromCart = (productId) => {
  cart.value = cart.value.filter((item) => item.id !== productId);
};

const handlePlaceOrderSuccess = () => {
  cart.value = []; // Clear cart after successful order
  router.push({ name: "orders" }); // Navigate to orders page after successful order
};

// If a component needs access to the `router` or `route` objects directly,
// they can use `useRouter()` and `useRoute()` within their setup.
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
