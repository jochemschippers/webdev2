<template>
  <div class="container mx-auto p-4 max-w-2xl bg-white rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      Checkout & Payment
    </h2>

    <Message :type="messageType" :message="message" />

    <div v-if="loadingOrder" class="text-center py-8">
      <LoadingSpinner />
      <p class="text-gray-600 mt-4">Loading order details...</p>
    </div>

    <div v-else-if="!order || !order.id" class="text-center text-red-500 py-8">
      Order details not found. Please go back to your cart.
    </div>

    <div v-else>
      <!-- Order Summary Section -->
      <div class="mb-8 border rounded-lg p-6 bg-gray-50">
        <h3 class="text-xl font-semibold mb-4 text-gray-700">
          Order Summary (Order #{{ order.id }})
        </h3>
        <ul class="divide-y divide-gray-200 mb-4">
          <li
            v-for="item in order.items"
            :key="item.id"
            class="py-2 flex justify-between items-center"
          >
            <span class="text-gray-700"
              >{{ item.graphic_card_name }} x {{ item.quantity }}</span
            >
            <span class="font-semibold text-gray-800"
              >${{ parseFloat(item.price_at_purchase).toFixed(2) }}</span
            >
          </li>
        </ul>
        <div class="text-right text-xl font-bold text-gray-800 border-t pt-4">
          Total: ${{ parseFloat(order.total_amount).toFixed(2) }}
        </div>
      </div>

      <!-- Shipping Address Section -->
      <div class="mb-8 border rounded-lg p-6 bg-white">
        <h3 class="text-xl font-semibold mb-4 text-gray-700">
          Shipping Address
        </h3>
        <form class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="fullName"
              >Full Name</label
            >
            <input
              type="text"
              id="fullName"
              v-model="shipping.fullName"
              class="form-input"
              required
            />
          </div>
          <div>
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="addressLine1"
              >Address Line 1</label
            >
            <input
              type="text"
              id="addressLine1"
              v-model="shipping.addressLine1"
              class="form-input"
              required
            />
          </div>
          <div>
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="addressLine2"
              >Address Line 2 (Optional)</label
            >
            <input
              type="text"
              id="addressLine2"
              v-model="shipping.addressLine2"
              class="form-input"
            />
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="city"
              >City</label
            >
            <input
              type="text"
              id="city"
              v-model="shipping.city"
              class="form-input"
              required
            />
          </div>
          <div>
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="state"
              >State/Province</label
            >
            <input
              type="text"
              id="state"
              v-model="shipping.state"
              class="form-input"
              required
            />
          </div>
          <div>
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="zipCode"
              >Zip/Postal Code</label
            >
            <input
              type="text"
              id="zipCode"
              v-model="shipping.zipCode"
              class="form-input"
              required
            />
          </div>
          <div class="md:col-span-2">
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="country"
              >Country</label
            >
            <input
              type="text"
              id="country"
              v-model="shipping.country"
              class="form-input"
              required
            />
          </div>
        </form>
      </div>

      <!-- Payment Details Section -->
      <div class="mb-8 border rounded-lg p-6 bg-white">
        <h3 class="text-xl font-semibold mb-4 text-gray-700">
          Payment Details
        </h3>
        <form>
          <div class="mb-4">
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="cardNumber"
              >Card Number</label
            >
            <input
              type="text"
              id="cardNumber"
              v-model="payment.cardNumber"
              class="form-input"
              placeholder="**** **** **** ****"
              required
            />
          </div>
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="expiryDate"
                >Expiry Date</label
              >
              <input
                type="text"
                id="expiryDate"
                v-model="payment.expiryDate"
                class="form-input"
                placeholder="MM/YY"
                required
              />
            </div>
            <div>
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="cvv"
                >CVV</label
              >
              <input
                type="text"
                id="cvv"
                v-model="payment.cvv"
                class="form-input"
                placeholder="***"
                required
              />
            </div>
          </div>
          <div class="mb-4">
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="cardName"
              >Name on Card</label
            >
            <input
              type="text"
              id="cardName"
              v-model="payment.cardName"
              class="form-input"
              required
            />
          </div>
        </form>
      </div>

      <!-- Place Order Button -->
      <div class="text-center">
        <button
          @click="finalizeOrder"
          :disabled="processingPayment || !isFormValid"
          class="w-full md:w-1/2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md shadow-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="processingPayment">Processing Payment...</span>
          <span v-else>Confirm & Pay</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";
import { apiCall } from "@/utils/api";
import { Message, LoadingSpinner } from "@/utils/components";
import { useRouter, useRoute } from "vue-router";

const props = defineProps({
  user: Object,
  authToken: String,
});

const emit = defineEmits(["order-completed"]);

const router = useRouter();
const route = useRoute();

const order = ref(null);
const loadingOrder = ref(true);
const processingPayment = ref(false);
const message = ref(null);
const messageType = ref("");

// Reactive state for shipping and payment details
const shipping = ref({
  fullName: props.user ? props.user.username : "",
  addressLine1: "",
  addressLine2: "",
  city: "",
  state: "",
  zipCode: "",
  country: "",
});

const payment = ref({
  cardNumber: "",
  expiryDate: "",
  cvv: "",
  cardName: props.user ? props.user.username : "",
});

// Computed property to check if all required fields are filled
const isFormValid = computed(() => {
  return (
    !!shipping.value.fullName &&
    !!shipping.value.addressLine1 &&
    !!shipping.value.city &&
    !!shipping.value.state &&
    !!shipping.value.zipCode &&
    !!shipping.value.country &&
    !!payment.value.cardNumber &&
    !!payment.value.expiryDate &&
    !!payment.value.cvv &&
    !!payment.value.cardName
  );
});

// Function to fetch order details based on orderId from route params
const fetchOrderDetails = async (orderId) => {
  console.log("Fetching order details for orderId:", orderId);
  if (!orderId) {
    messageType.value = "error";
    message.value = "No order ID provided for payment.";
    loadingOrder.value = false;
    return;
  }
  loadingOrder.value = true;
  message.value = null;
  try {
    const response = await apiCall(
      `orders/${orderId}`,
      "GET",
      null,
      props.authToken
    );
    order.value = response.order;
    console.log("Order details fetched:", order.value);
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load order details.";
    order.value = null;
    console.error("Error fetching order details:", error);
  } finally {
    loadingOrder.value = false;
  }
};

// Finalize order (simulate payment success and update order status)
const finalizeOrder = async () => {
  console.log("finalizeOrder method called.");
  console.log("Current user:", props.user);
  console.log("Current authToken:", props.authToken);
  console.log("Current order:", order.value);
  console.log("Shipping data:", shipping.value);
  console.log("Payment data:", payment.value);
  console.log("isFormValid:", isFormValid.value);

  if (!props.user || !props.authToken) {
    messageType.value = "error";
    message.value = "You must be logged in to finalize the order.";
    console.log("Validation failed: User not logged in.");
    return;
  }
  if (!order.value || !order.value.id) {
    messageType.value = "error";
    message.value = "No order to process.";
    console.log("Validation failed: No order found.");
    return;
  }

  // Basic validation for shipping and payment (can be more robust)
  if (!isFormValid.value) {
    messageType.value = "error";
    message.value = "Please fill in all required shipping and payment details.";
    console.log("Validation failed: Form not valid.");
    return;
  }

  processingPayment.value = true;
  message.value = null;
  console.log("Starting payment processing...");

  try {
    // Simulate payment processing
    console.log("Simulating API delay for payment...");
    await new Promise((resolve) => setTimeout(resolve, 1500));
    console.log("Payment simulation complete.");

    // Update order status in the backend
    console.log(
      "Attempting to update order status to 'processing' for order ID:",
      order.value.id
    );
    await apiCall(
      `orders/${order.value.id}`,
      "PUT",
      { status: "processing" }, // Change status to 'processing' or 'completed'
      props.authToken
    );
    console.log("Order status updated successfully.");

    messageType.value = "success";
    message.value = "Order successfully placed and payment processed!";
    emit("order-completed"); // Inform parent (App.vue) that order is finalized

    // Redirect to orders page or a success page after a short delay
    console.log(
      "Payment successful, redirecting to orders page in 2 seconds..."
    );
    setTimeout(() => {
      router.push({ name: "orders" });
    }, 2000);
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Payment failed or order update failed.";
    console.error("Error during payment finalization or order update:", error);
  } finally {
    processingPayment.value = false;
    console.log(
      "Payment processing finished. loadingOrder:",
      loadingOrder.value,
      "processingPayment:",
      processingPayment.value
    ); // CORRECTED
  }
};

// Watch for changes in route params (specifically orderId)
watch(
  () => route.params.orderId,
  (newOrderId) => {
    console.log("route.params.orderId changed to:", newOrderId);
    if (newOrderId) {
      fetchOrderDetails(newOrderId);
    }
  },
  { immediate: true }
);
</script>

<style scoped>
/* Add any specific styles for PaymentPage here */
</style>
