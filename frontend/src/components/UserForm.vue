<template>
  <div
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
  >
    <div
      class="bg-white p-8 rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 max-h-[90vh] overflow-y-auto"
    >
      <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
        {{ user ? "Edit User" : "Add New User" }}
      </h2>
      <Message :type="messageType" :message="message" />
      <form @submit.prevent="handleSubmit" class="grid grid-cols-1 gap-4">
        <div class="col-span-1">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="username"
          >
            Username
          </label>
          <input
            type="text"
            name="username"
            id="username"
            v-model="formData.username"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.username }"
            required
          />
          <p v-if="validationErrors.username" class="text-red-500 text-xs mt-1">
            {{ validationErrors.username }}
          </p>
        </div>
        <div class="col-span-1">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
            Email
          </label>
          <input
            type="email"
            name="email"
            id="email"
            v-model="formData.email"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.email }"
            required
          />
          <p v-if="validationErrors.email" class="text-red-500 text-xs mt-1">
            {{ validationErrors.email }}
          </p>
        </div>

        <!-- Password field only for adding new user or when explicitly changing password -->
        <div class="col-span-1" v-if="!user || showPasswordFields">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="password"
          >
            Password
            <span v-if="!user" class="text-red-500">*</span>
            <span v-else class="text-gray-500 text-xs"
              >(Leave blank to keep current)</span
            >
          </label>
          <input
            type="password"
            name="password"
            id="password"
            v-model="formData.password"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.password }"
            :required="!user"
            @input="validatePassword"
          />
          <ul
            v-if="validationErrors.password"
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
        <div class="col-span-1" v-if="user && !showPasswordFields">
          <button
            type="button"
            @click="showPasswordFields = true"
            class="text-blue-600 hover:underline text-sm"
          >
            Change Password
          </button>
        </div>

        <div class="col-span-1">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
            Role
          </label>
          <select
            name="role"
            id="role"
            v-model="formData.role"
            class="form-select"
            :class="{ 'border-red-500': validationErrors.role }"
            required
          >
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
          </select>
          <p v-if="validationErrors.role" class="text-red-500 text-xs mt-1">
            {{ validationErrors.role }}
          </p>
        </div>

        <div class="col-span-2 flex justify-end gap-4 mt-6">
          <button
            type="button"
            @click="emit('close')"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out"
            :disabled="!isFormValid || formSubmitting"
          >
            {{ user ? "Update User" : "Add User" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, defineProps, defineEmits, computed } from "vue";
import { Message } from "@/utils/components";

const props = defineProps({
  user: Object, // Can be null for new user
  authToken: String, // Prop to pass auth token for API calls if needed within form (not directly used in this form)
});

const emit = defineEmits(["close", "submit"]);

const formData = reactive({
  username: "",
  email: "",
  password: "", // Only used for new users or explicit password changes
  role: "customer",
});

const validationErrors = reactive({}); // To store validation error messages for each field
const passwordErrors = ref([]); // Specific errors for password complexity
const message = ref(null);
const messageType = ref("");
const formSubmitting = ref(false); // To disable button during submission
const showPasswordFields = ref(false); // To show/hide password fields in edit mode

// Computed property to check if the form is valid for submission
const isFormValid = computed(() => {
  // Check if there are any validation errors
  for (const key in validationErrors) {
    if (validationErrors[key]) {
      return false;
    }
  }

  // Check password errors specifically (for creation or explicit change)
  if (
    (!props.user || showPasswordFields.value) &&
    passwordErrors.value.length > 0 &&
    formData.password
  ) {
    return false;
  }
  if (!props.user && passwordErrors.value.length > 0) {
    // For new user, password is required
    return false;
  }

  // Basic check for required fields that might not have immediate validation errors
  const requiredFieldsFilled =
    formData.username && formData.email && formData.role;

  // For new user, password is always required
  if (!props.user && !formData.password) {
    return false;
  }

  return requiredFieldsFilled;
});

// Function to perform client-side validation
const validateForm = () => {
  // Clear previous errors
  for (const key in validationErrors) {
    validationErrors[key] = null;
  }
  passwordErrors.value = []; // Clear password errors too

  let isValid = true;

  // Validate username
  if (!formData.username || formData.username.trim() === "") {
    validationErrors.username = "Username is required.";
    isValid = false;
  } else if (formData.username.length < 3) {
    validationErrors.username = "Username must be at least 3 characters.";
    isValid = false;
  } else if (formData.username.length > 50) {
    validationErrors.username = "Username cannot exceed 50 characters.";
    isValid = false;
  }

  // Validate email
  if (!formData.email || formData.email.trim() === "") {
    validationErrors.email = "Email is required.";
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
    validationErrors.email = "Invalid email format.";
    isValid = false;
  }

  // Validate password (only if it's a new user OR if password fields are shown for edit)
  if (!props.user || showPasswordFields.value) {
    validatePassword(); // Run complexity check
    if (!formData.password && !props.user) {
      // Password is required for new user
      passwordErrors.value.push("Password is required.");
    }
    if (passwordErrors.value.length > 0) {
      validationErrors.password = "Password does not meet requirements."; // General error message
      isValid = false;
    }
  }

  // Validate role
  if (!formData.role || !["customer", "admin"].includes(formData.role)) {
    validationErrors.role = "Invalid role selected.";
    isValid = false;
  }

  // Display general message if any errors exist
  if (!isValid) {
    messageType.value = "error";
    message.value = "Please correct the errors in the form.";
  } else {
    message.value = null; // Clear general message if form is valid
  }

  return isValid;
};

// Client-side password complexity validation
const validatePassword = () => {
  passwordErrors.value = [];
  const currentPassword = formData.password;

  if (!currentPassword) {
    // If no password provided during edit, and it's not a new user,
    // this is valid (user chose not to change password).
    // If it's a new user, this will be caught by `!formData.password` in `validateForm`.
    if (props.user) {
      // If editing, and password field is empty, it's valid if not changing
      validationErrors.password = null;
      return;
    }
  }

  if (currentPassword.length < 8) {
    passwordErrors.value.push("At least 8 characters.");
  }
  if (!/[A-Z]/.test(currentPassword)) {
    passwordErrors.value.push("At least one uppercase letter.");
  }
  if (!/[a-z]/.test(currentPassword)) {
    passwordErrors.value.push("At least one lowercase letter.");
  }
  if (!/[0-9]/.test(currentPassword)) {
    passwordErrors.value.push("At least one number.");
  }
  if (!/[^A-Za-z0-9]/.test(currentPassword)) {
    passwordErrors.value.push("At least one special character.");
  }

  if (passwordErrors.value.length > 0) {
    validationErrors.password = "Password does not meet requirements.";
  } else {
    validationErrors.password = null;
  }
};

// Watch for changes in formData to trigger validation
watch(formData, validateForm, { deep: true });

// Watch for changes in the 'user' prop to populate form fields when editing
watch(
  () => props.user,
  (newUser) => {
    if (newUser) {
      Object.assign(formData, {
        username: newUser.username || "",
        email: newUser.email || "",
        role: newUser.role || "customer",
        password: "", // Never populate password field with existing hash
      });
      showPasswordFields.value = false; // Hide password fields initially for edit
    } else {
      // Reset form for adding a new user
      Object.assign(formData, {
        username: "",
        email: "",
        password: "",
        role: "customer",
      });
      showPasswordFields.value = true; // Always show password fields for new user
    }
    // Clear validation errors when user changes or form resets
    for (const key in validationErrors) {
      validationErrors[key] = null;
    }
    passwordErrors.value = []; // Clear password errors on reset
  },
  { immediate: true }
);

const handleSubmit = () => {
  formSubmitting.value = true;
  message.value = null; // Clear previous messages

  // Perform client-side validation
  if (!validateForm()) {
    formSubmitting.value = false;
    return; // Stop submission if validation fails
  }

  // Construct the data payload
  const submitData = {
    username: formData.username,
    email: formData.email,
    role: formData.role,
  };

  // Only include password if it's a new user OR if it was explicitly changed
  if (formData.password || !props.user) {
    submitData.password = formData.password;
  }

  emit("submit", submitData, !!props.user); // Pass data and whether it's an edit operation
};
</script>

<style scoped>
/* Scoped styles specific to this component */
.form-input,
.form-select,
.form-textarea {
  @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}

.form-input.border-red-500,
.form-select.border-red-500,
.form-textarea.border-red-500 {
  @apply border-red-500 focus:ring-red-500 focus:border-red-500;
}
</style>
