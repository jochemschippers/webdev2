<template>
  <div
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
  >
    <div
      class="bg-white p-8 rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 max-h-[90vh] overflow-y-auto"
    >
      <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
        {{ product ? "Edit Graphic Card" : "Add New Graphic Card" }}
      </h2>
      <Message :type="messageType" :message="message" />
      <div v-if="loading" class="text-center py-4">
        <LoadingSpinner />
      </div>
      <form
        v-else
        @submit.prevent="handleSubmit"
        class="grid grid-cols-1 md:grid-cols-2 gap-4"
      >
        <div class="col-span-1">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
            Name
          </label>
          <input
            type="text"
            name="name"
            id="name"
            v-model="formData.name"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.name }"
            required
          />
          <p v-if="validationErrors.name" class="text-red-500 text-xs mt-1">
            {{ validationErrors.name }}
          </p>
        </div>
        <div class="col-span-1">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="brand_id"
          >
            Brand
          </label>
          <select
            name="brand_id"
            id="brand_id"
            v-model="formData.brand_id"
            class="form-select"
            :class="{ 'border-red-500': validationErrors.brand_id }"
            required
          >
            <option value="">Select a Brand</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.name }} ({{ brand.manufacturer_name }})
            </option>
          </select>
          <p v-if="validationErrors.brand_id" class="text-red-500 text-xs mt-1">
            {{ validationErrors.brand_id }}
          </p>
        </div>
        <div class="col-span-1">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="gpu_model"
          >
            GPU Model
          </label>
          <input
            type="text"
            name="gpu_model"
            id="gpu_model"
            v-model="formData.gpu_model"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.gpu_model }"
            required
          />
          <p
            v-if="validationErrors.gpu_model"
            class="text-red-500 text-xs mt-1"
          >
            {{ validationErrors.gpu_model }}
          </p>
        </div>
        <div class="col-span-1">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="vram_gb"
          >
            VRAM (GB)
          </label>
          <input
            type="number"
            name="vram_gb"
            id="vram_gb"
            v-model.number="formData.vram_gb"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.vram_gb }"
            min="1"
            required
          />
          <p v-if="validationErrors.vram_gb" class="text-red-500 text-xs mt-1">
            {{ validationErrors.vram_gb }}
          </p>
        </div>
        <div class="col-span-1">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="interface"
          >
            Interface
          </label>
          <input
            type="text"
            name="interface"
            id="interface"
            v-model="formData.interface"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.interface }"
            required
          />
          <p
            v-if="validationErrors.interface"
            class="text-red-500 text-xs mt-1"
          >
            {{ validationErrors.interface }}
          </p>
        </div>
        <div class="col-span-1">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="boost_clock_mhz"
          >
            Boost Clock (MHz)
          </label>
          <input
            type="number"
            name="boost_clock_mhz"
            id="boost_clock_mhz"
            v-model.number="formData.boost_clock_mhz"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.boost_clock_mhz }"
            min="0"
          />
          <p
            v-if="validationErrors.boost_clock_mhz"
            class="text-red-500 text-xs mt-1"
          >
            {{ validationErrors.boost_clock_mhz }}
          </p>
        </div>
        <div class="col-span-1">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="cuda_cores"
          >
            CUDA Cores
          </label>
          <input
            type="number"
            name="cuda_cores"
            id="cuda_cores"
            v-model.number="formData.cuda_cores"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.cuda_cores }"
            min="0"
          />
          <p
            v-if="validationErrors.cuda_cores"
            class="text-red-500 text-xs mt-1"
          >
            {{ validationErrors.cuda_cores }}
          </p>
        </div>
        <div class="col-span-1">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="stream_processors"
          >
            Stream Processors
          </label>
          <input
            type="number"
            name="stream_processors"
            id="stream_processors"
            v-model.number="formData.stream_processors"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.stream_processors }"
            min="0"
          />
          <p
            v-if="validationErrors.stream_processors"
            class="text-red-500 text-xs mt-1"
          >
            {{ validationErrors.stream_processors }}
          </p>
        </div>
        <div class="col-span-1">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="price">
            Price ($)
          </label>
          <input
            type="number"
            name="price"
            id="price"
            v-model.number="formData.price"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.price }"
            step="0.01"
            min="0.01"
            required
          />
          <p v-if="validationErrors.price" class="text-red-500 text-xs mt-1">
            {{ validationErrors.price }}
          </p>
        </div>
        <div class="col-span-1">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="stock">
            Stock
          </label>
          <input
            type="number"
            name="stock"
            id="stock"
            v-model.number="formData.stock"
            class="form-input"
            :class="{ 'border-red-500': validationErrors.stock }"
            min="0"
            required
          />
          <p v-if="validationErrors.stock" class="text-red-500 text-xs mt-1">
            {{ validationErrors.stock }}
          </p>
        </div>
        <div class="col-span-2">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="description"
          >
            Description
          </label>
          <textarea
            name="description"
            id="description"
            v-model="formData.description"
            class="form-textarea"
            :class="{ 'border-red-500': validationErrors.description }"
          ></textarea>
          <p
            v-if="validationErrors.description"
            class="text-red-500 text-xs mt-1"
          >
            {{ validationErrors.description }}
          </p>
        </div>

        <div class="col-span-2">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="image">
            Image Upload
          </label>
          <input
            type="file"
            name="image"
            id="image"
            @change="handleFileChange"
            class="form-input border p-2 rounded-md"
            accept="image/*"
          />
          <div v-if="imagePreviewUrl" class="mt-4 text-center">
            <p class="text-gray-700 text-sm mb-2">Image Preview:</p>
            <img
              :src="imagePreviewUrl"
              alt="Image Preview"
              class="max-w-xs max-h-48 mx-auto border rounded-md shadow-sm"
            />
            <button
              type="button"
              @click="removeImage"
              class="mt-2 bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-xs"
            >
              Remove Image
            </button>
          </div>
          <p class="text-gray-600 text-xs mt-1">
            Upload a new image. Max 5MB. Existing image will be replaced.
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
            :disabled="!isFormValid"
          >
            {{ product ? "Update Graphic Card" : "Add Graphic Card" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import {
  reactive,
  ref,
  onMounted,
  watch,
  defineProps,
  defineEmits,
  computed,
} from "vue";
import { apiCall } from "@/utils/api";
import { LoadingSpinner, Message } from "@/utils/components";

const props = defineProps({
  product: Object, // Can be null for new product
  authToken: String,
});

const emit = defineEmits(["close", "submit"]);

const formData = reactive({
  name: "",
  brand_id: "",
  gpu_model: "",
  vram_gb: "",
  interface: "",
  boost_clock_mhz: "",
  cuda_cores: "",
  stream_processors: "",
  price: "",
  stock: "",
  description: "",
});

const selectedFile = ref(null);
const imagePreviewUrl = ref(null);
const brands = ref([]);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const validationErrors = reactive({}); // To store validation error messages for each field

// Computed property to check if the form is valid for submission
const isFormValid = computed(() => {
  // Check if there are any validation errors
  for (const key in validationErrors) {
    if (validationErrors[key]) {
      return false;
    }
  }

  // Basic check for required fields that might not have immediate validation errors
  // This covers cases where a field is empty but no specific rule highlights it yet (e.g., initial state)
  const requiredFieldsFilled =
    formData.name &&
    formData.brand_id &&
    formData.gpu_model &&
    formData.vram_gb !== null &&
    formData.vram_gb !== "" &&
    formData.interface &&
    formData.price !== null &&
    formData.price !== "" &&
    formData.stock !== null &&
    formData.stock !== "";

  return requiredFieldsFilled;
});

const fetchBrands = async () => {
  try {
    const data = await apiCall("brands", "GET", null, props.authToken);
    brands.value = data;
  } catch (error) {
    messageType.value = "error";
    message.value = "Failed to load brands for form.";
  } finally {
    loading.value = false;
  }
};

onMounted(fetchBrands);

// Function to perform client-side validation
const validateForm = () => {
  // Clear previous errors
  for (const key in validationErrors) {
    validationErrors[key] = null;
  }

  let isValid = true;

  // Validate required text fields
  if (!formData.name) {
    validationErrors.name = "Name is required.";
    isValid = false;
  }
  if (!formData.gpu_model) {
    validationErrors.gpu_model = "GPU Model is required.";
    isValid = false;
  }
  if (!formData.interface) {
    validationErrors.interface = "Interface is required.";
    isValid = false;
  }
  if (!formData.description) {
    validationErrors.description = "Description is required.";
    isValid = false;
  }

  // Validate brand_id
  if (!formData.brand_id) {
    validationErrors.brand_id = "Brand is required.";
    isValid = false;
  } else if (
    typeof formData.brand_id !== "number" &&
    typeof formData.brand_id !== "string"
  ) {
    validationErrors.brand_id = "Invalid Brand selected.";
    isValid = false;
  } else if (
    typeof formData.brand_id === "string" &&
    isNaN(parseInt(formData.brand_id))
  ) {
    validationErrors.brand_id = "Invalid Brand selected.";
    isValid = false;
  }

  // Validate numerical fields
  // VRAM (must be positive integer)
  if (formData.vram_gb === null || formData.vram_gb === "") {
    validationErrors.vram_gb = "VRAM is required.";
    isValid = false;
  } else if (
    typeof formData.vram_gb !== "number" ||
    formData.vram_gb < 1 ||
    !Number.isInteger(formData.vram_gb)
  ) {
    validationErrors.vram_gb = "VRAM must be a positive whole number.";
    isValid = false;
  }

  // Price (must be positive number)
  if (formData.price === null || formData.price === "") {
    validationErrors.price = "Price is required.";
    isValid = false;
  } else if (typeof formData.price !== "number" || formData.price <= 0) {
    validationErrors.price = "Price must be a positive number.";
    isValid = false;
  }

  // Stock (must be non-negative integer)
  if (formData.stock === null || formData.stock === "") {
    validationErrors.stock = "Stock is required.";
    isValid = false;
  } else if (
    typeof formData.stock !== "number" ||
    formData.stock < 0 ||
    !Number.isInteger(formData.stock)
  ) {
    validationErrors.stock = "Stock must be a non-negative whole number.";
    isValid = false;
  }

  // Optional numerical fields (must be non-negative integers if provided)
  if (formData.boost_clock_mhz !== null && formData.boost_clock_mhz !== "") {
    if (
      typeof formData.boost_clock_mhz !== "number" ||
      formData.boost_clock_mhz < 0 ||
      !Number.isInteger(formData.boost_clock_mhz)
    ) {
      validationErrors.boost_clock_mhz =
        "Boost Clock must be a non-negative whole number.";
      isValid = false;
    }
  }
  if (formData.cuda_cores !== null && formData.cuda_cores !== "") {
    if (
      typeof formData.cuda_cores !== "number" ||
      formData.cuda_cores < 0 ||
      !Number.isInteger(formData.cuda_cores)
    ) {
      validationErrors.cuda_cores =
        "CUDA Cores must be a non-negative whole number.";
      isValid = false;
    }
  }
  if (
    formData.stream_processors !== null &&
    formData.stream_processors !== ""
  ) {
    if (
      typeof formData.stream_processors !== "number" ||
      formData.stream_processors < 0 ||
      !Number.isInteger(formData.stream_processors)
    ) {
      validationErrors.stream_processors =
        "Stream Processors must be a non-negative whole number.";
      isValid = false;
    }
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

// Watch for changes in formData to trigger validation
watch(formData, validateForm, { deep: true });

// Watch for changes in the 'product' prop to populate form fields when editing
watch(
  () => props.product,
  (newProduct) => {
    if (newProduct) {
      Object.assign(formData, {
        name: newProduct.name || "",
        brand_id: newProduct.brand_id || "",
        gpu_model: newProduct.gpu_model || "",
        vram_gb: newProduct.vram_gb || "",
        interface: newProduct.interface || "",
        boost_clock_mhz: newProduct.boost_clock_mhz || "",
        cuda_cores: newProduct.cuda_cores || "",
        stream_processors: newProduct.stream_processors || "",
        price: newProduct.price || "",
        stock: newProduct.stock || "",
        description: newProduct.description || "",
      });
      // Set image preview if product has an existing image URL
      if (newProduct.image_url) {
        imagePreviewUrl.value = `http://localhost${newProduct.image_url}`;
      } else {
        imagePreviewUrl.value = null;
      }
      selectedFile.value = null; // Clear selected file when editing existing product
    } else {
      // Reset form for adding a new product
      Object.assign(formData, {
        name: "",
        brand_id: "",
        gpu_model: "",
        vram_gb: "",
        interface: "",
        boost_clock_mhz: "",
        cuda_cores: "",
        stream_processors: "",
        price: "",
        stock: "",
        description: "",
      });
      selectedFile.value = null;
      imagePreviewUrl.value = null;
    }
    // Clear validation errors when product changes or form resets
    for (const key in validationErrors) {
      validationErrors[key] = null;
    }
  },
  { immediate: true }
);

const handleFileChange = (e) => {
  const file = e.target.files[0];
  selectedFile.value = file;
  if (file) {
    imagePreviewUrl.value = URL.createObjectURL(file); // Create a URL for image preview
  } else {
    imagePreviewUrl.value = null;
  }
};

const removeImage = () => {
  selectedFile.value = null;
  imagePreviewUrl.value = null;
};

const handleSubmit = () => {
  // Perform client-side validation just before submission
  if (!validateForm()) {
    return; // Stop submission if validation fails
  }

  const submitData = new FormData();
  for (const key in formData) {
    // Convert numbers to strings for FormData as it treats everything as strings
    // Also, handle null for optional fields properly
    if (formData[key] !== null && formData[key] !== "") {
      submitData.append(key, String(formData[key]));
    } else if (
      key === "boost_clock_mhz" ||
      key === "cuda_cores" ||
      key === "stream_processors"
    ) {
      // Explicitly send null for empty optional number fields if backend expects it
      submitData.append(key, ""); // Send empty string for nullables
    }
  }

  // Append the selected file if it exists
  if (selectedFile.value) {
    submitData.append("image", selectedFile.value); // 'image' is the field name backend will look for
  } else if (
    props.product &&
    props.product.image_url &&
    imagePreviewUrl.value === null
  ) {
    // If editing an existing product and the image was explicitly removed (imagePreviewUrl is null),
    // tell the backend to clear the image_url.
    submitData.append("image_url", ""); // Send empty string to signal removal
  }
  // If editing and no new file selected, and imagePreviewUrl is still the old one,
  // we don't need to send image_url explicitly, backend will retain it.

  emit("submit", submitData, !!props.product); // Pass FormData, and whether it's an edit operation
};
</script>

<style scoped>
/* Scoped styles specific to this component */
.form-input,
.form-select,
.form-textarea {
  @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}

.form-textarea {
  @apply resize-y;
}

.form-input.border-red-500,
.form-select.border-red-500,
.form-textarea.border-red-500 {
  @apply border-red-500 focus:ring-red-500 focus:border-red-500;
}
</style>
