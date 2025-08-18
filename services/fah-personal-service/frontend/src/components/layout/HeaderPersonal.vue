<template>
  <div
    class="header-container bg-gradient-to-r from-fah-azul-naval via-fah-azul-medio to-fah-azul-naval text-white h-[70px] flex items-center justify-between px-6 shadow-lg fixed w-full top-0 z-[1000] border-b border-fah-dorado/20"
  >
    <!-- Header Left -->
    <div class="header-left flex items-center gap-6 min-w-[300px]">
      <!-- Logo Section FAH -->
      <div
        class="header-brand flex items-center gap-4 cursor-pointer transition-transform duration-200 hover:scale-105"
        @click="navigateToHome"
      >
        <!-- Escudo FAH Profesional -->
        <div class="relative">
          <div
            class="header-logo w-11 h-11 bg-gradient-to-b from-fah-dorado to-yellow-600 rounded-full p-0.5 shadow-md border border-fah-dorado/30"
          >
            <div
              class="w-full h-full bg-gradient-to-b from-fah-azul-naval to-fah-azul-medio rounded-full flex items-center justify-center relative"
            >
              <!-- Alas militares -->
              <svg
                class="w-6 h-4 text-fah-dorado"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  d="M12 2l1.5 4.5L18 8l-4.5 1.5L12 14l-1.5-4.5L6 8l4.5-1.5L12 2z"
                />
                <path d="M4 12l2 2 2-2-2-2-2 2z" />
                <path d="M16 12l2-2 2 2-2 2-2-2z" />
              </svg>
              <!-- Estrellas -->
              <div class="absolute bottom-0.5 flex gap-0.5">
                <div class="w-0.5 h-0.5 bg-fah-dorado rounded-full"></div>
                <div class="w-0.5 h-0.5 bg-fah-dorado rounded-full"></div>
                <div class="w-0.5 h-0.5 bg-fah-dorado rounded-full"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Información institucional - SE OCULTA EN MÓVIL -->
        <div class="brand-info">
          <h1 class="header-brand-text text-xl font-bold m-0 tracking-wide">
            <span class="text-fah-dorado">FAH</span
            ><span class="text-white"> Personal</span>
          </h1>
          <span
            class="header-subtitle text-xs text-slate-300 font-medium block mt-0.5 tracking-wide"
          >
            Sistema de Gestión de Personal • FA-1
          </span>
        </div>
      </div>

      <!-- Search Section - VISIBLE EN MÓVIL -->
      <div
        class="search-container absolute left-1/2 transform -translate-x-1/2 w-full max-w-md px-4 lg:relative lg:left-auto lg:transform-none lg:px-0 lg:w-80 lg:max-w-none"
      >
        <div class="relative">
          <InputText
            v-model="searchValue"
            placeholder="Buscar personal, unidades, reportes..."
            class="w-full pl-4 pr-12 py-2.5 text-sm bg-slate-600/30 border border-slate-500/50 rounded-xl backdrop-blur-sm placeholder-slate-400 text-white focus:bg-white focus:text-slate-800 focus:placeholder-slate-500 transition-all duration-200"
            @input="handleSearchInput"
            @keyup.enter="handleSearchSubmit"
          />
          <button
            @click="handleSearchSubmit"
            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-fah-dorado transition-colors duration-200"
          >
            <i class="pi pi-search text-sm"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Header Center - HIDDEN ON MOBILE -->
    <div class="header-center hidden lg:flex items-center gap-4">
      <!-- Status Indicators -->
      <div class="flex items-center gap-3">
        <!-- Personal Activo -->
        <div
          class="bg-green-500/20 border border-green-400/30 rounded-lg px-3 py-1.5 flex items-center gap-2"
        >
          <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
          <span class="text-green-300 text-xs font-medium"
            >Personal Activo</span
          >
        </div>

        <!-- Sistema Status -->
        <div
          class="bg-fah-dorado/20 border border-fah-dorado/30 rounded-lg px-3 py-1.5 flex items-center gap-2"
        >
          <div class="w-2 h-2 bg-fah-dorado rounded-full"></div>
          <span class="text-fah-dorado text-xs font-medium"
            >Sistema Online</span
          >
        </div>
      </div>
    </div>

    <!-- Header Right -->
    <div class="header-right flex items-center gap-4">
      <!-- Notifications - HIDDEN ON MOBILE -->
      <div class="notifications-section hidden md:flex items-center gap-3">
        <!-- Alertas -->
        <button
          class="relative p-2.5 rounded-xl bg-slate-600/30 border border-slate-500/50 hover:bg-slate-600/50 transition-all duration-200 group"
          title="Alertas del sistema"
          @click="toggleNotifications"
        >
          <i
            class="pi pi-bell text-slate-300 group-hover:text-fah-dorado text-lg"
          ></i>
          <span
            v-if="notificationCount > 0"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
          >
            {{ notificationCount }}
          </span>
        </button>

        <!-- Mensajes -->
        <button
          class="relative p-2.5 rounded-xl bg-slate-600/30 border border-slate-500/50 hover:bg-slate-600/50 transition-all duration-200 group"
          title="Mensajes internos"
          @click="toggleMessages"
        >
          <i
            class="pi pi-envelope text-slate-300 group-hover:text-fah-dorado text-lg"
          ></i>
          <span
            v-if="messageCount > 0"
            class="absolute -top-1 -right-1 bg-fah-dorado text-fah-azul-naval text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
          >
            {{ messageCount }}
          </span>
        </button>
      </div>

      <!-- User Profile Dropdown -->
      <div class="user-section flex items-center gap-2">
        <!-- Avatar -->
        <div
          class="w-10 h-10 bg-gradient-to-b from-fah-dorado to-yellow-600 rounded-full p-0.5 cursor-pointer transition-all duration-200 hover:scale-105"
          @click="toggleUserMenu"
          :title="user?.email_institucional"
        >
          <div
            class="w-full h-full bg-gradient-to-b from-fah-azul-naval to-fah-azul-medio rounded-full flex items-center justify-center"
          >
            <span class="text-white font-bold text-lg">{{
              getUserInitial()
            }}</span>
          </div>
        </div>

        <!-- Botón dropdown separado -->
        <button
          @click="toggleUserMenu"
          class="ml-1 p-1 rounded-full hover:bg-slate-600/50 transition-all duration-200"
          :title="'Opciones de ' + (user?.username || 'Usuario')"
        >
          <i class="pi pi-chevron-down text-slate-300 text-xs"></i>
        </button>

        <!-- Menu dropdown -->
        <Menu ref="userMenuRef" :model="userMenuItems" popup />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, onUnmounted, computed } from "vue";
