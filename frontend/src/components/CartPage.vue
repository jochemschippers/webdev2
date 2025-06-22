<template>
  <div class="container mx-auto p-4">
    <CartSummary
      :cart="props.cart"
      :user="props.user"
      :auth-token="props.authToken"
      @remove-from-cart="emit('remove-from-cart', $event)"
      @place-order="emit('place-order')"
      @update-quantity="
        emit('update-cart-quantity', $event.productId, $event.newQuantity)
      "
    />
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";
import CartSummary from "./CartSummary.vue";

const props = defineProps({
  cart: {
    type: Array,
    required: true,
  },
  user: Object,
  authToken: String,
});

// Added 'update-cart-quantity' to emits
const emit = defineEmits([
  "remove-from-cart",
  "place-order",
  "update-cart-quantity",
]);
</script>

<style scoped>
/* Scoped styles for CartPage if needed */
</style>
