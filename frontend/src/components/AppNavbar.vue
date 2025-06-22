<template>
  <nav class="bg-gray-800 p-4 rounded-b-lg shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
      <div class="text-white text-2xl font-bold cursor-pointer">
        <router-link :to="{ name: 'home' }">GPU Shop</router-link>
      </div>
      <div class="space-x-4">
        <router-link
          :to="{ name: 'home' }"
          class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
          :class="{
            'bg-gray-900 text-white': $route.name === 'home',
            'text-gray-300 hover:bg-gray-700 hover:text-white':
              $route.name !== 'home',
          }"
          >Home</router-link
        >
        <router-link
          :to="{ name: 'products' }"
          class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
          :class="{
            'bg-gray-900 text-white': $route.name === 'products',
            'text-gray-300 hover:bg-gray-700 hover:text-white':
              $route.name !== 'products',
          }"
          >Products</router-link
        >
        <router-link
          :to="{ name: 'cart' }"
          class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
          :class="{
            'bg-gray-900 text-white': $route.name === 'cart',
            'text-gray-300 hover:bg-gray-700 hover:text-white':
              $route.name !== 'cart',
          }"
          >Cart</router-link
        >
        <template v-if="user">
          <router-link
            v-if="user.role === 'admin'"
            :to="{ name: 'admin' }"
            class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
            :class="{
              'bg-gray-900 text-white': $route.name === 'admin',
              'text-gray-300 hover:bg-gray-700 hover:text-white':
                $route.name !== 'admin',
            }"
            >Admin</router-link
          >
          <router-link
            :to="{ name: 'orders' }"
            class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
            :class="{
              'bg-gray-900 text-white': $route.name === 'orders',
              'text-gray-300 hover:bg-gray-700 hover:text-white':
                $route.name !== 'orders',
            }"
            >My Orders</router-link
          >
          <button
            @click="emit('logout')"
            class="px-3 py-2 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out"
          >
            Logout ({{ user.username }})
          </button>
        </template>
        <template v-else>
          <router-link
            :to="{ name: 'login' }"
            class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
            :class="{
              'bg-gray-900 text-white': $route.name === 'login',
              'text-gray-300 hover:bg-gray-700 hover:text-white':
                $route.name !== 'login',
            }"
            >Login</router-link
          >
          <router-link
            :to="{ name: 'register' }"
            class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
            :class="{
              'bg-gray-900 text-white': $route.name === 'register',
              'text-gray-300 hover:bg-gray-700 hover:text-white':
                $route.name !== 'register',
            }"
            >Register</router-link
          >
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";
// import { useRoute } from "vue-router"; // Removed: useRoute is not used in script setup

defineProps({
  user: {
    type: Object, // Can be null
    default: null,
  },
});

const emit = defineEmits(["logout"]);
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
