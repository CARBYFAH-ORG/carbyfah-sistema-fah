<template>
  <div class="fa1-fuerza-view">
    <div class="fa1-header">
      <h1>FA-1 Fuerza Aérea Hondureña</h1>
      <p>Sistema estratégico de gestión de personal</p>
    </div>

    <div class="fa1-contenido">
      <!-- El contenido se carga según las rutas hijas -->
      <router-view />
    </div>
  </div>
</template>

<script>
import { computed } from "vue";
import { useNivelesStore } from "@/stores/nivelesStore";

export default {
  name: "FA1FuerzaView",
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
.fa1-fuerza-view {
  padding: 1.5rem;
}

.fa1-header {
  margin-bottom: 2rem;
}

.fa1-header h1 {
  color: #1e3a5f;
  margin-bottom: 0.5rem;
}

.fa1-header p {
  color: #6b7280;
}

.fa1-contenido {
  min-height: 400px;
}
</style>
