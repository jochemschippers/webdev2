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
        @submit="handleSubmit"
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
            @change="handleChange"
            class="form-input"
            required
          />
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
            @change="handleChange"
            class="form-select"
            required
          >
            <option value="">Select a Brand</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.name }} ({{ brand.manufacturer_name }})
            </option>
          </select>
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
            @change="handleChange"
            class="form-input"
            required
          />
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
            v-model="formData.vram_gb"
            @change="handleChange"
            class="form-input"
            required
          />
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
            @change="handleChange"
            class="form-input"
            required
          />
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
            v-model="formData.boost_clock_mhz"
            @change="handleChange"
            class="form-input"
          />
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
            v-model="formData.cuda_cores"
            @change="handleChange"
            class="form-input"
          />
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
            v-model="formData.stream_processors"
            @change="handleChange"
            class="form-input"
          />
        </div>
        <div class="col-span-1">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="price">
            Price ($)
          </label>
          <input
            type="number"
            name="price"
            id="price"
            v-model="formData.price"
            @change="handleChange"
            class="form-input"
            step="0.01"
            required
          />
        </div>
        <div class="col-span-1">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="stock">
            Stock
          </label>
          <input
            type="number"
            name="stock"
            id="stock"
            v-model="formData.stock"
            @change="handleChange"
            class="form-input"
            required
          />
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
            @change="handleChange"
            class="form-textarea"
          ></textarea>
        </div>
        <div class="col-span-2">
          <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="image_url"
          >
            Image URL
          </label>
          <input
            type="text"
            name="image_url"
            id="image_url"
            v-model="formData.image_url"
            @change="handleChange"
            class="form-input"
          />
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
          >
            {{ product ? "Update Graphic Card" : "Add Graphic Card" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, watch, defineProps, defineEmits } from "vue";
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
  image_url: "",
});
const brands = ref([]); // To populate the brand dropdown
const loading = ref(true); // Loading state for fetching brands
const message = ref(null);
const messageType = ref("");

const fetchBrands = async () => {
  try {
    const data = await apiCall("brands");
    brands.value = data;
  } catch (error) {
    messageType.value = "error";
    message.value = "Failed to load brands for form.";
  } finally {
    loading.value = false;
  }
};

onMounted(fetchBrands);

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
        image_url: newProduct.image_url || "",
      });
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
        image_url: "",
      });
    }
  },
  { immediate: true }
);

const handleChange = (e) => {
  const { name, value } = e.target;
  formData[name] = value;
};

const handleSubmit = (e) => {
  e.preventDefault();
  // Ensure numeric fields are numbers
  const dataToSubmit = {
    ...formData,
    vram_gb: Number(formData.vram_gb),
    boost_clock_mhz: formData.boost_clock_mhz
      ? Number(formData.boost_clock_mhz)
      : null,
    cuda_cores: formData.cuda_cores ? Number(formData.cuda_cores) : null,
    stream_processors: formData.stream_processors
      ? Number(formData.stream_processors)
      : null,
    price: Number(formData.price),
    stock: Number(formData.stock),
  };
  emit("submit", dataToSubmit, !!props.product);
};
</script>

<style scoped>
/* Scoped styles specific to this component */
</style>
