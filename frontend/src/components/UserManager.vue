<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      Manage Users
    </h2>
    <Message :type="messageType" :message="message" />

    <div class="mb-6 text-center">
      <button
        @click="
          () => {
            currentUser = null;
            isFormOpen = true;
          }
        "
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 ease-in-out"
      >
        Add New User
      </button>
    </div>

    <UserForm
      v-if="isFormOpen"
      :user="currentUser"
      :auth-token="authToken"
      @close="isFormOpen = false"
      @submit="handleFormSubmit"
    />

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
              Username
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Email
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Role
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Created At
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Updated At
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="users.length === 0 && !loading">
            <td colspan="7" class="py-3 px-4 text-center text-gray-500">
              No users available.
            </td>
          </tr>
          <tr v-for="u in users" :key="u.id">
            <td class="py-3 px-4 text-sm text-gray-700">{{ u.id }}</td>
            <td class="py-3 px-4 text-sm text-gray-700">{{ u.username }}</td>
            <td class="py-3 px-4 text-sm text-gray-700">{{ u.email }}</td>
            <td class="py-3 px-4 text-sm text-gray-700">{{ u.role }}</td>
            <td class="py-3 px-4 text-sm text-gray-700">
              {{ new Date(u.created_at).toLocaleDateString() }}
            </td>
            <td class="py-3 px-4 text-sm text-gray-700">
              {{
                u.updated_at
                  ? new Date(u.updated_at).toLocaleDateString()
                  : "N/A"
              }}
            </td>
            <td class="py-3 px-4 text-sm">
              <button
                @click="handleEdit(u)"
                class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded-md text-xs mr-2 transition duration-150 ease-in-out"
              >
                Edit
              </button>
              <button
                @click="handleDelete(u.id)"
                class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-xs transition duration-150 ease-in-out"
                :disabled="u.id === 1"
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
import UserForm from "./UserForm.vue"; // This component will be created next

const props = defineProps({
  authToken: String,
});

const users = ref([]);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const isFormOpen = ref(false);
const currentUser = ref(null); // To hold user data when editing

const fetchUsers = async () => {
  loading.value = true;
  try {
    const data = await apiCall("users", "GET", null, props.authToken);
    users.value = data;
    messageType.value = "success";
    message.value = "Users loaded.";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load users.";
  } finally {
    loading.value = false;
  }
};

onMounted(fetchUsers);
watch(() => props.authToken, fetchUsers);

const handleEdit = (user) => {
  currentUser.value = user;
  isFormOpen.value = true;
};

const handleDelete = async (id) => {
  if (id === 1) {
    // Prevent deleting the first admin user
    messageType.value = "error";
    message.value = "Cannot delete this default admin user.";
    return;
  }
  if (!window.confirm("Are you sure you want to delete this user?")) return;

  loading.value = true;
  try {
    await apiCall(`users/${id}`, "DELETE", null, props.authToken);
    messageType.value = "success";
    message.value = "User deleted successfully.";
    fetchUsers(); // Refresh list
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to delete user.";
  } finally {
    loading.value = false;
  }
};

const handleFormSubmit = async (formData, isEdit) => {
  loading.value = true;
  message.value = null;
  try {
    if (isEdit) {
      await apiCall(
        `users/${currentUser.value.id}`,
        "PUT",
        formData,
        props.authToken
      );
      messageType.value = "success";
      message.value = "User updated successfully.";
    } else {
      await apiCall("register", "POST", formData, props.authToken); // Use register endpoint for new users
      messageType.value = "success";
      message.value = "User added successfully.";
    }
    isFormOpen.value = false;
    currentUser.value = null;
    fetchUsers(); // Refresh the list of users
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to save user.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
