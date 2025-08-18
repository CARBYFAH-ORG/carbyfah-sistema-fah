<!-- services\fah-admin-frontend\src\components\layout\SidebarNavigation.vue -->

<template>
  <!-- Boton flotante movil -->
  <div
    v-if="isMobile && !showMobileSidebar"
    @click="toggleMobileSidebar"
    class="mobile-sidebar-trigger"
    role="button"
    tabindex="0"
    aria-label="Abrir menu de navegacion"
  >
    <i class="pi pi-bars"></i>
  </div>

  <!-- Backdrop movil -->
  <div
    v-if="isMobile && showMobileSidebar"
    @click="closeMobileSidebar"
    class="mobile-backdrop"
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
    <!-- Header del sidebar -->
    <div class="sidebar-header">
      <!-- Boton toggle personalizado -->
      <div
        @click="handleToggle"
        @keypress="handleToggleKeyPress"
        class="custom-toggle-button"
        :class="{ 'mobile-close-style': isMobile }"
        role="button"
        tabindex="0"
        :aria-label="
          isMobile
            ? 'Cerrar menu'
            : collapsed
            ? 'Expandir sidebar'
            : 'Colapsar sidebar'
        "
      >
        <i
          :class="
            isMobile
              ? 'pi pi-times'
              : collapsed
              ? 'pi pi-angle-right'
              : 'pi pi-angle-left'
          "
        ></i>
      </div>

      <h2 v-if="isMobile && showMobileSidebar" class="mobile-sidebar-title">
        CARBYFAH
      </h2>
    </div>

    <!-- Navegacion del sidebar -->
    <nav class="sidebar-navigation">
      <!-- Dashboard -->
      <div
        :class="[
          'nav-item',
          { 'nav-item-active': currentRoute === 'dashboard' },
        ]"
      >
        <router-link
          to="/dashboard"
          class="nav-link"
          @click="handleNavigation"
          role="menuitem"
          tabindex="0"
        >
          <i class="pi pi-home nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Inicio</span>
        </router-link>
      </div>

      <!-- Comandancia General -->
      <div class="nav-item">
        <div
          @click="navigateTo('comandancia')"
          class="nav-link nav-clickable"
          role="menuitem"
          tabindex="0"
          @keypress="handleKeyPress($event, 'comandancia')"
        >
          <i class="pi pi-crown nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text"
            >Comandancia General</span
          >
        </div>
      </div>

      <!-- IGFAH -->
      <div class="nav-item">
        <div
          @click="navigateTo('igfah')"
          class="nav-link nav-clickable"
          role="menuitem"
          tabindex="0"
          @keypress="handleKeyPress($event, 'igfah')"
        >
          <i class="pi pi-search nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">IGFAH</span>
        </div>
      </div>

      <!-- COFAH -->
      <div class="nav-item">
        <div
          @click="navigateTo('cofah')"
          class="nav-link nav-clickable"
          role="menuitem"
          tabindex="0"
          @keypress="handleKeyPress($event, 'cofah')"
        >
          <i class="pi pi-eye nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">COFAH</span>
        </div>
      </div>

      <!-- JEMGA (Dropdown) -->
      <div
        :class="[
          'nav-item',
          'nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'jemga' },
        ]"
      >
        <div
          @click="toggleDropdown('jemga')"
          @keypress="handleDropdownKeyPress($event, 'jemga')"
          class="nav-link nav-dropdown-trigger nav-clickable"
          role="button"
          tabindex="0"
          :aria-expanded="activeDropdown === 'jemga'"
          aria-haspopup="true"
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

        <!-- Contenido del dropdown JEMGA -->
        <div
          v-if="(!collapsed || isMobile) && activeDropdown === 'jemga'"
          class="nav-dropdown-content"
          role="menu"
        >
          <!-- FA-1: ACTIVO -->
          <div
            @click="navigateTo('fa1')"
            @keypress="handleKeyPress($event, 'fa1')"
            :class="['dropdown-item featured-item nav-clickable']"
            role="menuitem"
            tabindex="0"
          >
            <div class="dropdown-info">
              <span class="dropdown-title">FA-1: RRHH</span>
            </div>
            <span class="featured-badge">ACTIVO</span>
          </div>

          <!-- FA-2 -->
          <div
            @click="navigateTo('fa2')"
            @keypress="handleKeyPress($event, 'fa2')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <div class="dropdown-info">
              <span class="dropdown-title">FA-2: ICIA</span>
            </div>
          </div>

          <!-- FA-3 -->
          <div
            @click="navigateTo('fa3')"
            @keypress="handleKeyPress($event, 'fa3')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <div class="dropdown-info">
              <span class="dropdown-title">FA-3: Operaciones</span>
            </div>
          </div>

          <!-- FA-4 -->
          <div
            @click="navigateTo('fa4')"
            @keypress="handleKeyPress($event, 'fa4')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <div class="dropdown-info">
              <span class="dropdown-title">FA-4: Logistica</span>
            </div>
          </div>

          <!-- FA-5 -->
          <div
            @click="navigateTo('fa5')"
            @keypress="handleKeyPress($event, 'fa5')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <div class="dropdown-info">
              <span class="dropdown-title">FA-5: AA.CC</span>
            </div>
          </div>

          <!-- FA-6 -->
          <div
            @click="navigateTo('fa6')"
            @keypress="handleKeyPress($event, 'fa6')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <div class="dropdown-info">
              <span class="dropdown-title">FA-6: Comunicaciones</span>
            </div>
          </div>
        </div>
      </div>

      <!-- EME (Dropdown) -->
      <div
        :class="[
          'nav-item',
          'nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'eme' },
        ]"
      >
        <div
          @click="toggleDropdown('eme')"
          @keypress="handleDropdownKeyPress($event, 'eme')"
          class="nav-link nav-dropdown-trigger nav-clickable"
          role="button"
          tabindex="0"
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
          role="menu"
        >
          <div
            @click="navigateTo('historia')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Historia Militar</span>
          </div>

          <div
            @click="navigateTo('capellania')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Capellania</span>
          </div>

          <div
            @click="navigateTo('ddhh')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Derechos Humanos</span>
          </div>

          <div
            @click="navigateTo('demfah')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Doctrina Militar</span>
          </div>

          <div
            @click="navigateTo('investigacion')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Investigacion</span>
          </div>

          <div
            @click="navigateTo('sanidad')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Sanidad Militar</span>
          </div>
        </div>
      </div>

      <!-- EMP (Dropdown) -->
      <div
        :class="[
          'nav-item',
          'nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'emp' },
        ]"
      >
        <div
          @click="toggleDropdown('emp')"
          @keypress="handleDropdownKeyPress($event, 'emp')"
          class="nav-link nav-dropdown-trigger nav-clickable"
          role="button"
          tabindex="0"
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
          role="menu"
        >
          <div
            @click="navigateTo('ayudantia')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Ayudantia de Campo</span>
          </div>

          <div
            @click="navigateTo('ventas')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Venta y Servicios</span>
          </div>

          <div
            @click="navigateTo('auditoria')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Auditoria Juridica</span>
          </div>

          <div
            @click="navigateTo('pagaduria')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Pagaduria General</span>
          </div>

          <div
            @click="navigateTo('relaciones')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Relaciones Publicas</span>
          </div>

          <div
            @click="navigateTo('protocolo')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Protocolo</span>
          </div>

          <div
            @click="navigateTo('seguridad')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Seguridad Operacional</span>
          </div>
        </div>
      </div>

      <!-- Unidades (Dropdown) -->
      <div
        :class="[
          'nav-item',
          'nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'unidades' },
        ]"
      >
        <div
          @click="toggleDropdown('unidades')"
          @keypress="handleDropdownKeyPress($event, 'unidades')"
          class="nav-link nav-dropdown-trigger nav-clickable"
          role="button"
          tabindex="0"
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
          role="menu"
        >
          <div
            @click="navigateTo('ham')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Base Aerea HAM</span>
          </div>

          <div
            @click="navigateTo('hcm')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Base Aerea HCM</span>
          </div>

          <div
            @click="navigateTo('aee')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Base Aerea AEE</span>
          </div>

          <div
            @click="navigateTo('jesc')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Base Aerea JESC</span>
          </div>

          <div
            @click="navigateTo('peda')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">PEDA</span>
          </div>

          <div
            @click="navigateTo('cosecgfah')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">COSECGFAH</span>
          </div>

          <div
            @click="navigateTo('eaivr')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">EAIVR</span>
          </div>
        </div>
      </div>

      <!-- Centros de Estudio (Dropdown) -->
      <div
        :class="[
          'nav-item',
          'nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'centros' },
        ]"
      >
        <div
          @click="toggleDropdown('centros')"
          @keypress="handleDropdownKeyPress($event, 'centros')"
          class="nav-link nav-dropdown-trigger nav-clickable"
          role="button"
          tabindex="0"
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
          role="menu"
        >
          <div
            @click="navigateTo('ama')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">AMA</span>
          </div>

          <div
            @click="navigateTo('ecmi')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">ECMI</span>
          </div>

          <div
            @click="navigateTo('efsofah')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">EFSOFAH</span>
          </div>

          <div
            @click="navigateTo('ecsofah')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">ECSOFAH</span>
          </div>
        </div>
      </div>

      <!-- Admin (Dropdown) - FEATURED -->
      <div
        :class="[
          'nav-item',
          'nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'admin' },
        ]"
      >
        <div
          @click="toggleDropdown('admin')"
          @keypress="handleDropdownKeyPress($event, 'admin')"
          class="nav-link nav-dropdown-trigger nav-clickable"
          role="button"
          tabindex="0"
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
          role="menu"
        >
          <!-- Featured: Catalogos -->
          <div
            @click="navigateToCatalogos"
            @keypress="handleSpecialKeyPress($event, 'catalogos')"
            :class="[
              'dropdown-item featured-item nav-clickable',
              { 'featured-item-active': currentRoute === 'catalogos' },
            ]"
            role="menuitem"
            tabindex="0"
          >
            <div class="dropdown-info">
              <span class="dropdown-title">Catalogos</span>
              <span class="dropdown-subtitle">Gestion de datos maestros</span>
            </div>
            <span class="featured-badge">ACTIVO</span>
          </div>

          <!-- Separador -->
          <div class="dropdown-separator"></div>

          <!-- Futuras opciones -->
          <div
            @click="navigateTo('usuarios')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Usuarios</span>
          </div>

          <div
            @click="navigateTo('permisos')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Permisos y Roles</span>
          </div>

          <div
            @click="navigateToEstructuraOrganizacional"
            @keypress="handleSpecialKeyPress($event, 'estructura')"
            :class="[
              'dropdown-item featured-item nav-clickable',
              {
                'featured-item-active':
                  currentRoute === 'estructura-organizacional',
              },
            ]"
            role="menuitem"
            tabindex="0"
          >
            <div class="dropdown-info">
              <span class="dropdown-title">Estructura Organizacional</span>
              <span class="dropdown-subtitle"
                >Gestion geografica y militar</span
              >
            </div>
            <span class="featured-badge">ACTIVO</span>
          </div>

          <div
            @click="navigateTo('configuracion')"
            class="dropdown-item nav-clickable"
            role="menuitem"
            tabindex="0"
          >
            <span class="dropdown-title">Configuracion</span>
          </div>
        </div>
      </div>

      <!-- Separador -->
      <div class="nav-separator"></div>

      <!-- Ayuda -->
      <div class="nav-item">
        <div
          @click="navigateTo('ayuda')"
          @keypress="handleKeyPress($event, 'ayuda')"
          class="nav-link nav-clickable"
          role="menuitem"
          tabindex="0"
        >
          <i class="pi pi-question-circle nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Ayuda</span>
        </div>
      </div>

      <!-- Acerca de -->
      <div class="nav-item">
        <div
          @click="navigateTo('acerca')"
          @keypress="handleKeyPress($event, 'acerca')"
          class="nav-link nav-clickable"
          role="menuitem"
          tabindex="0"
        >
          <i class="pi pi-info-circle nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Acerca de</span>
        </div>
      </div>

      <!-- Ajustes -->
      <div class="nav-item">
        <div
          @click="navigateTo('ajustes')"
          @keypress="handleKeyPress($event, 'ajustes')"
          class="nav-link nav-clickable"
          role="menuitem"
          tabindex="0"
        >
          <i class="pi pi-cog nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Ajustes</span>
        </div>
      </div>
    </nav>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useToastFAH } from "@/composables/useToastFAH";

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
    const router = useRouter();
    const route = useRoute();
    const toast = useToastFAH();

    // Estado reactivo
    const activeDropdown = ref(null);
    const showMobileSidebar = ref(false);
    const windowWidth = ref(window.innerWidth);

    // Computed
    const isMobile = computed(() => windowWidth.value <= 480);
    const currentRoute = computed(() => route.name);

    // Metodos de navegacion
    const navigateTo = (section) => {
      // Secciones disponibles DENTRO del admin-frontend
      const availableSections = ["catalogos", "organizacion"];

      // Microservicios externos (abrir en nueva ventana)
      const externalMicroservices = {
        fa1: "http://localhost:5174",
        fa2: "http://localhost:5175",
        fa3: "http://localhost:5176",
        fa4: "http://localhost:5177",
        fa5: "http://localhost:5178",
        fa6: "http://localhost:5179",
      };

      // Si es seccion interna disponible
      if (availableSections.includes(section)) {
        switch (section) {
          case "catalogos":
            navigateToCatalogos();
            return;

          case "organizacion":
            navigateToEstructuraOrganizacional();
            return;

          default:
            break;
        }
      }

      // Si es microservicio externo
      if (externalMicroservices[section]) {
        const serviceUrl = externalMicroservices[section];

        toast.info(
          "Accediendo a Microservicio",
          `Abriendo ${section.toUpperCase()} en nueva ventana...`
        );

        // Abrir en nueva ventana
        window.open(serviceUrl, "_blank", "noopener,noreferrer");

        activeDropdown.value = null;
        emit("navigate", section);
        handleNavigation();
        return;
      }

      // Para secciones no disponibles aun
      toast.pending(
        "Funcion no disponible",
        `${section.toUpperCase()} estara disponible en proximas versiones`
      );

      emit("navigate", section);
      handleNavigation();
    };

    const navigateToCatalogos = async () => {
      try {
        toast.success(
          "Navegacion FAH",
          "Accediendo a Administrar Catalogos..."
        );

        activeDropdown.value = null;
        await router.push("/catalogos");
        emit("navigate", "catalogos");
        handleNavigation();
      } catch (error) {
        toast.error(
          "Error de Navegacion",
          "No se pudo acceder a Administrar Catalogos"
        );
      }
    };

    const navigateToEstructuraOrganizacional = async () => {
      try {
        toast.success(
          "Navegacion FAH",
          "Accediendo a Estructura Organizacional..."
        );

        activeDropdown.value = null;
        await router.push("/estructura-organizacional");
        emit("navigate", "estructura-organizacional");
        handleNavigation();
      } catch (error) {
        toast.error(
          "Error de Navegacion",
          "No se pudo acceder a Estructura Organizacional"
        );
      }
    };

    const handleNavigation = () => {
      if (isMobile.value) {
        showMobileSidebar.value = false;
      }
    };

    // Metodos de dropdown
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

    // Metodos de sidebar movil
    const toggleMobileSidebar = () => {
      showMobileSidebar.value = !showMobileSidebar.value;
    };

    const closeMobileSidebar = () => {
      showMobileSidebar.value = false;
    };

    // Metodo para manejar toggle con accesibilidad
    const handleToggle = () => {
      if (isMobile.value) {
        closeMobileSidebar();
      } else {
        emit("toggle-collapse");
      }
    };

    // Metodo para manejar teclas en el toggle personalizado
    const handleToggleKeyPress = (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        handleToggle();
      }
    };

    // Accesibilidad - teclado
    const handleKeyPress = (event, section) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        navigateTo(section);
      }
    };

    const handleDropdownKeyPress = (event, dropdown) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        toggleDropdown(dropdown);
      }
    };

    const handleSpecialKeyPress = (event, type) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        if (type === "catalogos") {
          navigateToCatalogos();
        } else if (type === "estructura") {
          navigateToEstructuraOrganizacional();
        }
      }
    };

    // Responsive listener
    const handleResize = () => {
      windowWidth.value = window.innerWidth;
      if (!isMobile.value) {
        showMobileSidebar.value = false;
      }
    };

    onMounted(() => {
      window.addEventListener("resize", handleResize);
    });

    onUnmounted(() => {
      window.removeEventListener("resize", handleResize);
    });

    return {
      // Estado
      activeDropdown,
      showMobileSidebar,
      isMobile,
      currentRoute,

      // Metodos
      navigateTo,
      navigateToCatalogos,
      navigateToEstructuraOrganizacional,
      handleNavigation,
      toggleDropdown,
      toggleMobileSidebar,
      closeMobileSidebar,
      handleToggle,
      handleToggleKeyPress,
      handleKeyPress,
      handleDropdownKeyPress,
      handleSpecialKeyPress,
    };
  },
};
</script>

