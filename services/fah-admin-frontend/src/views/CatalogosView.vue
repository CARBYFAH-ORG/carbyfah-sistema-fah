<!-- services\fah-admin-frontend\src\views\CatalogosView.vue -->

<template>
  <div class="catalogos-view-container">
    <!-- Header compacto FAH -->
    <div class="header-fah">
      <div class="header-content">
        <div class="header-info">
          <div class="header-icon">
            <i class="pi pi-cog"></i>
          </div>
          <div class="header-text">
            <h1>Administracion de Catalogos</h1>
            <p>
              Gestion centralizada de catalogos maestros del sistema FAH -
              Sistema Dinamico Completo
            </p>
          </div>
        </div>

        <div class="header-status">
          <div class="status-container">
            <div class="status-item">
              <div
                class="custom-status-tag"
                :class="{
                  'status-online': systemStatus.service === 'ONLINE',
                  'status-offline': systemStatus.service === 'OFFLINE',
                  'status-checking': systemStatus.service === 'CHECKING',
                }"
              >
                <i
                  :class="{
                    'pi pi-check': systemStatus.service === 'ONLINE',
                    'pi pi-times': systemStatus.service === 'OFFLINE',
                    'pi pi-spin pi-spinner':
                      systemStatus.service === 'CHECKING',
                  }"
                ></i>
                <span>{{ getStatusText(systemStatus.service) }}</span>
              </div>
              <span class="status-label">Sistema Dinamico</span>
            </div>
            <div class="status-item">
              <i class="pi pi-clock status-clock"></i>
              <span class="status-time">{{ lastUpdateTime }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Separador visual -->
    <div class="separator"></div>

    <!-- Loading state -->
    <div v-if="cargandoInicial" class="loading-container">
      <Card class="loading-card">
        <template #content>
          <div class="loading-content">
            <ProgressBar mode="indeterminate" class="loading-progress" />
            <div class="loading-info">
              <i class="pi pi-spin pi-spinner loading-spinner"></i>
              <h3 class="loading-title">Cargando catalogos dinamicos...</h3>
              <p class="loading-description">
                Obteniendo datos del servidor. Por favor espere un momento.
              </p>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Error state -->
    <div v-if="error && !cargandoInicial" class="error-container">
      <Card class="error-card">
        <template #content>
          <div class="error-content">
            <i class="pi pi-exclamation-triangle error-icon"></i>
            <h3 class="error-title">Error al cargar catalogos</h3>
            <p class="error-description">{{ error }}</p>
            <div class="error-actions">
              <Button
                label="Reintentar"
                icon="pi pi-refresh"
                @click="cargarDatosIniciales"
                class="btn-reintentar"
              />
              <Button
                label="Ir al Dashboard"
                icon="pi pi-home"
                @click="goToDashboard"
                class="btn-dashboard"
              />
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Componente principal con pestañas - sistema dinamico con selector -->
    <div v-if="!cargandoInicial && !error" class="main-container">
      <!-- Pestañas de navegacion FAH -->
      <div class="tabs-container">
        <div class="tabs-wrapper">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'tab-button',
              {
                'tab-active': activeTab === tab.id,
                'tab-inactive': activeTab !== tab.id,
              },
            ]"
          >
            <i :class="tab.icon" class="tab-icon"></i>
            <div class="tab-content">
              <span class="tab-label">{{ tab.label }}</span>
              <span class="tab-count">({{ tab.count }} catalogos)</span>
            </div>
          </button>
        </div>
      </div>

      <!-- Contenido de las pestañas -->
      <div class="content-container">
        <!-- Pestaña Basicos -->
        <div v-if="activeTab === 'basicos'" class="tab-content-wrapper">
          <!-- Selector de catalogo especifico -->
          <div class="selector-container selector-basicos">
            <div class="selector-content">
              <label class="selector-label">
                <div class="selector-icon selector-icon-basicos">
                  <i class="pi pi-filter"></i>
                </div>
                Seleccionar catalogo:
              </label>
              <CustomDropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.basicos"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un catalogo para cargar"
                class="selector-dropdown"
                @change="seleccionarCatalogo($event.value)"
              />
              <div class="selector-info selector-info-basicos">
                <i class="pi pi-info-circle"></i>
                Solo se carga 1 tabla
              </div>
            </div>
          </div>

          <!-- Contenido dinamico basado en seleccion -->
          <div
            v-if="catalogoActivo && esquemasBasicos.includes(catalogoActivo)"
            class="dynamic-content"
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

          <!-- Mensaje cuando no hay seleccion -->
          <div v-else class="empty-state">
            <i class="pi pi-arrow-up empty-icon"></i>
            <h3 class="empty-title">Selecciona un catalogo para comenzar</h3>
            <p class="empty-description">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>

        <!-- Pestaña Estados y Prioridades -->
        <div v-if="activeTab === 'estados'" class="tab-content-wrapper">
          <!-- Selector de catalogo especifico -->
          <div class="selector-container selector-estados">
            <div class="selector-content">
              <label class="selector-label">
                <div class="selector-icon selector-icon-estados">
                  <i class="pi pi-filter"></i>
                </div>
                Seleccionar catalogo:
              </label>
              <CustomDropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.estados"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un catalogo para cargar"
                class="selector-dropdown"
                @change="seleccionarCatalogo($event.value)"
              />
              <div class="selector-info selector-info-estados">
                <i class="pi pi-info-circle"></i>
                Solo se carga 1 tabla
              </div>
            </div>
          </div>

          <!-- Contenido dinamico basado en seleccion -->
          <div
            v-if="catalogoActivo && esquemasEstados.includes(catalogoActivo)"
            class="dynamic-content"
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

          <!-- Mensaje cuando no hay seleccion -->
          <div v-else class="empty-state">
            <i class="pi pi-arrow-up empty-icon"></i>
            <h3 class="empty-title">Selecciona un catalogo para comenzar</h3>
            <p class="empty-description">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>

        <!-- Pestaña Estructura Militar -->
        <div v-if="activeTab === 'estructura'" class="tab-content-wrapper">
          <!-- Selector de catalogo especifico -->
          <div class="selector-container selector-estructura">
            <div class="selector-content">
              <label class="selector-label">
                <div class="selector-icon selector-icon-estructura">
                  <i class="pi pi-filter"></i>
                </div>
                Seleccionar catalogo:
              </label>
              <CustomDropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.estructura"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un catalogo para cargar"
                class="selector-dropdown"
                @change="seleccionarCatalogo($event.value)"
              />
              <div class="selector-info selector-info-estructura">
                <i class="pi pi-info-circle"></i>
                Solo se carga 1 tabla
              </div>
            </div>
          </div>

          <!-- Contenido dinamico basado en seleccion -->
          <div
            v-if="catalogoActivo && esquemasEstructura.includes(catalogoActivo)"
            class="dynamic-content"
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

          <!-- Mensaje cuando no hay seleccion -->
          <div v-else class="empty-state">
            <i class="pi pi-arrow-up empty-icon"></i>
            <h3 class="empty-title">Selecciona un catalogo para comenzar</h3>
            <p class="empty-description">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>

        <!-- Pestaña Geograficos -->
        <div v-if="activeTab === 'geograficos'" class="tab-content-wrapper">
          <!-- Selector de catalogo especifico -->
          <div class="selector-container selector-geograficos">
            <div class="selector-content">
              <label class="selector-label">
                <div class="selector-icon selector-icon-geograficos">
                  <i class="pi pi-filter"></i>
                </div>
                Seleccionar catalogo:
              </label>
              <CustomDropdown
                v-model="catalogoActivo"
                :options="opcionesSelect.geograficos"
                optionLabel="label"
                optionValue="value"
                placeholder="Elige un catalogo para cargar"
                class="selector-dropdown"
                @change="seleccionarCatalogo($event.value)"
              />
              <div class="selector-info selector-info-geograficos">
                <i class="pi pi-info-circle"></i>
                Solo se carga 1 tabla
              </div>
            </div>
          </div>

          <!-- Contenido dinamico basado en seleccion -->
          <div
            v-if="
              catalogoActivo && esquemasGeograficos.includes(catalogoActivo)
            "
            class="dynamic-content"
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

          <!-- Mensaje cuando no hay seleccion -->
          <div v-else class="empty-state">
            <i class="pi pi-arrow-up empty-icon"></i>
            <h3 class="empty-title">Selecciona un catalogo para comenzar</h3>
            <p class="empty-description">
              Usa el selector de arriba para cargar solo la tabla que necesitas
              trabajar.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer de informacion -->
    <div class="footer-container" v-if="!cargandoInicial && !error">
      <Card class="footer-card">
        <template #content>
          <div class="footer-content">
            <div class="footer-info">
              <div class="footer-item">
                <i class="pi pi-info-circle footer-icon"></i>
                <span class="footer-text">
                  Sistema dinamico: selecciona solo el catalogo que necesitas
                  para un rendimiento optimo.
                </span>
              </div>
              <div class="footer-item">
                <i class="pi pi-shield footer-icon"></i>
                <span class="footer-text">
                  Carga individual de tablas: evita el scrolling innecesario en
                  tablas grandes.
                </span>
              </div>
              <div class="footer-item">
                <i class="pi pi-history footer-icon"></i>
                <span class="footer-text">
                  {{ Object.keys(ESQUEMAS_CATALOGOS).length }} esquemas
                  configurados, multiples campos dinamicos disponibles por
                  demanda.
                </span>
              </div>
            </div>

            <div class="footer-actions">
              <button
                @click="openUserManual"
                class="btn-fah btn-manual"
                type="button"
              >
                <i class="pi pi-book"></i>
                <span>Manual de Usuario</span>
              </button>

              <button
                @click="openSupport"
                class="btn-fah btn-soporte"
                type="button"
              >
                <i class="pi pi-question-circle"></i>
                <span>Soporte Tecnico</span>
              </button>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useCatalogosStore } from "@/stores/catalogosStore";
