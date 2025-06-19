<template>
  <div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-xl font-bold mb-4 text-gray-800">Your Cart</h3>
    <Message :type="messageType" :message="message" />
    <template v-if="cart.length === 0">
      <p class="text-gray-600">Your cart is empty.</p>
    </template>
    <template v-else>
      <ul class="mb-4 divide-y divide-gray-200">
        <li
          v-for="item in cart"
          :key="item.id"
          class="py-2 flex justify-between items-center"
        >
          <span class="text-gray-700"
            >{{ item.name }} x {{ item.quantity }}</span
          >
          <div class="flex items-center">
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
        @click="handleOrder"
        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out"
        :disabled="loading || !user"
      >
        {{
          loading
            ? "Placing Order..."
            : user
            ? "Place Order"
            : "Login to Place Order"
        }}
      </button>
    </template>
  </div>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from "vue";
import { apiCall } from "@/utils/api";
import { Message } from "@/utils/components";

const props = defineProps({
  cart: {
    type: Array,
    required: true,
  },
  user: Object, // Can be null
  authToken: String,
});

const emit = defineEmits(["remove-from-cart", "place-order"]);

const loading = ref(false);
const message = ref(null);
const messageType = ref("");

const total = ref(0);

watch(
  () => props.cart,
  (newCart) => {
    total.value = newCart.reduce(
      (sum, item) => sum + parseFloat(item.price) * item.quantity,
      0
    );
  },
  { immediate: true }
);

const handleOrder = async () => {
  if (!props.user) {
    messageType.value = "error";
    message.value = "Please log in to place an order.";
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
      price_at_purchase: parseFloat(item.price),
    }));

    await apiCall(
      "orders",
      "POST",
      { user_id: props.user.id, items: orderItems },
      props.authToken
    );
    messageType.value = "success";
    message.value = "Order placed successfully!";
    emit("place-order");
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to place order.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
