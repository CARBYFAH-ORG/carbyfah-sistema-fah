<!-- services\fah-admin-frontend\src\layouts\DashboardLayout.vue -->

<template>
  <div class="dashboard-layout-container">
    <!-- Header Superior - Componente separado -->
    <HeaderTop
      @search-change="handleGlobalSearch"
      @user-logout="handleUserLogout"
    />

    <!-- Layout Principal -->
    <div class="main-layout">
      <!-- Sidebar Izquierdo - Componente separado -->
      <SidebarNavigation
        :collapsed="sidebarCollapsed"
        @toggle-collapse="toggleSidebar"
        @navigate="handleNavigation"
      />

      <!-- Contenido Principal -->
      <div
        :class="['main-content', sidebarCollapsed ? 'content-expanded' : '']"
      >
        <!-- Header del Contenido - Breadcrumb personalizado -->
        <div class="content-header">
          <!-- Breadcrumb personalizado FAH -->
          <nav class="custom-breadcrumb">
            <ol class="breadcrumb-list">
              <li
                v-for="(item, index) in breadcrumbItems"
                :key="index"
                class="breadcrumb-item"
                :class="{
                  'breadcrumb-active': index === breadcrumbItems.length - 1,
                }"
              >
                <!-- Primer item con icono de inicio -->
                <router-link
                  v-if="item.to && index === 0"
                  :to="item.to"
                  class="breadcrumb-link home-link"
                >
                  <i :class="item.icon"></i>
                  <span>{{ item.label }}</span>
                </router-link>

                <!-- Items normales -->
                <router-link
                  v-else-if="item.to"
                  :to="item.to"
                  class="breadcrumb-link"
                >
                  {{ item.label }}
                </router-link>

                <!-- Item actual (sin link) -->
                <span v-else class="breadcrumb-current">
                  {{ item.label }}
                </span>

                <!-- Separador -->
                <i
                  v-if="index < breadcrumbItems.length - 1"
                  class="pi pi-chevron-right breadcrumb-separator"
                ></i>
              </li>
            </ol>
          </nav>
        </div>

        <!-- Cuerpo del Contenido -->
        <div class="content-body">
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

    // Metodos
    const toggleSidebar = () => {
      console.log("Toggling sidebar, current state:", sidebarCollapsed.value);
      sidebarCollapsed.value = !sidebarCollapsed.value;
      console.log("New sidebar state:", sidebarCollapsed.value);
    };

    // Busqueda sin notificacion
    const handleGlobalSearch = (searchValue) => {
      // Solo log, sin notificacion molesta
      console.log(`Busqueda global FAH: ${searchValue}`);
    };

    // Logout sin notificacion molesta
    const handleUserLogout = () => {
      // Solo mostrar en casos importantes
      console.log("Usuario ha cerrado sesion desde HeaderTop");
    };

    // Navegacion sin notificaciones
    const handleNavigation = (section) => {
      // Solo actualizar breadcrumb, sin notificaciones molestas
      updateBreadcrumb(section);
      console.log(`Navegacion FAH a: ${section}`);
    };

    const updateBreadcrumb = (section) => {
      const sectionNames = {
        comandancia: "Comandancia General",
        igfah: "Inspectoria General FAH",
        cofah: "Centro de Operaciones FAH",
        fa1: "FA-1: Recursos Humanos",
        fa2: "FA-2: Informacion y Analisis",
        fa3: "FA-3: Operaciones y Adiestramiento",
        fa4: "FA-4: Logistica",
        fa5: "FA-5: Planes y Politicas",
        fa6: "FA-6: Comunicaciones e Informatica",
        catalogos: "Administracion de Catalogos",
        "estructura-organizacional": "Estructura Organizacional",
        historia: "Historia Militar",
        capellania: "Capellania FAH",
        ddhh: "Derechos Humanos",
        ham: "Base Aerea HAM",
        hcm: "Base Aerea HCM",
        ama: "Academia Militar de Aviacion",
        ecmi: "ECMI",
        efsofah: "EFSOFAH",
        ecsofah: "ECSOFAH",
        peda: "PEDA",
        cosecgfah: "COSECGFAH",
        eaivr: "EAIVR",
        ayudantia: "Ayudantia de Campo",
        ventas: "Venta y Servicios",
        auditoria: "Auditoria Juridica",
        pagaduria: "Pagaduria General",
        relaciones: "Relaciones Publicas",
        protocolo: "Protocolo",
        seguridad: "Seguridad Operacional",
        investigacion: "Investigacion",
        sanidad: "Sanidad Militar",
        demfah: "Doctrina Militar",
        usuarios: "Gestion de Usuarios",
        permisos: "Permisos y Roles",
        configuracion: "Configuracion del Sistema",
        ayuda: "Centro de Ayuda",
        acerca: "Acerca de CARBYFAH",
        ajustes: "Ajustes del Sistema",
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

      // Metodos
      toggleSidebar,
      handleGlobalSearch,
      handleUserLogout,
      handleNavigation,
    };
  },
};
</script>

<style scoped>
/* Dashboard layout FAH - Completamente independiente */
/* Paleta de colores autorizada FAH - Sin dependencias externas */

/* Contenedor principal */
.dashboard-layout-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background: #f8f9fa;
  overflow: hidden;
}

/* Layout principal */
.main-layout {
  display: flex;
  flex: 1;
  margin-top: 70px;
  height: 100%;
}

/* Contenido principal */
.main-content {
  flex: 1;
  background: #f8f9fa;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.content-expanded {
  margin-left: 0;
}

