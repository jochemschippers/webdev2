<template>
  <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Login</h2>
    <Message :type="messageType" :message="message" />
    <form @submit="handleSubmit">
      <div class="mb-4">
        <label
          class="block text-gray-700 text-sm font-bold mb-2"
          for="username"
        >
          Username
        </label>
        <input
          type="text"
          id="username"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"
          v-model="username"
          required
        />
      </div>
      <div class="mb-6">
        <label
          class="block text-gray-700 text-sm font-bold mb-2"
          for="password"
        >
          Password
        </label>
        <input
          type="password"
          id="password"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"
          v-model="password"
          required
        />
      </div>
      <div class="flex items-center justify-between">
        <button
          type="submit"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out"
          :disabled="loading"
        >
          {{ loading ? "Logging In..." : "Login" }}
        </button>
        <button
          type="button"
          @click="emit('navigate', 'register')"
          class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800"
        >
          Don't have an account? Register!
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, defineEmits } from "vue";
import { apiCall } from "@/utils/api"; // Import apiCall utility
import { Message } from "@/utils/components"; // Import Message component

const username = ref("");
const password = ref("");
const loading = ref(false);
const message = ref(null);
const messageType = ref("");

const emit = defineEmits(["login-success", "navigate"]);

const handleSubmit = async (e) => {
  e.preventDefault();
  loading.value = true;
  message.value = null;
  try {
    const data = await apiCall("login", "POST", {
      username: username.value,
      password: password.value,
    });
    emit("login-success", data.user, data.token);
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Login failed.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