import { useToast } from "primevue/usetoast";
import { useRouter } from "vue-router";

export default {
  name: "HeaderPersonal",
  emits: ["search-change", "user-logout"],
  setup(props, { emit }) {
    const router = useRouter();
    const toast = useToast();

    // Estado reactivo
    const searchValue = ref("");
    const notificationCount = ref(3);
    const messageCount = ref(2);
    const user = ref(null);
    const userMenuRef = ref();

    // Detectar si es móvil
    const isMobile = ref(false);

    // Cargar datos del usuario al montar
    onMounted(() => {
      const userData = localStorage.getItem("fah_user");
      if (userData) {
        user.value = JSON.parse(userData);
      }

      // Detectar móvil
      checkIsMobile();
      window.addEventListener("resize", checkIsMobile);
    });

    // Limpiar event listener al desmontar
    onUnmounted(() => {
      window.removeEventListener("resize", checkIsMobile);
    });

    // Función para detectar móvil
    const checkIsMobile = () => {
      isMobile.value = window.innerWidth <= 480;
    };

    // Función para obtener la inicial del usuario
    const getUserInitial = () => {
      if (user.value?.username) {
        return user.value.username.charAt(0).toUpperCase();
      }
      return "U"; // Default
    };

    // Menu items del usuario
    const userMenuItems = reactive([
      {
        template: () => {
          return `
            <div class="user-profile-header">
              <div class="user-avatar-large">
                ${getUserInitial()}
              </div>
              <div class="user-info-detailed">
                <div class="user-name-large">${
                  user.value?.username || "Usuario"
                }</div>
                <div class="user-email">${
                  user.value?.email_institucional || "Sin email"
                }</div>
                <div class="user-role-badge">Personal FAH</div>
              </div>
            </div>
          `;
        },
      },
      { separator: true },
      {
        label: "Mi Perfil",
        icon: "pi pi-user",
        command: () => {
          router.push("/perfil");
        },
      },
      {
        label: "Configuración",
        icon: "pi pi-cog",
        command: () => {
          router.push("/configuracion");
        },
      },
      {
        label: "Ayuda",
        icon: "pi pi-question-circle",
        command: () => {
          router.push("/ayuda");
        },
      },
      { separator: true },
      {
        label: "Cerrar Sesión",
        icon: "pi pi-sign-out",
        command: () => {
          handleLogout();
        },
      },
    ]);

    // Handlers
    const handleSearchInput = () => {
      emit("search-change", searchValue.value);
    };

    const handleSearchSubmit = () => {
      if (searchValue.value.trim()) {
        console.log("Búsqueda ejecutada:", searchValue.value);
        // Implementar búsqueda avanzada aquí
      }
    };

    const navigateToHome = () => {
      router.push("/dashboard");
    };

    const toggleUserMenu = (event) => {
      userMenuRef.value.toggle(event);
    };

    const toggleNotifications = () => {
      console.log("Toggle notifications");
      // Implementar panel de notificaciones
    };

    const toggleMessages = () => {
      console.log("Toggle messages");
      // Implementar panel de mensajes
    };

    const handleLogout = async () => {
      try {
        // Limpiar datos del usuario
        localStorage.removeItem("fah_token");
        localStorage.removeItem("fah_user");

        // Emitir evento de logout
        emit("user-logout");

        // Redirigir al login
        await router.push("/login");

        console.log("Logout exitoso");
      } catch (error) {
        console.error("Error al cerrar sesión:", error);
      }
    };

    return {
      // Estado
      searchValue,
      notificationCount,
      messageCount,
      user,
      userMenuRef,
      isMobile,
      userMenuItems,

      // Métodos
      getUserInitial,
      handleSearchInput,
      handleSearchSubmit,
      navigateToHome,
      toggleUserMenu,
      toggleNotifications,
      toggleMessages,
      handleLogout,
    };
  },
};
</script>

