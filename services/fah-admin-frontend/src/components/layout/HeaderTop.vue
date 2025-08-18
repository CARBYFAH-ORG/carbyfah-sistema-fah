<!-- services\fah-admin-frontend\src\components\layout\HeaderTop.vue -->

<template>
  <div class="header-container">
    <!-- Header Left - Logo y busqueda -->
    <div class="header-left">
      <!-- Logo Section FAH -->
      <div class="header-brand">
        <!-- Escudo FAH Profesional -->
        <div class="header-logo">
          <div class="logo-inner">
            <!-- Alas militares -->
            <svg class="logo-wings" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M12 2l1.5 4.5L18 8l-4.5 1.5L12 14l-1.5-4.5L6 8l4.5-1.5L12 2z"
              />
              <path d="M4 12l2 2 2-2-2-2-2 2z" />
              <path d="M16 12l2-2 2 2-2 2-2-2z" />
            </svg>
            <!-- Estrellas -->
            <div class="logo-stars">
              <div class="star"></div>
              <div class="star"></div>
              <div class="star"></div>
            </div>
          </div>
        </div>

        <!-- Informacion institucional - SE OCULTA EN MOVIL -->
        <div class="brand-info">
          <h1 class="header-brand-text">
            <span class="carby-text">CARBY</span
            ><span class="fah-text">FAH</span>
          </h1>
          <span class="header-subtitle">
            Centro Automatizado de Recursos • FAH
          </span>
        </div>
      </div>

      <!-- Search Section -->
      <div class="search-container">
        <div class="search-wrapper">
          <input
            type="text"
            v-model="searchValue"
            placeholder="Buscar en CARBYFAH..."
            class="search-input-custom"
            @input="handleSearch"
            @focus="handleSearchFocus"
            @blur="handleSearchBlur"
          />
          <i class="pi pi-search search-icon"></i>
        </div>
      </div>
    </div>

    <!-- Header Right - Notificaciones y Usuario -->
    <div class="header-right">
      <!-- Notificaciones Desktop -->
      <div class="desktop-notifications">
        <!-- Boton notificaciones personalizado -->
        <div class="custom-notification-container">
          <button
            @click="showNotifications"
            @keypress="handleNotificationKeyPress"
            class="custom-notification-btn"
            :title="`${notificationCount} notificaciones`"
            role="button"
            tabindex="0"
          >
            <i class="pi pi-bell"></i>
            <span
              v-if="notificationCount > 0"
              class="custom-notification-badge"
            >
              {{ notificationCount > 99 ? "99+" : notificationCount }}
            </span>
          </button>
        </div>

        <!-- Boton mensajes personalizado -->
        <div class="custom-message-container">
          <button
            @click="showMessages"
            @keypress="handleMessageKeyPress"
            class="custom-message-btn"
            :title="`${messageCount} mensajes`"
            role="button"
            tabindex="0"
          >
            <i class="pi pi-envelope"></i>
            <span v-if="messageCount > 0" class="custom-message-badge">
              {{ messageCount > 99 ? "99+" : messageCount }}
            </span>
          </button>
        </div>
      </div>

      <!-- User Menu -->
      <div class="user-menu-container">
        <!-- Avatar del usuario -->
        <div
          class="user-avatar"
          @click="toggleUserMenu"
          :title="user?.email_institucional"
          role="button"
          tabindex="0"
          @keypress="handleUserKeyPress"
        >
          <div class="avatar-inner">
            <span class="avatar-text">{{ getUserInitial() }}</span>
          </div>
        </div>

        <!-- Boton dropdown -->
        <button
          @click="toggleUserMenu"
          class="user-dropdown-btn"
          :title="'Opciones de ' + (user?.username || 'Usuario')"
        >
          <i class="pi pi-chevron-down"></i>
        </button>

        <!-- Menu dropdown -->
        <Menu ref="userMenuRef" :model="userMenuItems" popup />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useToastFAH } from "@/composables/useToastFAH";
import { useRouter } from "vue-router";

