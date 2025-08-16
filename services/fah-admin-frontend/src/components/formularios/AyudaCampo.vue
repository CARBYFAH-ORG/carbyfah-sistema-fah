<template>
  <small v-if="texto" class="ayuda-campo">
    <i class="pi pi-info-circle mr-1"></i>
    {{ texto }}
  </small>
</template>

<script>
export default {
  name: "AyudaCampo",

  props: {
    texto: {
      type: String,
      default: null,
    },
  },
};
</script>

<style scoped>
.ayuda-campo {
  @apply text-blue-400 text-xs mt-1 font-normal;
  @apply bg-blue-900/30 px-2 py-1 rounded border-l-4 border-blue-400;
  @apply flex items-center;
}
</style>
