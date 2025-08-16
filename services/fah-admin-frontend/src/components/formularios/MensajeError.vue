<template>
  <small v-if="mensaje" class="mensaje-error">
    {{ mensaje }}
  </small>
</template>

<script>
export default {
  name: "MensajeError",

  props: {
    mensaje: {
      type: String,
      default: null,
    },
  },
};
</script>

<style scoped>
.mensaje-error {
  @apply text-red-500 text-xs mt-1 font-medium;
  @apply bg-red-900/50 px-2 py-1 rounded border-l-4 border-red-500;
  @apply block;
}
</style>