/* Header del contenido */
.content-header {
  background: #ffffff;
  padding: 20px 28px;
  border-bottom: 1px solid #e9ecef;
  box-shadow: 0 2px 8px rgba(30, 58, 95, 0.1);
  backdrop-filter: blur(12px);
  flex-shrink: 0;
  position: relative;
  z-index: 10;
}

/* Breadcrumb personalizado FAH */
.custom-breadcrumb {
  width: 100%;
}

.breadcrumb-list {
  display: flex;
  align-items: center;
  margin: 0;
  padding: 0;
  list-style: none;
  flex-wrap: wrap;
  gap: 4px;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  font-size: 14px;
  font-weight: 500;
}

/* Enlaces del breadcrumb */
.breadcrumb-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 6px;
  text-decoration: none;
  color: #495057;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.breadcrumb-link:hover {
  color: #1e3a5f;
  background: #f8f9fa;
  border-color: #e9ecef;
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(30, 58, 95, 0.1);
}

.breadcrumb-link:focus {
  outline: none;
  box-shadow: 0 0 0 2px #d4af37;
  color: #1e3a5f;
}

/* Enlace de inicio especial */
.home-link {
  background: linear-gradient(
    135deg,
    rgba(212, 175, 55, 0.1),
    rgba(212, 175, 55, 0.05)
  );
  border-color: rgba(212, 175, 55, 0.2);
  color: #1e3a5f;
  font-weight: 600;
}

.home-link:hover {
  background: linear-gradient(
    135deg,
    rgba(212, 175, 55, 0.15),
    rgba(212, 175, 55, 0.08)
  );
  border-color: #d4af37;
  color: #1e3a5f;
  box-shadow: 0 3px 8px rgba(212, 175, 55, 0.2);
}

.home-link i {
  color: #d4af37;
  font-size: 16px;
}

/* Item actual (sin enlace) */
.breadcrumb-current {
  padding: 6px 12px;
  color: #1e3a5f;
  font-weight: 600;
  background: linear-gradient(
    135deg,
    rgba(30, 58, 95, 0.05),
    rgba(30, 58, 95, 0.02)
  );
  border-radius: 6px;
  border: 1px solid rgba(30, 58, 95, 0.1);
}

.breadcrumb-active .breadcrumb-current {
  color: #1e3a5f;
  font-weight: 600;
}

/* Separadores */
.breadcrumb-separator {
  color: #6c757d;
  font-size: 10px;
  margin: 0 8px;
  opacity: 0.7;
}

/* Cuerpo del contenido */
.content-body {
  flex: 1;
  padding: 28px;
  overflow-y: auto;
  overflow-x: hidden;

  /* Scrollbar personalizado con paleta FAH */
  scrollbar-width: thin;
  scrollbar-color: rgba(30, 58, 95, 0.3) transparent;
}

.content-body::-webkit-scrollbar {
  width: 8px;
}

.content-body::-webkit-scrollbar-track {
  background: #f8f9fa;
  border-radius: 4px;
}

.content-body::-webkit-scrollbar-thumb {
  background: rgba(30, 58, 95, 0.3);
  border-radius: 4px;
  transition: background 0.2s ease;
}

.content-body::-webkit-scrollbar-thumb:hover {
  background: rgba(30, 58, 95, 0.5);
}

/* Animacion de entrada */
.dashboard-layout-container {
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Efectos especiales */
.content-header::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, #d4af37, transparent);
  opacity: 0.3;
}

/* Responsive design */

/* Tablet */
@media (max-width: 768px) {
  .content-header {
    padding: 16px 20px;
  }

  .content-body {
    padding: 20px;
  }

  .breadcrumb-list {
    flex-wrap: wrap;
    gap: 2px;
  }

  .breadcrumb-link {
    padding: 4px 8px;
    font-size: 13px;
  }

  .breadcrumb-current {
    padding: 4px 8px;
    font-size: 13px;
  }
}

/* Mobile */
@media (max-width: 480px) {
  .content-header {
    padding: 12px 16px;
  }

  .content-body {
    padding: 16px;
  }

  .breadcrumb-link {
    padding: 3px 6px;
    font-size: 12px;
  }

  .breadcrumb-current {
    padding: 3px 6px;
    font-size: 12px;
  }

  .breadcrumb-separator {
    margin: 0 4px;
    font-size: 9px;
  }

  /* Ocultar texto de Inicio en movil, solo mostrar icono */
  .home-link span {
    display: none;
  }
}

/* Mobile Small */
@media (max-width: 320px) {
  .content-header {
    padding: 8px 12px;
  }

  .content-body {
    padding: 12px;
  }
}

/* Mejoras de accesibilidad */
@media (prefers-reduced-motion: reduce) {
  .main-content,
  .breadcrumb-link,
  .dashboard-layout-container {
    transition: none;
    animation: none;
  }
}

/* Focus visible para mejor accesibilidad */
.breadcrumb-link:focus-visible {
  outline: none;
  box-shadow: 0 0 0 2px #d4af37;
  background: #f8f9fa;
}

/* Estado de carga opcional */
.content-body.loading {
  opacity: 0.7;
  pointer-events: none;
}

.content-body.loading::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  width: 32px;
  height: 32px;
  border: 3px solid #e9ecef;
  border-top-color: #d4af37;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  transform: translate(-50%, -50%);
}

@keyframes spin {
  to {
    transform: translate(-50%, -50%) rotate(360deg);
  }
}
</style>
