<template>
  <button
    @click="emit('navigate', page)"
    :class="`px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out \${currentPage === page ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'}`"
  >
    <slot></slot>
  </button>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

defineProps({
  page: {
    type: String,
    required: true,
  },
  currentPage: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["navigate"]);
</script>

<style scoped>
/* Add any scoped styles for NavLink if needed */
</style>
