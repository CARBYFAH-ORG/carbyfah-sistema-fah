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
        FAH PERSONAL
      </h2>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="sidebar-navigation">
      <!-- I. Dashboard -->
      <div
        :class="[
          'nav-item',
          { 'nav-item-active': currentRoute === 'dashboard' },
        ]"
      >
        <router-link to="/dashboard" class="nav-link" @click="handleNavigation">
          <i class="pi pi-chart-bar nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Dashboard</span>
        </router-link>
      </div>

      <!-- II. Inicio -->
      <div
        :class="['nav-item', { 'nav-item-active': currentRoute === 'inicio' }]"
      >
        <router-link to="/inicio" class="nav-link" @click="handleNavigation">
          <i class="pi pi-home nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Inicio</span>
        </router-link>
      </div>

      <!-- III. Módulo Inteligente del Rol (Jefe FA-1, Enc. S-1 HCM, etc.) -->
      <div v-if="rolUsuario" class="nav-item">
        <div
          @click="navigateToEspacioTrabajo()"
          class="nav-link nav-role-workspace"
        >
          <i :class="rolUsuario.icono + ' nav-icon'"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">{{
            rolUsuario.display_name
          }}</span>
          <span v-if="!collapsed || isMobile" class="role-badge">{{
            rolUsuario.codigo_rol
          }}</span>
        </div>
      </div>

      <!-- IV. Niveles Jerárquicos (Dropdown Inteligente) -->
      <div
        v-if="nivelesJerarquicos.length > 0"
        :class="[
          'nav-item nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'niveles' },
        ]"
      >
        <div
          @click="toggleDropdown('niveles')"
          class="nav-link nav-dropdown-trigger"
        >
          <i class="pi pi-sitemap nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text"
            >Niveles Jerárquicos</span
          >
          <i
            v-if="!collapsed || isMobile"
            :class="[
              'pi pi-chevron-down nav-chevron',
              { 'nav-chevron-rotated': activeDropdown === 'niveles' },
            ]"
          ></i>
        </div>

        <!-- Dropdown Content Niveles -->
        <div
          v-if="(!collapsed || isMobile) && activeDropdown === 'niveles'"
          class="nav-dropdown-content"
        >
          <!-- Unidad Principal del Usuario -->
          <div class="dropdown-section-header">
            {{ rolUsuario?.unidad_principal || "Mi Unidad" }}
          </div>

          <!-- Áreas de Responsabilidad según el rol -->
          <div
            v-for="area in areasResponsabilidad"
            :key="area.codigo"
            @click="navigateTo(`area-${area.codigo}`)"
            class="dropdown-item"
          >
            <span class="dropdown-emoji">{{ area.icono }}</span>
            <div class="dropdown-info">
              <span class="dropdown-title">{{ area.nombre }}</span>
              <span class="dropdown-description">({{ area.scope }})</span>
            </div>
          </div>
        </div>
      </div>

      <!-- V. Admin (Dropdown Inteligente) -->
      <div
        v-if="tieneAccesoAdmin"
        :class="[
          'nav-item nav-dropdown',
          { 'nav-dropdown-active': activeDropdown === 'admin' },
        ]"
      >
        <div
          @click="toggleDropdown('admin')"
          class="nav-link nav-dropdown-trigger nav-admin-trigger"
        >
          <i class="pi pi-shield nav-icon"></i>
          <span v-if="!collapsed || isMobile" class="nav-text">Admin</span>
          <span v-if="!collapsed || isMobile" class="admin-level-badge">{{
            rolUsuario?.tipo_admin || "LOCAL"
          }}</span>
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
          <!-- A. Roles y Usuarios -->
          <div
            @click="navigateToRolesUsuarios"
            :class="[
              'dropdown-item admin-item',
              { 'featured-item-active': currentRoute === 'roles-usuarios' },
            ]"
          >
            <span class="dropdown-emoji">👤</span>
            <div class="dropdown-info">
              <span class="dropdown-title">Roles y Usuarios</span>
              <span class="dropdown-badge">Disponible</span>
            </div>
          </div>

          <!-- B. Permisos y Roles -->
          <div
            @click="navigateTo('permisos-roles')"
            class="dropdown-item admin-item"
          >
            <span class="dropdown-emoji">🔐</span>
            <div class="dropdown-info">
              <span class="dropdown-title">Permisos y Roles</span>
              <span class="dropdown-badge">Próximamente</span>
            </div>
          </div>

          <!-- C. Ver Diagrama (Inteligente según rol) -->
          <div
            :class="[
              'nav-dropdown nav-dropdown-nested',
              { 'nav-dropdown-active': activeDropdown === 'diagrama' },
            ]"
          >
            <div
              @click="toggleDropdown('diagrama')"
              class="dropdown-item dropdown-trigger-nested"
            >
              <span class="dropdown-emoji">📊</span>
              <div class="dropdown-info">
                <span class="dropdown-title">Ver Diagrama</span>
                <span class="dropdown-description">{{
                  rolUsuario?.descripcion_acceso
                }}</span>
              </div>
              <i
                :class="[
                  'pi pi-chevron-down dropdown-chevron-nested',
                  { 'dropdown-chevron-rotated': activeDropdown === 'diagrama' },
                ]"
              ></i>
            </div>

            <!-- Nested Dropdown Content -->
            <div
              v-if="activeDropdown === 'diagrama'"
              class="dropdown-content-nested"
            >
              <!-- FAH Completa (solo Jefe FA-1) -->
              <div
                v-if="rolUsuario?.acceso === 'completo'"
                @click="navigateTo('diagrama-fah')"
                class="dropdown-item-nested"
              >
                <span class="nested-emoji">🚁</span>
                <span class="nested-title">FAH Completa</span>
              </div>

              <!-- Base Completa (Cmdte Base y Enc S-1) -->
              <div
                v-if="
                  rolUsuario?.acceso_base ||
                  rolUsuario?.acceso === 'base_completa' ||
                  rolUsuario?.acceso === 'seccion_s1'
                "
                @click="navigateTo(`diagrama-base-${rolUsuario.base_codigo}`)"
                class="dropdown-item-nested"
              >
                <span class="nested-emoji">🏭</span>
                <span class="nested-title"
                  >Base {{ rolUsuario?.base_codigo || "Local" }}</span
                >
              </div>

              <!-- Sección S-1 (solo Enc S-1) -->
              <div
                v-if="rolUsuario?.acceso_seccion"
                @click="
                  navigateTo(`diagrama-seccion-${rolUsuario.seccion_codigo}`)
                "
                class="dropdown-item-nested"
              >
                <span class="nested-emoji">🏗️</span>
                <span class="nested-title">{{
                  rolUsuario?.seccion_nombre || "Mi Sección"
                }}</span>
              </div>
            </div>
          </div>

          <!-- D. Configuración Sistema -->
          <div
            @click="navigateTo('configuracion-sistema')"
            class="dropdown-item admin-item"
          >
            <span class="dropdown-emoji">⚙️</span>
            <div class="dropdown-info">
              <span class="dropdown-title">Configuración Sistema</span>
              <span class="dropdown-badge">Próximamente</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Separador -->
      <div class="nav-separator"></div>

      <!-- Elemento informativo del usuario -->
      <div class="nav-item nav-user-info">
        <div class="user-info-compact">
          <div class="user-avatar">{{ iniciales }}</div>
          <div v-if="!collapsed || isMobile" class="user-details">
            <div class="user-name">{{ nombreUsuario }}</div>
            <div class="user-role">
              {{ rolUsuario?.nombre_corto || "Usuario" }}
            </div>
          </div>
        </div>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import { useAuthStore } from "@/stores/authStore";
