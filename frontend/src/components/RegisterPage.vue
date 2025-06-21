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
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-1 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"
          :class="{ 'border-red-500': passwordErrors.length > 0 }"
          v-model="password"
          @input="validatePassword"
          required
        />
        <ul
          v-if="passwordErrors.length > 0"
          class="text-red-500 text-xs mt-1 list-disc list-inside"
        >
          <li v-for="(error, index) in passwordErrors" :key="index">
            {{ error }}
          </li>
        </ul>
        <p class="text-gray-600 text-xs mt-1">
          Password must be at least 8 characters long, include an uppercase
          letter, a lowercase letter, a number, and a special character.
        </p>
      </div>
      <div class="flex items-center justify-between">
        <button
          type="submit"
          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out"
          :disabled="loading || passwordErrors.length > 0"
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
import { ref, defineEmits, watch } from "vue";
import { apiCall } from "@/utils/api";
import { Message } from "@/utils/components";

const username = ref("");
const email = ref("");
const password = ref("");
const loading = ref(false);
const message = ref(null);
const messageType = ref("");
const passwordErrors = ref([]); // Array to hold specific password validation errors

const emit = defineEmits(["register-success", "navigate"]);

// Client-side password validation
const validatePassword = () => {
  passwordErrors.value = [];

  if (password.value.length < 8) {
    passwordErrors.value.push("Password must be at least 8 characters long.");
  }
  if (!/[A-Z]/.test(password.value)) {
    passwordErrors.value.push(
      "Password must contain at least one uppercase letter."
    );
  }
  if (!/[a-z]/.test(password.value)) {
    passwordErrors.value.push(
      "Password must contain at least one lowercase letter."
    );
  }
  if (!/[0-9]/.test(password.value)) {
    passwordErrors.value.push("Password must contain at least one number.");
  }
  if (!/[^A-Za-z0-9]/.test(password.value)) {
    passwordErrors.value.push(
      "Password must contain at least one special character."
    );
  }
};

// Watch password changes to trigger validation
watch(password, () => {
  validatePassword();
});

const handleSubmit = async (e) => {
  e.preventDefault();

  // Perform client-side validation immediately before sending to backend
  validatePassword();
  if (passwordErrors.value.length > 0) {
    messageType.value = "error";
    message.value = "Please correct the password errors.";
    return; // Stop submission if client-side validation fails
  }

  loading.value = true;
  message.value = null;
  try {
    const data = await apiCall("register", "POST", {
      username: username.value,
      email: email.value,
      password: password.value,
    });

    emit("register-success", data.user, data.token); // Assuming backend returns user/token
    messageType.value = "success";
    message.value = "Registration successful! You can now log in.";
    username.value = "";
    email.value = "";
    password.value = "";
    passwordErrors.value = []; // Clear errors on success
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
