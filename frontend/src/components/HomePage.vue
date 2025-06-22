<template>
  <div
    class="min-h-screen bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100"
  >
    <!-- Hero Section -->
    <section
      class="relative bg-gradient-to-r from-purple-600 to-indigo-700 py-20 px-4 sm:px-6 lg:px-8 shadow-lg overflow-hidden rounded-b-xl"
    >
      <div class="absolute inset-0 z-0 opacity-20">
        <!-- Abstract background pattern for visual flair -->
        <svg
          class="absolute inset-0 h-full w-full stroke-current text-purple-200 dark:text-indigo-800 opacity-50"
          fill="none"
        >
          <defs>
            <pattern
              id="pattern-circles"
              x="0"
              y="0"
              width="20"
              height="20"
              patternUnits="userSpaceOnUse"
            >
              <circle cx="10" cy="10" r="1" />
            </pattern>
          </defs>
          <rect
            x="0"
            y="0"
            width="100%"
            height="100%"
            fill="url(#pattern-circles)"
          />
        </svg>
      </div>
      <div class="relative z-10 max-w-4xl mx-auto text-center">
        <h1
          class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6 animate-fade-in-down"
        >
          Unleash Your Gaming Potential
        </h1>
        <p
          class="text-lg sm:text-xl text-indigo-100 mb-8 max-w-2xl mx-auto animate-fade-in-up"
        >
          Explore the latest and most powerful graphic cards to elevate your
          visual experience and dominate every game.
        </p>
        <router-link
          to="/products"
          class="inline-block bg-white text-purple-700 hover:bg-purple-100 transition-all duration-300 ease-in-out px-8 py-3 rounded-full text-lg font-semibold shadow-xl hover:shadow-2xl transform hover:scale-105"
        >
          Browse Graphic Cards
        </router-link>
      </div>
    </section>

    <!-- Dynamic Featured Products/Ads Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8">
      <div class="max-w-6xl mx-auto">
        <h2
          class="text-3xl sm:text-4xl font-bold text-center mb-12 text-gray-800 dark:text-gray-200"
        >
          Featured Picks & Hot Deals
        </h2>

        <div v-if="loading" class="text-center py-8">
          <LoadingSpinner />
          <p class="text-gray-600 mt-4">Loading featured graphic cards...</p>
        </div>
        <!-- Re-enabled Message component display -->
        <div v-else-if="message" class="text-center py-8">
          <Message :type="messageType" :message="message" />
        </div>
        <div
          v-else-if="randomGraphicCards.length === 0"
          class="text-center text-gray-500 py-8"
        >
          No featured graphic cards available at the moment.
        </div>
        <div
          v-else
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"
        >
          <!-- Removed debugging borders and fixed height -->
          <router-link
            v-for="card in randomGraphicCards"
            :key="card.id"
            :to="{ name: 'graphic-card-detail', params: { id: card.id } }"
            class="block bg-white dark:bg-gray-700 rounded-lg shadow-xl p-4 flex flex-col items-center text-center transition-transform transform hover:scale-105 duration-300 group cursor-pointer"
          >
            <img
              :src="
                card.image_url
                  ? 'http://localhost' + card.image_url
                  : 'https://placehold.co/300x200/cccccc/333333?text=GPU+Image'
              "
              :alt="card.name"
              class="w-full h-48 object-contain rounded-md mb-4 group-hover:scale-110 transition-transform duration-300"
              @error="
                (e) => {
                  e.target.onerror = null;
                  e.target.src =
                    'https://placehold.co/300x200/cccccc/333333?text=Image+Not+Found'; // More descriptive placeholder
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
        <div class="text-center mt-10" v-if="randomGraphicCards.length > 0">
          <router-link
            :to="{ name: 'products' }"
            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            View All Products
            <svg
              class="ml-3 -mr-1 h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
              aria-hidden="true"
            >
              <path
                fill-rule="evenodd"
                d="M10.293 15.707a1 1 0 010-1.414L14.586 10H4a1 1 0 110-2h10.586l-4.293-4.293a1 1 0 111.414-1.414l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </router-link>
        </div>
      </div>
    </section>

    <!-- About Us / Value Proposition Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8 bg-purple-50 dark:bg-gray-800">
      <div class="max-w-4xl mx-auto text-center">
        <h2
          class="text-3xl sm:text-4xl font-bold text-purple-800 dark:text-purple-200 mb-8"
        >
          Why Choose Our GPU Shop?
        </h2>
        <p
          class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed mb-6"
        >
          At GPU Shop, we are passionate about providing the latest and most
          powerful graphic cards to gamers, creators, and enthusiasts alike. We
          offer a curated selection from top brands, competitive pricing, and
          exceptional customer service to ensure you find the perfect upgrade
          for your system.
        </p>
        <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
          Our team is dedicated to helping you unlock the full potential of your
          PC with a seamless shopping experience, from browsing to checkout.
          Experience high-fidelity gaming, accelerated content creation, and
          unparalleled performance with our cutting-edge GPUs.
        </p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { apiCall } from "@/utils/api";
import { LoadingSpinner, Message } from "@/utils/components"; // Re-added Message import

const allGraphicCards = ref([]);
const randomGraphicCards = ref([]);
const loading = ref(true);
const message = ref(null);
const messageType = ref("");

const fetchAndSelectRandomGraphicCards = async () => {
  loading.value = true;
  message.value = null; // Ensure message is null at the start of fetch
  try {
    const data = await apiCall("graphic-cards");
    allGraphicCards.value = data;

    // Shuffle and select up to 4 random cards (or fewer if not enough)
    const shuffled = [...allGraphicCards.value].sort(() => 0.5 - Math.random());
    randomGraphicCards.value = shuffled.slice(0, 4);

    // Re-enabled success message logic
    messageType.value = "success";
    message.value = "Featured picks loaded successfully.";
  } catch (error) {
    messageType.value = "error";
    message.value = error.message || "Failed to load featured picks.";
    randomGraphicCards.value = []; // Ensure empty array on error
  } finally {
    loading.value = false;
    // Clear message after a few seconds if it's a success message
    if (messageType.value === "success") {
      setTimeout(() => {
        message.value = null;
      }, 3000); // Clear message after 3 seconds
    }
  }
};

onMounted(() => {
  fetchAndSelectRandomGraphicCards();
});
</script>

<style scoped>
/* Add simple keyframe animations for a subtle entrance effect */
@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-down {
  animation: fadeInDown 1s ease-out forwards;
}

.animate-fade-in-up {
  animation: fadeInUp 1s ease-out 0.5s forwards; /* Delayed start */
}
</style>