import { usePersonalStore } from "@/stores/personalStore";

// Props y emits como el admin-frontend
const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle-collapse", "navigate"]);

// Composables
const route = useRoute();
const router = useRouter();
const toast = useToast();
const authStore = useAuthStore();
const personalStore = usePersonalStore();

// Estado reactivo (como admin-frontend)
const activeDropdown = ref(null);
const isMobile = ref(false);
const showMobileSidebar = ref(false);

// Computed
const currentRoute = computed(() => route.name);

// Detectar móvil (copiado del admin-frontend)
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

// Usuario y rol actual (inteligente)
const usuario = computed(() => authStore.user);
const rolUsuario = computed(() => {
  if (!authStore.userRole) return null;

  const rol = authStore.userRole.codigo_rol.toUpperCase();

  // Configuración específica por rol
  if (rol.includes("JEFE-FA-1")) {
    return {
      codigo_rol: "JEFE-FA-1",
      display_name: "Jefe FA-1",
      nombre_corto: "Jefe FA-1",
      icono: "pi pi-users",
      acceso: "completo",
      scope: "fah_completa",
      unidad_principal: "Departamento de Recursos Humanos FA-1",
      nivel_autoridad: authStore.userRole.nivel_autoridad || 10,
      tipo_admin: "NACIONAL",
      acceso_base: true,
      acceso_seccion: true,
      descripcion_acceso: "Acceso completo FAH",
    };
  } else if (rol.includes("ENC-S-1")) {
    const base = extraerCodigoBase(rol);
    return {
      codigo_rol: `ENC-S-1-${base}`,
      display_name: `Enc. S-1 ${base}`,
      nombre_corto: `Enc. S-1`,
      icono: "pi pi-user",
      acceso: "seccion_s1",
      scope: base,
      base_codigo: base,
      unidad_principal: `Sección de Recursos Humanos Base ${base}`,
      seccion_codigo: "S-1",
      seccion_nombre: "S-1 Recursos Humanos",
      nivel_autoridad: authStore.userRole.nivel_autoridad || 5,
      tipo_admin: "SECCIÓN",
      acceso_base: false,
      acceso_seccion: true,
      descripcion_acceso: `Sección y Base ${base}`,
    };
  } else if (rol.includes("CMDTE-BASE")) {
    const base = extraerCodigoBase(rol);
    return {
      codigo_rol: `CMDTE-BASE-${base}`,
      display_name: `Cmdte. Base ${base}`,
      nombre_corto: `Comandante`,
      icono: "pi pi-star",
      acceso: "base_completa",
      scope: base,
      base_codigo: base,
      unidad_principal: `Base Aérea ${base}`,
      nivel_autoridad: authStore.userRole.nivel_autoridad || 8,
      tipo_admin: "BASE",
      acceso_base: true,
      acceso_seccion: true,
      descripcion_acceso: `Base ${base} completa`,
    };
  } else if (rol.includes("BIENESTAR")) {
    return {
      codigo_rol: "BIENESTAR-PERSONAL",
      display_name: "Bienestar de Personal",
      nombre_corto: "Bienestar",
      icono: "pi pi-heart",
      acceso: "area_especifica",
      scope: "bienestar",
      unidad_principal: "Bienestar de Personal",
      nivel_autoridad: authStore.userRole.nivel_autoridad || 3,
      tipo_admin: "ÁREA",
      acceso_base: false,
      acceso_seccion: false,
      descripcion_acceso: "Área de Bienestar",
    };
  } else {
    return {
      codigo_rol: "GENERAL",
      display_name: "Usuario General",
      nombre_corto: "Usuario",
      icono: "pi pi-user",
      acceso: "limitado",
      scope: "consulta",
      unidad_principal: "Acceso General",
      nivel_autoridad: authStore.userRole.nivel_autoridad || 1,
      tipo_admin: "NINGUNO",
      acceso_base: false,
      acceso_seccion: false,
      descripcion_acceso: "Acceso limitado",
    };
  }
});

