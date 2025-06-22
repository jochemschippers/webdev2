<template>
  <div
    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-8 px-4 rounded-lg shadow-lg cursor-pointer flex items-center justify-center text-xl transition duration-300 ease-in-out transform hover:scale-105"
  >
    {{ title }}
  </div>
</template>

<script setup>
import { defineProps } from "vue";

defineProps({
  title: {
    type: String,
    required: true,
  },
});
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
