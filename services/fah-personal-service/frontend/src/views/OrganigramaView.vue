<template>
  <div class="organigrama-container">
    <!-- Header -->
    <div class="organigrama-header">
      <Toolbar class="mb-4">
        <template #start>
          <h1 class="text-2xl font-bold text-blue-800 m-0">
            🚁 Organigrama Fuerza Aérea Hondureña
          </h1>
        </template>
        <template #end>
          <div class="flex gap-2">
            <Dropdown
              v-model="tipoVista"
              :options="opcionesTipoVista"
              optionLabel="label"
              optionValue="value"
              placeholder="Tipo de vista"
              class="w-48"
            />
            <Button
              label="Exportar PDF"
              icon="pi pi-file-pdf"
              severity="danger"
              @click="exportarPDF"
            />
            <Button
              label="Exportar PNG"
              icon="pi pi-image"
              severity="info"
              @click="exportarPNG"
            />
          </div>
        </template>
      </Toolbar>
    </div>

    <!-- Contenedor del organigrama -->
    <Card class="organigrama-card">
      <template #content>
        <div v-if="cargando" class="text-center p-8">
          <ProgressSpinner />
          <p class="mt-4 text-gray-600">
            Cargando estructura organizacional...
          </p>
        </div>

        <div v-else-if="error" class="text-center p-8">
          <i class="pi pi-exclamation-triangle text-red-500 text-4xl"></i>
          <p class="mt-4 text-red-600">{{ error }}</p>
          <Button
            label="Reintentar"
            icon="pi pi-refresh"
            @click="cargarDatos"
            class="mt-4"
          />
        </div>

        <div v-else>
          <!-- Contenedor SVG para D3.js -->
          <div id="organigrama-svg-container" class="w-full overflow-auto">
            <!-- Aquí se renderizará el organigrama con D3.js -->
          </div>

          <!-- Información de debug (temporal) -->
          <div
            v-if="datos && datos.length > 0"
            class="mt-6 p-4 bg-gray-100 rounded"
          >
            <h3 class="font-bold mb-2">Datos cargados:</h3>
            <p>Total de unidades: {{ datos.length }}</p>
            <div
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4"
            >
              <div
                v-for="unidad in datos.slice(0, 6)"
                :key="unidad.id"
                class="p-3 bg-white rounded shadow"
              >
                <h4 class="font-semibold text-sm">
                  {{ unidad.codigo_unidad }}
                </h4>
                <p class="text-xs text-gray-600">{{ unidad.nombre_unidad }}</p>
                <p class="text-xs">
                  Personal: {{ unidad.total_personal_asignado || 0 }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </Card>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import organigramaService from "../services/organigramaService";

export default {
  name: "OrganigramaView",
  setup() {
    const toast = useToast();

    // Estado reactivo
    const cargando = ref(true);
    const error = ref(null);
    const datos = ref([]);
    const tipoVista = ref("fah");

    const opcionesTipoVista = [
      { label: "FAH Completa", value: "fah" },
      { label: "Por Departamentos", value: "departamentos" },
      { label: "Por Bases", value: "bases" },
    ];

    // Cargar datos
    const cargarDatos = async () => {
      try {
        cargando.value = true;
        error.value = null;

        const response = await organigramaService.obtenerEstructuraFAH();
        datos.value = response.data;

        toast.add({
          severity: "success",
          summary: "Éxito",
          detail: `Estructura cargada: ${response.data.length} unidades`,
          life: 3000,
        });
      } catch (err) {
        error.value = "Error al cargar la estructura organizacional";
        toast.add({
          severity: "error",
          summary: "Error",
          detail: "No se pudo cargar el organigrama",
          life: 5000,
        });
      } finally {
        cargando.value = false;
      }
    };

    // Exportar funciones (placeholder)
    const exportarPDF = () => {
      toast.add({
        severity: "info",
        summary: "Funcionalidad pendiente",
        detail: "Exportación a PDF en desarrollo",
        life: 3000,
      });
    };

    const exportarPNG = () => {
      toast.add({
        severity: "info",
        summary: "Funcionalidad pendiente",
        detail: "Exportación a PNG en desarrollo",
        life: 3000,
      });
    };

    // Lifecycle
    onMounted(() => {
      cargarDatos();
    });

    return {
      cargando,
      error,
      datos,
      tipoVista,
      opcionesTipoVista,
      cargarDatos,
      exportarPDF,
      exportarPNG,
    };
  },
};
</script>

<style scoped>
.organigrama-container {
  padding: 1rem;
  min-height: 100vh;
}

.organigrama-header {
  margin-bottom: 1rem;
}

.organigrama-card {
  min-height: 70vh;
}

#organigrama-svg-container {
  min-height: 500px;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  background: white;
}
</style>
