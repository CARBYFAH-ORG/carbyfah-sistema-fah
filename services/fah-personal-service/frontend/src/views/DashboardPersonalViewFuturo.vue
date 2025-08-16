<template>
  <div class="dashboard-personal">
    <!-- Header Contextual -->
    <div class="dashboard-header">
      <div class="header-info">
        <h1 class="dashboard-title">{{ tituloContextual }}</h1>
        <p class="dashboard-subtitle">{{ descripcionAlcance }}</p>
      </div>

      <div class="header-actions">
        <Button
          v-if="puedeEjecutarAccion('crud_total')"
          label="Nuevo Personal"
          icon="pi pi-plus"
          class="p-button-success"
          @click="abrirFormularioNuevo"
        />

        <Button
          v-if="puedeEjecutarAccion('ver_reportes')"
          label="Generar Reporte"
          icon="pi pi-file-pdf"
          class="p-button-outlined"
          @click="generarReporte"
        />
      </div>
    </div>

    <!-- Métricas Principales -->
    <div class="metricas-grid">
      <!-- Total Personal -->
      <Card class="metrica-card">
        <template #content>
          <div class="metrica-contenido">
            <div class="metrica-icono total">
              <i class="pi pi-users"></i>
            </div>
            <div class="metrica-datos">
              <h3>{{ metricasFiltradas.total_personal || 0 }}</h3>
              <p>Total Personal</p>
              <small>{{ porcentajeOcupacion }}% ocupación</small>
            </div>
          </div>
        </template>
      </Card>

      <!-- Vacantes -->
      <Card class="metrica-card">
        <template #content>
          <div class="metrica-contenido">
            <div class="metrica-icono vacantes">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            <div class="metrica-datos">
              <h3>{{ metricasFiltradas.total_vacantes || 0 }}</h3>
              <p>Vacantes</p>
              <small>{{ porcentajeVacantes }}% déficit</small>
            </div>
          </div>
        </template>
      </Card>

      <!-- Alertas -->
      <Card class="metrica-card">
        <template #content>
          <div class="metrica-contenido">
            <div class="metrica-icono alertas">
              <i class="pi pi-bell"></i>
            </div>
            <div class="metrica-datos">
              <h3>{{ alertasCriticas.length }}</h3>
              <p>Alertas Críticas</p>
              <small>Requieren atención</small>
            </div>
          </div>
        </template>
      </Card>

      <!-- Estado Operativo -->
      <Card class="metrica-card">
        <template #content>
          <div class="metrica-contenido">
            <div class="metrica-icono operativo">
              <i class="pi pi-check-circle"></i>
            </div>
            <div class="metrica-datos">
              <h3>{{ porcentajeOperativo }}%</h3>
              <p>Personal Operativo</p>
              <small>Disponible para misiones</small>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Dashboard Específico por Nivel -->
    <div class="dashboard-contenido">
      <!-- Vista FA-1: Dashboard Estratégico -->
      <template v-if="esJefeFA1">
        <div class="seccion-dashboard">
          <h2>Vista Estratégica FAH</h2>

          <!-- Gráfico de Personal por Base -->
          <Card class="chart-card">
            <template #header>
              <h3>Distribución de Personal por Base</h3>
            </template>
            <template #content>
              <div class="chart-container">
                <!-- Aquí iría un gráfico con recharts -->
                <div class="chart-placeholder">
                  Gráfico: Personal por Base (HAM: 406, HCM: 178, AEE: 357,
                  etc.)
                </div>
              </div>
            </template>
          </Card>

          <!-- Alertas Estratégicas -->
          <Card class="alertas-card">
            <template #header>
              <h3>Situación Crítica por Unidades</h3>
            </template>
            <template #content>
              <div class="alertas-estrategicas">
                <div
                  v-for="alerta in alertasEstrategicas"
                  :key="alerta.id"
                  class="alerta-item"
                  :class="alerta.nivel"
                >
                  <i :class="alerta.icono"></i>
                  <div class="alerta-info">
                    <strong>{{ alerta.unidad }}</strong>
                    <p>{{ alerta.mensaje }}</p>
                  </div>
                  <span class="alerta-tiempo">{{ alerta.tiempo }}</span>
                </div>
              </div>
            </template>
          </Card>
        </div>
      </template>

      <!-- Vista S-1: Dashboard Táctico -->
      <template v-if="esJefeS1">
        <div class="seccion-dashboard">
          <h2>Vista Táctica - {{ usuarioActual.unidad_principal }}</h2>

          <!-- Estado de la Unidad -->
          <div class="estado-unidad-grid">
            <Card class="estado-card">
              <template #content>
                <h4>Personal por Sección</h4>
                <div class="secciones-lista">
                  <div
                    v-for="seccion in seccionesUnidad"
                    :key="seccion.codigo"
                    class="seccion-item"
                  >
                    <span class="seccion-nombre">{{ seccion.nombre }}</span>
                    <span class="seccion-personal"
                      >{{ seccion.personal }}/{{ seccion.requerido }}</span
                    >
                    <ProgressBar
                      :value="(seccion.personal / seccion.requerido) * 100"
                      :class="
                        seccion.personal < seccion.requerido
                          ? 'p-progressbar-danger'
                          : 'p-progressbar-success'
                      "
                    />
                  </div>
                </div>
              </template>
            </Card>

            <Card class="servicios-card">
              <template #content>
                <h4>Servicios Activos</h4>
                <div class="servicios-lista">
                  <div class="servicio-item">
                    <span>Guardia:</span>
                    <strong>{{ serviciosActivos.guardia }} personas</strong>
                  </div>
                  <div class="servicio-item">
                    <span>Misión:</span>
                    <strong>{{ serviciosActivos.mision }} personas</strong>
                  </div>
                  <div class="servicio-item">
                    <span>Permiso:</span>
                    <strong>{{ serviciosActivos.permiso }} personas</strong>
                  </div>
                  <div class="servicio-item">
                    <span>Reposo:</span>
                    <strong>{{ serviciosActivos.reposo }} personas</strong>
                  </div>
                </div>
              </template>
            </Card>
          </div>
        </div>
      </template>

      <!-- Vista Sección: Dashboard Operativo -->
      <template v-if="esJefeSeccion">
        <div class="seccion-dashboard">
          <h2>Vista Operativa - {{ usuarioActual.seccion_principal }}</h2>

          <!-- Personal de la Sección -->
          <Card class="personal-seccion-card">
            <template #header>
              <h3>Personal de {{ usuarioActual.seccion_principal }}</h3>
            </template>
            <template #content>
              <div class="personal-seccion-lista">
                <div
                  v-for="persona in personalSeccion"
                  :key="persona.id"
                  class="persona-item"
                >
                  <div class="persona-avatar">
                    <i class="pi pi-user"></i>
                  </div>
                  <div class="persona-info">
                    <strong>{{ persona.grado }} {{ persona.nombre }}</strong>
                    <p>{{ persona.especialidad }}</p>
                    <span
                      class="persona-estado"
                      :class="persona.estado.toLowerCase()"
                    >
                      {{ persona.estado }}
                    </span>
                  </div>
                  <div class="persona-acciones">
                    <Button
                      icon="pi pi-eye"
                      class="p-button-text p-button-sm"
                      @click="verDetallePersona(persona)"
                    />
                  </div>
                </div>
              </div>
            </template>
          </Card>
        </div>
      </template>

      <!-- Sección Común: Acciones Rápidas -->
      <Card class="acciones-card">
        <template #header>
          <h3>Acciones Rápidas</h3>
        </template>
        <template #content>
          <div class="acciones-grid">
            <Button
              v-if="verificarPermiso('SOLICITUDES', 'crear_solicitud')"
              label="Nueva Solicitud"
              icon="pi pi-plus"
              class="p-button-outlined"
              @click="nuevaSolicitud"
            />

            <Button
              v-if="verificarPermiso('ADMINISTRACION_PERSONAL', 'ver_unidad')"
              label="Ver Personal"
              icon="pi pi-users"
              class="p-button-outlined"
              @click="verPersonal"
            />

            <Button
              v-if="verificarPermiso('CARBYCHAT', 'mensajes_unidad')"
              label="Enviar Mensaje"
              icon="pi pi-comment"
              class="p-button-outlined"
              @click="abrirCarbychat"
            />

            <Button
              v-if="verificarPermiso('DASHBOARD', 'metricas_consolidadas')"
              label="Reportes"
              icon="pi pi-chart-bar"
              class="p-button-outlined"
              @click="verReportes"
            />
          </div>
        </template>
      </Card>
    </div>

    <!-- Loading -->
    <div v-if="cargandoDashboard" class="loading-overlay">
      <ProgressSpinner />
      <p>Cargando dashboard...</p>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import { useNivelesStore } from "@/stores/nivelesStore";
