<!-- services\fah-admin-frontend\src\views\EstructuraOrganizacionalView.vue -->

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header compacto -->
    <div
      class="bg-gradient-to-r from-purple-800 to-purple-700 text-white shadow-lg"
    >
      <div class="max-w-7xl mx-auto px-6 py-5 responsive-header">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="bg-purple-500/20 p-3 rounded-lg header-icon">
              <i class="pi pi-building text-purple-400 text-xl"></i>
            </div>
            <div>
              <h1 class="text-xl font-semibold header-title">
                Estructura Organizacional
              </h1>
              <p class="text-sm text-purple-200 header-subtitle">
                Gestion de estructura geografica y militar del sistema FAH
              </p>
            </div>
          </div>

          <div class="flex items-center gap-4 header-status">
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
                <span class="text-xs text-purple-200 status-text"
                  >Organizacion Service</span
                >
              </div>
              <div class="flex items-center gap-2 time-display">
                <i class="pi pi-clock text-purple-400 text-sm"></i>
                <span class="text-xs font-mono text-purple-200">{{
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
    <div
      v-if="cargandoInicial"
      class="max-w-4xl mx-auto px-6 py-12 loading-container"
    >
      <Card class="border-0 shadow-lg">
        <template #content>
          <div class="text-center py-12">
            <ProgressBar mode="indeterminate" class="h-2 mb-6" />
            <div class="flex flex-col items-center gap-4">
              <i class="pi pi-spin pi-spinner text-purple-500 text-3xl"></i>
              <h3 class="text-lg font-semibold text-gray-800">
                Cargando estructura organizacional...
              </h3>
              <p class="text-gray-600 loading-text">
                Obteniendo datos del servicio de organizacion. Por favor espere
                un momento.
              </p>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Error state -->
    <div
      v-if="error && !cargandoInicial"
      class="max-w-4xl mx-auto px-6 py-12 error-container"
    >
      <Card class="border-0 shadow-lg">
        <template #content>
          <div class="text-center py-12">
            <i
              class="pi pi-exclamation-triangle text-red-500 text-5xl mb-6"
            ></i>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">
              Error al cargar estructura organizacional
            </h3>
            <p class="text-gray-600 mb-6">{{ error }}</p>
            <div class="flex justify-center gap-4 error-buttons">
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

    <!-- Componente principal con pestañas - sistema dinamico con selector -->
    <div
      v-if="!cargandoInicial && !error"
      class="max-w-7xl mx-auto px-6 pt-8 pb-6 main-container"
    >
      <!-- Pestañas de navegacion -->
      <div class="mb-8 mt-4">
        <div class="flex flex-wrap gap-2 lg:gap-4 tabs-container">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex items-center gap-3 px-6 py-4 rounded-xl font-medium transition-all duration-300 border-2 tab-button',
              {
                'bg-purple-600 text-white border-purple-600 shadow-lg transform scale-105':
                  activeTab === tab.id,
                'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:shadow-md':
                  activeTab !== tab.id,
              },
            ]"
          >
            <i :class="tab.icon" class="text-lg"></i>
            <div class="flex flex-col items-start tab-content">
              <span class="font-semibold">{{ tab.label }}</span>
              <span class="text-xs opacity-75 tab-count"
                >({{ tab.count }} modulos)</span
              >
            </div>
          </button>
        </div>
      </div>

      <!-- Contenido de las pestañas -->
      <div
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 content-panel"
      >
        <!-- Pestaña Geografia -->
        <div v-if="activeTab === 'geografia'" class="animate-fade-in">
          <!-- Selector de catalogo especifico -->
          <div
            class="mb-6 p-6 bg-gradient-to-r from-green-50 to-green-100 rounded-xl border-2 border-green-200 shadow-sm selector-container"
          >
            <div class="flex items-center gap-4 selector-content">
              <label
                class="text-sm font-bold text-green-800 whitespace-nowrap flex items-center selector-label"
              >
                <div class="bg-green-200 p-2 rounded-lg mr-3">
                  <i class="pi pi-filter text-green-700"></i>
                </div>
                <span class="label-text">Seleccionar modulo:</span>
              </label>
              <Dropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.geografia"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un modulo para cargar"
                class="w-full md:w-80 dropdown-responsive"
                :showClear="false"
                @change="seleccionarCatalogo($event.value)"
              />
              <div
                class="text-xs text-green-700 whitespace-nowrap font-medium bg-green-200 px-3 py-2 rounded-lg info-badge"
              >
                <i class="pi pi-info-circle mr-1"></i>
                <span class="badge-text">Solo se carga 1 tabla</span>
              </div>
            </div>
          </div>

          <!-- Contenido dinamico basado en seleccion -->
          <div
            v-if="catalogoActivo && esquemasGeografia.includes(catalogoActivo)"
            class="space-y-8"
          >
            <TablaDinamicaOrganizacion
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

          <!-- Mensaje cuando no hay seleccion -->
          <div v-else class="text-center py-12 empty-state">
            <i class="pi pi-arrow-up text-green-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 mb-2 empty-title">
              Selecciona un modulo para comenzar
            </h3>
            <p class="text-gray-500 empty-text">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>

        <!-- Pestaña Estructura -->
        <div v-if="activeTab === 'estructura'" class="animate-fade-in">
          <!-- Selector de catalogo especifico -->
          <div
            class="mb-6 p-6 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl border-2 border-purple-200 shadow-sm selector-container"
          >
            <div class="flex items-center gap-4 selector-content">
              <label
                class="text-sm font-bold text-purple-800 whitespace-nowrap flex items-center selector-label"
              >
                <div class="bg-purple-200 p-2 rounded-lg mr-3">
                  <i class="pi pi-filter text-purple-700"></i>
                </div>
                <span class="label-text">Seleccionar modulo:</span>
              </label>
              <Dropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.estructura"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un modulo para cargar"
                class="w-full md:w-80 dropdown-responsive"
                :showClear="false"
                @change="seleccionarCatalogo($event.value)"
              />
              <div
                class="text-xs text-purple-700 whitespace-nowrap font-medium bg-purple-200 px-3 py-2 rounded-lg info-badge"
              >
                <i class="pi pi-info-circle mr-1"></i>
                <span class="badge-text">Solo se carga 1 tabla</span>
              </div>
            </div>
          </div>

          <!-- Contenido dinamico basado en seleccion -->
          <div
            v-if="catalogoActivo && esquemasEstructura.includes(catalogoActivo)"
            class="space-y-8"
          >
            <TablaDinamicaOrganizacion
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

          <!-- Mensaje cuando no hay seleccion -->
          <div v-else class="text-center py-12 empty-state">
            <i class="pi pi-arrow-up text-purple-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 mb-2 empty-title">
              Selecciona un modulo para comenzar
            </h3>
            <p class="text-gray-500 empty-text">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer de informacion -->
    <div
      class="max-w-7xl mx-auto px-6 pt-12 pb-8 footer-container"
      v-if="!cargandoInicial && !error"
    >
      <Card class="border-0 bg-gray-50 border-gray-200">
        <template #content>
          <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 p-6 footer-content"
          >
            <div class="flex flex-col gap-3 flex-1">
              <div class="flex items-center gap-3 info-item">
                <i class="pi pi-info-circle text-purple-500"></i>
                <span class="text-gray-600 text-sm info-text">
                  Sistema dinamico: selecciona solo el modulo que necesitas para
                  un rendimiento optimo.
                </span>
              </div>
              <div class="flex items-center gap-3 info-item">
                <i class="pi pi-shield text-purple-500"></i>
                <span class="text-gray-600 text-sm info-text">
                  Carga individual de tablas: evita el scrolling innecesario en
                  tablas grandes.
                </span>
              </div>
              <div class="flex items-center gap-3 info-item">
                <i class="pi pi-history text-purple-500"></i>
                <span class="text-gray-600 text-sm info-text">
                  {{ Object.keys(ESQUEMAS_ORGANIZACION).length }} esquemas
                  configurados, multiples campos dinamicos disponibles por
                  demanda.
                </span>
              </div>
            </div>

            <div class="flex gap-3 footer-buttons">
              <Button
                label="Manual de Usuario"
                icon="pi pi-book"
                class="p-button-text p-button-sm"
                @click="openUserManual"
              />
              <Button
                label="Soporte Tecnico"
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
import { useOrganizacionStore } from "@/stores/organizacionStore";
import { useCatalogosStore } from "@/stores/catalogosStore";
import { ESQUEMAS_ORGANIZACION } from "@/config/esquemaOrganizacion";

// Componentes PrimeVue
import Card from "primevue/card";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Toast from "primevue/toast";
import ProgressBar from "primevue/progressbar";
import Dropdown from "primevue/dropdown";

// Sistema dinamico - importar solo TablaDinamicaOrganizacion
import TablaDinamicaOrganizacion from "@/components/formularios/TablaDinamicaOrganizacion.vue";

export default {
  name: "EstructuraOrganizacionalView",
  components: {
    Card,
    Button,
    Tag,
    Toast,
    ProgressBar,
    Dropdown,
    TablaDinamicaOrganizacion,
  },
  setup() {
    // Composables
    const router = useRouter();
    const organizacionStore = useOrganizacionStore();
    const catalogosStore = useCatalogosStore();

    // Estado local
    const activeTab = ref("geografia");
    const systemStatus = ref({
      service: "CHECKING",
      lastCheck: null,
    });
    const lastUpdateTime = ref("");
    const updateInterval = ref(null);

    // Variable unica catalogoActivo
    const catalogoActivo = ref(null);
    const cargandoInicial = ref(true);

    // Configuracion de esquemas
    const esquemasGeografia = ref([
      "departamentos",
      "municipios",
      "ciudades",
      "ubicaciones_geograficas",
    ]);

    const esquemasEstructura = ref([
      "estructura_militar",
      "cargos",
      "roles_funcionales",
    ]);

    // Opciones para los selects
    const opcionesSelect = computed(() => {
      return {
        geografia: [
          { value: "departamentos", label: "Departamentos" },
          { value: "municipios", label: "Municipios" },
          { value: "ciudades", label: "Ciudades" },
          {
            value: "ubicaciones_geograficas",
            label: "Ubicaciones Geograficas",
          },
        ],
        estructura: [
          { value: "estructura_militar", label: "Estructura Militar" },
          { value: "cargos", label: "Cargos" },
          { value: "roles_funcionales", label: "Roles Funcionales" },
        ],
      };
    });

    // Configuracion de tabs
    const tabs = computed(() => [
      {
        id: "geografia",
        label: "Geografia",
        icon: "pi pi-map",
        count: esquemasGeografia.value.length,
      },
      {
        id: "estructura",
        label: "Estructura Militar",
        icon: "pi pi-sitemap",
        count: esquemasEstructura.value.length,
      },
    ]);

    // Computed properties
    const totalRegistros = computed(() => organizacionStore.totalRegistros);
    const isLoading = computed(() => organizacionStore.isLoading);
    const error = computed(() => organizacionStore.error);
    const esquemaSeleccionado = computed(() => {
      return catalogoActivo.value
        ? ESQUEMAS_ORGANIZACION[catalogoActivo.value]
        : null;
    });

    // Funcion para seleccionar catalogo especifico
    const seleccionarCatalogo = async (nombreCatalogo) => {
      if (catalogoActivo.value === nombreCatalogo) return;

      catalogoActivo.value = nombreCatalogo;
      await recargarCatalogo();
    };

    // Funcion unica para recargar catalogo
    const recargarCatalogo = async () => {
      if (!catalogoActivo.value) return;

      try {
        switch (catalogoActivo.value) {
          case "departamentos":
            await catalogosStore.loadPaises();
            await organizacionStore.loadDepartamentos();
            break;

          case "municipios":
            await organizacionStore.loadMunicipios();
            await organizacionStore.loadDepartamentos();
            break;

          case "ciudades":
            await organizacionStore.loadCiudades();
            await organizacionStore.loadMunicipios();
            break;

          case "ubicaciones_geograficas":
            await organizacionStore.loadUbicacionesGeograficas();
            await catalogosStore.loadPaises();
            await organizacionStore.loadDepartamentos();
            await organizacionStore.loadMunicipios();
            await organizacionStore.loadCiudades();
            break;

          case "estructura_militar":
            await organizacionStore.loadEstructuraMilitar();
            await catalogosStore.loadTiposEstructuraMilitar();
            await organizacionStore.loadUbicacionesGeograficas();
            break;

          case "cargos":
            await organizacionStore.loadCargos();
            await organizacionStore.loadEstructuraMilitar();
            break;

          case "roles_funcionales":
            await organizacionStore.loadRolesFuncionales();
            break;

          default:
            console.warn(`Catalogo no reconocido: ${catalogoActivo.value}`);
        }
      } catch (error) {
        console.error(
          `Error recargando catalogo ${catalogoActivo.value}:`,
          error
        );
      }
    };

    // Funcion para obtener datos de un catalogo especifico
    const obtenerDatosCatalogo = (nombreCatalogo) => {
      switch (nombreCatalogo) {
        case "departamentos":
          return organizacionStore.departamentos;
        case "municipios":
          return organizacionStore.municipios;
        case "ciudades":
          return organizacionStore.ciudades;
        case "ubicaciones_geograficas":
          return organizacionStore.ubicacionesGeograficas;
        case "estructura_militar":
          return organizacionStore.estructuraMilitar;
        case "cargos":
          return organizacionStore.cargos;
        case "roles_funcionales":
          return organizacionStore.rolesFuncionales;
        default:
          return [];
      }
    };

    const obtenerContadorRegistros = (nombreCatalogo) => {
      const datos = obtenerDatosCatalogo(nombreCatalogo);
      return Array.isArray(datos) ? datos.length : 0;
    };

    // Manejadores para eventos CRUD
    const manejarRegistroCreado = (evento) => {
      recargarCatalogo();
    };

    const manejarRegistroActualizado = (evento) => {
      recargarCatalogo();
    };

    const manejarRegistroEliminado = (evento) => {
      recargarCatalogo();
    };

    const manejarError = (evento) => {
      console.error("Error en operacion CRUD:", evento);
    };

    // Funcion corregida para cargar datos iniciales
    const cargarDatosIniciales = async () => {
      cargandoInicial.value = true;

      try {
        // Cargar solo dependencias basicas (paises de catalogos)
        await catalogosStore.loadPaises();

        // Seleccionar primer catalogo por defecto
        const primerCatalogo = Object.keys(ESQUEMAS_ORGANIZACION)[0];
        if (primerCatalogo) {
          catalogoActivo.value = primerCatalogo;

          // Cargar datos especificos del catalogo seleccionado
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

    const openUserManual = () => {
      console.log("Manual de usuario solicitado");
    };

    const openSupport = () => {
      console.log("Soporte tecnico solicitado");
    };

    // Auto-actualizacion del tiempo
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
      esquemasGeografia,
      esquemasEstructura,

      // Opciones y tabs
      opcionesSelect,
      tabs,

      // Computed
      totalRegistros,
      isLoading,
      error,
      esquemaSeleccionado,

      // Datos
      ESQUEMAS_ORGANIZACION,

      // Metodos
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
@import "@/styles/components/formularios/tabla-dinamica-organizacion.css";
</style>