// Niveles jerárquicos según el rol
const nivelesJerarquicos = computed(() => {
  if (!rolUsuario.value) return [];
  // Retornar array no vacío para mostrar la sección
  return ["nivel1"]; // Placeholder
});

// Áreas de responsabilidad según el rol (IV. Niveles Jerárquicos)
const areasResponsabilidad = computed(() => {
  if (!rolUsuario.value) return [];

  const areas = [
    {
      codigo: "admin_personal",
      nombre: "Administración de Personal",
      icono: "👥",
      scope: rolUsuario.value.scope,
    },
    {
      codigo: "mantenimiento_efectivo",
      nombre: "Mantenimiento del Efectivo",
      icono: "📊",
      scope: rolUsuario.value.scope,
    },
    {
      codigo: "disciplina",
      nombre: "Disciplina y Orden",
      icono: "⚖️",
      scope: rolUsuario.value.scope,
    },
    {
      codigo: "servicios_personal",
      nombre: "Servicios de Personal",
      icono: "🛎️",
      scope: rolUsuario.value.scope,
    },
    {
      codigo: "bienestar",
      nombre: "Bienestar de Personal",
      icono: "🤝",
      scope: rolUsuario.value.scope,
    },
  ];

  // Filtrar según el tipo de acceso
  if (rolUsuario.value.acceso === "area_especifica") {
    return areas.filter((area) => area.codigo === rolUsuario.value.scope);
  }

  return areas;
});

