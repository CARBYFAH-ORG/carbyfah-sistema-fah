<template>
  <!-- FORMULARIO DINÁMICO UNIVERSAL - CARBYFAH -->
  <!-- Motor inteligente para TODOS los 11 catálogos -->
  <div class="contenedor-formulario-dinamico">
    <!-- Indicador de carga -->
    <div v-if="cargando" class="flex items-center justify-center py-8">
      <ProgressSpinner size="50" strokeWidth="4" />
      <span class="ml-3 text-gray-300">{{ mensajeCarga }}</span>
    </div>

    <!-- Contenido del formulario -->
    <div v-else-if="esquemaValido" class="formulario-contenido">
      <!-- Mensaje de error global -->
      <Message
        v-if="tieneErrores"
        severity="error"
        :closable="false"
        class="mb-5 rounded-lg bg-red-900/50 border-2 border-red-500"
      >
        <div>
          <strong class="block mb-2 text-red-300">Error de validación:</strong>
          <ul class="m-0 pl-5">
            <li
              v-for="(error, campo) in erroresValidacion"
              :key="campo"
              class="mb-1 text-sm text-red-300"
            >
              <strong>{{ obtenerEtiquetaCampo(campo) }}:</strong> {{ error }}
            </li>
          </ul>
        </div>
      </Message>

      <!-- Formulario dinámico -->
      <form @submit.prevent="manejarEnvio" class="formulario-dinamico">
        <div class="grid grid-cols-12 gap-5">
          <!-- Generar campos dinámicamente -->
          <CampoFormulario
            v-for="configuracionCampo in camposConfigurados"
            :key="configuracionCampo.nombre"
            :configuracion="configuracionCampo"
            :valor="datosFormulario[configuracionCampo.nombre]"
            :error="erroresValidacion[configuracionCampo.nombre]"
            :deshabilitado="cargando"
            :opciones-externas="obtenerOpcionesParaCampo(configuracionCampo)"
            @actualizar="actualizarCampo"
            @pais-seleccionado="manejarPaisSeleccionado"
            class="campo-wrapper"
          />
        </div>
      </form>
    </div>

    <!-- Error de configuración -->
    <div v-else class="p-5 text-center">
      <Message severity="warn" :closable="false">
        <div>
          <strong>Configuración no encontrada</strong>
          <p class="mt-2.5 text-gray-300">
            No hay configuración disponible para el esquema:
            <code
              class="bg-gray-800 px-1.5 py-0.5 rounded font-semibold text-red-500"
            >
              {{ nombreEsquema }}
            </code>
          </p>
        </div>
      </Message>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from "vue";
import { useToast } from "primevue/usetoast";

// Componentes PrimeVue
import ProgressSpinner from "primevue/progressspinner";
import Message from "primevue/message";

// Componentes propios
import CampoFormulario from "./CampoFormulario.vue";

// Composables y utilidades
import { usarFormularioDinamico } from "@/composables/usarFormularioDinamico";
import { useCatalogosStore } from "@/stores/catalogosStore";