<style scoped>
/* Estilos independientes sidebar navigation FAH */
/* Paleta de colores autorizada FAH - No dependiente de variables externas */

/* Contenedor principal del sidebar */
.sidebar-container {
  background: #1e3a5f;
  color: #ffffff;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100vh;
  position: relative;
  border-right: 1px solid #495057;
  box-shadow: 0 2px 8px rgba(30, 58, 95, 0.1);
}

/* Estados del sidebar */
.sidebar-expanded {
  width: 280px;
}

.sidebar-collapsed {
  width: 70px;
}

.sidebar-mobile-hidden {
  position: fixed;
  top: 0;
  left: -320px;
  width: 320px;
  z-index: 999;
  transition: left 0.3s ease;
}

.sidebar-mobile-visible {
  position: fixed;
  top: 0;
  left: 0;
  width: 320px;
  z-index: 999;
  transition: left 0.3s ease;
  box-shadow: 0 8px 25px rgba(30, 58, 95, 0.25);
}

/* Header del sidebar */
.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #495057;
  background: #1e3a5f;
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 70px;
}

/* Boton toggle personalizado - Diseno FAH premium */
.custom-toggle-button {
  background: linear-gradient(
    135deg,
    rgba(212, 175, 55, 0.1),
    rgba(212, 175, 55, 0.05)
  );
  border: 1px solid #d4af37;
  color: #d4af37;
  width: 20px;
  height: 20px;
  min-width: 20px;
  max-width: 20px;
  min-height: 20px;
  max-height: 20px;
  border-radius: 3px;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2),
    inset 0 1px 0 rgba(212, 175, 55, 0.3);
  flex-shrink: 0;
  user-select: none;
  position: relative;
  overflow: hidden;
}

