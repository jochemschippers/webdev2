<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      {{ isAdmin ? "Manage All Orders" : "My Orders" }}
    </h2>
    <Message :type="messageType" :message="message" />

    <div v-if="loading" class="text-center py-4">
      <LoadingSpinner />
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow overflow-hidden">
        <thead class="bg-gray-200">
          <tr>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              ID
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              User
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Total
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Status
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Order Date
            </th>
            <th
              v-if="isAdmin"
              class="py-3 px-4 text-left text-sm font-semibold text-gray-700"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="orders.length === 0 && !loading">
            <td
              :colspan="isAdmin ? '6' : '5'"
              class="py-3 px-4 text-center text-gray-500"
            >
              No orders available.
            </td>
          </tr>
          <tr v-for="order in orders" :key="order.id">
            <td class="py-3 px-4 text-sm text-gray-700">{{ order.id }}</td>
            <td class="py-3 px-4 text-sm text-gray-700">
              {{ order.username || "N/A" }} (ID: {{ order.user_id }})
            </td>
            <td class="py-3 px-4 text-sm text-gray-700">
              ${{ parseFloat(order.total_amount).toFixed(2) }}
            </td>
            <td class="py-3 px-4 text-sm text-gray-700">
              <template v-if="isAdmin">
                <select
                  v-model="order.status"
                  @change="handleUpdateStatus(order.id, order.status)"
                  class="form-select text-xs"
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
              </template>
              <template v-else>
                {{
                  order.status.charAt(0).toUpperCase() + order.status.slice(1)
                }}
              </template>
            </td>
            <td class="py-3 px-4 text-sm text-gray-700">
              {{ new Date(order.order_date).toLocaleDateString() }}
            </td>
            <td v-if="isAdmin" class="py-3 px-4 text-sm">
              <button
                @click="handleDeleteOrder(order.id)"
                class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-xs transition duration-150 ease-in-out"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
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

const fetchOrders = async () => {
  loading.value = true;
  try {
    const data = await apiCall("orders", "GET", null, props.authToken);
    orders.value = data;
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
</style>