export default {
  name: "HeaderTop",
  emits: ["search-change", "user-logout"],
  setup(props, { emit }) {
    const router = useRouter();
    const toast = useToastFAH();

    // Estado reactivo
    const searchValue = ref("");
    const isSearchFocused = ref(false);

    const handleSearchFocus = () => {
      isSearchFocused.value = true;
    };

    const handleSearchBlur = () => {
      isSearchFocused.value = false;
    };
    const notificationCount = ref(3);
    const messageCount = ref(2);
    const user = ref(null);
    const userMenuRef = ref();
    const isMobile = ref(false);

    // Cargar datos del usuario al montar
    onMounted(() => {
      const userData = localStorage.getItem("fah_user");
      if (userData) {
        user.value = JSON.parse(userData);
      }

      // Detectar movil
      checkIsMobile();
      window.addEventListener("resize", checkIsMobile);
    });

    // Limpiar event listener al desmontar
    onUnmounted(() => {
      window.removeEventListener("resize", checkIsMobile);
    });

    // Funcion para detectar movil
    const checkIsMobile = () => {
      isMobile.value = window.innerWidth <= 480;
    };

    // Funcion para obtener la inicial del usuario
    const getUserInitial = () => {
      if (user.value?.username) {
        return user.value.username.charAt(0).toUpperCase();
      }
      return "U"; // Default para Usuario
    };

    // Menu dinamico - incluye notificaciones en movil
    const userMenuItems = computed(() => {
      const baseItems = [
        {
          template: () => {
            return `
              <div class="user-profile-header">
                <div class="user-avatar-large">
                  ${getUserInitial()}
                </div>
                <div class="user-info">
                  <div class="user-name">${
                    user.value?.username || "Usuario"
                  }</div>
                  <div class="user-email">${
                    user.value?.email_institucional || "usuario@fah.mil.hn"
                  }</div>
                </div>
                <i class="pi pi-check user-check"></i>
              </div>
            `;
          },
        },
        {
          separator: true,
        },
      ];

      // Agregar notificaciones en movil
      if (isMobile.value) {
        baseItems.push(
          {
            label: `Notificaciones (${notificationCount.value})`,
            icon: "pi pi-bell",
            class: "mobile-notification-item",
            command: () => {
              showNotifications();
            },
          },
          {
            label: `Mensajes (${messageCount.value})`,
            icon: "pi pi-envelope",
            class: "mobile-message-item",
            command: () => {
              showMessages();
            },
          },
          {
            separator: true,
          }
        );
      }

      baseItems.push(
        {
          label: "Mi Perfil",
          icon: "pi pi-user",
          command: () => {
            toast.info(
              "Notificaciones",
              `Tienes ${notificationCount.value} notificaciones nuevas`
            );
          },
        },
        {
          label: "Configuracion",
          icon: "pi pi-cog",
          command: () => {
            toast.info(
              "Notificaciones",
              `Tienes ${notificationCount.value} notificaciones nuevas`
            );
          },
        },
        {
          separator: true,
        },
        {
          label: "Cerrar Sesion",
          icon: "pi pi-power-off",
          class: "logout-item",
          command: () => {
            handleLogout();
          },
        }
      );

      return baseItems;
    });

    // Metodos
    const handleSearch = () => {
      emit("search-change", searchValue.value);
    };

    const showNotifications = () => {
      toast.info(
        "Notificaciones",
        `Tienes ${notificationCount.value} notificaciones nuevas`
      );
    };

    const showMessages = () => {
      toast.info(
        "Notificaciones",
        `Tienes ${notificationCount.value} notificaciones nuevas`
      );
    };

    const toggleUserMenu = (event) => {
      userMenuRef.value.toggle(event);
    };

    const handleUserKeyPress = (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        toggleUserMenu(event);
      }
    };

    // Metodos para manejar teclas en botones personalizados
    const handleNotificationKeyPress = (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        showNotifications();
      }
    };

    const handleMessageKeyPress = (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        showMessages();
      }
    };

    const handleLogout = () => {
      localStorage.removeItem("fah_token");
      localStorage.removeItem("fah_user");

      toast.info(
        "Notificaciones",
        `Tienes ${notificationCount.value} notificaciones nuevas`
      );

      emit("user-logout");
      router.push("/login");
    };

    return {
      // Estado
      searchValue,
      isSearchFocused,
      handleSearchFocus,
      handleSearchBlur,
      notificationCount,
      messageCount,
      user,
      userMenuRef,
      userMenuItems,
      isMobile,

      // Metodos
      handleSearch,
      showNotifications,
      showMessages,
      toggleUserMenu,
      handleUserKeyPress,
      handleNotificationKeyPress,
      handleMessageKeyPress,
      handleLogout,
      getUserInitial,
    };
  },
};
</script>