import { usarFiltrosJerarquicos } from "@/composables/usarFiltrosJerarquicos";

export default {
  name: "DashboardPersonalView",
  setup() {
    const router = useRouter();
    const toast = useToast();
    const nivelesStore = useNivelesStore();
    const {
      filtrarMetricas,
      obtenerTituloContextual,
      puedeEjecutarAccion,
      logConContexto,
    } = usarFiltrosJerarquicos();

    // Estado reactivo
    const cargandoDashboard = ref(true);
    const metricas = ref({});
    const alertasCriticas = ref([]);
    const personalSeccion = ref([]);
    const seccionesUnidad = ref([]);
    const serviciosActivos = ref({});

    // Computadas
    const usuarioActual = computed(() => nivelesStore.usuarioActual);
    const esJefeFA1 = computed(() => nivelesStore.esJefeFA1);
    const esJefeS1 = computed(() => nivelesStore.esJefeS1);
    const esJefeSeccion = computed(() => nivelesStore.esJefeSeccion);

    const tituloContextual = computed(() =>
      obtenerTituloContextual("Dashboard Personal")
    );

    const descripcionAlcance = computed(() => nivelesStore.descripcionAlcance);

    const metricasFiltradas = computed(
      () => filtrarMetricas(metricas.value) || {}
    );

    const porcentajeOcupacion = computed(() => {
      const total = metricasFiltradas.value.total_personal || 0;
      const requerido = metricasFiltradas.value.total_requerido || 1;
      return Math.round((total / requerido) * 100);
    });

    const porcentajeVacantes = computed(() => {
      const vacantes = metricasFiltradas.value.total_vacantes || 0;
      const requerido = metricasFiltradas.value.total_requerido || 1;
      return Math.round((vacantes / requerido) * 100);
    });

    const porcentajeOperativo = computed(() => {
      const operativo = metricasFiltradas.value.personal_operativo || 0;
      const total = metricasFiltradas.value.total_personal || 1;
      return Math.round((operativo / total) * 100);
    });

    const alertasEstrategicas = computed(() =>
      alertasCriticas.value.filter((alerta) => alerta.nivel === "critica")
    );

    // Métodos
    const cargarDatos = async () => {
      try {
        cargandoDashboard.value = true;

        logConContexto("Cargando datos de dashboard");

        // Cargar métricas según el nivel
        await cargarMetricas();

        // Cargar datos específicos según nivel
        if (esJefeFA1.value) {
          await cargarDatosEstrategicos();
        } else if (esJefeS1.value) {
          await cargarDatosTacticos();
        } else if (esJefeSeccion.value) {
          await cargarDatosOperativos();
        }

        logConContexto("Dashboard cargado exitosamente");
      } catch (error) {
        console.error("Error cargando dashboard:", error);
        toast.add({
          severity: "error",
          summary: "Error",
          detail: "No se pudieron cargar los datos del dashboard",
          life: 5000,
        });
      } finally {
        cargandoDashboard.value = false;
      }
    };

    const cargarMetricas = async () => {
      // Simulación de datos - reemplazar por API real
      const datosSimulados = {
        total_personal: esJefeFA1.value
          ? 3644
          : esJefeS1.value
          ? 406
          : esJefeSeccion.value
          ? 45
          : 10,
        total_requerido: esJefeFA1.value
          ? 4231
          : esJefeS1.value
          ? 475
          : esJefeSeccion.value
          ? 55
          : 15,
        total_vacantes: esJefeFA1.value
          ? 587
          : esJefeS1.value
          ? 69
          : esJefeSeccion.value
          ? 10
          : 5,
        personal_operativo: esJefeFA1.value
          ? 2890
          : esJefeS1.value
          ? 340
          : esJefeSeccion.value
          ? 38
          : 8,
      };

      metricas.value = datosSimulados;
    };

    const cargarDatosEstrategicos = async () => {
      // Datos para vista FA-1
      alertasCriticas.value = [
        {
          id: 1,
          unidad: "Base HAM",
          mensaje: "Déficit crítico: 69 vacantes en personal técnico",
          nivel: "critica",
          icono: "pi pi-exclamation-triangle",
          tiempo: "Hace 2 horas",
        },
        {
          id: 2,
          unidad: "Base HCM",
          mensaje: "Personal de vuelo insuficiente para misiones",
          nivel: "alta",
          icono: "pi pi-exclamation-circle",
          tiempo: "Hace 4 horas",
        },
      ];
    };

    const cargarDatosTacticos = async () => {
      // Datos para vista S-1
      seccionesUnidad.value = [
        {
          codigo: "S1",
          nombre: "Recursos Humanos",
          personal: 12,
          requerido: 15,
        },
        { codigo: "S2", nombre: "Inteligencia", personal: 8, requerido: 10 },
        { codigo: "S3", nombre: "Operaciones", personal: 25, requerido: 30 },
        { codigo: "S4", nombre: "Logística", personal: 18, requerido: 20 },
        {
          codigo: "MANT",
          nombre: "Mantenimiento",
          personal: 45,
          requerido: 55,
        },
      ];

      serviciosActivos.value = {
        guardia: 25,
        mision: 12,
        permiso: 8,
        reposo: 3,
      };
    };

    const cargarDatosOperativos = async () => {
      // Datos para vista Sección
      personalSeccion.value = [
        {
          id: 1,
          grado: "Sargento",
          nombre: "Carlos Méndez",
          especialidad: "Mecánico de Motores",
          estado: "DISPONIBLE",
        },
        {
          id: 2,
          grado: "Cabo",
          nombre: "Ana Rodríguez",
          especialidad: "Técnico Aviónica",
          estado: "EN_MISION",
        },
      ];
    };

    // Acciones
    const verificarPermiso = (modulo, accion) => {
      return nivelesStore.verificarPermiso(modulo, accion);
    };

    const abrirFormularioNuevo = () => {
      router.push("/fa1-fuerza/personal/nuevo");
    };

    const generarReporte = () => {
      toast.add({
        severity: "info",
        summary: "Generando Reporte",
        detail: "El reporte se está generando...",
        life: 3000,
      });
    };

    const nuevaSolicitud = () => {
      router.push("/solicitudes/nueva");
    };

    const verPersonal = () => {
      router.push("/fa1-fuerza/personal");
    };

    const abrirCarbychat = () => {
      toast.add({
        severity: "info",
        summary: "CARBYCHAT",
        detail: "Abriendo sistema de mensajería...",
        life: 3000,
      });
    };

    const verReportes = () => {
      router.push("/fa1-fuerza/reportes");
    };

    const verDetallePersona = (persona) => {
      router.push(`/personal/${persona.id}`);
    };

    // Lifecycle
    onMounted(async () => {
      await cargarDatos();
    });

    return {
      // Estado
      cargandoDashboard,
      alertasCriticas,
      personalSeccion,
      seccionesUnidad,
      serviciosActivos,

      // Computadas
      usuarioActual,
      esJefeFA1,
      esJefeS1,
      esJefeSeccion,
      tituloContextual,
      descripcionAlcance,
      metricasFiltradas,
      porcentajeOcupacion,
      porcentajeVacantes,
      porcentajeOperativo,
      alertasEstrategicas,

      // Métodos
      verificarPermiso,
      puedeEjecutarAccion,
      abrirFormularioNuevo,
      generarReporte,
      nuevaSolicitud,
      verPersonal,
      abrirCarbychat,
      verReportes,
      verDetallePersona,
    };
  },
};
</script>

