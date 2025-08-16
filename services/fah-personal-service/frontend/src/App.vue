<template>
  <div id="app">
    <!-- Router View Principal -->
    <router-view />

    <!-- Toast Global para notificaciones -->
    <Toast />

    <!-- Dialog de Confirmación Global -->
    <ConfirmDialog />
  </div>
</template>

<script>
import { onMounted } from "vue";
import { usePersonalStore } from "@/stores/personalStore";

export default {
  name: "App",
  setup() {
    const personalStore = usePersonalStore();

    // Inicialización de la aplicación
    onMounted(async () => {
      console.log("🚁 FAH Personal Service iniciando...");

      try {
        // Cargar estadísticas iniciales
        await personalStore.cargarEstadisticas();
        console.log("✅ Personal Service inicializado correctamente");
      } catch (error) {
        console.error("❌ Error inicializando Personal Service:", error);
      }
    });

    return {};
  },
};
</script>

<style>
/* Estilos globales para la aplicación Personal FAH */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8f9fa;
  color: #343a40;
  line-height: 1.6;
}

#app {
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

/* Estilos para elementos de carga */
.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #1e3a5f 0%, #2a4a6f 100%);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(212, 175, 55, 0.3);
  border-top: 4px solid #d4af37;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Clases utilitarias globales */
.fah-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1rem;
}

.fah-section {
  margin-bottom: 2rem;
}

.fah-title {
  color: #1e3a5f;
  font-weight: 600;
  margin-bottom: 1rem;
}

.fah-subtitle {
  color: #495057;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

/* Animaciones suaves para transiciones */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from {
  transform: translateX(-100%);
}

.slide-leave-to {
  transform: translateX(100%);
}

/* Responsive helpers */
@media (max-width: 768px) {
  .fah-container {
    padding: 0 0.5rem;
  }

  .fah-section {
    margin-bottom: 1.5rem;
  }
}

/* Estilos para accesibilidad */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* Focus visible para elementos interactivos */
button:focus-visible,
a:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible {
  outline: 2px solid #d4af37;
  outline-offset: 2px;
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }

  body {
    background: white !important;
    color: black !important;
  }
}
</style>
