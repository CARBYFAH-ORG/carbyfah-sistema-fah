<!-- services\fah-admin-frontend\src\views\DashboardView.vue -->

<template>
  <div class="flex flex-col gap-8 p-0">
    <!-- Informacion del Usuario -->
    <div
      class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden"
    >
      <div class="p-6">
        <!-- Header Usuario -->
        <div
          class="flex items-center gap-3 pb-5 border-b-2 border-gray-100 mb-5"
        >
          <div
            class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center"
          >
            <i class="pi pi-user text-xl text-blue-600"></i>
          </div>
          <h3 class="m-0 text-gray-800 text-lg font-semibold">
            Informacion del Usuario
          </h3>
        </div>

        <!-- Grid Informacion Usuario -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500 transition-all duration-300 hover:bg-blue-50 hover:translate-x-1"
          >
            <strong class="text-gray-700">ID:</strong>
            <span class="text-gray-900">{{ user?.id || "N/A" }}</span>
          </div>

          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500 transition-all duration-300 hover:bg-blue-50 hover:translate-x-1"
          >
            <strong class="text-gray-700">Usuario:</strong>
            <span class="text-gray-900">{{ user?.username || "N/A" }}</span>
          </div>

          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500 transition-all duration-300 hover:bg-blue-50 hover:translate-x-1"
          >
            <strong class="text-gray-700">Email:</strong>
            <span class="text-gray-900">{{
              user?.email_institucional || "N/A"
            }}</span>
          </div>

          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500 transition-all duration-300 hover:bg-blue-50 hover:translate-x-1"
          >
            <strong class="text-gray-700">Perfil Militar ID:</strong>
            <span class="text-gray-900">{{
              user?.perfil_militar_id || "N/A"
            }}</span>
          </div>

          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500 transition-all duration-300 hover:bg-blue-50 hover:translate-x-1"
          >
            <strong class="text-gray-700">Estado:</strong>
            <span
              :class="[
                'px-3 py-1 rounded-full text-xs font-bold uppercase',
                user?.is_active
                  ? 'bg-green-500 text-white'
                  : 'bg-red-500 text-white',
              ]"
            >
              {{ user?.is_active ? "ACTIVO" : "INACTIVO" }}
            </span>
          </div>

          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500 transition-all duration-300 hover:bg-blue-50 hover:translate-x-1"
          >
            <strong class="text-gray-700">Ultimo Acceso:</strong>
            <span class="text-gray-900">{{
              formatDate(user?.ultimo_acceso)
            }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Estadisticas del Sistema -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
      <!-- Auth Service -->
      <div
        class="bg-white rounded-xl border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
      >
        <div class="flex items-center gap-5 p-6">
          <div
            class="w-15 h-15 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center"
          >
            <i class="pi pi-shield text-2xl text-white"></i>
          </div>
          <div class="flex-1">
            <h4 class="m-0 mb-2 text-gray-800 text-lg font-semibold">
              Auth Service
            </h4>
            <p class="m-0 text-green-600 font-semibold text-sm">Operativo</p>
          </div>
        </div>
      </div>

      <!-- PostgreSQL -->
      <div
        class="bg-white rounded-xl border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
      >
        <div class="flex items-center gap-5 p-6">
          <div
            class="w-15 h-15 rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center"
          >
            <i class="pi pi-database text-2xl text-white"></i>
          </div>
          <div class="flex-1">
            <h4 class="m-0 mb-2 text-gray-800 text-lg font-semibold">
              PostgreSQL
            </h4>
            <p class="m-0 text-gray-600 font-medium text-sm">149 Tablas</p>
          </div>
        </div>
      </div>

      <!-- Microservicios -->
      <div
        class="bg-white rounded-xl border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
      >
        <div class="flex items-center gap-5 p-6">
          <div
            class="w-15 h-15 rounded-xl bg-gradient-to-br from-yellow-500 to-yellow-600 flex items-center justify-center"
          >
            <i class="pi pi-sitemap text-2xl text-white"></i>
          </div>
          <div class="flex-1">
            <h4 class="m-0 mb-2 text-gray-800 text-lg font-semibold">
              Microservicios
            </h4>
            <p class="m-0 text-gray-600 font-medium text-sm">2/20 Activos</p>
          </div>
        </div>
      </div>

      <!-- Personal FAH -->
      <div
        class="bg-white rounded-xl border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
      >
        <div class="flex items-center gap-5 p-6">
          <div
            class="w-15 h-15 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center"
          >
            <i class="pi pi-users text-2xl text-white"></i>
          </div>
          <div class="flex-1">
            <h4 class="m-0 mb-2 text-gray-800 text-lg font-semibold">
              Personal FAH
            </h4>
            <p class="m-0 text-gray-600 font-medium text-sm">3,644 Efectivos</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Proximos Desarrollos -->
    <div
      class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden"
    >
      <div class="p-6">
        <!-- Header Desarrollos -->
        <div
          class="flex items-center gap-3 pb-5 border-b-2 border-gray-100 mb-6"
        >
          <div
            class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center"
          >
            <i class="pi pi-cog text-xl text-orange-600"></i>
          </div>
          <h3 class="m-0 text-gray-800 text-lg font-semibold">
            Proximos Desarrollos
          </h3>
        </div>

        <!-- Grid Desarrollos -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
          <!-- Assets Service -->
          <div
            class="flex flex-col items-center gap-3 p-5 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 transition-all duration-300 hover:border-blue-400 hover:bg-blue-50"
          >
            <button
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-gray-500 font-semibold cursor-not-allowed flex items-center justify-center gap-2"
              disabled
            >
              <i class="pi pi-file"></i>
              Assets Service
            </button>
            <small class="text-gray-500 text-center text-xs"
              >Gestion de archivos digitales</small
            >
          </div>

          <!-- Personal Service -->
          <div
            class="flex flex-col items-center gap-3 p-5 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 transition-all duration-300 hover:border-blue-400 hover:bg-blue-50"
          >
            <button
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-gray-500 font-semibold cursor-not-allowed flex items-center justify-center gap-2"
              disabled
            >
              <i class="pi pi-users"></i>
              Personal Service
            </button>
            <small class="text-gray-500 text-center text-xs"
              >Gestion de personal militar</small
            >
          </div>

          <!-- Operations Service -->
          <div
            class="flex flex-col items-center gap-3 p-5 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 transition-all duration-300 hover:border-blue-400 hover:bg-blue-50"
          >
            <button
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-gray-500 font-semibold cursor-not-allowed flex items-center justify-center gap-2"
              disabled
            >
              <i class="pi pi-flag"></i>
              Operations Service
            </button>
            <small class="text-gray-500 text-center text-xs"
              >FA-3 Operaciones y Adiestramiento</small
            >
          </div>

          <!-- Logistics Service -->
          <div
            class="flex flex-col items-center gap-3 p-5 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 transition-all duration-300 hover:border-blue-400 hover:bg-blue-50"
          >
            <button
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-gray-500 font-semibold cursor-not-allowed flex items-center justify-center gap-2"
              disabled
            >
              <i class="pi pi-truck"></i>
              Logistics Service
            </button>
            <small class="text-gray-500 text-center text-xs"
              >FA-4 Logistica</small
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "DashboardView",
  setup() {
    const user = ref(null);

    onMounted(() => {
      // Cargar datos del usuario desde localStorage
      const userData = localStorage.getItem("fah_user");
      if (userData) {
        try {
          user.value = JSON.parse(userData);
        } catch (error) {
          user.value = null;
        }
      }
    });

    // Formatear fechas
    const formatDate = (dateString) => {
      if (!dateString) return "N/A";
      try {
        return new Date(dateString).toLocaleString("es-HN");
      } catch (error) {
        return "Fecha invalida";
      }
    };

    return {
      user,
      formatDate,
    };
  },
};
</script>