<style scoped>
/* Header Top FAH - Completamente independiente */
/* Paleta de colores autorizada FAH - Sin dependencias externas */

/* Contenedor principal del header */
.header-container {
  background: #1e3a5f;
  color: #ffffff;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  box-shadow: 0 2px 8px rgba(30, 58, 95, 0.2);
  position: fixed;
  width: 100%;
  top: 0;
  z-index: 1000;
  border-bottom: 1px solid #495057;
}

/* Header Left - Logo y busqueda */
.header-left {
  display: flex;
  align-items: center;
  gap: 24px;
  min-width: 300px;
  flex: 1;
}

/* Header Brand - Logo FAH */
.header-brand {
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.header-brand:hover {
  transform: scale(1.02);
}

/* Logo del header - Diseno FAH con paleta autorizada */
.header-logo {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #0ea5e9, #5a9bd4);
  border-radius: 50%;
  padding: 2px;
  box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
  border: 2px solid rgba(14, 165, 233, 0.3);
  transition: all 0.3s ease;
}

.header-logo:hover {
  box-shadow: 0 6px 16px rgba(14, 165, 233, 0.4);
  transform: scale(1.05);
}

.logo-inner {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #495057, #1e3a5f);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.logo-wings {
  width: 24px;
  height: 16px;
  color: #0ea5e9;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
}

.logo-stars {
  position: absolute;
  bottom: 2px;
  display: flex;
  gap: 2px;
}

.star {
  width: 2px;
  height: 2px;
  background: #0ea5e9;
  border-radius: 50%;
  box-shadow: 0 0 2px rgba(14, 165, 233, 0.5);
}

/* Informacion de marca */
.brand-info {
  display: flex;
  flex-direction: column;
}

.header-brand-text {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  letter-spacing: 0.5px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.carby-text {
  color: #d4af37;
}

.fah-text {
  color: #ffffff;
}

.header-subtitle {
  font-size: 12px;
  color: #e9ecef;
  font-weight: 500;
  margin-top: 2px;
  letter-spacing: 0.3px;
}

/* Campo de busqueda personalizado FAH */
.search-input-custom {
  width: 100%;
  height: 48px;
  padding: 0 48px 0 16px;
  border: 2px solid #495057;
  border-radius: 12px;
  background: #ffffff;
  color: #1e3a5f;
  font-size: 14px;
  font-weight: 500;
  font-family: inherit;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(30, 58, 95, 0.1);
}

/* Placeholder personalizado */
.search-input-custom::placeholder {
  color: #6c757d;
  font-weight: 400;
  transition: color 0.3s ease;
}

/* Estado focus personalizado */
.search-input-custom:focus {
  outline: none;
  border-color: #d4af37;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2),
    0 4px 16px rgba(30, 58, 95, 0.15), 0 0 20px rgba(212, 175, 55, 0.1);
  transform: translateY(-1px);
}

/* Placeholder en focus */
.search-input-custom:focus::placeholder {
  color: #8a8d92;
  transform: scale(0.95);
}

/* Estados hover */
.search-input-custom:hover {
  border-color: #6c757d;
  box-shadow: 0 3px 12px rgba(30, 58, 95, 0.15);
}

/* Estados disabled */
.search-input-custom:disabled {
  background: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
  opacity: 0.6;
}

/* Animacion de writing */
.search-input-custom:not(:placeholder-shown) {
  border-color: #1e3a5f;
  background: rgba(212, 175, 55, 0.02);
}

/* Search Container */
.search-container {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  max-width: 480px;
  width: 480px;
  z-index: 30;
}

.search-wrapper {
  position: relative;
  width: 100%;
}

.search-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #495057;
  font-size: 16px;
  z-index: 15;
  transition: color 0.2s ease;
}

.search-wrapper:focus-within .search-icon {
  color: #d4af37;
}

/* Header Right - Notificaciones y usuario */
.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Notificaciones Desktop - Botones personalizados */
.desktop-notifications {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Contenedores de botones personalizados */
.custom-notification-container,
.custom-message-container {
  position: relative;
  display: inline-block;
}

/* Botones base personalizados */
.custom-notification-btn,
.custom-message-btn {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  border: 1px solid #495057;
  background: transparent;
  color: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  box-shadow: 0 2px 6px rgba(30, 58, 95, 0.1);
  overflow: visible;
  font-family: inherit;
  font-size: 16px;
}

/* Estados hover */
.custom-notification-btn:hover,
.custom-message-btn:hover {
  background: #495057;
  border-color: #d4af37;
  color: #d4af37;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(30, 58, 95, 0.2);
}

/* Estados focus */
.custom-notification-btn:focus,
.custom-message-btn:focus {
  outline: none;
  box-shadow: 0 0 0 2px #d4af37, 0 4px 12px rgba(30, 58, 95, 0.15);
}

/* Estados active */
.custom-notification-btn:active,
.custom-message-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 6px rgba(30, 58, 95, 0.1);
}

/* Iconos de los botones */
.custom-notification-btn i,
.custom-message-btn i {
  font-size: 16px;
  color: inherit;
  transition: all 0.2s ease;
}

.custom-notification-btn:hover i,
.custom-message-btn:hover i {
  transform: scale(1.1);
}

/* Badges personalizados */
.custom-notification-badge,
.custom-message-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  color: #ffffff;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  border: 2px solid #1e3a5f;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  z-index: 10;
  line-height: 1;
  padding: 0 4px;
}