<style scoped>
/* Variables CSS FAH */
.text-fah-azul-naval {
  color: #1e3a5f;
}
.text-fah-dorado {
  color: #d4af37;
}
.text-fah-azul-medio {
  color: #5a9bd4;
}
.bg-fah-azul-naval {
  background-color: #1e3a5f;
}
.bg-fah-dorado {
  background-color: #d4af37;
}
.bg-fah-azul-medio {
  background-color: #5a9bd4;
}
.from-fah-azul-naval {
  --tw-gradient-from: #1e3a5f;
}
.to-fah-azul-naval {
  --tw-gradient-to: #1e3a5f;
}
.via-fah-azul-medio {
  --tw-gradient-via: #5a9bd4;
}
.from-fah-dorado {
  --tw-gradient-from: #d4af37;
}
.to-fah-dorado {
  --tw-gradient-to: #d4af37;
}
.border-fah-dorado\/20 {
  border-color: rgba(212, 175, 55, 0.2);
}
.border-fah-dorado\/30 {
  border-color: rgba(212, 175, 55, 0.3);
}
.bg-fah-dorado\/20 {
  background-color: rgba(212, 175, 55, 0.2);
}
.border-fah-dorado\/30 {
  border-color: rgba(212, 175, 55, 0.3);
}

/* Ocultar brand info en móvil */
@media (max-width: 768px) {
  .brand-info {
    display: none;
  }
}

/* Responsive search */
@media (max-width: 1024px) {
  .search-container {
    max-width: 200px;
  }
}

/* Estados del input de búsqueda */
:deep(.p-inputtext) {
  color: #1e293b !important;
  background-color: rgba(203, 213, 225, 0.9) !important;
}

:deep(.p-inputtext::placeholder) {
  color: #6e6e6e !important;
}

:deep(.p-inputtext:focus) {
  box-shadow: rgba(212, 175, 55, 0.5) 0px 0px 0px 4px !important;
  background-color: white !important;
  color: #1e293b !important;
}

/* Dropdown del usuario con estilos FAH */
:global(.p-menu),
:global(.p-menu.p-component),
:global(.p-menu.p-overlay) {
  margin-top: 8px !important;
  border-radius: 16px !important;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
  border: 1px solid rgba(212, 175, 55, 0.2) !important;
  overflow: hidden !important;
  min-width: 300px !important;
  backdrop-filter: blur(12px) !important;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
  z-index: 9999 !important;
}

:global(.p-menu .p-menu-list) {
  padding: 20px !important;
  background: transparent !important;
  border: none !important;
  margin: 0 !important;
}

/* Header del perfil estilo FAH */
:global(.user-profile-header) {
  display: flex !important;
  align-items: center !important;
  padding: 20px !important;
  background: linear-gradient(135deg, #1e3a5f 0%, #5a9bd4 100%) !important;
  border: none !important;
  border-radius: 16px !important;
  margin-bottom: 16px !important;
  position: relative !important;
  color: white !important;
}

:global(.user-avatar-large) {
  width: 52px !important;
  height: 52px !important;
  background: linear-gradient(135deg, #d4af37 0%, #f0c674 100%) !important;
  border-radius: 50% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #1e3a5f !important;
  font-weight: bold !important;
  font-size: 20px !important;
  margin-right: 16px !important;
}

:global(.user-info-detailed) {
  flex: 1 !important;
}

:global(.user-name-large) {
  font-size: 16px !important;
  font-weight: bold !important;
  color: white !important;
  margin-bottom: 4px !important;
}

:global(.user-email) {
  font-size: 13px !important;
  color: rgba(255, 255, 255, 0.8) !important;
  margin-bottom: 8px !important;
}

:global(.user-role-badge) {
  background: rgba(212, 175, 55, 0.2) !important;
  color: #d4af37 !important;
  font-size: 11px !important;
  padding: 4px 8px !important;
  border-radius: 12px !important;
  font-weight: 600 !important;
  display: inline-block !important;
}
</style>