import { ESQUEMAS_CATALOGOS } from "@/config/esquemaCatalogos";
import { useToastFAH } from "@/composables/useToastFAH";

// Componentes PrimeVue
import Card from "primevue/card";
import Button from "primevue/button";
import Tag from "primevue/tag";
import ProgressBar from "primevue/progressbar";
import Dropdown from "primevue/dropdown";

// Sistema dinamico - importar solo TablaDinamica
import TablaDinamica from "@/components/formularios/TablaDinamica.vue";

// Componente CustomDropdown
import CustomDropdown from "@/components/ui/CustomDropdown.vue";

export default {
  name: "CatalogosView",
  components: {
    Card,
    Button,
    Tag,
    ProgressBar,
    Dropdown,
    TablaDinamica,
    CustomDropdown,
  },
  setup() {
    // Composables
    const router = useRouter();
    const catalogosStore = useCatalogosStore();
    const toast = useToastFAH();

    // Estado local
    const activeTab = ref("basicos");
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
          { value: "tipos_genero", label: "Tipos de Genero" },
          { value: "categorias_personal", label: "Categorias Personal" },
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
          { value: "tipos_jerarquia", label: "Tipos Jerarquia" },
          { value: "tipos_evento", label: "Tipos Evento" },
          { value: "grados", label: "Grados Militares" },
        ],
        geograficos: [{ value: "paises", label: "Paises" }],
      };
    });

    // Configuracion de tabs
    const tabs = computed(() => [
      {
        id: "basicos",
        label: "Basicos",
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
        label: "Geograficos",
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

    // Funcion para seleccionar catalogo especifico
    const seleccionarCatalogo = async (nombreCatalogo) => {
      if (catalogoActivo.value === nombreCatalogo) return;

      catalogoActivo.value = nombreCatalogo;
      await recargarCatalogo();

      toast.info(
        "Catalogo FAH",
        `Cargando ${nombreCatalogo.replace(/_/g, " ")}...`
      );
    };

    // Funcion para recargar catalogo actual
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

        toast.success(
          "Catalogo FAH",
          `${catalogoActivo.value.replace(/_/g, " ")} cargado exitosamente`
        );
      } catch (error) {
        toast.error(
          "Error FAH",
          `No se pudo cargar ${catalogoActivo.value.replace(/_/g, " ")}`
        );
      }
    };

    // Funcion para obtener datos de un catalogo especifico
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
      toast.success("Operacion FAH", "Registro creado exitosamente");
    };

    // Manejador para cuando se actualiza un registro exitosamente
    const manejarRegistroActualizado = (evento) => {
      recargarCatalogo();
      toast.success("Operacion FAH", "Registro actualizado exitosamente");
    };

    // Manejador para cuando se elimina un registro exitosamente
    const manejarRegistroEliminado = (evento) => {
      recargarCatalogo();
      toast.success("Operacion FAH", "Registro eliminado exitosamente");
    };

    // Manejador para errores en operaciones CRUD
    const manejarError = (evento) => {
      toast.error("Error FAH", "Error en operacion de base de datos");
    };

    // Funcion para cargar datos iniciales del sistema
    const cargarDatosIniciales = async () => {
      cargandoInicial.value = true;

      try {
        // Cargar datos basicos
        await catalogosStore.loadCatalogosBasicos();

        // Seleccionar primer catalogo por defecto
        const primerCatalogo = Object.keys(ESQUEMAS_CATALOGOS)[0];
        if (primerCatalogo) {
          catalogoActivo.value = primerCatalogo;
          await recargarCatalogo();
        }

        toast.success("Sistema FAH", "Catalogos cargados exitosamente");
      } catch (error) {
        toast.error(
          "Error FAH",
          "No se pudieron cargar los catalogos iniciales"
        );
      } finally {
        cargandoInicial.value = false;
      }
    };

    const checkServiceHealth = async () => {
      try {
        systemStatus.value.service = "CHECKING";

        // Verificacion real con tu endpoint de health-check
        const response = await fetch("/api/catalogos/health", {
          method: "GET",
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
          },
          // Timeout de 5 segundos
          signal: AbortSignal.timeout(5000),
        });

        if (response.ok) {
          const data = await response.json();
          // Verificar que la respuesta sea exitosa segun tu API
          if (data.success === true) {
            systemStatus.value.service = "ONLINE";
          } else {
            systemStatus.value.service = "OFFLINE";
          }
        } else {
          systemStatus.value.service = "OFFLINE";
        }

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

    const openUserManual = async () => {
      try {
        const response = await fetch(
          "http://localhost:8012/api/manual/tabla-dinamica"
        );
        const data = await response.json();

        if (data.success) {
          window.open(data.download_url, "_blank");
          toast.success("Manual FAH", "Abriendo manual de usuario");
        } else {
          toast.error("Manual FAH", "Manual no disponible");
        }
      } catch (error) {
        toast.error("Manual FAH", "Error al cargar manual");
      }
    };

    // Funcion para mostrar informacion de soporte tecnico
    const openSupport = () => {
      toast.pending("Soporte FAH", "Soporte tecnico proximamente disponible");
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

      // Funcion para mostrar texto del estado
      getStatusText: (status) => {
        switch (status) {
          case "ONLINE":
            return "EN LINEA";
          case "OFFLINE":
            return "FUERA DE SERVICIO";
          case "CHECKING":
            return "VERIFICANDO...";
          default:
            return "DESCONOCIDO";
        }
      },
    };
  },
};
</script>