<style scoped>
.dashboard-personal {
  padding: 1.5rem;
  min-height: 100vh;
  background: #f8f9fa;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dashboard-title {
  font-size: 1.75rem;
  font-weight: 600;
  color: #1e3a5f;
  margin: 0 0 0.5rem 0;
}

.dashboard-subtitle {
  color: #6c757d;
  margin: 0;
  font-size: 1rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.metricas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.metrica-card {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.metrica-contenido {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.metrica-icono {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.metrica-icono.total {
  background: #0ea5e9;
}
.metrica-icono.vacantes {
  background: #ef4444;
}
.metrica-icono.alertas {
  background: #f59e0b;
}
.metrica-icono.operativo {
  background: #10b981;
}

.metrica-datos h3 {
  font-size: 2rem;
  font-weight: 700;
  color: #1e3a5f;
  margin: 0 0 0.25rem 0;
}

.metrica-datos p {
  font-weight: 500;
  color: #374151;
  margin: 0 0 0.25rem 0;
}

.metrica-datos small {
  color: #6b7280;
  font-size: 0.875rem;
}

.dashboard-contenido {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.seccion-dashboard h2 {
  color: #1e3a5f;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.chart-card,
.alertas-card,
.estado-unidad-grid,
.personal-seccion-card,
.acciones-card {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chart-placeholder {
  height: 300px;
  background: #f3f4f6;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  font-style: italic;
}

.alertas-estrategicas {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.alerta-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid;
}

.alerta-item.critica {
  background: #fef2f2;
  border-left-color: #ef4444;
}

.alerta-item.alta {
  background: #fffbeb;
  border-left-color: #f59e0b;
}

.estado-unidad-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.secciones-lista,
.servicios-lista {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.seccion-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.seccion-nombre {
  font-weight: 500;
  color: #374151;
}

.seccion-personal {
  font-size: 0.875rem;
  color: #6b7280;
}

.servicio-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.personal-seccion-lista {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.persona-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
}

.persona-avatar {
  width: 40px;
  height: 40px;
  background: #e5e7eb;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
}

.persona-info {
  flex: 1;
}

.persona-estado {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: 500;
  text-transform: uppercase;
}

.persona-estado.disponible {
  background: #d1fae5;
  color: #065f46;
}

.persona-estado.en_mision {
  background: #dbeafe;
  color: #1e40af;
}

.acciones-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .estado-unidad-grid {
    grid-template-columns: 1fr;
  }
}
</style>
