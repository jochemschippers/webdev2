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
      <AdminCard
        title="Manage Users"
        @click="emit('navigate', 'admin-users')"
      />
    </div>
  </div>
</template>

<script setup>
import { defineEmits } from "vue";
import AdminCard from "./AdminCard.vue"; // Import the new AdminCard component

const emit = defineEmits(["navigate"]);
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
