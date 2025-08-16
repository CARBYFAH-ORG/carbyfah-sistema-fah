<template>
  <div class="seccion-view">
    <div class="seccion-header">
      <h1>Vista de Sección</h1>
      <p>Panel operativo para jefes de sección</p>
    </div>

    <div class="seccion-contenido">
      <!-- El contenido se carga según las rutas hijas -->
      <router-view />
    </div>
  </div>
</template>

<script>
import { computed } from "vue";
import { useNivelesStore } from "@/stores/nivelesStore";

export default {
  name: "SeccionView",
  setup() {
    const nivelesStore = useNivelesStore();

    const usuarioActual = computed(() => nivelesStore.usuarioActual);

    return {
      usuarioActual,
    };
  },
};
</script>

<style scoped>
.seccion-view {
  padding: 1.5rem;
}

.seccion-header {
  margin-bottom: 2rem;
}

.seccion-header h1 {
  color: #1e3a5f;
  margin-bottom: 0.5rem;
}

.seccion-header p {
  color: #6b7280;
}

.seccion-contenido {
  min-height: 400px;
}
</style>