/* Efecto de brillo sutil */
.custom-toggle-button::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(212, 175, 55, 0.4),
    transparent
  );
  transition: left 0.6s ease;
}

.custom-toggle-button:hover::before {
  left: 100%;
}

.custom-toggle-button:hover {
  background: linear-gradient(
    135deg,
    rgba(212, 175, 55, 0.2),
    rgba(212, 175, 55, 0.1)
  );
  border-color: #f0c674;
  color: #f0c674;
  transform: scale(1.15);
  box-shadow: 0 2px 8px rgba(212, 175, 55, 0.4),
    inset 0 1px 0 rgba(212, 175, 55, 0.5), 0 0 0 1px rgba(212, 175, 55, 0.3);
}

.custom-toggle-button:focus {
  outline: none;
  box-shadow: 0 2px 8px rgba(212, 175, 55, 0.4),
    inset 0 1px 0 rgba(212, 175, 55, 0.5), 0 0 0 2px rgba(212, 175, 55, 0.6);
}

.custom-toggle-button:active {
  transform: scale(0.95);
  background: linear-gradient(
    135deg,
    rgba(212, 175, 55, 0.3),
    rgba(212, 175, 55, 0.15)
  );
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2),
    0 1px 2px rgba(212, 175, 55, 0.3);
}

