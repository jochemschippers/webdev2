<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      Checkout & Payment
    </h2>
    <Message :type="messageType" :message="message" />

    <div v-if="loading" class="text-center py-8">
      <LoadingSpinner />
    </div>
    <div v-else>
      <div v-if="cart.length === 0" class="text-center text-gray-600 py-8">
        Your cart is empty. Please
        <router-link
          :to="{ name: 'products' }"
          class="text-blue-600 hover:underline"
          >add items to your cart</router-link
        >
        before proceeding to checkout.
      </div>
      <div v-else>
        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <h3 class="text-xl font-semibold mb-4 text-gray-800">
            Order Summary
          </h3>
          <ul class="divide-y divide-gray-200 mb-4">
            <li
              v-for="item in cart"
              :key="item.id"
              class="py-2 flex justify-between items-center"
            >
              <span class="text-gray-700"
                >{{ item.name }} x {{ item.quantity }}</span
              >
              <span class="font-semibold text-gray-800"
                >${{
                  (parseFloat(item.price) * item.quantity).toFixed(2)
                }}</span
              >
            </li>
          </ul>
          <div
            class="text-right text-2xl font-bold text-gray-800 border-t pt-4"
          >
            Total: ${{ total.toFixed(2) }}
          </div>
        </div>

        <!-- Payment Method Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <h3 class="text-xl font-semibold mb-4 text-gray-800">
            Payment Method
          </h3>
          <div class="flex flex-col space-y-3">
            <label class="inline-flex items-center">
              <input
                type="radio"
                class="form-radio text-blue-600 h-5 w-5"
                name="paymentMethod"
                value="credit_card"
                v-model="paymentMethod"
                checked
              />
              <span class="ml-2 text-gray-700">Credit Card</span>
            </label>
            <label class="inline-flex items-center">
              <input
                type="radio"
                class="form-radio text-blue-600 h-5 w-5"
                name="paymentMethod"
                value="paypal"
                v-model="paymentMethod"
              />
              <span class="ml-2 text-gray-700">PayPal</span>
            </label>
            <label class="inline-flex items-center">
              <input
                type="radio"
                class="form-radio text-blue-600 h-5 w-5"
                name="paymentMethod"
                value="bank_transfer"
                v-model="paymentMethod"
              />
              <span class="ml-2 text-gray-700">Bank Transfer</span>
            </label>
          </div>
        </div>

        <!-- Place Order Button -->
        <button
          @click="handlePlaceOrder"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md shadow-lg transition duration-150 ease-in-out text-xl"
          :disabled="isProcessingOrder || cart.length === 0"
        >
          {{
            isProcessingOrder ? "Processing Order..." : "Confirm & Place Order"
          }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from "vue";
import { useRouter } from "vue-router";
import { apiCall } from "@/utils/api";
import { LoadingSpinner, Message } from "@/utils/components";

const router = useRouter();

// Injected properties/functions from App.vue
const user = inject("user");
const authToken = inject("authToken");
const cart = inject("cart"); // The reactive cart state from App.vue
const handleAppPlaceOrderSuccess = inject("handleAppPlaceOrderSuccess"); // Function to clear cart in App.vue

const loading = ref(false); // Local loading for this component's data (if any async setup was needed)
const message = ref(null);
const messageType = ref("");
const isProcessingOrder = ref(false); // To disable button during order submission

const paymentMethod = ref("credit_card"); // Default payment method

const total = computed(() => {
  return cart.value.reduce(
    (sum, item) => sum + parseFloat(item.price) * item.quantity,
    0
  );
});

// Redirect if cart is empty on mount (user might directly navigate here)
onMounted(() => {
  if (!user.value) {
    router.push({ name: "login" }); // Redirect to login if not authenticated
  } else if (cart.value.length === 0) {
    messageType.value = "error";
    message.value = "Your cart is empty. Redirecting to products...";
    setTimeout(() => {
      router.push({ name: "products" }); // Redirect to products page
    }, 2000);
  }
});

const handlePlaceOrder = async () => {
  if (cart.value.length === 0) {
    messageType.value = "error";
    message.value = "Your cart is empty. Cannot place order.";
    return;
  }
  if (!user.value) {
    messageType.value = "error";
    message.value = "You must be logged in to place an order.";
    router.push({ name: "login" });
    return;
  }

  isProcessingOrder.value = true;
  message.value = null; // Clear previous messages

  // DEBUG LOG: Confirm authToken value before API call
  console.log(
    `PaymentPage Debug: authToken before apiCall: ${
      authToken.value ? authToken.value.substring(0, 10) + "..." : "null"
    }`
  );

  try {
    const orderItems = cart.value.map((item) => ({
      graphic_card_id: item.id,
      quantity: item.quantity,
      price_at_purchase: parseFloat(item.price), // Ensure price is float for backend
    }));

    // Call the backend API to create the order
    const response = await apiCall(
      "orders",
      "POST",
      { user_id: user.value.id, items: orderItems },
      authToken.value
    );

    messageType.value = "success";
    message.value = response.message || "Order placed successfully!";

    // Clear cart in App.vue after successful order
    handleAppPlaceOrderSuccess();

    // Redirect to the user's orders page after a short delay
    setTimeout(() => {
      router.push({ name: "orders" }); // Directly navigate to the orders list
    }, 1500); // Give a bit of time for the success message to be seen
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to place order. Please try again.";
    console.error("Order placement error:", error);
  } finally {
    isProcessingOrder.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
