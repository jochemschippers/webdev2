<template>
  <div
    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center"
  >
    <img
      :src="
        product.image_url ||
        'https://placehold.co/150x150/E2E8F0/1A202C?text=GPU'
      "
      :alt="product.name"
      class="w-32 h-32 object-contain mb-4 rounded-md"
      @error="
        (e) => {
          e.target.onerror = null;
          e.target.src = 'https://placehold.co/150x150/E2E8F0/1A202C?text=GPU';
        }
      "
    />
    <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ product.name }}</h3>
    <p class="text-gray-600 text-sm mb-1">Model: {{ product.gpu_model }}</p>
    <p class="text-gray-600 text-sm mb-1">VRAM: {{ product.vram_gb }}GB</p>
    <p class="text-gray-600 text-sm mb-1">Brand: {{ product.brand_name }}</p>
    <p class="text-gray-600 text-sm mb-1">
      Manufacturer: {{ product.manufacturer_name }}
    </p>
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

    <div class="mt-4 flex flex-col sm:flex-row gap-2 w-full">
      <button
        v-if="product.stock > 0"
        @click="emit('add-to-cart', product)"
        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
      >
        Add to Cart
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
import { defineProps, defineEmits } from "vue";

defineProps({
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
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