/* Icono del toggle personalizado con efectos */
.custom-toggle-button i {
  font-size: 16px;
  font-weight: bold;
  color: inherit;
  line-height: 1;
  width: auto;
  height: auto;
  margin: 0;
  padding: 0;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  transition: all 0.2s ease;
}

.custom-toggle-button:hover i {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4), 0 0 4px rgba(212, 175, 55, 0.6);
}

/* Estilo para cerrar movil - Tema rojo elegante */
.custom-toggle-button.mobile-close-style {
  background: linear-gradient(
    135deg,
    rgba(193, 102, 107, 0.1),
    rgba(193, 102, 107, 0.05)
  );
  border-color: #c1666b;
  color: #c1666b;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2),
    inset 0 1px 0 rgba(193, 102, 107, 0.3);
}

.custom-toggle-button.mobile-close-style::before {
  background: linear-gradient(
    90deg,
    transparent,
    rgba(193, 102, 107, 0.4),
    transparent
  );
}

.custom-toggle-button.mobile-close-style:hover {
  background: linear-gradient(
    135deg,
    rgba(193, 102, 107, 0.2),
    rgba(193, 102, 107, 0.1)
  );
  border-color: #d47479;
  color: #d47479;
  box-shadow: 0 2px 8px rgba(193, 102, 107, 0.4),
    inset 0 1px 0 rgba(193, 102, 107, 0.5), 0 0 0 1px rgba(193, 102, 107, 0.3);
}