// Verificar acceso admin
const tieneAccesoAdmin = computed(() => {
  return rolUsuario.value?.nivel_autoridad >= 3;
});

// Información del usuario
const nombreUsuario = computed(() => {
  return usuario.value?.nombre_completo || "Usuario";
});

const iniciales = computed(() => {
  const nombre = nombreUsuario.value;
  return nombre
    .split(" ")
    .map((n) => n[0])
    .join("")
    .substring(0, 2)
    .toUpperCase();
});

// Funciones (adaptadas del admin-frontend)
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
  // En móvil o cuando está expandido
  if (isMobile.value || !props.collapsed) {
    if (activeDropdown.value === dropdownName) {
      activeDropdown.value = null;
    } else {
      activeDropdown.value = dropdownName;
    }
    return;
  }

  // Si está colapsado en desktop, expandir primero
  emit("toggle-collapse");
  // Después abrir el dropdown
  setTimeout(() => {
    activeDropdown.value = dropdownName;
  }, 300);
};

const extraerCodigoBase = (rol) => {
  const partes = rol.split("-");
  return partes[partes.length - 1] || "HAM";
};

// Navegación específica (como admin-frontend con toasts)
const navigateToEspacioTrabajo = async () => {
  try {
    console.log(
      `🎯 Navegando al espacio de trabajo: ${rolUsuario.value.display_name}`
    );

    activeDropdown.value = null;
    await router.push("/espacio-trabajo");
    emit("navigate", "espacio-trabajo");
    handleNavigation();

    console.log("✅ Navegación al espacio de trabajo exitosa");
  } catch (error) {
    console.error("❌ Error navegando al espacio de trabajo:", error);
  }
};

const navigateToRolesUsuarios = async () => {
  try {
    console.log("👤 Navegando a Roles y Usuarios...");

    activeDropdown.value = null;
    await router.push("/admin/roles-usuarios");
    emit("navigate", "roles-usuarios");
    handleNavigation();

    console.log("✅ Navegación a Roles y Usuarios exitosa");
  } catch (error) {
    console.error("❌ Error navegando a Roles y Usuarios:", error);
  }
};

const navigateTo = (section) => {
  const availableSections = ["roles-usuarios", "espacio-trabajo"];

  if (availableSections.includes(section)) {
    switch (section) {
      case "roles-usuarios":
        navigateToRolesUsuarios();
        return;

      case "espacio-trabajo":
        navigateToEspacioTrabajo();
        return;

      default:
        console.warn(`Sección disponible pero no implementada: ${section}`);
        break;
    }
  }

  // Para secciones no disponibles - solo log, sin toast molesto
  console.log(`⏳ Función pendiente de implementación: ${section}`);
  emit("navigate", section);
  handleNavigation();
};
</script>

<style scoped>
/* COLORES FAH - PALETA INSTITUCIONAL */
:root {
  --fah-azul-naval: #1e3a5f;
  --fah-dorado: #d4af37;
  --fah-azul-celeste: #0ea5e9;
  --fah-azul-medio: #5a9bd4;
  --fah-rojo-suave: #c1666b;
  --fah-gris-neutro: #6c757d;
  --fah-fondo-claro: #f8f9fa;
  --fah-borde: #e9ecef;
  --fah-texto-secundario: #495057;
}

