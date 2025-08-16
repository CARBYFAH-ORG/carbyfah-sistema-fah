<template>
  <div class="dashboard-layout">
    <!-- Header Fijo -->
    <HeaderPersonal />

    <!-- Contenedor Principal -->
    <div class="main-container">
      <!-- Sidebar Inteligente -->
      <SidebarPersonal
        :collapsed="sidebarCollapsed"
        @toggle-collapse="toggleSidebar"
        @navigate="handleNavigation"
      />

      <!-- Área de Contenido -->
      <main
        class="content-area"
        :class="{ 'content-expanded': sidebarCollapsed }"
      >
        <!-- Breadcrumbs -->
        <Breadcrumbs />

        <!-- Contenido Dinámico -->
        <div class="content-wrapper">
          <router-view />
        </div>
      </main>
    </div>

    <!-- Toast Container para notificaciones -->
    <div id="toast-container"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from "vue";
import HeaderPersonal from "./HeaderPersonal.vue";
import SidebarPersonal from "./SidebarPersonal.vue";
import Breadcrumbs from "../shared/Breadcrumbs.vue";
import { usePersonalStore } from "@/stores/personalStore";
import { useAuthStore } from "@/stores/authStore";

// Stores
const personalStore = usePersonalStore();
const authStore = useAuthStore();

// Estado del sidebar
const sidebarCollapsed = ref(false);

// Métodos del sidebar
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  console.log("Sidebar toggled:", sidebarCollapsed.value);
};

const handleNavigation = (section) => {
  console.log("Navegando a:", section);
};

// Ciclo de vida del componente
onMounted(async () => {
  // Verificar autenticación
  if (!authStore.isAuthenticated) {
    // Redirigir a login si no está autenticado
    console.log("Usuario no autenticado");
    return;
  }

  // Inicializar datos del usuario y rol funcional
  await inicializarDatosUsuario();

  // Configurar eventos globales
  configurarEventosGlobales();
});

onUnmounted(() => {
  // Limpiar eventos
  limpiarEventosGlobales();
});

// Inicializar datos del usuario basado en rol funcional
const inicializarDatosUsuario = async () => {
  try {
    // Obtener información del usuario autenticado desde el token
    const usuario = authStore.user;

    if (usuario && usuario.rol_funcional) {
      // Cargar configuración específica del rol
      await personalStore.cargarConfiguracionRol(usuario.rol_funcional);

      // Cargar datos iniciales según el rol
      await cargarDatosSegunRol(usuario.rol_funcional);
    }
  } catch (error) {
    console.error("Error al inicializar datos del usuario:", error);
    mostrarNotificacion(
      "error",
      "Error de Inicialización",
      "No se pudieron cargar los datos del usuario"
    );
  }
};

// Cargar datos específicos según el rol funcional
const cargarDatosSegunRol = async (rolFuncional) => {
  const rol = rolFuncional.codigo_rol.toUpperCase();

  try {
    switch (true) {
      // Jefe FA-1 (Nivel Nacional FAH)
      case rol.includes("JEFE-FA-1"):
        await personalStore.cargarDashboardJefeFA1();
        break;

      // Encargado S-1 de Base
      case rol.includes("ENC-S-1"):
        const codigoBase = extraerCodigoBase(rol);
        await personalStore.cargarDashboardEncS1(codigoBase);
        break;

      // Comandante de Base
      case rol.includes("CMDTE-BASE"):
        const baseComandante = extraerCodigoBase(rol);
        await personalStore.cargarDashboardComandanteBase(baseComandante);
        break;

      // Rol específico de área (Bienestar, Administración, etc.)
      case rol.includes("BIENESTAR"):
      case rol.includes("ADMIN-PERSONAL"):
      case rol.includes("DISCIPLINA"):
      case rol.includes("SERVICIOS"):
        await personalStore.cargarDashboardAreaEspecifica(rolFuncional);
        break;

      // Rol por defecto
      default:
        await personalStore.cargarDashboardGeneral();
        break;
    }
  } catch (error) {
    console.error(`Error al cargar datos para rol ${rol}:`, error);
  }
};

// Extraer código de base del rol (ej: ENC-S-1-HCM -> HCM)
const extraerCodigoBase = (rol) => {
  const partes = rol.split("-");
  return partes[partes.length - 1] || "HAM";
};

// Configurar eventos globales del dashboard
const configurarEventosGlobales = () => {
  // Escuchar cambios de estado del personal
  document.addEventListener(
    "personal-estado-cambiado",
    manejarCambioEstadoPersonal
  );

  // Escuchar notificaciones del sistema
  document.addEventListener("sistema-notificacion", manejarNotificacionSistema);

  // Escuchar cambios de ubicación (para tracking)
  if ("geolocation" in navigator) {
    navigator.geolocation.watchPosition(
      actualizarUbicacion,
      manejarErrorUbicacion,
      {
        enableHighAccuracy: false,
        timeout: 30000,
        maximumAge: 300000, // 5 minutos
      }
    );
  }
};

