<template>
  <div class="container mx-auto p-8 bg-white rounded-lg shadow-md text-center">
    <h2 class="text-3xl font-bold mb-8 text-gray-800">Admin Dashboard</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <AdminCard
        title="Manage Manufacturers"
        @click="emit('navigate', 'admin-manufacturers')"
      />
      <AdminCard
        title="Manage Brands"
        @click="emit('navigate', 'admin-brands')"
      />
      <AdminCard
        title="Manage Graphic Cards"
        @click="emit('navigate', 'products')"
      />
      <AdminCard
        title="Manage Orders"
        @click="emit('navigate', 'admin-orders')"
      />
    </div>
  </div>
</template>

<script setup>
import { defineEmits } from "vue";

const emit = defineEmits(["navigate"]);

// Inner AdminCard component
const AdminCard = {
  props: ["title"],
  template: `
    <div
      class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-8 px-4 rounded-lg shadow-lg cursor-pointer flex items-center justify-center text-xl transition duration-300 ease-in-out transform hover:scale-105"
    >
      {{ title }}
    </div>
  `,
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
