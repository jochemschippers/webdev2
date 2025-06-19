<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      Graphic Cards
    </h2>
    <Message :type="messageType" :message="message" />

    <div v-if="isAdmin" class="mb-6 text-center">
      <button
        @click="
          () => {
            currentProduct = null;
            isFormOpen = true;
          }
        "
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 ease-in-out"
      >
        Add New Graphic Card
      </button>
    </div>

    <GraphicCardForm
      v-if="isFormOpen && isAdmin"
      :product="currentProduct"
      @close="isFormOpen = false"
      @submit="handleFormSubmit"
      :auth-token="authToken"
    />

    <div v-if="loading" class="text-center py-4">
      <LoadingSpinner />
    </div>
    <div
      v-else
      class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
    >
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
        :is-admin="isAdmin"
        @edit="handleEdit"
        @delete="handleDelete"
        @add-to-cart="emit('add-to-cart', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from "vue";
import { apiCall } from "@/utils/api";
import { LoadingSpinner, Message } from "@/utils/components";
import GraphicCardForm from "./GraphicCardForm.vue";
import ProductCard from "./ProductCard.vue";

const props = defineProps({
  user: Object,
  authToken: String,
});

const emit = defineEmits(["add-to-cart"]);

const products = ref([]);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const isFormOpen = ref(false);
const currentProduct = ref(null);
const isAdmin = ref(props.user && props.user.role === "admin");

const fetchProducts = async () => {
  loading.value = true;
  try {
    const data = await apiCall("graphic-cards");
    products.value = data;
    messageType.value = "success";
    message.value = "Products loaded.";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load products.";
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProducts);
watch(
  () => props.user,
  (newUser) => {
    isAdmin.value = newUser && newUser.role === "admin";
  }
);

const handleEdit = (product) => {
  currentProduct.value = product;
  isFormOpen.value = true;
};

const handleDelete = async (id) => {
  if (!window.confirm("Are you sure you want to delete this graphic card?"))
    return;
  loading.value = true;
  try {
    await apiCall(`graphic-cards/${id}`, "DELETE", null, props.authToken);
    messageType.value = "success";
    message.value = "Graphic card deleted successfully.";
    fetchProducts();
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to delete graphic card.";
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
        `graphic-cards/${currentProduct.value.id}`,
        "PUT",
        formData,
        props.authToken
      );
      messageType.value = "success";
      message.value = "Graphic card updated successfully.";
    } else {
      await apiCall("graphic-cards", "POST", formData, props.authToken);
      messageType.value = "success";
      message.value = "Graphic card added successfully.";
    }
    isFormOpen.value = false;
    currentProduct.value = null;
    fetchProducts();
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to save graphic card.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