/* Badge de notificaciones (rojo) */
.custom-notification-badge {
  background: linear-gradient(135deg, #c1666b, #a85459);
  box-shadow: 0 2px 8px rgba(193, 102, 107, 0.4), 0 1px 3px rgba(0, 0, 0, 0.2);
}

.custom-notification-btn:hover .custom-notification-badge {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(193, 102, 107, 0.5), 0 2px 6px rgba(0, 0, 0, 0.3);
  animation: pulseRed 2s infinite;
}

/* Badge de mensajes (azul) */
.custom-message-badge {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  box-shadow: 0 2px 8px rgba(14, 165, 233, 0.4), 0 1px 3px rgba(0, 0, 0, 0.2);
}

.custom-message-btn:hover .custom-message-badge {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(14, 165, 233, 0.5), 0 2px 6px rgba(0, 0, 0, 0.3);
  animation: pulseBlue 2s infinite;
}

/* Animaciones de badges */
@keyframes pulseRed {
  0% {
    box-shadow: 0 4px 12px rgba(193, 102, 107, 0.5),
      0 2px 6px rgba(0, 0, 0, 0.3), 0 0 0 0 rgba(193, 102, 107, 0.4);
  }
  50% {
    box-shadow: 0 4px 12px rgba(193, 102, 107, 0.5),
      0 2px 6px rgba(0, 0, 0, 0.3), 0 0 0 8px rgba(193, 102, 107, 0);
  }
  100% {
    box-shadow: 0 4px 12px rgba(193, 102, 107, 0.5),
      0 2px 6px rgba(0, 0, 0, 0.3), 0 0 0 0 rgba(193, 102, 107, 0);
  }
}

@keyframes pulseBlue {
  0% {
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.5), 0 2px 6px rgba(0, 0, 0, 0.3),
      0 0 0 0 rgba(14, 165, 233, 0.4);
  }
  50% {
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.5), 0 2px 6px rgba(0, 0, 0, 0.3),
      0 0 0 8px rgba(14, 165, 233, 0);
  }
  100% {
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.5), 0 2px 6px rgba(0, 0, 0, 0.3),
      0 0 0 0 rgba(14, 165, 233, 0);
  }
}

/* Efectos especiales */
.custom-notification-btn::before,
.custom-message-btn::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 8px;
  background: linear-gradient(
    45deg,
    transparent 30%,
    rgba(212, 175, 55, 0.1) 50%,
    transparent 70%
  );
  opacity: 0;
  transition: opacity 0.3s ease;
}