<style scoped>
/* Estilos autocontenidos catalogos view FAH */

/* Contenedor principal */
.catalogos-view-container {
  min-height: 100vh;
  background: #1f6a8f;
}

/* Header FAH profesional */
.header-fah {
  background: linear-gradient(135deg, #1e3a5f 0%, #495057 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15);
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  background: rgba(212, 175, 55, 0.2);
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #d4af37;
}

.header-icon i {
  color: #d4af37;
  font-size: 20px;
}

.header-text h1 {
  font-size: 24px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #ffffff;
}

.header-text p {
  font-size: 14px;
  color: #e9ecef;
  margin: 0;
  font-weight: 400;
}

.header-status {
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  padding: 12px 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.status-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-tag {
  font-size: 12px;
}

.status-label {
  font-size: 12px;
  color: #e9ecef;
  font-weight: 500;
}

.status-clock {
  color: #d4af37;
  font-size: 14px;
}

.status-time {
  font-size: 12px;
  font-family: "Courier New", monospace;
  color: #e9ecef;
  font-weight: 500;
}

/* Separador */
.separator {
  height: 1px;
  background: #e9ecef;
}

/* Loading y error states */
.loading-container,
.error-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 48px 24px;
}

.loading-card,
.error-card {
  border: none;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}

.loading-content,
.error-content {
  text-align: center;
  padding: 48px;
}

.loading-progress {
  height: 8px;
  margin-bottom: 24px;
  border-radius: 4px;
}

.loading-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.loading-spinner {
  color: #1e3a5f;
  font-size: 48px;
}

.loading-title,
.error-title {
  font-size: 24px;
  font-weight: 600;
  color: #1e3a5f;
  margin: 0;
}

.loading-description,
.error-description {
  font-size: 16px;
  color: #495057;
  margin: 0;
}

.error-icon {
  color: #c1666b;
  font-size: 80px;
  margin-bottom: 24px;
}

.error-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-top: 24px;
}

