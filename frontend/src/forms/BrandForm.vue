<template>
  <div
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
  >
    <div class="bg-white p-8 rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/3">
      <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
        {{ brand ? "Edit Brand" : "Add New Brand" }}
      </h2>
      <form @submit="handleSubmit">
        <div class="mb-4">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="brand-name"
            >Name</label
          >
          <input
            type="text"
            id="brand-name"
            name="name"
            class="form-input"
            v-model="formData.name"
            required
          />
        </div>
        <div class="mb-6">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="manufacturer-select"
            >Manufacturer</label
          >
          <select
            id="manufacturer-select"
            name="manufacturer_id"
            class="form-select"
            v-model="formData.manufacturer_id"
            required
          >
            <option value="">Select a Manufacturer</option>
            <option v-for="m in manufacturers" :key="m.id" :value="m.id">
              {{ m.name }}
            </option>
          </select>
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
            {{ brand ? "Update Brand" : "Add Brand" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, defineProps, defineEmits, watch } from "vue";

const props = defineProps({
  brand: Object, // Can be null for new brand
  manufacturers: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["close", "submit"]);

const formData = reactive({
  name: props.brand ? props.brand.name : "",
  manufacturer_id: props.brand ? props.brand.manufacturer_id : "",
});

// Watch for changes in the 'brand' prop to update the form fields
watch(
  () => props.brand,
  (newBrand) => {
    Object.assign(formData, {
      name: newBrand ? newBrand.name : "",
      manufacturer_id: newBrand ? newBrand.manufacturer_id : "",
    });
  }
);

const handleSubmit = (e) => {
  e.preventDefault();
  // Pass the form data and whether it's an edit operation to the parent
  emit("submit", formData, !!props.brand);
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