.custom-notification-btn:hover::before,
.custom-message-btn:hover::before {
  opacity: 1;
}

/* Responsive - Ocultar en movil */
@media (max-width: 480px) {
  .desktop-notifications {
    display: none;
  }
}

/* User Menu Container */
.user-menu-container {
  display: flex;
  align-items: center;
  position: relative;
}

/* Avatar del usuario - Paleta FAH */
.user-avatar {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #d4af37, #b8941f);
  border-radius: 50%;
  padding: 2px;
  box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
  border: 2px solid rgba(212, 175, 55, 0.3);
  cursor: pointer;
  transition: all 0.2s ease;
}

.user-avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 16px rgba(212, 175, 55, 0.4);
}

.user-avatar:focus {
  outline: none;
  box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #d4af37;
}

.avatar-inner {
  width: 100%;
  height: 100%;
  background: #1e3a5f;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-text {
  color: #d4af37;
  font-weight: 700;
  font-size: 18px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Boton dropdown del usuario */
.user-dropdown-btn {
  margin-left: 4px;
  padding: 4px;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: #e9ecef;
  cursor: pointer;
  transition: all 0.2s ease;
}

.user-dropdown-btn:hover {
  background: #495057;
  color: #d4af37;
}

.user-dropdown-btn:focus {
  outline: none;
  box-shadow: 0 0 0 2px #d4af37;
}

.user-dropdown-btn i {
  font-size: 12px;
}

/* Responsive Design */

/* Tablet */
@media (max-width: 768px) {
  .header-container {
    padding: 0 16px;
  }

  .header-left {
    gap: 16px;
  }

  .search-container {
    max-width: 360px;
    width: 360px;
  }
}

/* Mobile */
@media (max-width: 480px) {
  .header-container {
    padding: 0 12px;
  }

  .header-left {
    gap: 12px;
    min-width: auto;
    flex: 1;
  }

  /* Ocultar texto CARBYFAH */
  .brand-info {
    display: none;
  }

  .search-container {
    position: relative;
    left: auto;
    transform: none;
    max-width: 180px;
    width: 180px;
    flex-shrink: 1;
  }

  /* Input mas pequeno */
  .search-input-custom {
    height: 36px !important;
    font-size: 13px !important;
    padding: 0 36px 0 12px !important;
    border-radius: 8px !important;
    border-width: 1px !important;
  }

  .search-icon {
    right: 10px !important;
    font-size: 14px !important;
  }

  /* Ocultar notificaciones desktop */
  .desktop-notifications {
    display: none;
  }

  .header-logo {
    width: 36px;
    height: 36px;
  }

  .user-avatar {
    width: 36px;
    height: 36px;
  }

  .avatar-text {
    font-size: 16px;
  }
}

/* Tablet - Intermedio */
@media (max-width: 768px) and (min-width: 481px) {
  .search-container {
    max-width: 300px;
    width: 300px;
  }

  .search-input-custom {
    height: 42px;
  }
}

/* Mobile Small */
@media (max-width: 320px) {
  .header-container {
    padding: 0 8px;
  }

  .search-container {
    max-width: 150px;
    width: 150px;
  }
}

/* Animaciones */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.header-container {
  animation: fadeIn 0.3s ease;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.4);
  }
  70% {
    box-shadow: 0 0 0 8px rgba(212, 175, 55, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(212, 175, 55, 0);
  }
}

.notification-badge:hover,
.message-badge:hover {
  animation: pulse 1.5s infinite;
}
</style>

<style>
/* Estilos globales para dropdown menu - Paleta FAH */
/* Estos estilos deben ser globales para afectar el componente Menu de PrimeVue */

/* Contenedor principal del menu */
.p-menu,
.p-menu.p-component,
.p-menu.p-overlay,
.p-overlaypanel,
div[data-pc-name="menu"],
[data-pc-section="root"] {
  margin-top: 8px !important;
  border-radius: 12px !important;
  box-shadow: 0 8px 25px rgba(30, 58, 95, 0.3), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
  border: 1px solid #495057 !important;
  overflow: hidden !important;
  min-width: 300px !important;
  background: #ffffff !important;
  z-index: 9999 !important;
  backdrop-filter: blur(12px) !important;
}

