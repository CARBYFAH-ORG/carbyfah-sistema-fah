<template>
  <!-- Botón flotante móvil -->
  <div
    v-if="isMobile"
    class="mobile-sidebar-trigger"
    @click="showMobileSidebar = true"
  >
    <i class="pi pi-bars"></i>
  </div>

  <!-- Backdrop para móvil -->
  <div
    v-if="isMobile && showMobileSidebar"
    class="mobile-backdrop"
    @click="showMobileSidebar = false"
  ></div>

  <!-- Sidebar principal -->
  <div
    :class="[
      'sidebar-container',
      {
        'sidebar-expanded': !collapsed && !isMobile,
        'sidebar-collapsed': collapsed && !isMobile,
        'sidebar-mobile-hidden': isMobile && !showMobileSidebar,
        'sidebar-mobile-visible': isMobile && showMobileSidebar,
      },
    ]"
  >
    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <Button
        icon="pi pi-bars"
        :class="['toggle-button', { 'mobile-close-button': isMobile }]"
        @click="handleToggle"
        :title="
          isMobile
            ? 'Cerrar menú'
            : collapsed
            ? 'Expandir sidebar'
            : 'Colapsar sidebar'
        "
        unstyled
      />

      <!-- Título solo en móvil expandido -->
      <h2 v-if="isMobile && showMobileSidebar" class="mobile-sidebar-title">
        CARBYFAH
      </h2>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="sidebar-navigation">
      <!-- Dashboard -->
      <div
        :class="[
          'nav-item',
          { 'nav-item-active': currentRoute === 'dashboard' },
        ]"
      >
        <router-link to="/dashboard" class="nav-link" @click="handleNavigation">
          <i class="pi pi-home nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Inicio</span>
        </router-link>
      </div>

      <!-- Comandancia General -->
      <div class="nav-item">
        <div @click="navigateTo('comandancia')" class="nav-link">
          <i class="pi pi-crown nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text"
            >Comandancia General</span
          >
        </div>
      </div>

      <!-- IGFAH -->
      <div class="nav-item">
        <div @click="navigateTo('igfah')" class="nav-link">
          <i class="pi pi-search nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">IGFAH</span>
        </div>
      </div>

      <!-- COFAH -->
      <div class="nav-item">
        <div @click="navigateTo('cofah')" class="nav-link">
          <i class="pi pi-eye nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">COFAH</span>
        </div>
      </div>

      <!-- JEMGA (Dropdown) -->
      <div
        :class="[
          'nav-item nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'jemga' },
        ]"
      >
        <div
          @click="toggleDropdown('jemga')"
          class="nav-link nav-dropdown-trigger"
        >
          <i class="pi pi-sitemap nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">JEMGA</span>
          <i
            v-if="!collapsed || isMobile"
            :class="[
              'pi pi-chevron-down nav-chevron',
              { 'nav-chevron-rotated': activeDropdown === 'jemga' },
            ]"
          ></i>
        </div>

        <!-- Dropdown Content -->
        <div
          v-if="(!collapsed || isMobile) && activeDropdown === 'jemga'"
          class="nav-dropdown-content"
        >
          <!-- FA-1: ACTIVO - Microservicio Independiente -->
          <div
            @click="navigateTo('fa1')"
            :class="['dropdown-item featured-item']"
          >
            <span class="dropdown-emoji featured-emoji">👥</span>
            <div class="dropdown-info">
              <span class="dropdown-title">FA-1: Recursos Humanos</span>
              <span class="dropdown-subtitle">Gestión de personal FAH</span>
            </div>
            <span class="featured-badge">ACTIVO</span>
          </div>

          <!-- FA-2: Próximamente -->
          <div @click="navigateTo('fa2')" class="dropdown-item">
            <span class="dropdown-emoji">📊</span>
            <div class="dropdown-info">
              <span class="dropdown-title">FA-2: Información y Análisis</span>
            </div>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <!-- FA-3: Próximamente -->
          <div @click="navigateTo('fa3')" class="dropdown-item">
            <span class="dropdown-emoji">⚡</span>
            <div class="dropdown-info">
              <span class="dropdown-title"
                >FA-3: Operaciones, Adiestramiento</span
              >
            </div>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <!-- FA-4: Próximamente -->
          <div @click="navigateTo('fa4')" class="dropdown-item">
            <span class="dropdown-emoji">📦</span>
            <div class="dropdown-info">
              <span class="dropdown-title">FA-4: Logística</span>
            </div>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <!-- FA-5: Próximamente -->
          <div @click="navigateTo('fa5')" class="dropdown-item">
            <span class="dropdown-emoji">📋</span>
            <div class="dropdown-info">
              <span class="dropdown-title"
                >FA-5: Planes, Políticas, Programas</span
              >
            </div>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <!-- FA-6: Próximamente -->
          <div @click="navigateTo('fa6')" class="dropdown-item">
            <span class="dropdown-emoji">💻</span>
            <div class="dropdown-info">
              <span class="dropdown-title"
                >FA-6: Comunicaciones e Informática</span
              >
            </div>
            <span class="dropdown-badge">Próximamente</span>
          </div>
        </div>
      </div>

      <!-- EME (Dropdown) -->
      <div
        :class="[
          'nav-item nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'eme' },
        ]"
      >
        <div
          @click="toggleDropdown('eme')"
          class="nav-link nav-dropdown-trigger"
        >
          <i class="pi pi-list nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">EME</span>
          <i
            v-if="!collapsed || isMobile"
            :class="[
              'pi pi-chevron-down nav-chevron',
              { 'nav-chevron-rotated': activeDropdown === 'eme' },
            ]"
          ></i>
        </div>

        <div
          v-if="(!collapsed || isMobile) && activeDropdown === 'eme'"
          class="nav-dropdown-content"
        >
          <div @click="navigateTo('historia')" class="dropdown-item">
            <span class="dropdown-emoji">📚</span>
            <span class="dropdown-title">Historia Militar</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('capellania')" class="dropdown-item">
            <span class="dropdown-emoji">⛪</span>
            <span class="dropdown-title">Capellanía</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('ddhh')" class="dropdown-item">
            <span class="dropdown-emoji">⚖️</span>
            <span class="dropdown-title">Derechos Humanos (DDHH)</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('demfah')" class="dropdown-item">
            <span class="dropdown-emoji">📖</span>
            <span class="dropdown-title">Doctrina Militar (DEMFAH)</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('investigacion')" class="dropdown-item">
            <span class="dropdown-emoji">🔬</span>
            <span class="dropdown-title">Investigación y Desarrollo</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('sanidad')" class="dropdown-item">
            <span class="dropdown-emoji">🏥</span>
            <span class="dropdown-title">Sanidad Militar (FA-9)</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>
        </div>
      </div>

      <!-- EMP (Dropdown) -->
      <div
        :class="[
          'nav-item nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'emp' },
        ]"
      >
        <div
          @click="toggleDropdown('emp')"
          class="nav-link nav-dropdown-trigger"
        >
          <i class="pi pi-briefcase nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">EMP</span>
          <i
            v-if="!collapsed || isMobile"
            :class="[
              'pi pi-chevron-down nav-chevron',
              { 'nav-chevron-rotated': activeDropdown === 'emp' },
            ]"
          ></i>
        </div>

        <div
          v-if="(!collapsed || isMobile) && activeDropdown === 'emp'"
          class="nav-dropdown-content"
        >
          <div @click="navigateTo('ayudantia')" class="dropdown-item">
            <span class="dropdown-emoji">🤝</span>
            <span class="dropdown-title">Ayudantía de Campo</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('ventas')" class="dropdown-item">
            <span class="dropdown-emoji">💼</span>
            <span class="dropdown-title">Unidad Venta y Servicios</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('auditoria')" class="dropdown-item">
            <span class="dropdown-emoji">⚖️</span>
            <span class="dropdown-title">Auditoría Jurídico Militar</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('pagaduria')" class="dropdown-item">
            <span class="dropdown-emoji">💰</span>
            <span class="dropdown-title">Pagaduría General</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('relaciones')" class="dropdown-item">
            <span class="dropdown-emoji">📢</span>
            <span class="dropdown-title">Relaciones Públicas</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('protocolo')" class="dropdown-item">
            <span class="dropdown-emoji">🎖️</span>
            <span class="dropdown-title">Protocolo</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('seguridad')" class="dropdown-item">
            <span class="dropdown-emoji">🛡️</span>
            <span class="dropdown-title">Seguridad Operacional</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>
        </div>
      </div>

      <!-- Unidades (Dropdown) -->
      <div
        :class="[
          'nav-item nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'unidades' },
        ]"
      >
        <div
          @click="toggleDropdown('unidades')"
          class="nav-link nav-dropdown-trigger"
        >
          <i class="pi pi-building nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Unidades</span>
          <i
            v-if="!collapsed || isMobile"
            :class="[
              'pi pi-chevron-down nav-chevron',
              { 'nav-chevron-rotated': activeDropdown === 'unidades' },
            ]"
          ></i>
        </div>

        <div
          v-if="(!collapsed || isMobile) && activeDropdown === 'unidades'"
          class="nav-dropdown-content"
        >
          <div @click="navigateTo('ham')" class="dropdown-item">
            <span class="dropdown-emoji">🛩️</span>
            <span class="dropdown-title">Base Aérea HAM</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('hcm')" class="dropdown-item">
            <span class="dropdown-emoji">🛩️</span>
            <span class="dropdown-title">Base Aérea HCM</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('aee')" class="dropdown-item">
            <span class="dropdown-emoji">🛩️</span>
            <span class="dropdown-title">Base Aérea AEE</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('jesc')" class="dropdown-item">
            <span class="dropdown-emoji">🛩️</span>
            <span class="dropdown-title">Base Aérea JESC</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('peda')" class="dropdown-item">
            <span class="dropdown-emoji">🎯</span>
            <span class="dropdown-title">PEDA</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('cosecgfah')" class="dropdown-item">
            <span class="dropdown-emoji">🛡️</span>
            <span class="dropdown-title">COSECGFAH</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('eaivr')" class="dropdown-item">
            <span class="dropdown-emoji">👁️</span>
            <span class="dropdown-title">EAIVR</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>
        </div>
      </div>

      <!-- Centros de Estudio (Dropdown) -->
      <div
        :class="[
          'nav-item nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'centros' },
        ]"
      >
        <div
          @click="toggleDropdown('centros')"
          class="nav-link nav-dropdown-trigger"
        >
          <i class="pi pi-graduation-cap nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text"
            >Centros de Estudio</span
          >
          <i
            v-if="!collapsed || isMobile"
            :class="[
              'pi pi-chevron-down nav-chevron',
              { 'nav-chevron-rotated': activeDropdown === 'centros' },
            ]"
          ></i>
        </div>

        <div
          v-if="(!collapsed || isMobile) && activeDropdown === 'centros'"
          class="nav-dropdown-content"
        >
          <div @click="navigateTo('ama')" class="dropdown-item">
            <span class="dropdown-emoji">🎓</span>
            <span class="dropdown-title">AMA (Academia Militar Aviación)</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('ecmi')" class="dropdown-item">
            <span class="dropdown-emoji">📚</span>
            <span class="dropdown-title">ECMI</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('efsofah')" class="dropdown-item">
            <span class="dropdown-emoji">🎯</span>
            <span class="dropdown-title">EFSOFAH</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('ecsofah')" class="dropdown-item">
            <span class="dropdown-emoji">📖</span>
            <span class="dropdown-title">ECSOFAH</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>
        </div>
      </div>

      <!-- Admin (Dropdown) - FEATURED -->
      <div
        :class="[
          'nav-item nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'admin' },
        ]"
      >
        <div
          @click="toggleDropdown('admin')"
          class="nav-link nav-dropdown-trigger"
        >
          <i class="pi pi-shield nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Admin</span>
          <i
            v-if="!collapsed || isMobile"
            :class="[
              'pi pi-chevron-down nav-chevron',
              { 'nav-chevron-rotated': activeDropdown === 'admin' },
            ]"
          ></i>
        </div>

        <div
          v-if="(!collapsed || isMobile) && activeDropdown === 'admin'"
          class="nav-dropdown-content"
        >
          <!-- FEATURED: Catálogos -->
          <div
            @click="navigateToCatalogos"
            :class="[
              'dropdown-item featured-item',
              { 'featured-item-active': currentRoute === 'catalogos' },
            ]"
          >
            <span class="dropdown-emoji featured-emoji">⚙️</span>
            <div class="dropdown-info">
              <span class="dropdown-title">Catálogos</span>
              <span class="dropdown-subtitle">Gestión de datos maestros</span>
            </div>
            <span class="featured-badge">PERRO</span>
          </div>

          <!-- Separador -->
          <div class="dropdown-separator"></div>

          <!-- Futuras opciones -->
          <div @click="navigateTo('usuarios')" class="dropdown-item">
            <span class="dropdown-emoji">👥</span>
            <span class="dropdown-title">Gestión de Usuarios</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div @click="navigateTo('permisos')" class="dropdown-item">
            <span class="dropdown-emoji">🔐</span>
            <span class="dropdown-title">Permisos y Roles</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>

          <div
            @click="navigateToEstructuraOrganizacional"
            :class="[
              'dropdown-item featured-item',
              {
                'featured-item-active':
                  currentRoute === 'estructura-organizacional',
              },
            ]"
          >
            <span class="dropdown-emoji featured-emoji">🏢</span>
            <div class="dropdown-info">
              <span class="dropdown-title">Estructura Organizacional</span>
              <span class="dropdown-subtitle"
                >Gestión geográfica y militar</span
              >
            </div>
            <span class="featured-badge">ACTIVO</span>
          </div>

          <div @click="navigateTo('configuracion')" class="dropdown-item">
            <span class="dropdown-emoji">⚙️</span>
            <span class="dropdown-title">Configuración del Sistema</span>
            <span class="dropdown-badge">Próximamente</span>
          </div>
        </div>
      </div>

      <!-- Separador -->
      <div class="nav-separator"></div>

      <!-- Ayuda -->
      <div class="nav-item">
        <div @click="navigateTo('ayuda')" class="nav-link">
          <i class="pi pi-question-circle nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Ayuda</span>
        </div>
      </div>

      <!-- Acerca de -->
      <div class="nav-item">
        <div @click="navigateTo('acerca')" class="nav-link">
          <i class="pi pi-info-circle nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Acerca de</span>
        </div>
      </div>

      <!-- Ajustes -->
      <div class="nav-item">
        <div @click="navigateTo('ajustes')" class="nav-link">
          <i class="pi pi-cog nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Ajustes</span>
        </div>
      </div>
    </nav>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";

