<template>
  <div
    class="fixed top-0 left-0 w-full h-[70px] bg-gradient-to-r from-[#1e3a5f] to-[#2a4a6f] shadow-lg z-50 flex items-center justify-between px-6"
  >
    <!-- Logo y Título Izquierdo -->
    <div class="flex items-center gap-4">
      <!-- Logo FAH Personal -->
      <div
        class="w-12 h-12 bg-gradient-to-br from-[#d4af37] to-[#b8941f] rounded-xl flex items-center justify-center shadow-md"
      >
        <i class="pi pi-users text-white text-xl font-bold"></i>
      </div>

      <!-- Título Sistema -->
      <div class="flex flex-col">
        <h1 class="text-white font-bold text-lg leading-tight">
          FA-1 Personal
        </h1>
        <p class="text-[#d4af37] text-xs font-medium">Fuerza Aérea Hondureña</p>
      </div>

      <!-- Separador Visual -->
      <div class="h-8 w-px bg-white bg-opacity-20 ml-2"></div>

      <!-- Indicador de Servicio -->
      <div class="flex items-center gap-2">
        <div class="w-2 h-2 bg-[#28a745] rounded-full animate-pulse"></div>
        <span class="text-white text-sm font-medium">Personal Service</span>
      </div>
    </div>

    <!-- Controles Centrales -->
    <div class="flex items-center gap-4 flex-1 justify-center max-w-md">
      <!-- Barra de Búsqueda Global -->
      <div class="relative flex-1 max-w-sm">
        <i
          class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"
        ></i>
        <InputText
          v-model="searchTerm"
          placeholder="Buscar personal FAH..."
          class="w-full pl-10 pr-4 py-2 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-gray-300 focus:bg-opacity-20 focus:border-[#d4af37] transition-all duration-300"
          @keyup.enter="handleSearch"
          @input="handleSearchInput"
        />
      </div>
    </div>

    <!-- Controles Derechos -->
    <div class="flex items-center gap-4">
      <!-- Estadísticas Rápidas -->
      <div class="hidden md:flex items-center gap-4 mr-4">
        <div class="text-center">
          <div class="text-[#d4af37] text-xs font-medium">Total</div>
          <div class="text-white text-sm font-bold">{{ totalPersonal }}</div>
        </div>
        <div class="text-center">
          <div class="text-[#28a745] text-xs font-medium">Activos</div>
          <div class="text-white text-sm font-bold">{{ personalActivo }}</div>
        </div>
        <div class="text-center">
          <div class="text-[#ffc107] text-xs font-medium">Misión</div>
          <div class="text-white text-sm font-bold">{{ personalMision }}</div>
        </div>
      </div>

      <!-- Notificaciones -->
      <Button
        icon="pi pi-bell"
        class="p-2 bg-white bg-opacity-10 border border-white border-opacity-20 text-white hover:bg-opacity-20 hover:border-[#d4af37] transition-all duration-300 rounded-lg"
        @click="toggleNotifications"
        v-tooltip.bottom="'Notificaciones'"
      >
        <Badge
          v-if="notificationsCount > 0"
          :value="notificationsCount"
          severity="danger"
          class="absolute -top-1 -right-1"
        />
      </Button>

      <!-- Enlaces Rápidos -->
      <Button
        icon="pi pi-external-link"
        class="p-2 bg-white bg-opacity-10 border border-white border-opacity-20 text-white hover:bg-opacity-20 hover:border-[#d4af37] transition-all duration-300 rounded-lg"
        @click="abrirAdminPrincipal"
        v-tooltip.bottom="'Ir a Admin Principal'"
      />

      <!-- Perfil de Usuario -->
      <div class="flex items-center gap-3">
        <!-- Avatar -->
        <Avatar
          :label="userInitials"
          class="bg-[#d4af37] text-[#1e3a5f] font-bold"
          shape="circle"
          size="normal"
        />

        <!-- Info Usuario -->
        <div class="hidden lg:block text-right">
          <div class="text-white text-sm font-medium">{{ userName }}</div>
          <div class="text-[#d4af37] text-xs">{{ userRank }}</div>
        </div>

        <!-- Menú Usuario -->
        <Button
          icon="pi pi-angle-down"
          class="p-2 bg-transparent border-none text-white hover:bg-white hover:bg-opacity-10 transition-all duration-300 rounded-lg"
          @click="toggleUserMenu"
        />
      </div>
    </div>

    <!-- Menú Desplegable de Usuario -->
    <Menu ref="userMenuRef" :model="userMenuItems" :popup="true" class="mt-2" />

    <!-- Panel de Notificaciones -->
    <div
      v-if="showNotifications"
      class="absolute top-[70px] right-6 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
    >
      <div class="p-4 border-b border-gray-200">
        <h3 class="font-semibold text-[#1e3a5f]">Notificaciones Personal</h3>
      </div>
      <div class="max-h-64 overflow-y-auto">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="p-3 border-b border-gray-100 hover:bg-gray-50 transition-colors"
        >
          <div class="text-sm font-medium text-gray-900">
            {{ notification.title }}
          </div>
          <div class="text-xs text-gray-500 mt-1">
            {{ notification.message }}
          </div>
          <div class="text-xs text-[#d4af37] mt-1">
            {{ notification.time }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import { usePersonalStore } from "@/stores/personalStore";

export default {
  name: "HeaderPersonal",
  emits: ["search-change", "user-logout"],
  setup(props, { emit }) {
    const personalStore = usePersonalStore();

    // Estado reactivo
    const searchTerm = ref("");
    const showNotifications = ref(false);
    const notificationsCount = ref(3);
    const userMenuRef = ref();

    // Datos del usuario (simulados por ahora)
    const currentUser = ref({
      name: "Mayor Carlos Mendoza",
      rank: "Mayor FAH",
      initials: "CM",
      unit: "FA-1 Personal",
    });

    // Estadísticas rápidas
    const totalPersonal = computed(
      () => personalStore.estadisticas.total_efectivos || 3644
    );
    const personalActivo = computed(
      () => personalStore.estadisticas.activos || 3580
    );
    const personalMision = computed(
      () => personalStore.estadisticas.en_mision || 64
    );

    // Usuario computados
    const userName = computed(() => currentUser.value.name);
    const userRank = computed(() => currentUser.value.rank);
    const userInitials = computed(() => currentUser.value.initials);

    // Notificaciones simuladas
    const notifications = ref([
      {
        id: 1,
        title: "Personal en Misión",
        message: "64 efectivos actualmente desplegados",
        time: "Hace 5 min",
      },
      {
        id: 2,
        title: "Reporte Mensual",
        message: "Estadísticas de agosto disponibles",
        time: "Hace 1 hora",
      },
      {
        id: 3,
        title: "Actualización Sistema",
        message: "Nueva funcionalidad agregada",
        time: "Hace 2 horas",
      },
    ]);

    // Menú de usuario
    const userMenuItems = ref([
      {
        label: "Mi Perfil",
        icon: "pi pi-user",
        command: () => {
          console.log("🔧 Abrir perfil de usuario");
        },
      },
      {
        label: "Configuración",
        icon: "pi pi-cog",
        command: () => {
          console.log("⚙️ Abrir configuración");
        },
      },
      {
        separator: true,
      },
      {
        label: "Cerrar Sesión",
        icon: "pi pi-sign-out",
        command: () => {
          emit("user-logout");
        },
      },
    ]);

    // Métodos
    const handleSearch = () => {
      if (searchTerm.value.trim()) {
        emit("search-change", searchTerm.value.trim());
        console.log(`🔍 Búsqueda ejecutada: ${searchTerm.value}`);
      }
    };

    const handleSearchInput = () => {
      if (searchTerm.value.length >= 3) {
        emit("search-change", searchTerm.value);
      }
    };

    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value;
    };

    const toggleUserMenu = (event) => {
      userMenuRef.value.toggle(event);
    };

    const abrirAdminPrincipal = () => {
      console.log("🔗 Abriendo Admin Principal FAH");
      window.open("http://localhost:5173", "_blank");
    };

    // Cargar estadísticas al montar
    onMounted(async () => {
      try {
        await personalStore.cargarEstadisticas();
      } catch (error) {
        console.error("❌ Error cargando estadísticas en header:", error);
      }
    });

    // Cerrar notificaciones al hacer clic fuera
    const handleClickOutside = (event) => {
      if (!event.target.closest(".notifications-panel")) {
        showNotifications.value = false;
      }
    };

    onMounted(() => {
      document.addEventListener("click", handleClickOutside);
    });

    return {
      // Estado
      searchTerm,
      showNotifications,
      notificationsCount,
      userMenuRef,
      notifications,
      userMenuItems,

      // Computed
      totalPersonal,
      personalActivo,
      personalMision,
      userName,
      userRank,
      userInitials,

      // Métodos
      handleSearch,
      handleSearchInput,
      toggleNotifications,
      toggleUserMenu,
      abrirAdminPrincipal,
    };
  },
};
</script>

<style scoped>
/* Estilos personalizados para el header */
:deep(.p-inputtext) {
  font-size: 14px;
}

:deep(.p-inputtext:focus) {
  box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
  outline: none;
}

:deep(.p-button) {
  min-width: auto;
}

:deep(.p-avatar) {
  width: 36px;
  height: 36px;
}

/* Animación para las estadísticas */
.stats-item {
  transition: all 0.3s ease;
}

.stats-item:hover {
  transform: scale(1.05);
}

/* Efectos de brillo para elementos interactivos */
.glow-effect {
  position: relative;
  overflow: hidden;
}

.glow-effect::before {
  content: "";
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(
    45deg,
    transparent,
    rgba(212, 175, 55, 0.1),
    transparent
  );
  transform: rotate(45deg);
  transition: all 0.5s;
  opacity: 0;
}

.glow-effect:hover::before {
  opacity: 1;
  animation: shine 0.5s ease-out;
}

@keyframes shine {
  0% {
    transform: translateX(-100%) translateY(-100%) rotate(45deg);
  }
  100% {
    transform: translateX(100%) translateY(100%) rotate(45deg);
  }
}
</style>
