<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      Graphic Cards
    </h2>
    <Message :type="messageType" :message="message" />

    <div v-if="user && user.role === 'admin'" class="mb-6 text-center">
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
      v-if="isFormOpen && user && user.role === 'admin'"
      :product="currentProduct"
      :auth-token="authToken"
      @close="isFormOpen = false"
      @submit="handleFormSubmit"
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
        :isAdmin="user && user.role === 'admin'"
        @edit="handleEdit"
        @delete="handleDelete"
        @add-to-cart="handleAddToCartWithNotification"
      />
    </div>

    <!-- Add to Cart Notification (moved here as ProductListPage is the common parent for ProductCards) -->
    <AddToCartNotification
      :is-visible="showAddToCartNotification"
      :product-name="notificationProductName"
      :quantity-added="notificationQuantityAdded"
      @close="showAddToCartNotification = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, defineProps, inject, provide } from "vue";
import { apiCall } from "@/utils/api";
import { LoadingSpinner, Message } from "@/utils/components";
import GraphicCardForm from "@/forms/GraphicCardForm.vue";
import ProductCard from "@/cards/ProductCard.vue";
import AddToCartNotification from "@/components/AddToCartNotification.vue"; // FIXED: Changed to double quotes

const props = defineProps({
  user: Object,
  authToken: String,
});

const products = ref([]);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const isFormOpen = ref(false);
const currentProduct = ref(null); // For edit form

// State for AddToCartNotification
const showAddToCartNotification = ref(false);
const notificationProductName = ref("");
const notificationQuantityAdded = ref(0);
let notificationTimeout = null;

// Inject the handleAppAddToCart function from App.vue
const handleAppAddToCart = inject("handleAppAddToCart");

// This function will be called by ProductCard when "Add to Cart" is clicked
const handleAddToCartWithNotification = (product, quantity = 1) => {
  handleAppAddToCart(product, quantity);

  // Show notification
  notificationProductName.value = product.name;
  notificationQuantityAdded.value = quantity;
  showAddToCartNotification.value = true;

  // Clear any existing timeout and set a new one
  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
  }
  notificationTimeout = setTimeout(() => {
    showAddToCartNotification.value = false;
  }, 3000); // Notification visible for 3 seconds
};

// Provide this wrapped function to children components (ProductCard)
// This overrides the 'handleAppAddToCart' for this component's subtree
provide("handleAppAddToCart", handleAddToCartWithNotification); // FIXED: Removed trailing newline

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
watch(() => props.authToken, fetchProducts);

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
    fetchProducts(); // Refresh list
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
        "POST", // Use POST for FormData even for PUT logic, PHP backend needs to handle _method for PUT
        formData,
        props.authToken
      );
      messageType.value = "success";
      message.value = "Graphic card updated successfully.";
    } else {
      await apiCall("graphic-cards", "POST", formData, props.authToken); // formData can be FormData object now
      messageType.value = "success";
      message.value = "Graphic card added successfully.";
    }
    isFormOpen.value = false;
    currentProduct.value = null;
    fetchProducts(); // Refresh list
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