.btn-reintentar {
  background: #1e3a5f;
  border: none;
  color: #ffffff;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: 600;
}

.btn-dashboard {
  background: transparent;
  border: 1px solid #495057;
  color: #495057;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: 600;
}

/* Contenedor principal */
.main-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 32px 24px 24px;
}

/* Pestañas FAH */
.tabs-container {
  margin-bottom: 32px;
  margin-top: 16px;
}

.tabs-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 24px;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  cursor: pointer;
  background: transparent;
  min-height: 64px;
}

.tab-active {
  background: #1e3a5f;
  color: #ffffff;
  border-color: #1e3a5f;
  box-shadow: 0 8px 25px rgba(30, 58, 95, 0.2);
  transform: scale(1.05);
}

.tab-inactive {
  background: #ffffff;
  color: #495057;
  border-color: #e9ecef;
}

.tab-inactive:hover {
  background: #f8f9fa;
  border-color: #495057;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.tab-icon {
  font-size: 18px;
  color: #d4af37;
}

.tab-active .tab-icon {
  color: #d4af37;
}

.tab-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.tab-label {
  font-weight: 600;
  font-size: 16px;
}

.tab-count {
  font-size: 12px;
  opacity: 0.75;
  margin-top: 2px;
}

/* Contenido de pestañas */
.content-container {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  padding: 24px;
}

