<template>
  <div
    :class="[
      'fixed left-0 top-[70px] h-[calc(100vh-70px)] bg-white border-r border-gray-200 shadow-lg transition-all duration-300 z-40 flex flex-col',
      collapsed ? 'w-20' : 'w-80',
    ]"
  >
    <!-- Header del Sidebar -->
    <div
      class="p-4 border-b border-gray-200 bg-gradient-to-r from-[#1e3a5f] to-[#2a4a6f]"
    >
      <div class="flex items-center justify-between">
        <div v-if="!collapsed" class="flex items-center gap-3">
          <div
            class="w-8 h-8 bg-[#d4af37] rounded-lg flex items-center justify-center"
          >
            <i class="pi pi-users text-white text-sm font-bold"></i>
          </div>
          <div>
            <h3 class="text-white font-semibold text-sm">Personal FAH</h3>
            <p class="text-[#d4af37] text-xs">FA-1 Management</p>
          </div>
        </div>

        <!-- Botón de colapsar -->
        <Button
          :icon="collapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'"
          class="p-1 bg-white bg-opacity-10 border border-white border-opacity-20 text-white hover:bg-opacity-20 rounded"
          @click="toggleCollapse"
          v-tooltip.right="collapsed ? 'Expandir menú' : 'Contraer menú'"
        />
      </div>
    </div>

    <!-- Navegación Principal -->
    <div class="flex-1 overflow-y-auto personal-scrollbar">
      <nav class="p-2">
        <!-- Dashboard -->
        <div class="mb-6">
          <div
            v-if="!collapsed"
            class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider"
          >
            Panel Principal
          </div>

          <router-link
            to="/dashboard"
            class="navigation-item group"
            :class="{ active: currentRoute === 'dashboard' }"
          >
            <div class="nav-icon-container">
              <i class="pi pi-chart-line text-lg"></i>
            </div>
            <div v-if="!collapsed" class="nav-content">
              <span class="nav-title">Dashboard Personal</span>
              <span class="nav-subtitle">Estadísticas generales</span>
            </div>
          </router-link>
        </div>

        <!-- Gestión de Personal -->
        <div class="mb-6">
          <div
            v-if="!collapsed"
            class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider"
          >
            Gestión Personal
          </div>

          <!-- Datos Personales -->
          <router-link
            to="/gestion-personal"
            class="navigation-item group"
            :class="{ active: currentRoute === 'gestion-personal' }"
          >
            <div class="nav-icon-container">
              <i class="pi pi-users text-lg"></i>
            </div>
            <div v-if="!collapsed" class="nav-content">
              <span class="nav-title">Gestión Personal</span>
              <span class="nav-subtitle">3,644 efectivos FAH</span>
            </div>
            <div v-if="!collapsed" class="nav-badge">
              <Badge value="3644" severity="info" />
            </div>
          </router-link>

          <!-- Perfiles Militares -->
          <div
            class="navigation-item cursor-pointer group"
            @click="toggleSubmenu('perfiles')"
          >
            <div class="nav-icon-container">
              <i class="pi pi-id-card text-lg"></i>
            </div>
            <div v-if="!collapsed" class="nav-content">
              <span class="nav-title">Perfiles Militares</span>
              <span class="nav-subtitle">Rangos y especialidades</span>
            </div>
            <div v-if="!collapsed" class="nav-arrow">
              <i
                :class="[
                  'pi transition-transform duration-200',
                  openSubmenus.perfiles
                    ? 'pi-chevron-down'
                    : 'pi-chevron-right',
                ]"
              ></i>
            </div>
          </div>

          <!-- Submenu Perfiles -->
          <div
            v-if="!collapsed && openSubmenus.perfiles"
            class="ml-12 mt-2 space-y-1"
          >
            <div class="submenu-item">
              <i class="pi pi-star text-sm"></i>
              <span>Oficiales (551)</span>
            </div>
            <div class="submenu-item">
              <i class="pi pi-certificate text-sm"></i>
              <span>Suboficiales (711)</span>
            </div>
            <div class="submenu-item">
              <i class="pi pi-shield text-sm"></i>
              <span>Tropa (1,472)</span>
            </div>
            <div class="submenu-item">
              <i class="pi pi-user text-sm"></i>
              <span>Auxiliares (460)</span>
            </div>
          </div>

          <!-- Asignaciones -->
          <div
            class="navigation-item cursor-pointer group"
            @click="navigateToAsignaciones"
          >
            <div class="nav-icon-container">
              <i class="pi pi-sitemap text-lg"></i>
            </div>
            <div v-if="!collapsed" class="nav-content">
              <span class="nav-title">Asignaciones</span>
              <span class="nav-subtitle">Puestos actuales</span>
            </div>
            <div v-if="!collapsed" class="nav-badge">
              <Badge :value="personalActivo" severity="success" />
            </div>
          </div>
        </div>

        <!-- Estructura y Organigramas -->
        <div class="mb-6">
          <div
            v-if="!collapsed"
            class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider"
          >
            Estructura FAH
          </div>

          <router-link
            to="/organigramas"
            class="navigation-item group"
            :class="{ active: currentRoute === 'organigramas' }"
          >
            <div class="nav-icon-container">
              <i class="pi pi-share-alt text-lg"></i>
            </div>
            <div v-if="!collapsed" class="nav-content">
              <span class="nav-title">Organigramas</span>
              <span class="nav-subtitle">Estructura organizacional</span>
            </div>
          </router-link>
        </div>

        <!-- Reportes y Estadísticas -->
        <div class="mb-6">
          <div
            v-if="!collapsed"
            class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider"
          >
            Análisis
          </div>

          <router-link
            to="/reportes"
            class="navigation-item group"
            :class="{ active: currentRoute === 'reportes' }"
          >
            <div class="nav-icon-container">
              <i class="pi pi-chart-bar text-lg"></i>
            </div>
            <div v-if="!collapsed" class="nav-content">
              <span class="nav-title">Reportes</span>
              <span class="nav-subtitle">Estadísticas y análisis</span>
            </div>
          </router-link>
        </div>

        <!-- Enlaces Externos -->
        <div class="mb-6">
          <div
            v-if="!collapsed"
            class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider"
          >
            Enlaces Rápidos
          </div>

          <div
            class="navigation-item cursor-pointer group"
            @click="abrirAdminPrincipal"
          >
            <div class="nav-icon-container">
              <i class="pi pi-external-link text-lg"></i>
            </div>
            <div v-if="!collapsed" class="nav-content">
              <span class="nav-title">Admin Principal</span>
              <span class="nav-subtitle">Sistema centralizado</span>
            </div>
          </div>
        </div>
      </nav>
    </div>

    <!-- Footer del Sidebar -->
    <div class="p-4 border-t border-gray-200 bg-gray-50">
      <div v-if="!collapsed" class="text-center">
        <div class="text-xs text-gray-500 mb-1">Personal Service v1.0</div>
        <div class="flex items-center justify-center gap-2">
          <div class="w-2 h-2 bg-[#28a745] rounded-full"></div>
          <span class="text-xs text-gray-600">Sistema Operativo</span>
        </div>
      </div>
      <div v-else class="flex justify-center">
        <div class="w-3 h-3 bg-[#28a745] rounded-full"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useToast } from "primevue/usetoast";