// Limpiar eventos al destruir componente
const limpiarEventosGlobales = () => {
  document.removeEventListener(
    "personal-estado-cambiado",
    manejarCambioEstadoPersonal
  );
  document.removeEventListener(
    "sistema-notificacion",
    manejarNotificacionSistema
  );
};

// Manejar cambios de estado del personal
const manejarCambioEstadoPersonal = (evento) => {
  const { personal, estadoAnterior, estadoNuevo } = evento.detail;

  // Actualizar dashboard si es necesario
  personalStore.actualizarEstadoPersonalEnDashboard(personal, estadoNuevo);

  // Mostrar notificación
  mostrarNotificacion(
    "info",
    "Cambio de Estado",
    `${personal.nombre_completo} cambió de ${estadoAnterior} a ${estadoNuevo}`
  );
};

// Manejar notificaciones del sistema
const manejarNotificacionSistema = (evento) => {
  const { tipo, titulo, mensaje, duracion } = evento.detail;
  mostrarNotificacion(tipo, titulo, mensaje, duracion);
};

// Actualizar ubicación para tracking
const actualizarUbicacion = (posicion) => {
  const { latitude, longitude } = posicion.coords;

  // Solo actualizar si el usuario está en misión
  const usuario = authStore.user;
  if (usuario && usuario.estado_personal === "EN_MISION") {
    personalStore.actualizarUbicacionPersonal(usuario.id, {
      latitud: latitude,
      longitud: longitude,
      timestamp: new Date().toISOString(),
    });
  }
};

// Manejar errores de ubicación
const manejarErrorUbicacion = (error) => {
  console.warn("Error de geolocalización:", error.message);
  // No mostrar error al usuario, solo log interno
};

// Función helper para mostrar notificaciones
const mostrarNotificacion = (tipo, titulo, mensaje, duracion = 3000) => {
  // Crear elemento de notificación
  const notificacion = document.createElement("div");
  notificacion.className = `toast toast-${tipo}`;
  notificacion.innerHTML = `
    <div class="toast-header">
      <strong class="toast-title">${titulo}</strong>
      <button type="button" class="toast-close" aria-label="Cerrar">×</button>
    </div>
    <div class="toast-body">${mensaje}</div>
  `;

  // Agregar al container
  const container = document.getElementById("toast-container");
  if (container) {
    container.appendChild(notificacion);

    // Auto-remove después de duración
    setTimeout(() => {
      if (notificacion.parentNode) {
        notificacion.remove();
      }
    }, duracion);

    // Botón de cerrar
    const botonCerrar = notificacion.querySelector(".toast-close");
    if (botonCerrar) {
      botonCerrar.addEventListener("click", () => notificacion.remove());
    }
  }
};
</script>

<style scoped>
/* Layout principal */
.dashboard-layout {
  min-height: 100vh;
  background: #f8f9fa;
  display: flex;
  flex-direction: column;
}

.main-container {
  display: flex;
  flex: 1;
  min-height: calc(100vh - 70px);
}

.content-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  margin-left: 300px;
  transition: margin-left 0.3s ease;
}

.content-area.content-expanded {
  margin-left: 80px;
}

.content-wrapper {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  background: #f8f9fa;
}

/* Responsive */
@media (max-width: 768px) {
  .main-container {
    flex-direction: column;
  }

  .content-wrapper {
    padding: 15px;
  }
}

/* Estados de carga */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(30, 58, 95, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 3px solid rgba(212, 175, 55, 0.3);
  border-top: 3px solid #d4af37;
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
</style>

<style>
/* Estilos globales para notificaciones */
#toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  pointer-events: none;
}

.toast {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
  margin-bottom: 10px;
  min-width: 300px;
  max-width: 400px;
  pointer-events: auto;
  animation: slideInRight 0.3s ease;
}

.toast-success {
  border-left: 4px solid #28a745;
}

.toast-error {
  border-left: 4px solid #dc3545;
}

.toast-warning {
  border-left: 4px solid #ffc107;
}

.toast-info {
  border-left: 4px solid #17a2b8;
}

.toast-header {
  padding: 12px 15px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: between;
  align-items: center;
}

.toast-title {
  color: #1e3a5f;
  font-size: 14px;
  font-weight: 600;
  flex: 1;
}

.toast-close {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 0;
  margin-left: 10px;
  color: #6c757d;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-close:hover {
  color: #1e3a5f;
}

.toast-body {
  padding: 12px 15px;
  color: #495057;
  font-size: 13px;
  line-height: 1.4;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsivo para notificaciones */
@media (max-width: 768px) {
  #toast-container {
    left: 10px;
    right: 10px;
    top: 10px;
  }

  .toast {
    min-width: auto;
    max-width: none;
  }
}
</style>
