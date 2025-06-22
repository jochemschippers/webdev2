<template>
  <footer class="bg-gray-800 text-white p-6 mt-8 rounded-t-lg shadow-lg">
    <div
      class="container mx-auto flex flex-col md:flex-row justify-between items-center text-center md:text-left"
    >
      <div class="mb-4 md:mb-0">
        <h3 class="text-xl font-bold mb-2">GPU Shop</h3>
        <p class="text-sm text-gray-400">
          Your one-stop shop for the latest graphic cards.
        </p>
      </div>

      <div
        class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-8 mb-4 md:mb-0"
      >
        <router-link
          :to="{ name: 'home' }"
          class="text-gray-300 hover:text-white transition-colors duration-200"
          >Home</router-link
        >
        <router-link
          :to="{ name: 'products' }"
          class="text-gray-300 hover:text-white transition-colors duration-200"
          >Products</router-link
        >
        <router-link
          :to="{ name: 'cart' }"
          class="text-gray-300 hover:text-white transition-colors duration-200"
          >Cart</router-link
        >
        <router-link
          :to="{ name: 'login' }"
          class="text-gray-300 hover:text-white transition-colors duration-200"
          >Login</router-link
        >
        <router-link
          :to="{ name: 'register' }"
          class="text-gray-300 hover:text-white transition-colors duration-200"
          >Register</router-link
        >
      </div>

      <div class="text-sm text-gray-400">
        &copy; {{ currentYear }} GPU Shop. All rights reserved.
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref } from "vue";

const currentYear = ref(new Date().getFullYear());
</script>

<style scoped>
/* Scoped styles for the footer */
</style>