.tab-content-wrapper {
  animation: fadeIn 0.3s ease-out;
}

/* Selectores de catalogo */
.selector-container {
  margin-bottom: 24px;
  padding: 24px;
  border-radius: 12px;
  border: 2px solid transparent;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.selector-basicos {
  background: linear-gradient(
    135deg,
    rgba(30, 58, 95, 0.05),
    rgba(30, 58, 95, 0.02)
  );
  border-color: rgba(30, 58, 95, 0.2);
}

.selector-estados {
  background: linear-gradient(
    135deg,
    rgba(212, 175, 55, 0.05),
    rgba(212, 175, 55, 0.02)
  );
  border-color: rgba(212, 175, 55, 0.2);
}

.selector-estructura {
  background: linear-gradient(
    135deg,
    rgba(193, 102, 107, 0.05),
    rgba(193, 102, 107, 0.02)
  );
  border-color: rgba(193, 102, 107, 0.2);
}

.selector-geograficos {
  background: linear-gradient(
    135deg,
    rgba(40, 167, 69, 0.05),
    rgba(40, 167, 69, 0.02)
  );
  border-color: rgba(40, 167, 69, 0.2);
}

.selector-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.selector-label {
  display: flex;
  align-items: center;
  font-size: 14px;
  font-weight: 700;
  white-space: nowrap;
  color: #1e3a5f;
}

.selector-icon {
  padding: 8px;
  border-radius: 8px;
  margin-right: 12px;
}

.selector-icon-basicos {
  background: rgba(30, 58, 95, 0.2);
  color: #1e3a5f;
}

.selector-icon-estados {
  background: rgba(212, 175, 55, 0.2);
  color: #d4af37;
}

.selector-icon-estructura {
  background: rgba(193, 102, 107, 0.2);
  color: #c1666b;
}

.selector-icon-geograficos {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.selector-dropdown {
  flex: 1;
  max-width: 320px;
}

.selector-basicos .custom-dropdown .dropdown-panel {
  background: linear-gradient(
    135deg,
    rgba(30, 58, 95, 0.95),
    rgba(30, 58, 95, 0.98)
  ) !important;
  border-color: rgba(30, 58, 95, 0.4) !important;
}

.selector-basicos .custom-dropdown .dropdown-option:hover {
  background: rgba(30, 58, 95, 0.3) !important;
  color: #ffffff !important;
}

.selector-basicos .custom-dropdown .option-selected {
  background: rgba(30, 58, 95, 0.4) !important;
  color: #ffffff !important;
}

.selector-info {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  padding: 8px 12px;
  border-radius: 8px;
}

.selector-info-basicos {
  background: rgba(30, 58, 95, 0.2);
  color: #1e3a5f;
}

.selector-info-estados {
  background: rgba(212, 175, 55, 0.2);
  color: #d4af37;
}

.selector-info-estructura {
  background: rgba(193, 102, 107, 0.2);
  color: #c1666b;
}

.selector-info-geograficos {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

/* Contenido dinamico */
.dynamic-content {
  margin-top: 32px;
}

/* Estado vacio */
.empty-state {
  text-align: center;
  padding: 96px 20px;
}

.empty-icon {
  color: #d4af37;
  font-size: 64px;
  margin-bottom: 16px;
  opacity: 0.6;
}

.empty-title {
  font-size: 20px;
  font-weight: 600;
  color: #1e3a5f;
  margin: 0 0 8px 0;
}

.empty-description {
  font-size: 16px;
  color: #495057;
  margin: 0;
}

/* Footer informativo */
.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 48px 24px 32px;
}

.footer-card {
  border: none;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid #e9ecef;
  backdrop-filter: blur(10px);
}

.footer-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 24px;
}

.footer-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.footer-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.footer-icon {
  color: #1e3a5f;
  font-size: 16px;
  min-width: 16px;
}

.footer-text {
  color: #495057;
  font-size: 14px;
  font-weight: 500;
}

.footer-actions {
  display: flex;
  gap: 12px;
}

/* Botones FAH personalizados */
.btn-fah {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: 2px solid;
  border-radius: 8px;
  background: transparent;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: inherit;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-fah:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.btn-fah:active {
  transform: translateY(0);
}

/* Boton Manual - Azul FAH */
.btn-manual {
  border-color: #1e3a5f;
  color: #1e3a5f;
}

.btn-manual:hover {
  background: #1e3a5f;
  color: #ffffff;
}

/* Boton Soporte - Dorado FAH */
.btn-soporte {
  border-color: #d4af37;
  color: #d4af37;
}

.btn-soporte:hover {
  background: #d4af37;
  color: #1e3a5f;
}

/* Animaciones */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .header-content {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }

  .header-status {
    width: 100%;
    justify-content: flex-start;
  }

  .footer-content {
    flex-direction: column;
    gap: 20px;
  }

  .footer-actions {
    align-self: flex-start;
  }
}

@media (max-width: 768px) {
  .tabs-wrapper {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }

  .tab-button {
    padding: 12px 16px;
    min-height: 56px;
  }

  .tab-icon {
    font-size: 16px;
  }

  .tab-label {
    font-size: 14px;
  }

  .tab-count {
    font-size: 11px;
  }

  .selector-content {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }

  .selector-label {
    justify-content: center;
  }

  .selector-dropdown {
    max-width: none;
  }

  .selector-info {
    justify-content: center;
  }

  .main-container {
    padding: 24px 16px 16px;
  }

  .header-content {
    padding: 16px 16px;
  }

  .loading-container,
  .error-container {
    padding: 32px 16px;
  }

  .footer-container {
    padding: 32px 16px 24px;
  }
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: rgba(30, 58, 95, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(30, 58, 95, 0.5);
}

/* Transiciones globales */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Efectos hover para botones de pestañas */
button:hover {
  transform: translateY(-1px);
}
</style>