.custom-toggle-button.mobile-close-style:focus {
  box-shadow: 0 2px 8px rgba(193, 102, 107, 0.4),
    inset 0 1px 0 rgba(193, 102, 107, 0.5), 0 0 0 2px rgba(193, 102, 107, 0.6);
}

.custom-toggle-button.mobile-close-style:active {
  background: linear-gradient(
    135deg,
    rgba(193, 102, 107, 0.3),
    rgba(193, 102, 107, 0.15)
  );
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2),
    0 1px 2px rgba(193, 102, 107, 0.3);
}

.custom-toggle-button.mobile-close-style:hover i {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4), 0 0 4px rgba(193, 102, 107, 0.6);
}

/* Navegacion */
.sidebar-navigation {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;
  scrollbar-width: thin;
  scrollbar-color: #495057 transparent;
}

.sidebar-navigation::-webkit-scrollbar {
  width: 4px;
}

.sidebar-navigation::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-navigation::-webkit-scrollbar-thumb {
  background: #495057;
  border-radius: 2px;
}

.sidebar-navigation::-webkit-scrollbar-thumb:hover {
  background: #6c757d;
}

/* Items de navegacion */
.nav-item {
  margin: 2px 8px;
}

.nav-item-active {
  background: #1f6a8f;
  border-radius: 6px;
}

