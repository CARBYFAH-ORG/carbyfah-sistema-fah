<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header compacto -->
    <div
      class="bg-gradient-to-r from-slate-800 to-slate-700 text-white shadow-lg"
    >
      <div class="max-w-7xl mx-auto px-6 py-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="bg-blue-500/20 p-3 rounded-lg">
              <i class="pi pi-cog text-blue-400 text-xl"></i>
            </div>
            <div>
              <h1 class="text-xl font-semibold">Administración de Catálogos</h1>
              <p class="text-sm text-slate-300">
                Gestión centralizada de catálogos maestros del sistema FAH -
                Sistema Dinámico Completo
              </p>
            </div>
          </div>

          <div class="flex items-center gap-4">
            <div
              class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg flex items-center gap-3"
            >
              <div class="flex items-center gap-2">
                <Tag
                  :value="systemStatus.service"
                  :severity="
                    systemStatus.service === 'ONLINE' ? 'success' : 'danger'
                  "
                  :icon="
                    systemStatus.service === 'ONLINE'
                      ? 'pi pi-check'
                      : 'pi pi-times'
                  "
                  class="text-xs"
                />
                <span class="text-xs text-slate-300">Sistema Dinámico</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="pi pi-clock text-blue-400 text-sm"></i>
                <span class="text-xs font-mono text-slate-300">{{
                  lastUpdateTime
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Separador visual -->
    <div class="border-b border-gray-200"></div>

    <!-- Loading state -->
    <div v-if="cargandoInicial" class="max-w-4xl mx-auto px-6 py-12">
      <Card class="border-0 shadow-lg">
        <template #content>
          <div class="text-center py-12">
            <ProgressBar mode="indeterminate" class="h-2 mb-6" />
            <div class="flex flex-col items-center gap-4">
              <i class="pi pi-spin pi-spinner text-blue-500 text-3xl"></i>
              <h3 class="text-lg font-semibold text-gray-800">
                Cargando catálogos dinámicos...
              </h3>
              <p class="text-gray-600">
                Obteniendo datos del servidor. Por favor espere un momento.
              </p>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Error state -->
    <div v-if="error && !cargandoInicial" class="max-w-4xl mx-auto px-6 py-12">
      <Card class="border-0 shadow-lg">
        <template #content>
          <div class="text-center py-12">
            <i
              class="pi pi-exclamation-triangle text-red-500 text-5xl mb-6"
            ></i>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">
              Error al cargar catálogos
            </h3>
            <p class="text-gray-600 mb-6">{{ error }}</p>
            <div class="flex justify-center gap-4">
              <Button
                label="Reintentar"
                icon="pi pi-refresh"
                @click="cargarDatosIniciales"
                class="p-button-outlined"
              />
              <Button
                label="Ir al Dashboard"
                icon="pi pi-home"
                @click="goToDashboard"
                class="p-button-text"
              />
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- COMPONENTE PRINCIPAL CON PESTAÑAS - SISTEMA DINÁMICO CON SELECTOR -->
    <div
      v-if="!cargandoInicial && !error"
      class="max-w-7xl mx-auto px-6 pt-8 pb-6"
    >
      <!-- Pestañas de navegación -->
      <div class="mb-8 mt-4">
        <div class="flex flex-wrap gap-2 lg:gap-4">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex items-center gap-3 px-6 py-4 rounded-xl font-medium transition-all duration-300 border-2',
              {
                'bg-blue-600 text-white border-blue-600 shadow-lg transform scale-105':
                  activeTab === tab.id,
                'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:shadow-md':
                  activeTab !== tab.id,
              },
            ]"
          >
            <i :class="tab.icon" class="text-lg"></i>
            <div class="flex flex-col items-start">
              <span class="font-semibold">{{ tab.label }}</span>
              <span class="text-xs opacity-75"
                >({{ tab.count }} catálogos)</span
              >
            </div>
          </button>
        </div>
      </div>

      <!-- Contenido de las pestañas -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <!-- Pestaña Básicos -->
        <div v-if="activeTab === 'basicos'" class="animate-fade-in">
          <!-- Selector de catálogo específico -->
          <div
            class="mb-6 p-6 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200 shadow-sm"
          >
            <div class="flex items-center gap-4">
              <label
                class="text-sm font-bold text-blue-800 whitespace-nowrap flex items-center"
              >
                <div class="bg-blue-200 p-2 rounded-lg mr-3">
                  <i class="pi pi-filter text-blue-700"></i>
                </div>
                Seleccionar catálogo:
              </label>
              <Dropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.basicos"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un catálogo para cargar"
                class="w-full md:w-80"
                :showClear="false"
                @change="seleccionarCatalogo($event.value)"
              />
              <div
                class="text-xs text-blue-700 whitespace-nowrap font-medium bg-blue-200 px-3 py-2 rounded-lg"
              >
                <i class="pi pi-info-circle mr-1"></i>
                Solo se carga 1 tabla
              </div>
            </div>
          </div>

          <!-- Contenido dinámico basado en selección -->
          <div
            v-if="catalogoActivo && esquemasBasicos.includes(catalogoActivo)"
            class="space-y-8"
          >
            <TablaDinamica
              :esquema="catalogoActivo"
              :datos="obtenerDatosCatalogo(catalogoActivo)"
              :cargando="isLoading"
              :error="error"
              @recargar="recargarCatalogo"
              @creado="manejarRegistroCreado"
              @actualizado="manejarRegistroActualizado"
              @eliminado="manejarRegistroEliminado"
              @error="manejarError"
            />
          </div>

          <!-- Mensaje cuando no hay selección -->
          <div v-else class="text-center py-12">
            <i class="pi pi-arrow-up text-blue-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">
              Selecciona un catálogo para comenzar
            </h3>
            <p class="text-gray-500">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>

        <!-- Pestaña Estados y Prioridades -->
        <div v-if="activeTab === 'estados'" class="animate-fade-in">
          <!-- Selector de catálogo específico -->
          <div
            class="mb-6 p-6 bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200 shadow-sm"
          >
            <div class="flex items-center gap-4">
              <label
                class="text-sm font-bold text-orange-800 whitespace-nowrap flex items-center"
              >
                <div class="bg-orange-200 p-2 rounded-lg mr-3">
                  <i class="pi pi-filter text-orange-700"></i>
                </div>
                Seleccionar catálogo:
              </label>
              <Dropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.estados"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un catálogo para cargar"
                class="w-full md:w-80"
                :showClear="false"
                @change="seleccionarCatalogo($event.value)"
              />
              <div
                class="text-xs text-orange-700 whitespace-nowrap font-medium bg-orange-200 px-3 py-2 rounded-lg"
              >
                <i class="pi pi-info-circle mr-1"></i>
                Solo se carga 1 tabla
              </div>
            </div>
          </div>

          <!-- Contenido dinámico basado en selección -->
          <div
            v-if="catalogoActivo && esquemasEstados.includes(catalogoActivo)"
            class="space-y-8"
          >
            <TablaDinamica
              :esquema="catalogoActivo"
              :datos="obtenerDatosCatalogo(catalogoActivo)"
              :cargando="isLoading"
              :error="error"
              @recargar="recargarCatalogo"
              @creado="manejarRegistroCreado"
              @actualizado="manejarRegistroActualizado"
              @eliminado="manejarRegistroEliminado"
              @error="manejarError"
            />
          </div>

          <!-- Mensaje cuando no hay selección -->
          <div v-else class="text-center py-12">
            <i class="pi pi-arrow-up text-orange-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">
              Selecciona un catálogo para comenzar
            </h3>
            <p class="text-gray-500">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>

        <!-- Pestaña Estructura Militar -->
        <div v-if="activeTab === 'estructura'" class="animate-fade-in">
          <!-- Selector de catálogo específico -->
          <div
            class="mb-6 p-6 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl border-2 border-purple-200 shadow-sm"
          >
            <div class="flex items-center gap-4">
              <label
                class="text-sm font-bold text-purple-800 whitespace-nowrap flex items-center"
              >
                <div class="bg-purple-200 p-2 rounded-lg mr-3">
                  <i class="pi pi-filter text-purple-700"></i>
                </div>
                Seleccionar catálogo:
              </label>
              <Dropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.estructura"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un catálogo para cargar"
                class="w-full md:w-80"
                :showClear="false"
                @change="seleccionarCatalogo($event.value)"
              />
              <div
                class="text-xs text-purple-700 whitespace-nowrap font-medium bg-purple-200 px-3 py-2 rounded-lg"
              >
                <i class="pi pi-info-circle mr-1"></i>
                Solo se carga 1 tabla
              </div>
            </div>
          </div>

          <!-- Contenido dinámico basado en selección -->
          <div
            v-if="catalogoActivo && esquemasEstructura.includes(catalogoActivo)"
            class="space-y-8"
          >
            <TablaDinamica
              :esquema="catalogoActivo"
              :datos="obtenerDatosCatalogo(catalogoActivo)"
              :cargando="isLoading"
              :error="error"
              @recargar="recargarCatalogo"
              @creado="manejarRegistroCreado"
              @actualizado="manejarRegistroActualizado"
              @eliminado="manejarRegistroEliminado"
              @error="manejarError"
            />
          </div>

          <!-- Mensaje cuando no hay selección -->
          <div v-else class="text-center py-12">
            <i class="pi pi-arrow-up text-purple-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">
              Selecciona un catálogo para comenzar
            </h3>
            <p class="text-gray-500">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>

        <!-- Pestaña Geográficos -->
        <div v-if="activeTab === 'geograficos'" class="animate-fade-in">
          <!-- Selector de catálogo específico -->
          <div
            class="mb-6 p-6 bg-gradient-to-r from-green-50 to-green-100 rounded-xl border-2 border-green-200 shadow-sm"
          >
            <div class="flex items-center gap-4">
              <label
                class="text-sm font-bold text-green-800 whitespace-nowrap flex items-center"
              >
                <div class="bg-green-200 p-2 rounded-lg mr-3">
                  <i class="pi pi-filter text-green-700"></i>
                </div>
                Seleccionar catálogo:
              </label>
              <Dropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.geograficos"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un catálogo para cargar"
                class="w-full md:w-80"
                :showClear="false"
                @change="seleccionarCatalogo($event.value)"
              />
              <div
                class="text-xs text-green-700 whitespace-nowrap font-medium bg-green-200 px-3 py-2 rounded-lg"
              >
                <i class="pi pi-info-circle mr-1"></i>
                Solo se carga 1 tabla
              </div>
            </div>
          </div>

          <!-- Contenido dinámico basado en selección -->
          <div
            v-if="
              catalogoActivo && esquemasGeograficos.includes(catalogoActivo)
            "
            class="space-y-8"
          >
            <TablaDinamica
              :esquema="catalogoActivo"
              :datos="obtenerDatosCatalogo(catalogoActivo)"
              :cargando="isLoading"
              :error="error"
              @recargar="recargarCatalogo"
              @creado="manejarRegistroCreado"
              @actualizado="manejarRegistroActualizado"
              @eliminado="manejarRegistroEliminado"
              @error="manejarError"
            />
          </div>

          <!-- Mensaje cuando no hay selección -->
          <div v-else class="text-center py-12">
            <i class="pi pi-arrow-up text-green-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">
              Selecciona un catálogo para comenzar
            </h3>
            <p class="text-gray-500">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer de información -->
    <div
      class="max-w-7xl mx-auto px-6 pt-12 pb-8"
      v-if="!cargandoInicial && !error"
    >
      <Card class="border-0 bg-gray-50 border-gray-200">
        <template #content>
          <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 p-6"
          >
            <div class="flex flex-col gap-3 flex-1">
              <div class="flex items-center gap-3">
                <i class="pi pi-info-circle text-blue-500"></i>
                <span class="text-gray-600 text-sm">
                  Sistema dinámico: selecciona solo el catálogo que necesitas
                  para un rendimiento óptimo.
                </span>
              </div>
              <div class="flex items-center gap-3">
                <i class="pi pi-shield text-blue-500"></i>
                <span class="text-gray-600 text-sm">
                  Carga individual de tablas: evita el scrolling innecesario en
                  tablas grandes.
                </span>
              </div>
              <div class="flex items-center gap-3">
                <i class="pi pi-history text-blue-500"></i>
                <span class="text-gray-600 text-sm">
                  {{ Object.keys(ESQUEMAS_CATALOGOS).length }} esquemas
                  configurados, múltiples campos dinámicos disponibles por
                  demanda.
                </span>
              </div>
            </div>

            <div class="flex gap-3">
              <Button
                label="Manual de Usuario"
                icon="pi pi-book"
                class="p-button-text p-button-sm"
                @click="openUserManual"
              />
              <Button
                label="Soporte Técnico"
                icon="pi pi-question-circle"
                class="p-button-text p-button-sm"
                @click="openSupport"
              />
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Toast bonito para notificaciones CARBYFAH -->
    <Toast />
  </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useCatalogosStore } from "@/stores/catalogosStore";
