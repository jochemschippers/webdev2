<template>
  <div
    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center min-h-[480px]"
  >
    <router-link
      :to="{ name: 'graphic-card-detail', params: { id: product.id } }"
      class="flex-shrink-0"
    >
      <img
        :src="'http://localhost' + (product.image_url || '/placeholder.png')"
        :alt="product.name"
        class="w-32 h-32 object-contain mb-4 rounded-md transition-transform duration-300 hover:scale-105"
        @error="
          (e) => {
            e.target.onerror = null;
            e.target.src =
              'https://placehold.co/150x150/E2E8F0/1A202C?text=GPU';
          }
        "
      />
    </router-link>

    <router-link
      :to="{ name: 'graphic-card-detail', params: { id: product.id } }"
      class="text-xl font-semibold text-gray-800 hover:text-blue-600 transition-colors duration-200 mb-2 flex-shrink-0"
    >
      <h3>{{ product.name }}</h3>
    </router-link>

    <!-- Content area that should grow to fill space -->
    <div class="flex-grow flex flex-col justify-center items-center">
      <!-- Fixed height container for consistent spacing of variable text -->
      <div class="h-24 flex flex-col justify-center items-center">
        <p class="text-gray-600 text-sm mb-1">Model: {{ product.gpu_model }}</p>
        <p class="text-gray-600 text-sm mb-1">VRAM: {{ product.vram_gb }}GB</p>
        <p class="text-gray-600 text-sm mb-1">
          Brand: {{ product.brand_name }}
        </p>
        <p class="text-gray-600 text-sm mb-1">
          Manufacturer: {{ product.manufacturer_name }}
        </p>
      </div>

      <p class="text-2xl font-bold text-blue-600 mt-2 mb-4">
        ${{ parseFloat(product.price).toFixed(2) }}
      </p>
      <p
        :class="`text-sm font-medium ${
          product.stock > 0 ? 'text-green-600' : 'text-red-600'
        }`"
      >
        Stock: {{ product.stock > 0 ? product.stock : "Out of Stock" }}
      </p>
    </div>

    <div class="mt-4 flex flex-col sm:flex-row gap-2 w-full flex-shrink-0">
      <!-- Quantity Input -->
      <div
        class="flex items-center border border-gray-300 rounded-md p-1 w-full sm:w-auto mb-2 sm:mb-0"
      >
        <button
          @click="decreaseQuantity"
          class="px-2 py-1 text-gray-600 hover:bg-gray-200 rounded-l-md"
        >
          -
        </button>
        <input
          type="number"
          v-model.number="quantity"
          min="1"
          :max="product.stock"
          class="w-16 text-center border-x border-gray-300 text-gray-700 focus:outline-none focus:ring-0 focus:border-transparent"
          @input="validateQuantity"
        />
        <button
          @click="increaseQuantity"
          class="px-2 py-1 text-gray-600 hover:bg-gray-200 rounded-r-md"
        >
          +
        </button>
      </div>

      <button
        @click="handleAddToCartClick"
        :disabled="product.stock === 0 || quantity === 0"
        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ product.stock > 0 ? "Add to Cart" : "Out of Stock" }}
      </button>
      <template v-if="isAdmin">
        <button
          @click="emit('edit', product)"
          class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
        >
          Edit
        </button>
        <button
          @click="emit('delete', product.id)"
          class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          Delete
        </button>
      </template>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, watch } from "vue";

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
  isAdmin: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["edit", "delete", "add-to-cart"]);

const quantity = ref(1); // Default quantity for the product card

// Watch for product stock changes to update quantity if current quantity exceeds new stock
watch(
  () => props.product.stock,
  (newStock) => {
    if (quantity.value > newStock) {
      quantity.value = newStock > 0 ? newStock : 0; // If stock becomes 0, set quantity to 0
    }
    if (quantity.value === 0 && newStock > 0) {
      quantity.value = 1; // If stock becomes available, reset to 1
    }
  }
);

const handleAddToCartClick = () => {
  // Emit the product and the selected quantity to the parent.
  // The parent will handle the cart logic AND the notification.
  emit("add-to-cart", props.product, quantity.value);
};

const increaseQuantity = () => {
  if (props.product && quantity.value < props.product.stock) {
    quantity.value++;
  }
};

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
};

const validateQuantity = () => {
  if (props.product) {
    if (quantity.value < 1) {
      quantity.value = 1;
    } else if (quantity.value > props.product.stock) {
      quantity.value = props.product.stock;
    }
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
/* Hide default input number arrows */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>