export default {
  name: "SidebarNavigation",
  props: {
    collapsed: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["toggle-collapse", "navigate"],
  setup(props, { emit }) {
    const route = useRoute();
    const router = useRouter();
    const toast = useToast();

    // Estado reactivo
    const activeDropdown = ref(null);
    const isMobile = ref(false);
    const showMobileSidebar = ref(false);

    // Computed
    const currentRoute = computed(() => route.name);

    // Detectar móvil
    const checkIsMobile = () => {
      isMobile.value = window.innerWidth <= 480;
      if (!isMobile.value) {
        showMobileSidebar.value = false;
      }
    };

    onMounted(() => {
      checkIsMobile();
      window.addEventListener("resize", checkIsMobile);
    });

    onUnmounted(() => {
      window.removeEventListener("resize", checkIsMobile);
    });

    // Métodos
    const handleToggle = () => {
      if (isMobile.value) {
        showMobileSidebar.value = false;
      } else {
        emit("toggle-collapse");
      }
    };

    const handleNavigation = () => {
      if (isMobile.value) {
        showMobileSidebar.value = false;
      }
    };

    const toggleDropdown = (dropdownName) => {
      if (isMobile.value || !props.collapsed) {
        if (activeDropdown.value === dropdownName) {
          activeDropdown.value = null;
        } else {
          activeDropdown.value = dropdownName;
        }
        return;
      }

      emit("toggle-collapse");
      setTimeout(() => {
        activeDropdown.value = dropdownName;
      }, 300);
    };

    const navigateToCatalogos = async () => {
      try {
        console.log("🎯 Navegando a Administrar Catálogos...");

        toast.add({
          severity: "success",
          summary: "Navegación FAH",
          detail: "Accediendo a Administrar Catálogos...",
          life: 2000,
        });

        activeDropdown.value = null;
        await router.push("/catalogos");
        emit("navigate", "catalogos");
        handleNavigation();

        console.log("✅ Navegación a catálogos exitosa");
      } catch (error) {
        console.error("❌ Error navegando a catálogos:", error);

        toast.add({
          severity: "error",
          summary: "Error de Navegación",
          detail: "No se pudo acceder a Administrar Catálogos",
          life: 4000,
        });
      }
    };

    const navigateToEstructuraOrganizacional = async () => {
      try {
        console.log("🏢 Navegando a Estructura Organizacional...");

        toast.add({
          severity: "success",
          summary: "Navegación FAH",
          detail: "Accediendo a Estructura Organizacional...",
          life: 2000,
        });

        activeDropdown.value = null;
        await router.push("/estructura-organizacional");
        emit("navigate", "estructura-organizacional");
        handleNavigation();

        console.log("✅ Navegación a estructura organizacional exitosa");
      } catch (error) {
        console.error("❌ Error navegando a estructura organizacional:", error);

        toast.add({
          severity: "error",
          summary: "Error de Navegación",
          detail: "No se pudo acceder a Estructura Organizacional",
          life: 4000,
        });
      }
    };

    const navigateTo = (section) => {
      // Secciones disponibles DENTRO del admin-frontend
      const availableSections = ["catalogos", "organizacion"];

      // Microservicios externos (abrir en nueva ventana)
      const externalMicroservices = {
        fa1: "http://localhost:5174", // fah-personal-service frontend
        fa2: "http://localhost:5175", // fah-inteligencia-service frontend (futuro)
        fa3: "http://localhost:5176", // fah-operaciones-service frontend (futuro)
        fa4: "http://localhost:5177", // fah-logistica-service frontend (futuro)
        fa5: "http://localhost:5178", // fah-asuntos-civiles-service frontend (futuro)
        fa6: "http://localhost:5179", // fah-comunicaciones-service frontend (futuro)
      };

      // Si es sección interna disponible
      if (availableSections.includes(section)) {
        switch (section) {
          case "catalogos":
            navigateToCatalogos();
            return;

          case "organizacion":
            navigateToEstructuraOrganizacional();
            return;

          default:
            console.warn(`Sección disponible pero no implementada: ${section}`);
            break;
        }
      }

      // Si es microservicio externo
      if (externalMicroservices[section]) {
        const serviceUrl = externalMicroservices[section];

        toast.add({
          severity: "info",
          summary: "Accediendo a Microservicio",
          detail: `Abriendo ${section.toUpperCase()} en nueva ventana...`,
          life: 3000,
        });

        // Abrir en nueva ventana
        window.open(serviceUrl, "_blank", "noopener,noreferrer");

        activeDropdown.value = null;
        emit("navigate", section);
        handleNavigation();
        console.log(
          `🚀 Navegando a microservicio externo: ${section} (${serviceUrl})`
        );
        return;
      }

      // Para secciones no disponibles aún
      toast.add({
        severity: "warn",
        summary: "Función no disponible",
        detail: `${section.toUpperCase()} estará disponible en próximas versiones`,
        life: 4000,
      });

      emit("navigate", section);
      handleNavigation();
      console.log(`⏳ Función pendiente de implementación: ${section}`);
    };

    return {
      // Estado
      activeDropdown,
      currentRoute,
      isMobile,
      showMobileSidebar,

      // Métodos
      handleToggle,
      handleNavigation,
      toggleDropdown,
      navigateTo,
      navigateToCatalogos,
      navigateToEstructuraOrganizacional,
    };
  },
};
</script>

<style>
/* Importar estilos externos organizados */
@import "@/styles/components/layout/sidebar-navigation.css";
</style>
