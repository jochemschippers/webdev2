<template>
  <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
    <h3 class="text-2xl font-bold mb-4 text-gray-800 text-center">
      Your Cart
      <span v-if="props.cart.length > 0" class="text-blue-600"
        >({{ props.cart.length }})</span
      >
    </h3>

    <Message :type="messageType" :message="message" />

    <div v-if="props.cart.length === 0" class="text-center text-gray-500 py-4">
      Your cart is empty.
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="item in props.cart"
        :key="item.id"
        class="flex items-center justify-between border-b pb-2"
      >
        <div class="flex flex-1 items-center space-x-3">
          <img
            :src="'http://localhost' + (item.image_url || '/placeholder.png')"
            :alt="item.name"
            class="w-12 h-12 object-contain rounded-md"
            @error="
              (e) => {
                e.target.onerror = null;
                e.target.src =
                  'https://placehold.co/50x50/E2E8F0/1A202C?text=GPU';
              }
            "
          />
          <div class="flex-1">
            <p class="font-semibold text-gray-700">{{ item.name }}</p>
            <p class="text-sm text-gray-500">
              \${{ parseFloat(item.price).toFixed(2) }}
            </p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <!-- Quantity Controls -->
          <div
            class="flex items-center border border-gray-300 rounded-md p-0.5"
          >
            <button
              @click="updateQuantity(item.id, item.quantity - 1)"
              class="px-1.5 py-0.5 text-gray-600 hover:bg-gray-200 rounded-l-md"
              :disabled="item.quantity <= 1"
            >
              -
            </button>
            <input
              type="number"
              :value="item.quantity"
              @input="handleQuantityInput(item.id, $event.target.value)"
              min="1"
              class="w-10 text-center border-x border-gray-300 text-gray-700 text-sm focus:outline-none focus:ring-0 focus:border-transparent"
            />
            <button
              @click="updateQuantity(item.id, item.quantity + 1)"
              class="px-1.5 py-0.5 text-gray-600 hover:bg-gray-200 rounded-r-md"
              :disabled="item.quantity >= item.stock"
            >
              +
            </button>
          </div>

          <span class="font-semibold text-gray-800 ml-2 mr-2 w-16 text-right"
            >\${{ (parseFloat(item.price) * item.quantity).toFixed(2) }}</span
          >
          <button
            @click="emit('remove-from-cart', item.id)"
            class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-full text-xs"
            aria-label="Remove item"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>

      <div class="pt-4 border-t-2 border-gray-200">
        <div class="flex justify-between font-bold text-lg text-gray-800">
          <span>Total:</span>
          <span>\${{ cartTotal.toFixed(2) }}</span>
        </div>
      </div>

      <button
        @click="placeOrder"
        :disabled="props.cart.length === 0 || loading || !props.user"
        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mt-4 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="loading">Placing Order...</span>
        <span v-else>{{
          props.user ? "Place Order" : "Login to Place Order"
        }}</span>
      </button>

      <p v-if="!props.user" class="text-center text-sm text-red-500 mt-2">
        Please log in to place an order.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, defineProps, defineEmits } from "vue";
import { apiCall } from "@/utils/api";
import { Message } from "@/utils/components";
import { useRouter } from "vue-router";

const props = defineProps({
  cart: {
    type: Array,
    required: true,
  },
  user: Object,
  authToken: String,
});

const emit = defineEmits([
  "remove-from-cart",
  "place-order",
  "update-quantity",
]);

const router = useRouter();

const loading = ref(false);
const message = ref(null);
const messageType = ref("");

const cartTotal = computed(() => {
  return props.cart.reduce(
    (total, item) => total + item.price * item.quantity,
    0
  );
});

const updateQuantity = (itemId, newQuantity) => {
  const item = props.cart.find((i) => i.id === itemId);
  if (!item) return;

  let validatedQuantity = Math.max(1, newQuantity);
  if (item.stock !== undefined && validatedQuantity > item.stock) {
    validatedQuantity = item.stock;
    messageType.value = "error";
    message.value = `Cannot add more than available stock (${item.stock}) for ${item.name}.`;
  } else {
    message.value = null;
  }

  if (validatedQuantity !== item.quantity) {
    // Corrected: Emit an object instead of separate arguments
    emit("update-quantity", {
      productId: itemId,
      newQuantity: validatedQuantity,
    });
  }
};

const handleQuantityInput = (itemId, value) => {
  let newQuantity = parseInt(value, 10);
  if (isNaN(newQuantity) || newQuantity < 1) {
    newQuantity = 1;
  }
  updateQuantity(itemId, newQuantity);
};

const placeOrder = async () => {
  if (!props.user) {
    messageType.value = "error";
    message.value = "You must be logged in to place an order.";
    return;
  }
  if (props.cart.length === 0) {
    messageType.value = "error";
    message.value = "Your cart is empty.";
    return;
  }

  loading.value = true;
  message.value = null;

  try {
    const orderItems = props.cart.map((item) => ({
      graphic_card_id: item.id,
      quantity: item.quantity,
      price_at_purchase: item.price,
    }));

    const response = await apiCall(
      "orders",
      "POST",
      {
        user_id: props.user.id,
        total_amount: cartTotal.value,
        status: "pending", // Always set to pending initially
        items: orderItems,
      },
      props.authToken
    );

    if (response.order && response.order.id) {
      messageType.value = "success";
      message.value = "Order placed successfully! Redirecting to payment...";

      const orderId = response.order.id;

      // Use a slight delay before redirecting to allow message to be seen
      setTimeout(() => {
        router.push({ name: "checkout", params: { orderId: orderId } });
      }, 1000); // 1-second delay

      emit("place-order"); // Emit to parent to clear the cart
    } else {
      messageType.value = "error";
      message.value =
        "Order placed, but failed to get order ID for payment. Check server logs.";
      console.error("API response missing order ID:", response);
    }
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to place order.";
    console.error("Error during order placement:", error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
