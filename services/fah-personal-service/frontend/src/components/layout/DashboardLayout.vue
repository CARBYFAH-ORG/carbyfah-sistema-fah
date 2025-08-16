<template>
  <div class="flex flex-col h-screen font-sans bg-gray-100 overflow-hidden">
    <!-- Header Simple -->
    <div class="bg-blue-900 text-white p-4 flex items-center justify-between">
      <h1 class="text-xl font-bold">FAH Personal Service</h1>
      <div class="flex items-center gap-4">
        <span class="nivel-badge nivel-desarrollo">DESARROLLO</span>
        <span class="text-sm">Sistema en construcción</span>
      </div>
    </div>

    <!-- Layout Principal -->
    <div class="flex flex-1">
      <!-- Sidebar Simple -->
      <div class="w-64 bg-gray-800 text-white">
        <nav class="p-4">
          <ul class="space-y-2">
            <li>
              <router-link
                to="/dashboard"
                class="block px-4 py-2 rounded hover:bg-gray-700"
                :class="{ 'bg-gray-700': $route.name === 'dashboard-personal' }"
              >
                🏠 Dashboard
              </router-link>
            </li>

            <li>
              <router-link
                to="/fa1-fuerza"
                class="block px-4 py-2 rounded hover:bg-gray-700"
                :class="{ 'bg-gray-700': $route.path.includes('fa1-fuerza') }"
              >
                ⭐ FA-1 Fuerza
              </router-link>
            </li>

            <li>
              <router-link
                to="/s1-unidad"
                class="block px-4 py-2 rounded hover:bg-gray-700"
                :class="{ 'bg-gray-700': $route.path.includes('s1-unidad') }"
              >
                🏢 S-1 Unidad
              </router-link>
            </li>

            <li>
              <router-link
                to="/seccion"
                class="block px-4 py-2 rounded hover:bg-gray-700"
                :class="{ 'bg-gray-700': $route.path.includes('seccion') }"
              >
                ⚙️ Mi Sección
              </router-link>
            </li>

            <li>
              <router-link
                to="/organigrama"
                class="block px-4 py-2 rounded hover:bg-gray-700"
                :class="{ 'bg-gray-700': $route.name === 'organigrama' }"
              >
                📊 Organigrama
              </router-link>
            </li>

            <li>
              <router-link
                to="/carbychat"
                class="block px-4 py-2 rounded hover:bg-gray-700"
                :class="{ 'bg-gray-700': $route.name === 'carbychat' }"
              >
                💬 CARBYCHAT
              </router-link>
            </li>

            <li>
              <router-link
                to="/solicitudes"
                class="block px-4 py-2 rounded hover:bg-gray-700"
                :class="{ 'bg-gray-700': $route.name === 'solicitudes' }"
              >
                📋 Solicitudes
              </router-link>
            </li>

            <li>
              <router-link
                to="/admin"
                class="block px-4 py-2 rounded hover:bg-gray-700"
                :class="{ 'bg-gray-700': $route.name === 'admin' }"
              >
                ⚙️ Admin
              </router-link>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Contenido Principal -->
      <div class="flex-1 flex flex-col">
        <!-- Breadcrumbs -->
        <div class="bg-white px-6 py-3 border-b border-gray-200">
          <div class="text-sm text-gray-600">
            Inicio > {{ $route.meta.breadcrumb || $route.name || "Página" }}
          </div>
        </div>

        <!-- Contenido -->
        <div class="flex-1 p-6 overflow-y-auto">
          <router-view />
        </div>
      </div>
    </div>

    <!-- Toast para notificaciones -->
    <Toast />
  </div>
</template>

<script>
import { onMounted } from "vue";
import { useRoute } from "vue-router";

export default {
  name: "DashboardLayout",
  setup() {
    const route = useRoute();

    // Lifecycle
    onMounted(() => {
      console.log("✅ DashboardLayout cargado en:", route.path);
    });

    return {
      // Solo exportamos lo básico
    };
  },
};
</script>

<style scoped>
/* Nivel badges */
.nivel-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.nivel-desarrollo {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

/* Router link activo */
.router-link-active {
  background-color: rgb(55, 65, 81);
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f3f4f6;
}

::-webkit-scrollbar-thumb {
  background: rgba(147, 197, 253, 0.3);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(96, 165, 250, 0.5);
}
</style>
