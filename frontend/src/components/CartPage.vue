<template>
  <div class="container mx-auto p-4">
    <CartSummary
      :cart="props.cart"
      :user="props.user"
      :auth-token="props.authToken"
      @remove-from-cart="emit('remove-from-cart', $event)"
      @place-order="emit('place-order')"
    />
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";
import CartSummary from "./CartSummary.vue";

// Define props and assign them to a variable named 'props'.
// This ensures that 'props' itself is a reactive object,
// and its properties (props.cart, props.user, props.authToken)
// remain reactive when accessed through 'props'.
const props = defineProps({
  cart: {
    type: Array,
    required: true,
  },
  user: Object,
  authToken: String,
});

const emit = defineEmits(["remove-from-cart", "place-order"]);
</script>

<style scoped>
/* Scoped styles for CartPage if needed */
</style>
