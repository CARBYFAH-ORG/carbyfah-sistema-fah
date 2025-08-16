<template>
  <div class="flex flex-col h-screen font-sans bg-gray-100 overflow-hidden">
    <!-- Header Superior - Componente separado -->
    <HeaderTop
      @search-change="handleGlobalSearch"
      @user-logout="handleUserLogout"
    />

    <!-- Layout Principal -->
    <div class="flex flex-1 mt-[70px] h-full">
      <!-- Sidebar Izquierdo - Componente separado -->
      <SidebarNavigation
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
          class="flex-1 p-7 overflow-y-auto scrollbar-thin scrollbar-track-gray-100 scrollbar-thumb-blue-300/30 hover:scrollbar-thumb-blue-400/50"
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
import HeaderTop from "../components/layout/HeaderTop.vue";
import SidebarNavigation from "../components/layout/SidebarNavigation.vue";

export default {
  name: "DashboardLayout",
  components: {
    HeaderTop,
    SidebarNavigation,
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
      { label: "Dashboard CARBYFAH" },
    ]);

    // Métodos
    const toggleSidebar = () => {
      console.log("Toggling sidebar, current state:", sidebarCollapsed.value);
      sidebarCollapsed.value = !sidebarCollapsed.value;
      console.log("New sidebar state:", sidebarCollapsed.value);
    };

    // ✅ BÚSQUEDA SIN NOTIFICACIÓN
    const handleGlobalSearch = (searchValue) => {
      // Solo log, sin notificación molesta
      console.log(`Búsqueda global FAH: ${searchValue}`);
    };

    // ✅ LOGOUT SIN NOTIFICACIÓN MOLESTA
    const handleUserLogout = () => {
      // Solo mostrar en casos importantes
      console.log("Usuario ha cerrado sesión desde HeaderTop");
    };

    // ✅ NAVEGACIÓN SIN NOTIFICACIONES
    const handleNavigation = (section) => {
      // Solo actualizar breadcrumb, sin notificaciones molestas
      updateBreadcrumb(section);
      console.log(`Navegación FAH a: ${section}`);
    };

    const updateBreadcrumb = (section) => {
      const sectionNames = {
        comandancia: "Comandancia General",
        igfah: "Inspectoría General FAH",
        cofah: "Centro de Operaciones FAH",
        fa1: "FA-1: Recursos Humanos",
        fa2: "FA-2: Información y Análisis",
        fa3: "FA-3: Operaciones y Adiestramiento",
        fa4: "FA-4: Logística",
        fa5: "FA-5: Planes y Políticas",
        fa6: "FA-6: Comunicaciones e Informática",
        catalogos: "Administración de Catálogos",
        historia: "Historia Militar",
        capellania: "Capellanía FAH",
        ddhh: "Derechos Humanos",
        ham: "Base Aérea HAM",
        hcm: "Base Aérea HCM",
        ama: "Academia Militar de Aviación",
        ecmi: "ECMI",
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
@import "@/styles/components/layout/dashboard-layout.css";
</style>
