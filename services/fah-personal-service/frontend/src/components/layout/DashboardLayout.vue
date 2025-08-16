<template>
  <div class="flex flex-col h-screen font-sans bg-gray-100 overflow-hidden">
    <!-- Header Superior -->
    <HeaderPersonal
      @search-change="handleGlobalSearch"
      @user-logout="handleUserLogout"
    />

    <!-- Layout Principal -->
    <div class="flex flex-1 mt-[70px] h-full">
      <!-- Sidebar Izquierdo -->
      <SidebarPersonal
        :collapsed="sidebarCollapsed"
        @toggle-collapse="toggleSidebar"
        @navigate="handleNavigation"
      />

      <!-- Contenido Principal -->
      <div
        :class="[
          'flex-1 bg-gray-100 transition-all duration-300 flex flex-col h-full',
          sidebarCollapsed ? 'content-expanded' : '',
        ]"
      >
        <!-- Header del Contenido -->
        <div
          class="bg-white px-7 py-4 border-b border-gray-300 shadow-sm backdrop-blur-md flex-shrink-0"
        >
          <Breadcrumb :model="breadcrumbItems" />
        </div>

        <!-- Cuerpo del Contenido -->
        <div class="flex-1 p-7 overflow-y-auto personal-scrollbar">
          <router-view />
        </div>
      </div>
    </div>

    <!-- Toast para notificaciones -->
    <Toast />
  </div>
</template>

<script>
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useToast } from "primevue/usetoast";
import HeaderPersonal from "../components/layout/HeaderPersonal.vue";
import SidebarPersonal from "../components/layout/SidebarPersonal.vue";

export default {
  name: "DashboardLayout",
  components: {
    HeaderPersonal,
    SidebarPersonal,
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const toast = useToast();

    // Estado reactivo
    const sidebarCollapsed = ref(false);

    // Ruta actual
    const currentRoute = computed(() => route.name);

    // Breadcrumb items dinámicos
    const breadcrumbItems = computed(() => {
      const items = [
        {
          label: "FA-1 Personal",
          icon: "pi pi-users",
          command: () => router.push("/dashboard"),
        },
      ];

      switch (currentRoute.value) {
        case "dashboard":
          items.push({ label: "Dashboard" });
          break;
        case "gestion-personal":
          items.push({
            label: "Gestión",
            command: () => router.push("/gestion-personal"),
          });
          items.push({ label: "Personal FAH" });
          break;
        case "organigramas":
          items.push({
            label: "Estructuras",
            command: () => router.push("/organigramas"),
          });
          items.push({ label: "Organigramas" });
          break;
        case "reportes":
          items.push({
            label: "Análisis",
            command: () => router.push("/reportes"),
          });
          items.push({ label: "Reportes" });
          break;
        default:
          items.push({ label: "Sistema Personal" });
      }

      return items;
    });

    // Métodos
    const toggleSidebar = () => {
      sidebarCollapsed.value = !sidebarCollapsed.value;

      toast.add({
        severity: "info",
        summary: "Navegación FAH",
        detail: sidebarCollapsed.value
          ? "Sidebar contraído"
          : "Sidebar expandido",
        life: 1500,
      });
    };

    const handleNavigation = (section) => {
      console.log(`🚁 Navegando a sección: ${section}`);
    };

    const handleGlobalSearch = (searchTerm) => {
      console.log(`🔍 Búsqueda global en Personal: ${searchTerm}`);

      if (searchTerm.trim()) {
        toast.add({
          severity: "info",
          summary: "Búsqueda Personal",
          detail: `Buscando: "${searchTerm}"`,
          life: 3000,
        });
      }
    };

    const handleUserLogout = () => {
      console.log("👋 Cerrando sesión en Personal Service");

      toast.add({
        severity: "warn",
        summary: "Cerrando Sesión",
        detail: "Hasta pronto, personal FAH",
        life: 2000,
      });

      setTimeout(() => {
        localStorage.removeItem("fah_personal_token");
        localStorage.removeItem("fah_personal_user");
        router.push("/login");
      }, 1000);
    };

    return {
      // Estado
      sidebarCollapsed,
      currentRoute,
      breadcrumbItems,

      // Métodos
      toggleSidebar,
      handleNavigation,
      handleGlobalSearch,
      handleUserLogout,
    };
  },
};
</script>

<style scoped>
/* Transiciones para el contenido */
.content-expanded {
  margin-left: -200px;
}

/* Estilos para breadcrumb personalizado */
:deep(.p-breadcrumb) {
  background: transparent;
  border: none;
  padding: 0;
}

:deep(.p-breadcrumb .p-breadcrumb-list) {
  background: transparent;
}

:deep(.p-breadcrumb .p-menuitem-text) {
  color: var(--fah-personal-primary);
  font-weight: 500;
}

:deep(.p-breadcrumb .p-menuitem-separator) {
  color: var(--text-muted);
}

:deep(.p-breadcrumb .p-menuitem-link:hover .p-menuitem-text) {
  color: var(--crud-create);
}

/* Personalización del scrollbar */
.personal-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: var(--personal-main) var(--bg-secondary);
}

.personal-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.personal-scrollbar::-webkit-scrollbar-track {
  background: var(--bg-secondary);
  border-radius: 3px;
}

.personal-scrollbar::-webkit-scrollbar-thumb {
  background: var(--personal-main);
  border-radius: 3px;
  transition: background var(--transition-fast);
}

.personal-scrollbar::-webkit-scrollbar-thumb:hover {
  background: var(--personal-dark);
}
</style>