.nav-item-active .nav-link {
  color: #ffffff !important;
}

.nav-item-active .nav-icon {
  color: #ffffff !important;
}

.nav-item-active .nav-text {
  color: #ffffff !important;
}

/* Enlaces de navegacion */
.nav-link {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  text-decoration: none;
  color: #ffffff;
  border-radius: 6px;
  transition: all 0.2s ease;
  min-height: 44px;
  position: relative;
  outline: none;
}

/* Cursor pointer para elementos clickeables */
.nav-clickable {
  cursor: pointer !important;
}

.nav-link:hover {
  background: #495057;
  color: #ffffff;
  transform: translateX(2px);
}

.nav-link:focus {
  outline: none;
  box-shadow: inset 0 0 0 2px #d4af37;
}

/* Iconos de navegacion */
.nav-icon {
  font-size: 16px;
  margin-right: 12px;
  color: #d4af37;
  min-width: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

/* Texto de navegacion */
.nav-text {
  font-size: 14px;
  font-weight: 500;
  color: inherit;
  white-space: nowrap;
  transition: all 0.2s ease;
}

/* Dropdowns */
.nav-dropdown-trigger {
  justify-content: space-between;
  cursor: pointer !important;
}

.nav-dropdown-trigger:hover {
  background: #495057;
}

.nav-dropdown-active .nav-dropdown-trigger {
  background: #495057;
}

.nav-chevron {
  font-size: 12px;
  color: #d4af37;
  transition: transform 0.2s ease;
  margin-left: auto;
}

.nav-chevron-rotated {
  transform: rotate(180deg);
}

/* Contenido del dropdown */
.nav-dropdown-content {
  background: #495057;
  margin: 4px 12px 4px 40px;
  border-radius: 4px;
  overflow: hidden;
  border-left: 2px solid #d4af37;
}

/* Dropdown items optimizados - Sin emojis */
.dropdown-item {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  cursor: pointer !important;
  transition: all 0.2s ease;
  gap: 10px;
  background: transparent;
  border: none;
  width: 100%;
  text-align: left;
  color: #ffffff;
}

.dropdown-item:hover {
  background: #6c757d;
  transform: translateX(4px);
}

/* Titulo principal - Simplificado */
.dropdown-title {
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
  line-height: 1.2;
  flex: 1;
}

/* Subtitulo - Mas pequeno y discreto */
.dropdown-subtitle {
  font-size: 10px;
  color: #e9ecef;
  font-weight: 400;
  opacity: 0.8;
}

/* Badges del dropdown */
.dropdown-badge {
  font-size: 9px;
  background: #6c757d;
  color: #ffffff;
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

/* Items destacados */
.featured-item {
  background: rgba(212, 175, 55, 0.1);
  border-left: 3px solid #d4af37;
}

.featured-item:hover {
  background: rgba(212, 175, 55, 0.2);
}

.featured-badge {
  font-size: 9px;
  background: #d4af37;
  color: #1e3a5f;
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.featured-item-active {
  background: rgba(212, 175, 55, 0.25);
  border-left-color: #d4af37;
}

/* Separador del dropdown */
.dropdown-separator {
  height: 1px;
  background: #6c757d;
  margin: 4px 8px;
}

/* Separador de navegacion */
.nav-separator {
  height: 1px;
  background: #495057;
  margin: 8px 16px;
}

/* Boton flotante movil */
.mobile-sidebar-trigger {
  position: fixed;
  top: 85px;
  left: 20px;
  z-index: 1001;
  width: 44px;
  height: 44px;
  background: #1e3a5f;
  border: 1px solid #495057;
  border-radius: 6px;
  display: none;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
}

.mobile-sidebar-trigger:hover {
  background: #495057;
  border-color: #d4af37;
  transform: scale(1.05);
}

.mobile-sidebar-trigger i {
  color: #d4af37;
  font-size: 16px;
}

/* Backdrop movil */
.mobile-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
  cursor: pointer;
}

/* Titulo movil */
.mobile-sidebar-title {
  color: #d4af37;
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  flex: 1;
}

/* Estados de focus para accesibilidad */
.nav-link:focus-visible {
  outline: none;
  box-shadow: inset 0 0 0 2px #d4af37;
}

.nav-dropdown-trigger:focus-visible {
  outline: none;
  box-shadow: inset 0 0 0 2px #d4af37;
}

.dropdown-item:focus-visible {
  outline: none;
  box-shadow: inset 0 0 0 2px #d4af37;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar-expanded {
    width: 260px;
  }

  .sidebar-collapsed {
    width: 60px;
  }

  .nav-link {
    padding: 10px 14px;
  }

  .nav-icon {
    font-size: 15px;
  }
}

@media (max-width: 480px) {
  .mobile-sidebar-trigger {
    display: flex;
  }

  .sidebar-mobile-visible {
    width: 280px;
  }

  .nav-link {
    padding: 12px 16px;
  }
}

@media (min-width: 481px) {
  .mobile-sidebar-trigger {
    display: none !important;
  }
}

/* Animaciones suaves */
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

.nav-item {
  animation: slideIn 0.3s ease forwards;
}

/* Estados hover adicionales */
.nav-dropdown-content .nav-link:hover {
  background: #6c757d;
}

/* Asegurar cursor pointer en todos los elementos clickeables */
.nav-link[role="menuitem"],
.nav-link[role="button"],
.nav-dropdown-trigger,
.nav-clickable,
.dropdown-item,
button,
[onclick],
[role="button"] {
  cursor: pointer !important;
}

/* Informacion del dropdown - Contenedor para titulo y subtitulo */
.dropdown-info {
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 2px;
}
</style>
