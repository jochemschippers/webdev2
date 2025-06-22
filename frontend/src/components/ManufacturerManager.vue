<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      Manage Manufacturers
    </h2>
    <Message :type="messageType" :message="message" />

    <div class="mb-6 text-center">
      <button
        @click="
          () => {
            currentManufacturer = null;
            isFormOpen = true;
          }
        "
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 ease-in-out"
      >
        Add New Manufacturer
      </button>
    </div>

    <ManufacturerForm
      v-if="isFormOpen"
      :manufacturer="currentManufacturer"
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
              Name
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="manufacturers.length === 0 && !loading">
            <td colspan="3" class="py-3 px-4 text-center text-gray-500">
              No manufacturers available.
            </td>
          </tr>
          <tr v-for="m in manufacturers" :key="m.id">
            <td class="py-3 px-4 text-sm text-gray-700">{{ m.id }}</td>
            <td class="py-3 px-4 text-sm text-gray-700">{{ m.name }}</td>
            <td class="py-3 px-4 text-sm">
              <button
                @click="handleEdit(m)"
                class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded-md text-xs mr-2 transition duration-150 ease-in-out"
              >
                Edit
              </button>
              <button
                @click="handleDelete(m.id)"
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
import ManufacturerForm from "@/forms/ManufacturerForm.vue";

const props = defineProps({
  authToken: String,
});

const manufacturers = ref([]);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const isFormOpen = ref(false);
const currentManufacturer = ref(null);

const fetchManufacturers = async () => {
  loading.value = true;
  try {
    const data = await apiCall("manufacturers", "GET", null, props.authToken);
    manufacturers.value = data;
    messageType.value = "success";
    message.value = "Manufacturers loaded.";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load manufacturers.";
  } finally {
    loading.value = false;
  }
};

onMounted(fetchManufacturers);
watch(() => props.authToken, fetchManufacturers);

const handleEdit = (manufacturer) => {
  currentManufacturer.value = manufacturer;
  isFormOpen.value = true;
};

const handleDelete = async (id) => {
  if (!window.confirm("Are you sure you want to delete this manufacturer?"))
    return;
  loading.value = true;
  try {
    await apiCall(`manufacturers/${id}`, "DELETE", null, props.authToken);
    messageType.value = "success";
    message.value = "Manufacturer deleted successfully.";
    fetchManufacturers();
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to delete manufacturer.";
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
        `manufacturers/${currentManufacturer.value.id}`,
        "PUT",
        formData,
        props.authToken
      );
      messageType.value = "success";
      message.value = "Manufacturer updated successfully.";
    } else {
      await apiCall("manufacturers", "POST", formData, props.authToken);
      messageType.value = "success";
      message.value = "Manufacturer added successfully.";
    }
    isFormOpen.value = false;
    currentManufacturer.value = null;
    fetchManufacturers();
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to save manufacturer.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
