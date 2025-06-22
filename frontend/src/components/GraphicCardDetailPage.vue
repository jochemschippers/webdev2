<template>
  <div
    class="container mx-auto p-4 bg-gray-50 rounded-lg shadow-md min-h-[500px]"
  >
    <div v-if="loading" class="text-center py-8">
      <LoadingSpinner />
      <p class="text-gray-600 mt-4">Loading graphic card details...</p>
    </div>
    <div v-else-if="!graphicCard" class="text-center text-red-500 py-8">
      <Message type="error" message="Graphic card not found." />
      <router-link
        :to="{ name: 'products' }"
        class="text-blue-600 hover:underline mt-4 inline-block"
      >
        &larr; Back to Products
      </router-link>
    </div>
    <div v-else class="max-w-4xl mx-auto">
      <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
        <!-- Image Section -->
        <div class="md:w-1/3 flex-shrink-0">
          <img
            :src="
              'http://localhost' + (graphicCard.image_url || '/placeholder.png')
            "
            :alt="graphicCard.name"
            class="w-32 h-32 object-contain mb-4 rounded-md transition-transform duration-300 hover:scale-105"
            @error="
              (e) => {
                e.target.onerror = null;
                e.target.src =
                  'https://placehold.co/150x150/E2E8F0/1A202C?text=GPU';
              }
            "
          />
        </div>

        <!-- Details Section -->
        <div class="md:w-2/3">
          <h1 class="text-4xl font-bold text-gray-800 mb-4">
            {{ graphicCard.name }}
          </h1>
          <p class="text-xl font-semibold text-blue-600 mb-6">
            ${{ parseFloat(graphicCard.price).toFixed(2) }}
          </p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-gray-700">
            <p><strong>Model:</strong> {{ graphicCard.gpu_model }}</p>
            <p><strong>Brand:</strong> {{ graphicCard.brand_name }}</p>
            <p>
              <strong>Manufacturer:</strong> {{ graphicCard.manufacturer_name }}
            </p>
            <p><strong>VRAM:</strong> {{ graphicCard.vram_gb }} GB</p>
            <p><strong>Interface:</strong> {{ graphicCard.interface }}</p>
            <p v-if="graphicCard.boost_clock_mhz">
              <strong>Boost Clock:</strong>
              {{ graphicCard.boost_clock_mhz }} MHz
            </p>
            <p v-if="graphicCard.cuda_cores">
              <strong>CUDA Cores:</strong> {{ graphicCard.cuda_cores }}
            </p>
            <p v-if="graphicCard.stream_processors">
              <strong>Stream Processors:</strong>
              {{ graphicCard.stream_processors }}
            </p>
            <p
              :class="`font-medium ${
                graphicCard.stock > 0 ? 'text-green-600' : 'text-red-600'
              }`"
            >
              <strong>Stock:</strong>
              {{ graphicCard.stock > 0 ? graphicCard.stock : "Out of Stock" }}
            </p>
          </div>

          <p
            v-if="graphicCard.description"
            class="text-gray-800 leading-relaxed mb-6"
          >
            {{ graphicCard.description }}
          </p>
          <p v-else class="text-gray-500 italic mb-6">
            No detailed description available.
          </p>

          <div class="flex items-center space-x-4">
            <!-- Quantity Input -->
            <div
              class="flex items-center border border-gray-300 rounded-md p-1"
            >
              <button
                @click="decreaseQuantity"
                class="px-2 py-1 text-gray-600 hover:bg-gray-200 rounded-l-md"
              >
                -
              </button>
              <input
                type="number"
                v-model.number="quantity"
                min="1"
                :max="graphicCard.stock"
                class="w-16 text-center border-x border-gray-300 text-gray-700 focus:outline-none focus:ring-0 focus:border-transparent"
                @input="validateQuantity"
              />
              <button
                @click="increaseQuantity"
                class="px-2 py-1 text-gray-600 hover:bg-gray-200 rounded-r-md"
              >
                +
              </button>
            </div>

            <button
              @click="handleAddToCartClick"
              :disabled="graphicCard.stock === 0 || quantity === 0"
              class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-md shadow-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ graphicCard.stock > 0 ? "Add to Cart" : "Out of Stock" }}
            </button>
            <router-link
              :to="{ name: 'products' }"
              class="text-blue-600 hover:underline"
            >
              &larr; Back to Products
            </router-link>
          </div>
        </div>
      </div>

      <!-- NEW: Random Graphic Card Suggestions/Ads Section -->
      <section
        v-if="randomGraphicCards.length > 0"
        class="mt-16 py-8 px-4 sm:px-6 lg:px-8 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-inner"
      >
        <h2
          class="text-2xl sm:text-3xl font-bold text-center mb-10 text-gray-800 dark:text-gray-200"
        >
          You Might Also Like
        </h2>
        <div class="flex overflow-x-auto space-x-4 pb-4">
          <!-- Changed to flex for horizontal layout -->
          <router-link
            v-for="card in randomGraphicCards"
            :key="card.id"
            :to="{ name: 'graphic-card-detail', params: { id: card.id } }"
            class="flex-shrink-0 w-72 bg-white dark:bg-gray-700 rounded-lg shadow-xl p-4 flex flex-col items-center text-center transition-transform transform hover:scale-105 duration-300 group cursor-pointer"
          >
            <img
              :src="'http://localhost' + (card.image_url || '/placeholder.png')"
              :alt="card.name"
              class="w-32 h-32 object-contain mb-4 rounded-md transition-transform duration-300 hover:scale-105"
              @error="
                (e) => {
                  e.target.onerror = null;
                  e.target.src =
                    'https://placehold.co/150x150/E2E8F0/1A202C?text=GPU';
                }
              "
            />
            <h3 class="text-xl font-bold mb-1 text-gray-800 dark:text-gray-100">
              {{ card.name }}
            </h3>
            <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">
              {{ card.gpu_model }} | {{ card.vram_gb }} GB VRAM
            </p>
            <span
              class="text-2xl font-extrabold text-green-600 dark:text-green-400"
              >${{ parseFloat(card.price).toFixed(2) }}</span
            >
          </router-link>
        </div>
      </section>
    </div>

    <!-- Add to Cart Notification -->
    <AddToCartNotification
      :is-visible="showAddToCartNotification"
      :product-name="notificationProductName"
      :quantity-added="notificationQuantityAdded"
      @close="showAddToCartNotification = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, inject } from "vue";
import { useRoute } from "vue-router";
import { apiCall } from "@/utils/api";
import { LoadingSpinner, Message } from "@/utils/components";
import AddToCartNotification from "./AddToCartNotification.vue"; // NEW: Import AddToCartNotification

const route = useRoute();
const graphicCard = ref(null);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");
const allGraphicCards = ref([]); // To store all graphic cards for random selection
const randomGraphicCards = ref([]); // To store selected random cards

// NEW: Quantity state for adding to cart
const quantity = ref(1);

// NEW: State for AddToCartNotification
const showAddToCartNotification = ref(false);
const notificationProductName = ref("");
const notificationQuantityAdded = ref(0);
let notificationTimeout = null; // To clear previous timeouts

// Inject the cart functions from App.vue
// Removed 'cart' as it was unused here. 'handleAppAddToCart' is sufficient.
const handleAppAddToCart = inject("handleAppAddToCart"); // Inject the main add to cart function from App.vue

const handleAddToCartClick = () => {
  if (
    graphicCard.value &&
    quantity.value > 0 &&
    quantity.value <= graphicCard.value.stock
  ) {
    handleAppAddToCart(graphicCard.value, quantity.value); // Call the main App.vue cart logic

    // Show notification
    notificationProductName.value = graphicCard.value.name;
    notificationQuantityAdded.value = quantity.value;
    showAddToCartNotification.value = true;

    // Clear any existing timeout and set a new one
    if (notificationTimeout) {
      clearTimeout(notificationTimeout);
    }
    notificationTimeout = setTimeout(() => {
      showAddToCartNotification.value = false;
    }, 3000); // Notification visible for 3 seconds
  } else if (graphicCard.value.stock === 0) {
    // Optionally provide feedback if out of stock
    messageType.value = "error";
    message.value = "Item is out of stock.";
  } else if (quantity.value === 0) {
    messageType.value = "error";
    message.value = "Quantity must be at least 1.";
  } else if (quantity.value > graphicCard.value.stock) {
    messageType.value = "error";
    message.value = `Cannot add more than available stock (${graphicCard.value.stock}).`;
  }
};

const increaseQuantity = () => {
  if (graphicCard.value && quantity.value < graphicCard.value.stock) {
    quantity.value++;
  }
};

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
};

const validateQuantity = () => {
  if (graphicCard.value) {
    if (quantity.value < 1) {
      quantity.value = 1;
    } else if (quantity.value > graphicCard.value.stock) {
      quantity.value = graphicCard.value.stock;
    }
  }
};

const fetchGraphicCard = async (id) => {
  loading.value = true;
  message.value = null; // Clear previous messages
  try {
    const data = await apiCall(`graphic-cards/${id}`);
    graphicCard.value = data;
    // Reset quantity to 1 when a new card is loaded
    quantity.value = 1;
    messageType.value = "success";
    message.value = "Graphic card details loaded.";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load graphic card details.";
    graphicCard.value = null;
  } finally {
    loading.value = false;
  }
};

const fetchAllGraphicCardsForRandomSelection = async () => {
  try {
    const data = await apiCall("graphic-cards");
    allGraphicCards.value = data;
    selectRandomGraphicCards(); // Select random cards after fetching all
  } catch (error) {
    console.error(
      "Failed to fetch all graphic cards for random selection:",
      error
    );
  }
};

const selectRandomGraphicCards = () => {
  if (!graphicCard.value) {
    randomGraphicCards.value = [];
    return;
  }

  // Filter out the current graphic card from the list
  const availableCards = allGraphicCards.value.filter(
    (card) => card.id !== graphicCard.value.id
  );

  // Shuffle the available cards
  for (let i = availableCards.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [availableCards[i], availableCards[j]] = [
      availableCards[j],
      availableCards[i],
    ];
  }

  // Select up to 5 random cards (or fewer if not enough are available)
  randomGraphicCards.value = availableCards.slice(0, 5);
};

// Fetch data when component is mounted or route ID changes
onMounted(() => {
  if (route.params.id) {
    fetchGraphicCard(route.params.id);
    fetchAllGraphicCardsForRandomSelection(); // Fetch all for random selection
  } else {
    messageType.value = "error";
    message.value = "No graphic card ID provided.";
    loading.value = false;
  }
});

watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      fetchGraphicCard(newId);
      // Re-run random selection when the main graphic card changes
      // This is important when navigating between detail pages directly
      fetchAllGraphicCardsForRandomSelection();
    }
  }
);

// Watch for graphicCard change to re-select random ones
// This handles the initial load of graphicCard data and ensures random cards update
watch(graphicCard, (newVal) => {
  if (newVal) {
    selectRandomGraphicCards();
  }
});
</script>

<style scoped>
/* Add any specific styles for GraphicCardDetailPage here */
/* Hide default input number arrows */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>
