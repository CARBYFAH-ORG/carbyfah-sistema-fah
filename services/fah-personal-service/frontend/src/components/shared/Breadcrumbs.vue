<template>
  <nav class="breadcrumbs-container">
    <div class="breadcrumbs-wrapper">
      <!-- Icono de inicio siempre visible -->
      <router-link
        to="/dashboard"
        class="breadcrumb-home"
        :class="{ active: isHome }"
      >
        <i class="icon-home"></i>
        <span class="home-text">Inicio</span>
      </router-link>

      <!-- Separador si hay más elementos -->
      <span v-if="breadcrumbItems.length > 1" class="separator">›</span>

      <!-- Items dinámicos del breadcrumb -->
      <div class="breadcrumb-items">
        <template
          v-for="(item, index) in breadcrumbItems.slice(1)"
          :key="index"
        >
          <!-- Separador entre items -->
          <span v-if="index > 0" class="separator">›</span>

          <!-- Item con enlace -->
          <router-link
            v-if="item.to && !item.disabled"
            :to="item.to"
            class="breadcrumb-item"
            :class="{ active: index === breadcrumbItems.length - 2 }"
          >
            <i v-if="item.icon" :class="item.icon" class="item-icon"></i>
            <span class="item-text">{{ item.label }}</span>
          </router-link>

          <!-- Item sin enlace (actual) -->
          <span
            v-else
            class="breadcrumb-item current"
            :class="{ 'with-role': item.rolContext }"
          >
            <i v-if="item.icon" :class="item.icon" class="item-icon"></i>
            <span class="item-text">{{ item.label }}</span>
            <span v-if="item.rolContext" class="role-badge">{{
              item.rolContext
            }}</span>
          </span>
        </template>
      </div>

      <!-- Información del rol actual (opcional) -->
      <div v-if="mostrarInfoRol && rolActual" class="role-info">
        <div class="role-indicator">
          <i class="icon-role"></i>
          <span class="role-text">{{ rolActual.nombre_rol }}</span>
          <span class="role-level">Nivel {{ rolActual.nivel_autoridad }}</span>
        </div>
      </div>
    </div>

    <!-- Indicador de ubicación jerarquica -->
    <div v-if="ubicacionJerarquica" class="hierarchy-indicator">
      <span class="hierarchy-text">{{ ubicacionJerarquica }}</span>
    </div>
  </nav>
</template>

<script setup>
import { computed, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/authStore";
import { usePersonalStore } from "@/stores/personalStore";

// Props
const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  mostrarInfoRol: {
    type: Boolean,
    default: true,
  },
  mostrarJerarquia: {
    type: Boolean,
    default: true,
  },
});

// Composables
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const personalStore = usePersonalStore();

// Estado computado
const isHome = computed(() => {
  return route.path === "/dashboard" || route.path === "/";
});

const rolActual = computed(() => {
  return authStore.user?.rol_funcional || null;
});

const ubicacionJerarquica = computed(() => {
  if (!props.mostrarJerarquia || !rolActual.value) return null;

  const rol = rolActual.value.codigo_rol.toUpperCase();

  // Generar ubicación jerarquica basada en el rol
  if (rol.includes("JEFE-FA-1")) {
    return "FAH › JEMGA › FA-1 Recursos Humanos";
  } else if (rol.includes("ENC-S-1")) {
    const base = extraerCodigoBase(rol);
    return `FAH › Base Aérea ${base} › S-1 Recursos Humanos`;
  } else if (rol.includes("CMDTE-BASE")) {
    const base = extraerCodigoBase(rol);
    return `FAH › Base Aérea ${base} › Comandancia`;
  } else if (rol.includes("BIENESTAR")) {
    return "FAH › Bienestar de Personal";
  } else {
    return "FAH › Recursos Humanos";
  }
});

// Items del breadcrumb generados automáticamente
const breadcrumbItems = computed(() => {
  // Si hay items prop, usarlos
  if (props.items && props.items.length > 0) {
    return props.items;
  }

  // Generar automáticamente basado en la ruta
  return generarBreadcrumbsAutomatico();
});

// Funciones
const extraerCodigoBase = (rol) => {
  const partes = rol.split("-");
  return partes[partes.length - 1] || "HAM";
};

const generarBreadcrumbsAutomatico = () => {
  const items = [
    { label: "Dashboard", to: "/dashboard", icon: "icon-dashboard" },
  ];

  const rutaActual = route.path;
  const nombreRuta = route.name;
  const metaRuta = route.meta;

  // Analizar ruta actual y generar breadcrumbs
  switch (true) {
    case rutaActual.includes("/personal"):
      items.push({
        label: "Gestión de Personal",
        icon: "icon-users",
        rolContext: rolActual.value?.codigo_rol,
      });
      break;

    case rutaActual.includes("/organigrama"):
      items.push({
        label: "Organigrama",
        icon: "icon-org-chart",
      });
      break;

    case rutaActual.includes("/reportes"):
      items.push({
        label: "Reportes",
        icon: "icon-reports",
      });
      break;

    case rutaActual.includes("/solicitudes"):
      items.push({
        label: "Solicitudes",
        icon: "icon-requests",
      });
      break;

    case rutaActual.includes("/configuracion"):
      items.push({
        label: "Configuración",
        icon: "icon-settings",
      });
      break;

    default:
      // Usar meta de la ruta si está disponible
      if (metaRuta?.breadcrumb) {
        items.push({
          label: metaRuta.breadcrumb,
          icon: metaRuta.icon,
        });
      } else if (nombreRuta) {
        items.push({
          label: formatearNombreRuta(nombreRuta),
          icon: "icon-page",
        });
      }
      break;
  }

  return items;
};

