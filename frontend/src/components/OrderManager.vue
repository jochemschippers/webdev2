<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      {{ isAdmin ? "Manage All Orders" : "My Orders" }}
    </h2>
    <Message :type="messageType" :message="message" />

    <div v-if="loading" class="text-center py-4">
      <LoadingSpinner />
    </div>
    <div v-else class="space-y-6">
      <div v-if="orders.length === 0" class="text-center text-gray-500 py-4">
        No orders available.
      </div>

      <div
        v-for="order in orders"
        :key="order.id"
        class="bg-white rounded-lg shadow-md overflow-hidden"
      >
        <div
          class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center cursor-pointer hover:bg-gray-50 transition duration-150 ease-in-out"
          @click="toggleOrderDetails(order.id)"
        >
          <div class="flex-1 mb-2 md:mb-0">
            <h3 class="text-lg font-semibold text-gray-800">
              Order #{{ order.id }}
            </h3>
            <p class="text-sm text-gray-600">
              User: {{ order.username || "N/A" }} (ID: {{ order.user_id }})
            </p>
            <p class="text-sm text-gray-600">
              Date:
              {{ new Date(order.order_date).toLocaleDateString() }}
            </p>
          </div>
          <div
            class="flex flex-col md:flex-row items-start md:items-center gap-2"
          >
            <span class="text-xl font-bold text-blue-600 mr-4">
              Total: ${{ parseFloat(order.total_amount).toFixed(2) }}
            </span>
            <div class="flex items-center gap-2">
              <template v-if="isAdmin">
                <select
                  v-model="order.status"
                  @change="handleUpdateStatus(order.id, order.status)"
                  class="form-select text-sm p-1 rounded-md border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                  @click.stop
                >
                  <option
                    v-for="status in [
                      'pending',
                      'processing',
                      'shipped',
                      'completed',
                      'cancelled',
                    ]"
                    :key="status"
                    :value="status"
                  >
                    {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                  </option>
                </select>
                <button
                  @click.stop="handleDeleteOrder(order.id)"
                  class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-xs transition duration-150 ease-in-out"
                >
                  Delete
                </button>
              </template>
              <template v-else>
                <span class="text-base font-medium text-gray-700"
                  >Status:
                  {{
                    order.status.charAt(0).toUpperCase() + order.status.slice(1)
                  }}</span
                >
              </template>
              <svg
                :class="{ 'rotate-180': expandedOrders[order.id] }"
                class="w-5 h-5 text-gray-600 transition-transform duration-200 ml-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                ></path>
              </svg>
            </div>
          </div>
        </div>

        <transition name="fade">
          <div
            v-if="expandedOrders[order.id]"
            class="p-4 bg-gray-50 border-t border-gray-200"
          >
            <h4 class="text-md font-semibold mb-3 text-gray-700">
              Order Items:
            </h4>
            <ul class="space-y-2">
              <li
                v-for="item in order.items"
                :key="item.id"
                class="flex justify-between items-center text-sm text-gray-700 bg-white p-3 rounded-md shadow-sm"
              >
                <span>{{ item.graphic_card_name }} x {{ item.quantity }}</span>
                <span class="font-medium"
                  >${{ parseFloat(item.price_at_purchase).toFixed(2) }}</span
                >
              </li>
            </ul>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, defineProps } from "vue";
import { apiCall } from "@/utils/api";
import { LoadingSpinner, Message } from "@/utils/components";

const props = defineProps({
  user: Object,
  authToken: String,
});

const orders = ref([]);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const isAdmin = ref(props.user && props.user.role === "admin");
const expandedOrders = ref({}); // To manage expanded/collapsed state of each order

const fetchOrders = async () => {
  loading.value = true;
  try {
    const data = await apiCall("orders", "GET", null, props.authToken);
    orders.value = data;
    // Initialize all orders as collapsed
    data.forEach((order) => {
      expandedOrders.value[order.id] = false;
    });
    messageType.value = "success";
    message.value = "Orders loaded.";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load orders.";
  } finally {
    loading.value = false;
  }
};

onMounted(fetchOrders);
watch(
  () => props.user,
  (newUser) => {
    isAdmin.value = newUser && newUser.role === "admin";
    fetchOrders(); // Refetch if user changes (e.g., login/logout)
  }
);
watch(() => props.authToken, fetchOrders);

const toggleOrderDetails = (orderId) => {
  expandedOrders.value[orderId] = !expandedOrders.value[orderId];
};

const handleUpdateStatus = async (orderId, newStatus) => {
  if (!isAdmin.value) {
    message.value = "You do not have permission to update order status.";
    messageType.value = "error";
    return;
  }
  loading.value = true;
  message.value = null;
  try {
    await apiCall(
      `orders/${orderId}`,
      "PUT",
      { status: newStatus },
      props.authToken
    );
    messageType.value = "success";
    message.value = `Order ${orderId} status updated to ${newStatus}.`;
    fetchOrders(); // Refresh list
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to update order status.";
  } finally {
    loading.value = false;
  }
};

const handleDeleteOrder = async (orderId) => {
  if (!isAdmin.value) {
    message.value = "You do not have permission to delete orders.";
    messageType.value = "error";
    return;
  }
  if (
    !window.confirm(
      "Are you sure you want to delete this order? This action cannot be undone."
    )
  )
    return;
  loading.value = true;
  message.value = null;
  try {
    await apiCall(`orders/${orderId}`, "DELETE", null, props.authToken);
    messageType.value = "success";
    message.value = `Order ${orderId} deleted successfully.`;
    fetchOrders(); // Refresh list
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to delete order.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
