<template>
  <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Register</h2>
    <Message :type="messageType" :message="message" />
    <form @submit="handleSubmit">
      <div class="mb-4">
        <label
          class="block text-gray-700 text-sm font-bold mb-2"
          for="reg-username"
        >
          Username
        </label>
        <input
          type="text"
          id="reg-username"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"
          v-model="username"
          required
        />
      </div>
      <div class="mb-4">
        <label
          class="block text-gray-700 text-sm font-bold mb-2"
          for="reg-email"
        >
          Email
        </label>
        <input
          type="email"
          id="reg-email"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"
          v-model="email"
          required
        />
      </div>
      <div class="mb-6">
        <label
          class="block text-gray-700 text-sm font-bold mb-2"
          for="reg-password"
        >
          Password
        </label>
        <input
          type="password"
          id="reg-password"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"
          v-model="password"
          required
        />
      </div>
      <div class="flex items-center justify-between">
        <button
          type="submit"
          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out"
          :disabled="loading"
        >
          {{ loading ? "Registering..." : "Register" }}
        </button>
        <button
          type="button"
          @click="emit('navigate', 'login')"
          class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800"
        >
          Already have an account? Login!
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
const email = ref("");
const password = ref("");
const loading = ref(false);
const message = ref(null);
const messageType = ref("");

const emit = defineEmits(["register-success", "navigate"]);

const handleSubmit = async (e) => {
  e.preventDefault();
  loading.value = true;
  message.value = null;
  try {
    const data = await apiCall("register", "POST", {
      username: username.value,
      email: email.value,
      password: password.value,
    });
    // For registration, we typically just indicate success and then prompt login
    // If the backend directly logs in after register, adjust login-success emit
    emit("register-success", data.user, data.token); // Assuming backend returns user/token
    messageType.value = "success";
    message.value = "Registration successful! You can now log in.";
    username.value = "";
    email.value = "";
    password.value = "";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Registration failed.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