const formatearNombreRuta = (nombreRuta) => {
  return nombreRuta
    .replace(/([A-Z])/g, " $1")
    .replace(/^./, (str) => str.toUpperCase())
    .trim();
};

// Watchers para actualizar breadcrumbs cuando cambie la ruta
watch(
  () => route.fullPath,
  () => {
    // Permitir que el componente se re-compute automáticamente
    console.log("Ruta cambió, regenerando breadcrumbs:", route.path);
  },
  { immediate: true }
);
</script>

<style scoped>
/* Contenedor principal del breadcrumb */
.breadcrumbs-container {
  background: white;
  border-bottom: 1px solid #e9ecef;
  padding: 12px 0;
  margin-bottom: 0;
}

.breadcrumbs-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  padding: 0 24px;
}

/* Home breadcrumb */
.breadcrumb-home {
  display: flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  color: #1e3a5f;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.2s ease;
  font-weight: 500;
}

.breadcrumb-home:hover {
  background: rgba(30, 58, 95, 0.1);
  color: #d4af37;
}

.breadcrumb-home.active {
  background: #1e3a5f;
  color: white;
}

/* Items del breadcrumb */
.breadcrumb-items {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  color: #495057;
  padding: 6px 10px;
  border-radius: 4px;
  transition: all 0.2s ease;
  font-size: 14px;
}

.breadcrumb-item:hover {
  background: rgba(212, 175, 55, 0.1);
  color: #d4af37;
}

.breadcrumb-item.active {
  background: rgba(30, 58, 95, 0.1);
  color: #1e3a5f;
  font-weight: 600;
}

.breadcrumb-item.current {
  color: #1e3a5f;
  font-weight: 600;
  cursor: default;
  background: rgba(30, 58, 95, 0.05);
}

.breadcrumb-item.current.with-role {
  background: linear-gradient(
    135deg,
    rgba(30, 58, 95, 0.1) 0%,
    rgba(212, 175, 55, 0.1) 100%
  );
}

/* Separadores */
.separator {
  color: #6c757d;
  font-size: 14px;
  font-weight: 300;
  margin: 0 2px;
}

/* Iconos */
.icon-home,
.item-icon {
  font-size: 12px;
  width: 14px;
  height: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Texto de elementos */
.home-text,
.item-text {
  font-size: 13px;
  line-height: 1.2;
}

/* Badge del rol */
.role-badge {
  background: #d4af37;
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 6px;
  font-weight: 600;
  text-transform: uppercase;
}

/* Información del rol */
.role-info {
  margin-left: auto;
  display: flex;
  align-items: center;
}

.role-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(30, 58, 95, 0.1);
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
}

.icon-role {
  color: #d4af37;
  font-size: 12px;
}

.role-text {
  color: #1e3a5f;
  font-weight: 600;
}

.role-level {
  color: #6c757d;
  font-size: 11px;
}

/* Indicador de jerarquía */
.hierarchy-indicator {
  background: rgba(212, 175, 55, 0.1);
  border-top: 1px solid rgba(212, 175, 55, 0.2);
  padding: 8px 24px;
  font-size: 12px;
}

.hierarchy-text {
  color: #6c757d;
  font-style: italic;
}

/* Iconos usando pseudo-elementos para compatibilidad */
.icon-home::before {
  content: "🏠";
}
.icon-dashboard::before {
  content: "📊";
}
.icon-users::before {
  content: "👥";
}
.icon-org-chart::before {
  content: "🏢";
}
.icon-reports::before {
  content: "📋";
}
.icon-requests::before {
  content: "📝";
}
.icon-settings::before {
  content: "⚙️";
}
.icon-page::before {
  content: "📄";
}
.icon-role::before {
  content: "👔";
}

/* Responsive */
@media (max-width: 768px) {
  .breadcrumbs-wrapper {
    padding: 0 16px;
  }

  .role-info {
    order: -1;
    margin-left: 0;
    margin-bottom: 8px;
    width: 100%;
  }

  .hierarchy-indicator {
    padding: 6px 16px;
  }

  .home-text,
  .item-text {
    display: none;
  }

  .breadcrumb-home,
  .breadcrumb-item {
    padding: 8px;
  }
}

@media (max-width: 480px) {
  .breadcrumbs-wrapper {
    gap: 4px;
  }

  .separator {
    font-size: 12px;
  }

  .role-badge {
    display: none;
  }
}
</style>
