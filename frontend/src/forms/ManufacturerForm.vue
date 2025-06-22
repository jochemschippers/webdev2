<template>
  <div
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
  >
    <div class="bg-white p-8 rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/3">
      <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
        {{ manufacturer ? "Edit Manufacturer" : "Add New Manufacturer" }}
      </h2>
      <form @submit="handleSubmit">
        <div class="mb-4">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="manufacturer-name"
            >Name</label
          >
          <input
            type="text"
            id="manufacturer-name"
            name="name"
            class="form-input"
            v-model="name"
            required
          />
        </div>
        <div class="flex justify-end gap-4 mt-6">
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
          >
            {{ manufacturer ? "Update Manufacturer" : "Add Manufacturer" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from "vue";

const props = defineProps({
  manufacturer: Object, // Can be null for new manufacturer
});

const emit = defineEmits(["close", "submit"]);

const name = ref(props.manufacturer ? props.manufacturer.name : "");

// Watch for changes in the 'manufacturer' prop to update the form fields
watch(
  () => props.manufacturer,
  (newManufacturer) => {
    name.value = newManufacturer ? newManufacturer.name : "";
  }
);

const handleSubmit = (e) => {
  e.preventDefault();
  // Pass the form data and whether it's an edit operation to the parent
  emit("submit", { name: name.value }, !!props.manufacturer);
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