/* SIDEBAR CONTAINER */
.sidebar-container {
  background: linear-gradient(180deg, var(--fah-azul-naval) 0%, #2a4a6f 100%);
  color: white;
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  z-index: 1000;
  transition: all 0.3s ease;
  overflow: hidden;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-expanded {
  width: 300px;
}

.sidebar-collapsed {
  width: 80px;
}

.sidebar-mobile-hidden {
  transform: translateX(-100%);
  width: 300px;
}

.sidebar-mobile-visible {
  transform: translateX(0);
  width: min(320px, 90vw);
}

/* MOBILE BACKDROP */
.mobile-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}

.mobile-sidebar-trigger {
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 1001;
  width: 48px;
  height: 48px;
  background: var(--fah-dorado);
  color: var(--fah-azul-naval);
  border: none;
  border-radius: 12px;
  display: none;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
  transition: all 0.3s ease;
}

.mobile-sidebar-trigger:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
}

/* SIDEBAR HEADER */
.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding: 20px;
  border-bottom: 1px solid rgba(212, 175, 55, 0.2);
  min-height: 70px;
  background: rgba(0, 0, 0, 0.1);
  gap: 12px;
}

.toggle-button {
  color: white !important;
  background: rgba(212, 175, 55, 0.1) !important;
  border: 2px solid rgba(212, 175, 55, 0.2) !important;
  border-radius: 8px !important;
  min-width: 40px !important;
  width: 40px !important;
  height: 40px !important;
  padding: 0 !important;
  transition: all 0.3s ease !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

.toggle-button:hover {
  background: rgba(212, 175, 55, 0.3) !important;
  border-color: rgba(212, 175, 55, 0.4) !important;
  transform: scale(1.1) !important;
}

.mobile-close-button {
  background: rgba(193, 102, 107, 0.1) !important;
  border-color: rgba(193, 102, 107, 0.2) !important;
}

.mobile-close-button:hover {
  background: rgba(193, 102, 107, 0.3) !important;
  border-color: rgba(193, 102, 107, 0.4) !important;
}

.mobile-sidebar-title {
  color: white;
  font-size: 18px;
  font-weight: bold;
  margin: 0;
  background: linear-gradient(135deg, var(--fah-dorado), #f0c674);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* SIDEBAR NAVIGATION */
.sidebar-navigation {
  padding: 8px 0;
  overflow-y: auto;
  flex: 1;
  scrollbar-width: thin;
  scrollbar-color: rgba(212, 175, 55, 0.3) transparent;
}

.sidebar-navigation::-webkit-scrollbar {
  width: 4px;
}

.sidebar-navigation::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-navigation::-webkit-scrollbar-thumb {
  background: rgba(212, 175, 55, 0.3);
  border-radius: 2px;
}

/* NAV ITEMS */
.nav-item {
  margin: 0 8px 4px 8px;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.nav-item-active {
  background: linear-gradient(
    135deg,
    rgba(212, 175, 55, 0.15) 0%,
    rgba(14, 165, 233, 0.1) 100%
  );
  border-left: 4px solid var(--fah-dorado);
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 14px 16px;
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
  cursor: pointer;
  border-radius: 6px;
}

.nav-link:hover {
  background: rgba(212, 175, 55, 0.1);
  color: var(--fah-dorado);
  transform: translateX(2px);
}

.nav-role-workspace {
  background: rgba(212, 175, 55, 0.1);
  border: 1px solid rgba(212, 175, 55, 0.2);
}

.nav-role-workspace:hover {
  background: rgba(212, 175, 55, 0.2);
  border-color: var(--fah-dorado);
}

.nav-admin-trigger {
  background: rgba(193, 102, 107, 0.1);
}

.nav-admin-trigger:hover {
  background: rgba(193, 102, 107, 0.2);
}

.nav-icon {
  font-size: 18px;
  margin-right: 12px;
  width: 24px;
  text-align: center;
  transition: all 0.3s ease;
}

.nav-text {
  font-size: 14px;
  font-weight: 500;
  flex: 1;
}

.role-badge {
  background: var(--fah-dorado);
  color: var(--fah-azul-naval);
  font-size: 10px;
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 600;
  text-transform: uppercase;
  margin-left: 8px;
}

.admin-level-badge {
  background: var(--fah-rojo-suave);
  color: white;
  font-size: 10px;
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 600;
  margin-left: 8px;
}

/* DROPDOWNS */
.nav-dropdown-active .nav-dropdown-trigger {
  background: rgba(212, 175, 55, 0.1);
  color: var(--fah-dorado);
}

.nav-chevron {
  font-size: 12px;
  margin-left: auto;
  transition: transform 0.3s ease;
}

.nav-chevron-rotated {
  transform: rotate(180deg);
}

.nav-dropdown-content {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 0 0 8px 8px;
  padding: 8px 0;
  margin: 0 8px 0 20px;
  border-left: 2px solid rgba(212, 175, 55, 0.3);
}

.dropdown-section-header {
  color: var(--fah-dorado);
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  padding: 8px 16px 4px;
  margin-bottom: 4px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  padding: 10px 16px;
  margin: 2px 0;
  color: rgba(255, 255, 255, 0.9);
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 4px;
}

.dropdown-item:hover {
  background: rgba(212, 175, 55, 0.1);
  color: var(--fah-dorado);
  transform: translateX(4px);
}

.admin-item {
  background: rgba(193, 102, 107, 0.05);
}

.admin-item:hover {
  background: rgba(193, 102, 107, 0.15);
}

.dropdown-emoji {
  font-size: 16px;
  margin-right: 12px;
  width: 20px;
}

.dropdown-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.dropdown-title {
  font-size: 13px;
  font-weight: 500;
  line-height: 1.2;
}

.dropdown-description {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.6);
  line-height: 1.2;
}

.dropdown-badge {
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 8px;
  font-weight: 600;
  margin-left: 8px;
  background: rgba(212, 175, 55, 0.2);
  color: var(--fah-dorado);
}

/* NESTED DROPDOWNS */
.nav-dropdown-nested {
  margin: 0;
}

.dropdown-trigger-nested {
  position: relative;
}

.dropdown-chevron-nested {
  font-size: 10px;
  margin-left: 8px;
  transition: transform 0.3s ease;
}

.dropdown-chevron-rotated {
  transform: rotate(180deg);
}

.dropdown-content-nested {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
  padding: 4px 0;
  margin: 4px 0 0 32px;
  border-left: 2px solid rgba(212, 175, 55, 0.2);
}

.dropdown-item-nested {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 3px;
  margin: 1px 0;
}

.dropdown-item-nested:hover {
  background: rgba(212, 175, 55, 0.1);
  color: var(--fah-dorado);
}

.nested-emoji {
  font-size: 14px;
  margin-right: 8px;
  width: 16px;
}

.nested-title {
  font-size: 12px;
  font-weight: 500;
}

/* SEPARADOR */
.nav-separator {
  border-top: 1px solid rgba(212, 175, 55, 0.2);
  margin: 20px 20px;
  opacity: 0.5;
}

/* USER INFO */
.nav-user-info {
  margin: 8px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}

.user-info-compact {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: var(--fah-dorado);
  color: var(--fah-azul-naval);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

.user-details {
  flex: 1;
}

.user-name {
  font-size: 13px;
  font-weight: 600;
  color: white;
  line-height: 1.2;
}

.user-role {
  font-size: 11px;
  color: var(--fah-dorado);
  line-height: 1.2;
}

/* RESPONSIVE */
@media (max-width: 480px) {
  .mobile-sidebar-trigger {
    display: flex;
  }
}

@media (min-width: 481px) {
  .mobile-sidebar-trigger {
    display: none !important;
  }
}

/* ANIMACIONES */
.sidebar-navigation > .nav-item:nth-child(1) {
  animation: fadeInLeft 0.5s ease forwards 0.1s both;
}
.sidebar-navigation > .nav-item:nth-child(2) {
  animation: fadeInLeft 0.5s ease forwards 0.15s both;
}
.sidebar-navigation > .nav-item:nth-child(3) {
  animation: fadeInLeft 0.5s ease forwards 0.2s both;
}
.sidebar-navigation > .nav-item:nth-child(4) {
  animation: fadeInLeft 0.5s ease forwards 0.25s both;
}
.sidebar-navigation > .nav-item:nth-child(5) {
  animation: fadeInLeft 0.5s ease forwards 0.3s both;
}

@keyframes fadeInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