export default {
  name: "FormularioDinamico",

  components: {
    ProgressSpinner,
    Message,
    CampoFormulario,
  },

  props: {
    esquema: {
      type: String,
      required: true,
      validator: (valor) => {
        const esquemasValidos = [
          "tipos_genero",
          "tipos_estado_general",
          "niveles_prioridad",
          "niveles_seguridad",
          "paises",
          "tipos_estructura_militar",
          "tipos_jerarquia",
          "categorias_personal",
          "especialidades",
          "tipos_evento",
          "grados",
        ];
        return esquemasValidos.includes(valor);
      },
    },
    modo: {
      type: String,
      default: "crear",
      validator: (valor) => ["crear", "editar"].includes(valor),
    },
    datosIniciales: {
      type: Object,
      default: () => ({}),
    },
    tema: {
      type: String,
      default: "militar-oscuro",
    },
  },

  emits: ["enviado", "error", "cancelado"],

  setup(props, { emit }) {
    // Composables
    const toast = useToast();
    const catalogosStore = useCatalogosStore();

    const {
      configurarEsquema,
      validarDatos,
      formatearParaBackend,
      obtenerValoresDefecto,
      analizarConfiguracionCampo,
      mostrarNotificacion,
      limpiarErrores,
    } = usarFormularioDinamico();

    // Estado reactivo
    const cargando = ref(false);
    const datosFormulario = ref({});
    const erroresValidacion = ref({});
    const configuracionEsquema = ref(null);
    const camposConfigurados = ref([]);

    // Computed properties
    const nombreEsquema = computed(() => props.esquema);

    const esquemaValido = computed(() => {
      return configuracionEsquema.value !== null;
    });

    const tieneErrores = computed(() => {
      return Object.keys(erroresValidacion.value).length > 0;
    });

    const mensajeCarga = computed(() => {
      if (props.modo === "crear") {
        return "Preparando formulario...";
      } else {
        return "Cargando datos para editar...";
      }
    });

    // Obtener opciones para campos de selección
    const obtenerOpcionesParaCampo = (configuracionCampo) => {
      if (
        configuracionCampo.tipo !== "seleccion" ||
        !configuracionCampo.tablaReferencia
      ) {
        return [];
      }

      switch (configuracionCampo.tablaReferencia) {
        case "categorias_personal":
          const categorias = catalogosStore.categoriasPersonal || [];

          if (categorias.length === 0) {
            catalogosStore.loadCategoriasPersonal();

            return [
              {
                etiqueta: "Cargando categorías...",
                valor: null,
                disabled: true,
              },
              { etiqueta: "Oficial", valor: 1 },
              { etiqueta: "Suboficial", valor: 2 },
              { etiqueta: "Soldado", valor: 3 },
            ];
          }

          const opciones = categorias
            .filter((categoria) => categoria.is_active !== false)
            .map((categoria) => ({
              etiqueta:
                categoria.nombre_categoria ||
                categoria.codigo_categoria ||
                `Categoría ${categoria.id}`,
              valor: categoria.id,
              datos: categoria,
            }));

          return opciones;

        case "especialidades":
          const especialidades = catalogosStore.especialidades || [];
          return especialidades
            .filter((especialidad) => especialidad.is_active !== false)
            .map((especialidad) => ({
              etiqueta: especialidad.nombre_especialidad,
              valor: especialidad.id,
              datos: especialidad,
            }));

        default:
          return [];
      }
    };

    // Esta función está lista para inicializar formulario, analizaremos CampoFormulario.vue para seguir el análisis
    const inicializarFormulario = async () => {
      cargando.value = true;
      limpiarErrores();
      erroresValidacion.value = {};

      try {
        const configuracion = await configurarEsquema(props.esquema);

        if (!configuracion) {
          return;
        }

        configuracionEsquema.value = configuracion;

        // ✅ CÓDIGO CORREGIDO:
        if (props.esquema === "grados") {
          // 🔥 FUERZA LETAL: Siempre cargar categorías para grados
          await catalogosStore.loadCategoriasPersonal();
          console.log(
            "✅ Categorías cargadas para grados:",
            catalogosStore.categoriasPersonal?.length
          );
        }

        camposConfigurados.value = configuracion.campos
          .map((configuracionCampo) => {
            return analizarConfiguracionCampo(configuracionCampo);
          })
          .filter((config) => config !== null);

        if (props.modo === "crear") {
          datosFormulario.value = obtenerValoresDefecto(configuracion);
        } else {
          datosFormulario.value = { ...props.datosIniciales };
        }
      } catch (error) {
        mostrarNotificacion("errorConexion");
      } finally {
        cargando.value = false;
      }
    };

    // Actualizar valor de campo específico
    const actualizarCampo = (nombreCampo, nuevoValor) => {
      datosFormulario.value[nombreCampo] = nuevoValor;

      if (erroresValidacion.value[nombreCampo]) {
        delete erroresValidacion.value[nombreCampo];
      }
    };

    // ✅ NUEVO: AUTO-LLENADO CUANDO SELECCIONA PAÍS
    const manejarPaisSeleccionado = (paisData) => {
      console.log("🌍 País seleccionado para auto-llenado:", paisData);

      // Mapear datos del país a campos del formulario
      const mapaAutollenado = {
        nombre_oficial: paisData.nombreOficial,
        codigo_iso3: paisData.codigoISO3,
        codigo_telefono: paisData.codigoTelefono,
        moneda_oficial: paisData.monedaOficial,
      };

      // Auto-llenar campos que existen en el formulario
      Object.keys(mapaAutollenado).forEach((nombreCampo) => {
        if (datosFormulario.value.hasOwnProperty(nombreCampo)) {
          datosFormulario.value[nombreCampo] = mapaAutollenado[nombreCampo];
          console.log(
            `🔄 Auto-llenado: ${nombreCampo} = ${mapaAutollenado[nombreCampo]}`
          );
        }
      });

      // Limpiar errores de campos auto-llenados
      Object.keys(mapaAutollenado).forEach((nombreCampo) => {
        if (erroresValidacion.value[nombreCampo]) {
          delete erroresValidacion.value[nombreCampo];
        }
      });

      // Mostrar notificación de auto-llenado
      toast.add({
        severity: "success",
        summary: "Auto-llenado completado",
        detail: `Campos llenados automáticamente para ${paisData.nombre}`,
        life: 3000,
      });
    };

    // Obtener etiqueta legible de un campo
    const obtenerEtiquetaCampo = (nombreCampo) => {
      const campo = camposConfigurados.value.find(
        (c) => c.nombre === nombreCampo
      );
      return campo ? campo.etiqueta : nombreCampo;
    };

    // Validar todos los datos del formulario
    const validarFormulario = () => {
      const resultadoValidacion = validarDatos(
        configuracionEsquema.value,
        datosFormulario.value
      );

      erroresValidacion.value = resultadoValidacion.errores;

      return resultadoValidacion.esValido;
    };

    // Procesar envío del formulario
    const manejarEnvio = async () => {
      if (!validarFormulario()) {
        mostrarNotificacion("errorValidacion");
        return;
      }

      cargando.value = true;

      try {
        const datosFormateados = formatearParaBackend(
          configuracionEsquema.value,
          datosFormulario.value
        );

        emit("enviado", {
          esquema: props.esquema,
          modo: props.modo,
          datos: datosFormateados,
          datosOriginales: datosFormulario.value,
        });
      } catch (error) {
        emit("error", {
          esquema: props.esquema,
          modo: props.modo,
          error: error.message || "Error desconocido",
        });
      } finally {
        cargando.value = false;
      }
    };

    // Cancelar operación del formulario
    const cancelarFormulario = () => {
      datosFormulario.value = {};
      erroresValidacion.value = {};

      emit("cancelado", {
        esquema: props.esquema,
        modo: props.modo,
      });
    };

    // Watchers
    watch(
      () => props.esquema,
      () => {
        inicializarFormulario();
      }
    );

    watch(
      () => props.modo,
      () => {
        inicializarFormulario();
      }
    );

    watch(
      () => props.datosIniciales,
      (nuevosDatos) => {
        if (props.modo === "editar" && nuevosDatos) {
          datosFormulario.value = { ...nuevosDatos };
        }
      },
      { deep: true }
    );

    // Lifecycle
    onMounted(() => {
      inicializarFormulario();
    });

    return {
      // Estado
      cargando,
      datosFormulario,
      erroresValidacion,
      configuracionEsquema,
      camposConfigurados,

      // Computed
      nombreEsquema,
      esquemaValido,
      tieneErrores,
      mensajeCarga,

      // Métodos
      actualizarCampo,
      manejarPaisSeleccionado,
      obtenerEtiquetaCampo,
      obtenerOpcionesParaCampo,
      manejarEnvio,
      cancelarFormulario,
      validarFormulario,
      inicializarFormulario,
    };
  },
};
</script>

<style scoped>
.contenedor-formulario-dinamico {
  @apply w-full;
}

.formulario-contenido {
  @apply space-y-6;
}

.formulario-dinamico {
  @apply w-full;
}

.campo-wrapper {
  animation: aparecer 0.3s ease-out;
}

@keyframes aparecer {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.cargando {
  @apply opacity-50 pointer-events-none;
}

@media (max-width: 768px) {
  .formulario-dinamico .grid {
    @apply grid-cols-1 gap-3;
  }
}
</style>