import { ESQUEMAS_CATALOGOS } from "@/config/esquemaCatalogos";

// Componentes PrimeVue
import Card from "primevue/card";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Toast from "primevue/toast";
import ProgressBar from "primevue/progressbar";
import Dropdown from "primevue/dropdown";

// Sistema dinámico - importar solo TablaDinamica
import TablaDinamica from "@/components/formularios/TablaDinamica.vue";

export default {
  name: "CatalogosView",
  components: {
    Card,
    Button,
    Tag,
    Toast,
    ProgressBar,
    Dropdown,
    TablaDinamica,
  },
  setup() {
    // Composables
    const router = useRouter();
    const catalogosStore = useCatalogosStore();

    // Estado local
    const activeTab = ref("basicos");
    const systemStatus = ref({
      service: "CHECKING",
      lastCheck: null,
    });
    const lastUpdateTime = ref("");
    const updateInterval = ref(null);

    // Variable única catalogoActivo
    const catalogoActivo = ref(null);
    const cargandoInicial = ref(true);

    // Configuración de esquemas
    const esquemasBasicos = ref([
      "tipos_genero",
      "categorias_personal",
      "especialidades",
    ]);

    const esquemasEstados = ref([
      "tipos_estado_general",
      "niveles_prioridad",
      "niveles_seguridad",
    ]);

    const esquemasEstructura = ref([
      "tipos_estructura_militar",
      "tipos_jerarquia",
      "tipos_evento",
      "grados",
    ]);

    const esquemasGeograficos = ref(["paises"]);

    // Opciones para los selects
    const opcionesSelect = computed(() => {
      return {
        basicos: [
          { value: "tipos_genero", label: "Tipos de Género" },
          { value: "categorias_personal", label: "Categorías Personal" },
          { value: "especialidades", label: "Especialidades" },
        ],
        estados: [
          { value: "tipos_estado_general", label: "Tipos Estado General" },
          { value: "niveles_prioridad", label: "Niveles Prioridad" },
          { value: "niveles_seguridad", label: "Niveles Seguridad" },
        ],
        estructura: [
          {
            value: "tipos_estructura_militar",
            label: "Tipos Estructura Militar",
          },
          { value: "tipos_jerarquia", label: "Tipos Jerarquía" },
          { value: "tipos_evento", label: "Tipos Evento" },
          { value: "grados", label: "Grados Militares" },
        ],
        geograficos: [{ value: "paises", label: "Países" }],
      };
    });

    // Configuración de tabs
    const tabs = computed(() => [
      {
        id: "basicos",
        label: "Básicos",
        icon: "pi pi-list",
        count: esquemasBasicos.value.length,
      },
      {
        id: "estados",
        label: "Estados y Prioridades",
        icon: "pi pi-chart-bar",
        count: esquemasEstados.value.length,
      },
      {
        id: "estructura",
        label: "Estructura Militar",
        icon: "pi pi-sitemap",
        count: esquemasEstructura.value.length,
      },
      {
        id: "geograficos",
        label: "Geográficos",
        icon: "pi pi-map",
        count: esquemasGeograficos.value.length,
      },
    ]);

    // Computed properties
    const totalRegistros = computed(() => catalogosStore.totalRegistros);
    const isLoading = computed(() => catalogosStore.isLoading);
    const error = computed(() => catalogosStore.error);
    const esquemaSeleccionado = computed(() => {
      return catalogoActivo.value
        ? ESQUEMAS_CATALOGOS[catalogoActivo.value]
        : null;
    });

    // Función para seleccionar catálogo específico
    const seleccionarCatalogo = async (nombreCatalogo) => {
      if (catalogoActivo.value === nombreCatalogo) return;

      catalogoActivo.value = nombreCatalogo;
      await recargarCatalogo();
    };

    // Función para recargar catálogo actual
    const recargarCatalogo = async () => {
      if (!catalogoActivo.value) return;

      try {
        switch (catalogoActivo.value) {
          case "tipos_genero":
            await catalogosStore.loadTiposGenero();
            break;
          case "categorias_personal":
            await catalogosStore.loadCategoriasPersonal();
            break;
          case "especialidades":
            await catalogosStore.loadEspecialidades();
            break;
          case "grados":
            await catalogosStore.loadGrados();
            await catalogosStore.loadCategoriasPersonal();
            console.log(
              "✅ Categorías cargadas para tabla:",
              catalogosStore.categoriasPersonal?.length
            );
            await new Promise((resolve) => setTimeout(resolve, 100));
            break;
          case "niveles_prioridad":
            await catalogosStore.loadNivelesPrioridad();
            break;
          default:
            if (!catalogosStore.catalogosBasicos) {
              await catalogosStore.loadCatalogosBasicos();
            }
        }
      } catch (error) {
        console.error(
          `Error recargando catálogo ${catalogoActivo.value}:`,
          error
        );
      }
    };

    // Función para obtener datos de un catálogo específico
    const obtenerDatosCatalogo = (nombreCatalogo) => {
      switch (nombreCatalogo) {
        case "tipos_genero":
          return catalogosStore.tiposGenero;
        case "categorias_personal":
          return catalogosStore.categoriasPersonal;
        case "especialidades":
          return catalogosStore.especialidades;
        case "grados":
          return catalogosStore.grados;
        case "niveles_prioridad":
          return catalogosStore.nivelesPrioridad;
        case "niveles_seguridad":
          return catalogosStore.nivelesSeguridad;
        case "tipos_estado_general":
          return catalogosStore.tiposEstadoGeneral;
        case "tipos_estructura_militar":
          return catalogosStore.tiposEstructuraMilitar;
        case "tipos_evento":
          return catalogosStore.tiposEvento;
        case "tipos_jerarquia":
          return catalogosStore.tiposJerarquia;
        case "paises":
          return catalogosStore.paises;
        default:
          return [];
      }
    };

    const obtenerContadorRegistros = (nombreCatalogo) => {
      const datos = obtenerDatosCatalogo(nombreCatalogo);
      return Array.isArray(datos) ? datos.length : 0;
    };

    // Manejador para cuando se crea un registro exitosamente
    const manejarRegistroCreado = (evento) => {
      recargarCatalogo();
    };

    // Manejador para cuando se actualiza un registro exitosamente
    const manejarRegistroActualizado = (evento) => {
      recargarCatalogo();
    };

    // Manejador para cuando se elimina un registro exitosamente
    const manejarRegistroEliminado = (evento) => {
      recargarCatalogo();
    };

    // Manejador para errores en operaciones CRUD
    const manejarError = (evento) => {
      console.error("Error en operación CRUD:", evento);
    };

    // Función para cargar datos iniciales del sistema
    const cargarDatosIniciales = async () => {
      cargandoInicial.value = true;

      try {
        // Cargar datos básicos
        await catalogosStore.loadCatalogosBasicos();

        // Seleccionar primer catálogo por defecto
        const primerCatalogo = Object.keys(ESQUEMAS_CATALOGOS)[0];
        if (primerCatalogo) {
          catalogoActivo.value = primerCatalogo;
          await recargarCatalogo();
        }
      } catch (error) {
        console.error("Error cargando datos iniciales:", error);
      } finally {
        cargandoInicial.value = false;
      }
    };

    const checkServiceHealth = async () => {
      try {
        systemStatus.value.service = "CHECKING";
        await new Promise((resolve) => setTimeout(resolve, 500));
        systemStatus.value.service = "ONLINE";
        systemStatus.value.lastCheck = new Date();
        updateLastUpdateTime();
      } catch (err) {
        systemStatus.value.service = "OFFLINE";
      }
    };

    const updateLastUpdateTime = () => {
      const now = new Date();
      lastUpdateTime.value = now.toLocaleTimeString("es-HN", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      });
    };

    const goToDashboard = () => {
      router.push("/dashboard");
    };

    // Función para mostrar información del manual de usuario
    const openUserManual = () => {
      console.log("Manual de usuario solicitado");
    };

    // Función para mostrar información de soporte técnico
    const openSupport = () => {
      console.log("Soporte técnico solicitado");
    };

    // Auto-actualización del tiempo
    const startAutoUpdate = () => {
      updateInterval.value = setInterval(() => {
        if (systemStatus.value.service === "ONLINE") {
          updateLastUpdateTime();
        }
      }, 30000);
    };

    const stopAutoUpdate = () => {
      if (updateInterval.value) {
        clearInterval(updateInterval.value);
        updateInterval.value = null;
      }
    };

    // Lifecycle
    onMounted(() => {
      cargarDatosIniciales();
      checkServiceHealth();
      startAutoUpdate();
    });

    return {
      // Estado
      catalogoActivo,
      cargandoInicial,
      activeTab,
      systemStatus,
      lastUpdateTime,

      // Esquemas
      esquemasBasicos,
      esquemasEstados,
      esquemasEstructura,
      esquemasGeograficos,

      // Opciones y tabs
      opcionesSelect,
      tabs,

      // Computed
      totalRegistros,
      isLoading,
      error,
      esquemaSeleccionado,

      // Datos
      ESQUEMAS_CATALOGOS,

      // Métodos
      seleccionarCatalogo,
      recargarCatalogo,
      obtenerDatosCatalogo,
      obtenerContadorRegistros,
      manejarRegistroCreado,
      manejarRegistroActualizado,
      manejarRegistroEliminado,
      manejarError,
      cargarDatosIniciales,
      goToDashboard,
      openUserManual,
      openSupport,
    };
  },
};
</script>

<style>
/* Importar estilos externos organizados */
@import "@/styles/views/catalogos-view.css";
</style>
