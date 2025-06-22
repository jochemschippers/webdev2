<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      Manage Brands
    </h2>
    <Message :type="messageType" :message="message" />

    <div class="mb-6 text-center">
      <button
        @click="
          () => {
            currentBrand = null;
            isFormOpen = true;
          }
        "
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 ease-in-out"
      >
        Add New Brand
      </button>
    </div>

    <BrandForm
      v-if="isFormOpen"
      :brand="currentBrand"
      :manufacturers="manufacturers"
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
              Manufacturer
            </th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="brands.length === 0 && !loading">
            <td colspan="4" class="py-3 px-4 text-center text-gray-500">
              No brands available.
            </td>
          </tr>
          <tr v-for="b in brands" :key="b.id">
            <td class="py-3 px-4 text-sm text-gray-700">{{ b.id }}</td>
            <td class="py-3 px-4 text-sm text-gray-700">{{ b.name }}</td>
            <td class="py-3 px-4 text-sm text-gray-700">
              {{ b.manufacturer_name || "N/A" }}
            </td>
            <td class="py-3 px-4 text-sm">
              <button
                @click="handleEdit(b)"
                class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded-md text-xs mr-2 transition duration-150 ease-in-out"
              >
                Edit
              </button>
              <button
                @click="handleDelete(b.id)"
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
import BrandForm from "@/forms/BrandForm.vue"; // Will be created next

const props = defineProps({
  authToken: String,
});

const brands = ref([]);
const manufacturers = ref([]); // For dropdown in form
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const isFormOpen = ref(false);
const currentBrand = ref(null); // For edit form

const fetchBrandsAndManufacturers = async () => {
  loading.value = true;
  try {
    const brandsData = await apiCall("brands", "GET", null, props.authToken);
    const manufacturersData = await apiCall(
      "manufacturers",
      "GET",
      null,
      props.authToken
    );
    brands.value = brandsData;
    manufacturers.value = manufacturersData;
    messageType.value = "success";
    message.value = "Brands and manufacturers loaded.";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load brands or manufacturers.";
  } finally {
    loading.value = false;
  }
};

onMounted(fetchBrandsAndManufacturers);
watch(() => props.authToken, fetchBrandsAndManufacturers);

const handleEdit = (brand) => {
  currentBrand.value = brand;
  isFormOpen.value = true;
};

const handleDelete = async (id) => {
  if (!window.confirm("Are you sure you want to delete this brand?")) return;
  loading.value = true;
  try {
    await apiCall(`brands/${id}`, "DELETE", null, props.authToken);
    messageType.value = "success";
    message.value = "Brand deleted successfully.";
    fetchBrandsAndManufacturers();
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to delete brand.";
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
        `brands/${currentBrand.value.id}`,
        "PUT",
        formData,
        props.authToken
      );
      messageType.value = "success";
      message.value = "Brand updated successfully.";
    } else {
      await apiCall("brands", "POST", formData, props.authToken);
      messageType.value = "success";
      message.value = "Brand added successfully.";
    }
    isFormOpen.value = false;
    currentBrand.value = null;
    fetchBrandsAndManufacturers();
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to save brand.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
