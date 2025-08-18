<template>
  <div class="flex flex-col h-screen font-sans bg-gray-100 overflow-hidden">
    <!-- Header Superior - Componente separado -->
    <HeaderPersonal
      @search-change="handleGlobalSearch"
      @user-logout="handleUserLogout"
    />

    <!-- Layout Principal -->
    <div class="flex flex-1 mt-[70px] h-full">
      <!-- Sidebar Izquierdo - Componente separado -->
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
        <div
          class="flex-1 p-7 overflow-y-auto scrollbar-thin scrollbar-track-gray-100 scrollbar-thumb-fah-dorado/30 hover:scrollbar-thumb-fah-dorado/50"
        >
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import HeaderPersonal from "./HeaderPersonal.vue";
import SidebarPersonal from "./SidebarPersonal.vue";

export default {
  name: "DashboardLayout",
  components: {
    HeaderPersonal,
    SidebarPersonal,
  },
  setup() {
    const router = useRouter();
    const route = useRoute();

    // Estado reactivo
    const sidebarCollapsed = ref(false);

    // Ruta actual
    const currentRoute = computed(() => route.name);

    // Breadcrumb items
    const breadcrumbItems = ref([
      { label: "Inicio", icon: "pi pi-home", to: "/dashboard" },
      { label: "Dashboard Personal FAH" },
    ]);

    // Métodos
    const toggleSidebar = () => {
      console.log("Toggling sidebar, current state:", sidebarCollapsed.value);
      sidebarCollapsed.value = !sidebarCollapsed.value;
      console.log("New sidebar state:", sidebarCollapsed.value);
    };

    // Búsqueda sin notificación
    const handleGlobalSearch = (searchValue) => {
      // Solo log, sin notificación molesta
      console.log(`Búsqueda global Personal FAH: ${searchValue}`);
    };

    // Logout sin notificación molesta
    const handleUserLogout = () => {
      // Solo mostrar en casos importantes
      console.log("Usuario ha cerrado sesión desde HeaderPersonal");
    };

    // Navegación sin notificaciones
    const handleNavigation = (section) => {
      // Solo actualizar breadcrumb, sin notificaciones molestas
      updateBreadcrumb(section);
      console.log(`Navegación Personal FAH a: ${section}`);
    };

    const updateBreadcrumb = (section) => {
      const sectionNames = {
        dashboard: "Dashboard Personal",
        inicio: "Inicio",
        "espacio-trabajo": "Espacio de Trabajo",
        "jefe-fa1": "Jefe FA-1",
        "enc-s1": "Encargado S-1",
        "cmdte-base": "Comandante Base",
        "admin-personal": "Administración Personal",
        "mantenimiento-efectivo": "Mantenimiento Efectivo",
        "disciplina-orden": "Disciplina y Orden",
        "servicios-personal": "Servicios Personal",
        "bienestar-personal": "Bienestar Personal",
        "roles-usuarios": "Roles y Usuarios",
        "permisos-roles": "Permisos y Roles",
        "diagrama-fah": "Diagrama FAH",
        "configuracion-sistema": "Configuración Sistema",
        organigrama: "Organigrama",
        solicitudes: "Solicitudes",
        "situacion-personal": "Situación Personal",
        agenda: "Agenda Inteligente",
      };

      breadcrumbItems.value = [
        { label: "Inicio", icon: "pi pi-home", to: "/dashboard" },
        { label: sectionNames[section] || section.toUpperCase() },
      ];
    };

    return {
      // Estado
      sidebarCollapsed,
      currentRoute,
      breadcrumbItems,

      // Métodos
      toggleSidebar,
      handleGlobalSearch,
      handleUserLogout,
      handleNavigation,
    };
  },
};
</script>

<style>
/* Importar estilos externos organizados */
@import "@/styles/components/layout/dashboard-layout-fah.css";
</style>
