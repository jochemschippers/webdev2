<template>
  <div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-xl font-bold mb-4 text-gray-800">Your Cart</h3>
    <Message :type="messageType" :message="message" />
    <div v-if="cart.length === 0">
      <p class="text-gray-600">Your cart is empty.</p>
      <router-link
        :to="{ name: 'products' }"
        class="text-blue-600 hover:underline mt-4 inline-block"
      >
        Continue Shopping
      </router-link>
    </div>
    <div v-else>
      <ul class="mb-4 divide-y divide-gray-200">
        <li
          v-for="item in cart"
          :key="item.id"
          class="py-2 flex justify-between items-center"
        >
          <span class="text-gray-700">{{ item.name }}</span>
          <div class="flex items-center">
            <input
              type="number"
              min="1"
              :max="item.stock"
              v-model.number="item.quantity"
              @change="updateQuantity(item.id, item.quantity)"
              class="w-16 text-center border border-gray-300 rounded-md py-1 px-2 mr-2"
            />
            <span class="font-semibold text-gray-800 mr-4"
              >${{ (parseFloat(item.price) * item.quantity).toFixed(2) }}</span
            >
            <button
              @click="emit('remove-from-cart', item.id)"
              class="bg-red-500 hover:bg-red-600 text-white text-xs py-1 px-2 rounded-md transition duration-150 ease-in-out"
            >
              Remove
            </button>
          </div>
        </li>
      </ul>
      <div class="text-right text-lg font-bold text-gray-800 mb-4">
        Total: ${{ total.toFixed(2) }}
      </div>
      <button
        @click="proceedToCheckout"
        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out"
        :disabled="cart.length === 0 || !user"
      >
        {{ !user ? "Login to Proceed to Checkout" : "Proceed to Checkout" }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, computed } from "vue";
import { useRouter } from "vue-router"; // Import useRouter
import { Message } from "@/utils/components"; // Only Message needed directly in this component

const props = defineProps({
  cart: {
    type: Array,
    required: true,
  },
  user: Object, // The authenticated user object
  authToken: String, // Auth token
});

// Define emits for communicating with parent (CartPage.vue)
const emit = defineEmits(["remove-from-cart", "update-cart-quantity"]);

const router = useRouter(); // Initialize router

const message = ref(null);
const messageType = ref("");

const total = computed(() => {
  return props.cart.reduce(
    (sum, item) => sum + parseFloat(item.price) * item.quantity,
    0
  );
});

// Function to update quantity in the parent App.vue
const updateQuantity = (productId, newQuantity) => {
  // Ensure quantity is at least 1 and not more than stock
  const item = props.cart.find((i) => i.id === productId);
  if (item) {
    if (newQuantity < 1) newQuantity = 1;
    if (newQuantity > item.stock) newQuantity = item.stock;
    emit("update-cart-quantity", { productId, newQuantity });
  }
};

// NEW: Function to navigate to the Payment Page
const proceedToCheckout = () => {
  if (!props.user) {
    messageType.value = "error";
    message.value = "You must be logged in to proceed to checkout.";
    return;
  }
  if (props.cart.length === 0) {
    messageType.value = "error";
    message.value = "Your cart is empty. Please add items before checking out.";
    return;
  }

  // Navigate to the payment page. No order ID is passed here.
  router.push({ name: "payment" });
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
