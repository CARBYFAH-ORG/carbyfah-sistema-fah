<template>
  <div id="app">
    <Toast />
    <router-view />
  </div>
</template>

<script>
export default {
  name: "App",
};
</script>

<style>
/* Importar estilos globales organizados */
@import "@/styles/base/global.css";
</style>
