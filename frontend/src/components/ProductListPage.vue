<template>
  <div class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">
      Graphic Cards
    </h2>
    <Message :type="messageType" :message="message" />

    <!-- Search and Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-inner mb-6">
      <h3 class="text-xl font-semibold mb-4 text-gray-800">Filter Products</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Search Query -->
        <div>
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="search_query"
            >Search</label
          >
          <input
            type="text"
            id="search_query"
            v-model.trim="filters.search_query"
            @input="debouncedFetchProducts"
            placeholder="Search by name, model..."
            class="form-input"
          />
        </div>

        <!-- Min Price -->
        <div>
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="min_price"
            >Min Price</label
          >
          <input
            type="number"
            id="min_price"
            v-model.number="filters.min_price"
            @input="debouncedFetchProducts"
            placeholder="Min Price"
            step="0.01"
            class="form-input"
          />
        </div>

        <!-- Max Price -->
        <div>
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="max_price"
            >Max Price</label
          >
          <input
            type="number"
            id="max_price"
            v-model.number="filters.max_price"
            @input="debouncedFetchProducts"
            placeholder="Max Price"
            step="0.01"
            class="form-input"
          />
        </div>

        <!-- Brand Filter -->
        <div>
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="brand_id"
            >Brand</label
          >
          <select
            id="brand_id"
            v-model.number="filters.brand_id"
            @change="fetchProducts"
            class="form-select"
          >
            <option value="">All Brands</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.name }} ({{ brand.manufacturer_name }})
            </option>
          </select>
        </div>

        <!-- Manufacturer Filter -->
        <div>
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="manufacturer_id"
            >Manufacturer</label
          >
          <select
            id="manufacturer_id"
            v-model.number="filters.manufacturer_id"
            @change="fetchProducts"
            class="form-select"
          >
            <option value="">All Manufacturers</option>
            <option
              v-for="manufacturer in manufacturers"
              :key="manufacturer.id"
              :value="manufacturer.id"
            >
              {{ manufacturer.name }}
            </option>
          </select>
        </div>

        <!-- Min VRAM -->
        <div>
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="min_vram"
            >Min VRAM (GB)</label
          >
          <input
            type="number"
            id="min_vram"
            v-model.number="filters.min_vram"
            @input="debouncedFetchProducts"
            placeholder="Min VRAM"
            class="form-input"
          />
        </div>

        <!-- Max VRAM -->
        <div>
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="max_vram"
            >Max VRAM (GB)</label
          >
          <input
            type="number"
            id="max_vram"
            v-model.number="filters.max_vram"
            @input="debouncedFetchProducts"
            placeholder="Max VRAM"
            class="form-input"
          />
        </div>

        <!-- Reset Filters Button -->
        <div class="col-span-full text-right">
          <button
            @click="resetFilters"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 ease-in-out"
          >
            Reset Filters
          </button>
        </div>
      </div>
    </div>

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
      v-else-if="products.length === 0"
      class="text-center text-gray-500 py-8"
    >
      No graphic cards found matching your criteria.
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
        @add-to-cart="handleProductCardAddToCart"
      />
    </div>

    <!-- Add to Cart Notification -->
    <AddToCartNotification
      :is-visible="showAddToCartNotification"
      :product-name="notificationProductName"
      :quantity-added="notificationQuantityAdded"
      @close="showAddToCartNotification = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, defineProps, defineEmits } from "vue";
import { apiCall } from "@/utils/api";
import { LoadingSpinner, Message } from "@/utils/components";
import ProductCard from "@/components/ProductCard.vue";
import GraphicCardForm from "@/components/GraphicCardForm.vue";
import AddToCartNotification from "./AddToCartNotification.vue"; // NEW: Import AddToCartNotification

const props = defineProps({
  user: Object,
  authToken: String,
});

const emit = defineEmits(["add-to-cart"]);

const products = ref([]);
const brands = ref([]);
const manufacturers = ref([]);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const isFormOpen = ref(false);
const currentProduct = ref(null);

const isAdmin = computed(() => props.user && props.user.role === "admin");

const filters = ref({
  search_query: "",
  min_price: null,
  max_price: null,
  min_vram: null,
  max_vram: null,
  brand_id: "",
  manufacturer_id: "",
  is_featured: false,
});

// NEW: State for AddToCartNotification
const showAddToCartNotification = ref(false);
const notificationProductName = ref("");
const notificationQuantityAdded = ref(0);
let notificationTimeout = null; // To clear previous timeouts

let debounceTimer = null;
const debouncedFetchProducts = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchProducts();
  }, 500);
};

const fetchProducts = async () => {
  loading.value = true;
  message.value = null;

  const params = new URLSearchParams();
  for (const key in filters.value) {
    const value = filters.value[key];
    if (
      value !== null &&
      value !== "" &&
      !(typeof value === "boolean" && value === false)
    ) {
      if (typeof value === "boolean") {
        params.append(key, value ? "1" : "0");
      } else {
        params.append(key, value);
      }
    }
  }

  try {
    const data = await apiCall(`graphic-cards?${params.toString()}`);
    products.value = data;
    messageType.value = "success";
    message.value = "Products loaded.";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load products.";
    products.value = [];
  } finally {
    loading.value = false;
  }
};

const fetchBrandsAndManufacturers = async () => {
  try {
    const brandsData = await apiCall("brands", "GET", null, props.authToken);
    brands.value = brandsData;
    const manufacturersData = await apiCall(
      "manufacturers",
      "GET",
      null,
      props.authToken
    );
    manufacturers.value = manufacturersData;
  } catch (error) {
    console.error("Failed to load brands or manufacturers for filters:", error);
  }
};

const resetFilters = () => {
  filters.value = {
    search_query: "",
    min_price: null,
    max_price: null,
    min_vram: null,
    max_vram: null,
    brand_id: "",
    manufacturer_id: "",
    is_featured: false,
  };
  fetchProducts();
};

onMounted(() => {
  fetchProducts();
  fetchBrandsAndManufacturers();
});

watch(
  () => props.authToken,
  () => {
    fetchProducts();
    fetchBrandsAndManufacturers();
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

// NEW: Local handler for add-to-cart from ProductCard
const handleProductCardAddToCart = (product, quantity) => {
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

  // Emit the event upwards to App.vue for global cart state management
  emit("add-to-cart", product, quantity);
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
