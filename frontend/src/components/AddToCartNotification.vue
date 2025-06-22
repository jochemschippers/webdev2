<template>
  <transition name="slide-fade">
    <div
      v-if="props.isVisible"
      class="fixed bottom-4 right-4 bg-green-600 text-white p-4 rounded-lg shadow-xl z-50 max-w-sm"
      role="alert"
    >
      <div class="flex items-center mb-2">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-6 w-6 mr-2"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 13l4 4L19 7"
          />
        </svg>
        <span class="font-semibold text-lg"
          >{{ props.quantityAdded }} item(s) added to cart!</span
        >
      </div>
      <p class="text-sm mb-4">"{{ props.productName }}" has been added.</p>
      <div class="flex justify-end space-x-2">
        <router-link
          :to="{ name: 'cart' }"
          class="bg-green-700 hover:bg-green-800 text-white font-bold py-2 px-3 rounded-md transition duration-150 ease-in-out text-sm"
          @click="emit('close')"
        >
          Go to Cart
        </router-link>
        <button
          @click="emit('close')"
          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-md transition duration-150 ease-in-out text-sm"
        >
          Continue Shopping
        </button>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { defineProps, defineEmits } from "vue"; // Ensure defineEmits is imported

// Assign defineProps to a variable 'props' to retain reactivity
const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  productName: {
    type: String,
    default: "",
  },
  quantityAdded: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(["close"]);
</script>

<style scoped>
/* Transition for sliding up/down and fading */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.5s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>