import { usePersonalStore } from "@/stores/personalStore";

export default {
  name: "SidebarPersonal",
  props: {
    collapsed: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["toggle-collapse", "navigate"],
  setup(props, { emit }) {
    const router = useRouter();
    const route = useRoute();
    const toast = useToast();
    const personalStore = usePersonalStore();

    // Estado reactivo
    const openSubmenus = ref({
      perfiles: false,
      asignaciones: false,
    });

    // Computed properties
    const currentRoute = computed(() => route.name);
    const personalActivo = computed(
      () => personalStore.estadisticas.activos || 3580
    );

    // Métodos
    const toggleCollapse = () => {
      emit("toggle-collapse");
    };

    const toggleSubmenu = (menu) => {
      openSubmenus.value[menu] = !openSubmenus.value[menu];
    };

    const navigateToAsignaciones = () => {
      toast.add({
        severity: "info",
        summary: "Función Disponible",
        detail: "Asignaciones estará disponible en próximas versiones",
        life: 3000,
      });

      emit("navigate", "asignaciones");
    };

    const abrirAdminPrincipal = () => {
      console.log("🔗 Abriendo Admin Principal desde Sidebar");

      toast.add({
        severity: "info",
        summary: "Enlace Externo",
        detail: "Abriendo Sistema Admin Principal FAH",
        life: 2000,
      });

      setTimeout(() => {
        window.open("http://localhost:5173", "_blank");
      }, 500);
    };

    return {
      // Estado
      openSubmenus,

      // Computed
      currentRoute,
      personalActivo,

      // Métodos
      toggleCollapse,
      toggleSubmenu,
      navigateToAsignaciones,
      abrirAdminPrincipal,
    };
  },
};
</script>

<style scoped>
/* Estilos base para navegación */
.navigation-item {
  @apply flex items-center px-3 py-3 mx-1 rounded-lg transition-all duration-200 cursor-pointer;
  @apply hover:bg-gray-50 hover:shadow-sm;
}

.navigation-item.active {
  @apply bg-gradient-to-r from-[#0ea5e9] to-[#0284c7] text-white shadow-md;
}

.navigation-item.active .nav-icon-container {
  @apply text-white;
}

.nav-icon-container {
  @apply w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600;
  @apply group-hover:bg-gray-200 transition-colors duration-200;
}

.navigation-item.active .nav-icon-container {
  @apply bg-white bg-opacity-20 text-white;
}

.nav-content {
  @apply flex-1 ml-3;
}

.nav-title {
  @apply block text-sm font-medium text-gray-900;
}

.navigation-item.active .nav-title {
  @apply text-white;
}

.nav-subtitle {
  @apply block text-xs text-gray-500 mt-0.5;
}

.navigation-item.active .nav-subtitle {
  @apply text-blue-100;
}

.nav-badge {
  @apply ml-auto;
}

.nav-arrow {
  @apply ml-auto text-gray-400;
}

.navigation-item.active .nav-arrow {
  @apply text-white;
}

/* Submenu items */
.submenu-item {
  @apply flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900;
  @apply hover:bg-gray-50 rounded cursor-pointer transition-colors duration-200;
}

/* Scrollbar personalizado */
.personal-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.personal-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.personal-scrollbar::-webkit-scrollbar-track {
  background: #f7fafc;
}

.personal-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 2px;
}

.personal-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

/* Animaciones */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.submenu-item {
  animation: slideIn 0.2s ease-out;
}

/* Estados responsivos */
@media (max-width: 768px) {
  .navigation-item {
    @apply py-4;
  }

  .nav-title {
    @apply text-base;
  }
}
</style>
