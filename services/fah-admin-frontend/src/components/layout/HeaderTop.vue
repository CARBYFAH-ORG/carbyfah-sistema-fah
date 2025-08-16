<template>
  <div
    class="header-container bg-gradient-to-r from-slate-800 via-slate-700 to-slate-800 text-white h-[70px] flex items-center justify-between px-6 shadow-lg fixed w-full top-0 z-[1000] border-b border-slate-600/50"
  >
    <!-- Header Left -->
    <div class="header-left flex items-center gap-6 min-w-[300px]">
      <!-- Logo Section FAH -->
      <div
        class="header-brand flex items-center gap-4 cursor-pointer transition-transform duration-200 hover:scale-105"
      >
        <!-- Escudo FAH Profesional -->
        <div class="relative">
          <div
            class="header-logo w-11 h-11 bg-gradient-to-b from-blue-600 to-blue-700 rounded-full p-0.5 shadow-md border border-blue-500/30"
          >
            <div
              class="w-full h-full bg-gradient-to-b from-slate-700 to-slate-800 rounded-full flex items-center justify-center relative"
            >
              <!-- Alas militares -->
              <svg
                class="w-6 h-4 text-blue-400"
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
                <div class="w-0.5 h-0.5 bg-blue-400 rounded-full"></div>
                <div class="w-0.5 h-0.5 bg-blue-400 rounded-full"></div>
                <div class="w-0.5 h-0.5 bg-blue-400 rounded-full"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Información institucional - SE OCULTA EN MÓVIL -->
        <div class="brand-info">
          <h1 class="header-brand-text text-xl font-bold m-0 tracking-wide">
            <span class="text-blue-400">CARBY</span
            ><span class="text-white">FAH</span>
          </h1>
          <span
            class="header-subtitle text-xs text-slate-300 font-medium block mt-0.5 tracking-wide"
          >
            Centro Automatizado de Recursos • FAH
          </span>
        </div>
      </div>

      <!-- Search Section - AHORA VISIBLE EN MÓVIL -->
      <div
        class="search-container absolute left-1/2 transform -translate-x-1/2 max-w-[480px] w-[480px] flex justify-center items-center"
        style="z-index: 30"
      >
        <div class="relative w-full" style="z-index: 20">
          <InputText
            v-model="searchValue"
            placeholder="Buscar en CARBYFAH..."
            class="w-full border-0 font-medium transition-all duration-300 focus:outline-none"
            style="
              padding: 0px 48px 0px 16px;
              height: 48px;
              border-radius: 12px;
              background-color: rgba(203, 213, 225, 0.9) !important;
              color: #1e293b !important;
              z-index: 10;
              position: relative;
            "
            @input="handleSearch"
          />
          <!-- Icono de búsqueda -->
          <i
            class="pi pi-search absolute right-4 top-1/2 transform -translate-y-1/2 text-slate-600 text-base"
            style="z-index: 15"
          ></i>
        </div>
      </div>
    </div>

    <!-- Header Right -->
    <div class="flex items-center gap-3">
      <!-- ✅ NOTIFICACIONES DESKTOP - SE OCULTAN EN MÓVIL -->
      <div
        :class="['desktop-notifications', { hidden: isMobile }]"
        class="flex items-center gap-3"
      >
        <!-- Notifications Button -->
        <Button
          icon="pi pi-bell"
          class="!text-slate-300 !rounded-lg !w-10 !h-10 transition-all duration-200 hover:!bg-slate-600/50 hover:!text-white !border-0"
          :badge="notificationCount.toString()"
          badge-class="p-badge-danger"
          @click="showNotifications"
          :title="`${notificationCount} notificaciones`"
        />

        <!-- Messages Button -->
        <Button
          icon="pi pi-envelope"
          class="!text-slate-300 !rounded-lg !w-10 !h-10 transition-all duration-200 hover:!bg-slate-600/50 hover:!text-white !border-0"
          :badge="messageCount.toString()"
          badge-class="p-badge-info"
          @click="showMessages"
          :title="`${messageCount} mensajes`"
        />
      </div>

      <!-- User Menu - Estilo moderno responsive -->
      <div class="relative flex items-center">
        <!-- Avatar del usuario -->
        <div
          class="user-avatar w-11 h-11 bg-gradient-to-b from-blue-600 to-blue-700 rounded-full p-0.5 shadow-md border border-blue-500/30 cursor-pointer transition-all duration-200 hover:scale-105"
          @click="toggleUserMenu"
          :title="user?.email_institucional"
        >
          <div
            class="w-full h-full bg-gradient-to-b from-slate-700 to-slate-800 rounded-full flex items-center justify-center"
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
  name: "HeaderTop",
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

    // ✅ DETECTAR SI ES MÓVIL (≤480px según CSS)
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

    // Función para detectar móvil (≤480px según el diseño responsivo)
    const checkIsMobile = () => {
      isMobile.value = window.innerWidth <= 480;
    };

    // Función para obtener la inicial del usuario
    const getUserInitial = () => {
      if (user.value?.username) {
        return user.value.username.charAt(0).toUpperCase();
      }
      return "U"; // Default para Usuario
    };

    // ✅ MENÚ DINÁMICO - INCLUYE NOTIFICACIONES EN MÓVIL
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

      // ✅ SI ES MÓVIL, AGREGAR NOTIFICACIONES AL DROPDOWN
      if (isMobile.value) {
        baseItems.push(
          {
            label: `Notificaciones (${notificationCount.value})`,
            icon: "pi pi-bell",
            template: () => {
              return `
                <div class="notification-item">
                  <div class="flex items-center gap-3">
                    <i class="pi pi-bell text-orange-500"></i>
                    <span class="text-gray-700 font-medium">Notificaciones</span>
                  </div>
                  <span class="notification-count">${notificationCount.value}</span>
                </div>
              `;
            },
            command: () => showNotifications(),
          },
          {
            label: `Mensajes (${messageCount.value})`,
            icon: "pi pi-envelope",
            template: () => {
              return `
                <div class="notification-item">
                  <div class="flex items-center gap-3">
                    <i class="pi pi-envelope text-blue-500"></i>
                    <span class="text-gray-700 font-medium">Mensajes</span>
                  </div>
                  <span class="message-count">${messageCount.value}</span>
                </div>
              `;
            },
            command: () => showMessages(),
          },
          {
            separator: true,
          }
        );
      }

      // Opciones estándar del menú
      baseItems.push(
        {
          label: "Mi Perfil",
          icon: "pi pi-user",
          command: () => {
            toast.add({
              severity: "info",
              summary: "Mi Perfil",
              detail: "Navegando a perfil de usuario",
              life: 3000,
            });
          },
        },
        {
          label: "Configuración",
          icon: "pi pi-cog",
          command: () => {
            toast.add({
              severity: "info",
              summary: "Configuración",
              detail: "Navegando a configuración",
              life: 3000,
            });
          },
        },
        {
          separator: true,
        },
        {
          label: "Cerrar Sesión",
          icon: "pi pi-power-off",
          class: "logout-item",
          command: () => {
            handleLogout();
          },
        }
      );

      return baseItems;
    });

    // Métodos
    const handleSearch = () => {
      emit("search-change", searchValue.value);
    };

    const showNotifications = () => {
      toast.add({
        severity: "info",
        summary: "Notificaciones",
        detail: `Tienes ${notificationCount.value} notificaciones nuevas`,
        life: 3000,
      });
    };

    const showMessages = () => {
      toast.add({
        severity: "info",
        summary: "Mensajes",
        detail: `Tienes ${messageCount.value} mensajes sin leer`,
        life: 3000,
      });
    };

    const toggleUserMenu = (event) => {
      userMenuRef.value.toggle(event);
    };

    const handleLogout = () => {
      localStorage.removeItem("fah_token");
      localStorage.removeItem("fah_user");

      toast.add({
        severity: "success",
        summary: "Sesión Cerrada",
        detail: "Has cerrado sesión correctamente",
        life: 3000,
      });

      emit("user-logout");
      router.push("/login");
    };

    return {
      // Estado
      searchValue,
      notificationCount,
      messageCount,
      user,
      userMenuRef,
      userMenuItems,
      isMobile,

      // Métodos
      handleSearch,
      showNotifications,
      showMessages,
      toggleUserMenu,
      handleLogout,
      getUserInitial,
    };
  },
};
</script>

<style>
/* Importar estilos externos organizados */
@import "@/styles/components/layout/header-top.css";
</style>