.p-menu .p-menu-list,
.p-menu-list,
[data-pc-section="menu"] {
  padding: 16px !important;
  background: transparent !important;
  border: none !important;
  margin: 0 !important;
}

/* Header del perfil en el dropdown */
.user-profile-header {
  display: flex !important;
  align-items: center !important;
  padding: 16px !important;
  background: #1e3a5f !important;
  border-radius: 8px !important;
  margin-bottom: 12px !important;
  color: #ffffff !important;
  border: none !important;
  box-shadow: 0 2px 8px rgba(30, 58, 95, 0.2) !important;
}

.user-avatar-large {
  width: 48px !important;
  height: 48px !important;
  background: #d4af37 !important;
  border-radius: 50% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #1e3a5f !important;
  font-weight: 700 !important;
  font-size: 18px !important;
  margin-right: 12px !important;
  flex-shrink: 0 !important;
  box-shadow: 0 2px 6px rgba(212, 175, 55, 0.3) !important;
}

.user-info {
  flex: 1 !important;
}

.user-name {
  font-size: 16px !important;
  font-weight: 600 !important;
  color: #ffffff !important;
  margin: 0 !important;
  line-height: 1.2 !important;
}

.user-email {
  font-size: 12px !important;
  color: #e9ecef !important;
  margin: 2px 0 0 0 !important;
  line-height: 1.2 !important;
}

.user-check {
  color: #d4af37 !important;
  font-size: 16px !important;
}

/* Items del menu */
.p-menu .p-menuitem {
  margin: 2px 0 !important;
}

.p-menu .p-menuitem-link {
  padding: 12px !important;
  border-radius: 6px !important;
  color: #1e3a5f !important;
  text-decoration: none !important;
  transition: all 0.2s ease !important;
  cursor: pointer !important;
  display: flex !important;
  align-items: center !important;
  font-weight: 500 !important;
  border: 1px solid transparent !important;
}

.p-menu .p-menuitem-link:hover {
  background: #f8f9fa !important;
  color: #1e3a5f !important;
  transform: translateX(2px) !important;
  border-color: #e9ecef !important;
  box-shadow: 0 2px 6px rgba(30, 58, 95, 0.1) !important;
}

.p-menu .p-menuitem-icon {
  color: #d4af37 !important;
  margin-right: 10px !important;
  font-size: 14px !important;
}

.p-menu .p-menuitem-text {
  color: inherit !important;
  font-weight: 500 !important;
  font-size: 14px !important;
}

/* Items especiales movil */
.mobile-notification-item .p-menuitem-link,
.mobile-message-item .p-menuitem-link {
  background: rgba(14, 165, 233, 0.1) !important;
  border: 1px solid rgba(14, 165, 233, 0.2) !important;
}

.mobile-notification-item .p-menuitem-link:hover,
.mobile-message-item .p-menuitem-link:hover {
  background: rgba(14, 165, 233, 0.15) !important;
  border-color: #0ea5e9 !important;
}

.mobile-notification-item .p-menuitem-icon,
.mobile-message-item .p-menuitem-icon {
  color: #0ea5e9 !important;
}

/* Item de logout */
.logout-item .p-menuitem-link {
  color: #c1666b !important;
  border: 1px solid rgba(193, 102, 107, 0.2) !important;
}

.logout-item .p-menuitem-link:hover {
  background: rgba(193, 102, 107, 0.1) !important;
  color: #c1666b !important;
  border-color: #c1666b !important;
}

.logout-item .p-menuitem-icon {
  color: #c1666b !important;
}

/* Separadores */
.p-menu .p-menu-separator {
  background: #e9ecef !important;
  height: 1px !important;
  margin: 8px 0 !important;
  border: none !important;
}

/* Responsive para el menu */
@media (max-width: 480px) {
  .p-menu {
    min-width: 240px !important;
    margin-top: 2px !important;
  }
}

@media (max-width: 320px) {
  .p-menu {
    min-width: 220px !important;
    left: -100px !important;
  }
}
</style>
