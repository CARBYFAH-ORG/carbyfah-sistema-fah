<template>
  <!-- FORMULARIO DINÁMICO ORGANIZACIÓN - CARBYFAH -->
  <!-- Motor inteligente para TODOS los 7 módulos de organización -->
  <div class="contenedor-formulario-dinamico-organizacion">
    <!-- Indicador de carga -->
    <div v-if="cargando" class="flex items-center justify-center py-8">
      <ProgressSpinner size="50" strokeWidth="4" />
      <span class="ml-3 text-purple-300">{{ mensajeCarga }}</span>
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
      <form
        @submit.prevent="manejarEnvio"
        class="formulario-dinamico-organizacion"
      >
        <div class="grid grid-cols-12 gap-5">
          <!-- Generar campos dinámicamente -->
          <CampoFormularioOrganizacion
            v-for="configuracionCampo in camposConfigurados"
            :key="configuracionCampo.nombre"
            :configuracion="configuracionCampo"
            :valor="datosFormulario[configuracionCampo.nombre]"
            :error="erroresValidacion[configuracionCampo.nombre]"
            :deshabilitado="cargando"
            :opciones-externas="obtenerOpcionesParaCampo(configuracionCampo)"
            @actualizar="actualizarCampo"
            @ubicacion-seleccionada="manejarUbicacionSeleccionada"
            @departamento-seleccionado="manejarDepartamentoSeleccionado"
            @municipio-seleccionado="manejarMunicipioSeleccionado"
            class="campo-wrapper"
          />
        </div>
      </form>
    </div>

    <!-- Error de configuración -->
    <div v-else-if="!cargando" class="p-5 text-center">
      <Message severity="warn" :closable="false">
        <div>
          <strong>Configuración no encontrada</strong>
          <p class="mt-2.5 text-purple-300">
            No hay configuración disponible para el esquema:
            <code
              class="bg-purple-800 px-1.5 py-0.5 rounded font-semibold text-red-500"
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
import { ref, computed, watch, onMounted, nextTick } from "vue";
import { useToast } from "primevue/usetoast";

// Componentes PrimeVue
import ProgressSpinner from "primevue/progressspinner";
import Message from "primevue/message";

// Componentes propios especializados
import CampoFormularioOrganizacion from "./CampoFormularioOrganizacion.vue";

// Composables y utilidades para organización
import { usarFormularioDinamico } from "@/composables/usarFormularioDinamico";
import { useOrganizacionStore } from "@/stores/organizacionStore";
import { useCatalogosStore } from "@/stores/catalogosStore"; // Para países y tipos_estructura_militar

// ✅ AGREGAR ESTA LÍNEA AQUÍ:
import { obtenerEsquema } from "@/config/esquemaOrganizacion";

export default {
  name: "FormularioDinamicoOrganizacion",

  components: {
    ProgressSpinner,
    Message,
    CampoFormularioOrganizacion,
  },

  props: {
    esquema: {
      type: String,
      required: true,
      validator: (valor) => {
        const esquemasValidos = [
          "departamentos",
          "municipios",
          "ciudades",
          "ubicaciones_geograficas",
          "estructura_militar",
          "cargos",
          "roles_funcionales",
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
      default: "organizacion-purple",
    },
  },

  emits: ["enviado", "error", "cancelado"],

  setup(props, { emit }) {
    // Composables especializados
    const toast = useToast();
    const organizacionStore = useOrganizacionStore();
    const catalogosStore = useCatalogosStore(); // Para foráneas de catálogos

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

    // 🔥 NUEVOS REFS PARA FILTRADO JERÁRQUICO
    const paisSeleccionado = ref(null);
    const departamentoSeleccionado = ref(null);
    const municipioSeleccionado = ref(null);
    const ciudadSeleccionada = ref(null);

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
        return "Preparando formulario de organización...";
      } else {
        return "Cargando datos organizacionales para editar...";
      }
    });

    // 🌍 COMPUTED PARA FILTRADO JERÁRQUICO
    const departamentosFiltrados = computed(() => {
      if (!paisSeleccionado.value) {
        return organizacionStore.departamentos || [];
      }

      return (organizacionStore.departamentos || []).filter(
        (depto) => depto.pais_id === paisSeleccionado.value
      );
    });

    const municipiosFiltrados = computed(() => {
      if (!departamentoSeleccionado.value) {
        return organizacionStore.municipios || [];
      }

      return (organizacionStore.municipios || []).filter(
        (municipio) =>
          municipio.departamento_id === departamentoSeleccionado.value
      );
    });

    const ciudadesFiltradas = computed(() => {
      if (!municipioSeleccionado.value) {
        return organizacionStore.ciudades || [];
      }

      return (organizacionStore.ciudades || []).filter(
        (ciudad) => ciudad.municipio_id === municipioSeleccionado.value
      );
    });

    const ubicacionesFiltradas = computed(() => {
      let ubicaciones = organizacionStore.ubicacionesGeograficas || [];

      // Filtrar por país si está seleccionado
      if (paisSeleccionado.value) {
        ubicaciones = ubicaciones.filter(
          (ubicacion) => ubicacion.pais_id === paisSeleccionado.value
        );
      }

      // Filtrar por departamento si está seleccionado
      if (departamentoSeleccionado.value) {
        ubicaciones = ubicaciones.filter(
          (ubicacion) =>
            ubicacion.departamento_id === departamentoSeleccionado.value
        );
      }

      // Filtrar por municipio si está seleccionado
      if (municipioSeleccionado.value) {
        ubicaciones = ubicaciones.filter(
          (ubicacion) => ubicacion.municipio_id === municipioSeleccionado.value
        );
      }

      // Filtrar por ciudad si está seleccionada
      if (ciudadSeleccionada.value) {
        ubicaciones = ubicaciones.filter(
          (ubicacion) => ubicacion.ciudad_id === ciudadSeleccionada.value
        );
      }

      return ubicaciones;
    });

    // 🎯 FUNCIÓN PARA DETECTAR CAMBIOS EN SELECCIONES Y FILTRAR
    const actualizarFiltrosJerarquicos = (nombreCampo, valor) => {
      console.log(`🔄 Actualizando filtros: ${nombreCampo} = ${valor}`);

      switch (nombreCampo) {
        case "pais_id":
          paisSeleccionado.value = valor;
          // Reset cascada
          departamentoSeleccionado.value = null;
          municipioSeleccionado.value = null;
          ciudadSeleccionada.value = null;

          // ✅ LIMPIAR CAMPOS DEPENDIENTES EN EL FORMULARIO
          if (datosFormulario.value.departamento_id) {
            datosFormulario.value.departamento_id = null;
          }
          if (datosFormulario.value.municipio_id) {
            datosFormulario.value.municipio_id = null;
          }
          if (datosFormulario.value.ciudad_id) {
            datosFormulario.value.ciudad_id = null;
          }
          console.log("🌍 País seleccionado, campos dependientes reseteados");
          break;

        case "departamento_id":
          departamentoSeleccionado.value = valor;
          // Reset cascada siguiente
          municipioSeleccionado.value = null;
          ciudadSeleccionada.value = null;

          // ✅ LIMPIAR CAMPOS DEPENDIENTES
          if (datosFormulario.value.municipio_id) {
            datosFormulario.value.municipio_id = null;
          }
          if (datosFormulario.value.ciudad_id) {
            datosFormulario.value.ciudad_id = null;
          }
          console.log(
            "🏛️ Departamento seleccionado, municipios/ciudades reseteados"
          );
          break;

        case "municipio_id":
          municipioSeleccionado.value = valor;
          // Reset cascada siguiente
          ciudadSeleccionada.value = null;

          // ✅ LIMPIAR CAMPOS DEPENDIENTES
          if (datosFormulario.value.ciudad_id) {
            datosFormulario.value.ciudad_id = null;
          }
          console.log("🏙️ Municipio seleccionado, ciudades reseteadas");
          break;

        case "ciudad_id":
          ciudadSeleccionada.value = valor;
          console.log("🌃 Ciudad seleccionada");
          break;
      }

      // ✅ FORZAR ACTUALIZACIÓN DE LA UI
      nextTick(() => {
        console.log("🔄 UI actualizada con nuevos filtros");
      });
    };

    // 🚀 FUNCIÓN MEJORADA PARA OBTENER OPCIONES CON FILTRADO JERÁRQUICO
    const obtenerOpcionesParaCampo = (configuracionCampo) => {
      console.log("🔍 Obteniendo opciones para:", configuracionCampo);
      if (
        (configuracionCampo.tipo !== "seleccion" &&
          configuracionCampo.tipo !== "foraneo_autocompletado") ||
        !configuracionCampo.tablaReferencia
      ) {
        console.log(
          "❌ Campo no válido para opciones:",
          configuracionCampo.tipo,
          configuracionCampo.tablaReferencia
        );
        return [];
      }

      switch (configuracionCampo.tablaReferencia) {
        // REFERENCIAS A CATÁLOGOS (foráneas)
        case "paises":
          const paises = catalogosStore.paises || [];
          console.log("🌍 Países disponibles:", paises.length, paises);

          if (paises.length === 0) {
            console.log("📡 Forzando carga de países...");
            catalogosStore.loadPaises();
            return [
              {
                etiqueta: "Cargando países...",
                valor: null,
                disabled: true,
              },
            ];
          }

          console.log("✅ Retornando países:", paises.length);
          return paises
            .filter((pais) => pais.is_active !== false)
            .map((pais) => ({
              etiqueta: pais.nombre || `País ${pais.id}`,
              valor: pais.id,
              datos: pais,
            }));

        case "tipos_estructura_militar":
          const tiposEstructura = catalogosStore.tiposEstructuraMilitar || [];
          if (tiposEstructura.length === 0) {
            catalogosStore.loadTiposEstructuraMilitar();
            return [
              {
                etiqueta: "Cargando tipos estructura...",
                valor: null,
                disabled: true,
              },
            ];
          }
          return tiposEstructura
            .filter((tipo) => tipo.is_active !== false)
            .map((tipo) => ({
              etiqueta: tipo.nombre_tipo || `Tipo ${tipo.id}`,
              valor: tipo.id,
              datos: tipo,
            }));

        // 🎯 REFERENCIAS FILTRADAS JERÁRQUICAMENTE
        case "departamentos":
          const departamentosDisponibles = departamentosFiltrados.value;
          console.log(
            `🏘️ Departamentos filtrados (país: ${paisSeleccionado.value}):`,
            departamentosDisponibles.length
          );

          if (
            departamentosDisponibles.length === 0 &&
            organizacionStore.departamentos?.length === 0
          ) {
            organizacionStore.loadDepartamentos();
            return [
              {
                etiqueta: "Cargando departamentos...",
                valor: null,
                disabled: true,
              },
            ];
          }

          if (departamentosDisponibles.length === 0 && paisSeleccionado.value) {
            return [
              {
                etiqueta: "No hay departamentos para este país",
                valor: null,
                disabled: true,
              },
            ];
          }

          return departamentosDisponibles
            .filter((depto) => depto.is_active !== false)
            .map((depto) => ({
              etiqueta: depto.nombre_departamento || `Departamento ${depto.id}`,
              valor: depto.id,
              datos: depto,
            }));

        case "municipios":
          const municipiosDisponibles = municipiosFiltrados.value;
          console.log(
            `🏙️ Municipios filtrados (depto: ${departamentoSeleccionado.value}):`,
            municipiosDisponibles.length
          );

          if (
            municipiosDisponibles.length === 0 &&
            organizacionStore.municipios?.length === 0
          ) {
            organizacionStore.loadMunicipios();
            return [
              {
                etiqueta: "Cargando municipios...",
                valor: null,
                disabled: true,
              },
            ];
          }

          if (
            municipiosDisponibles.length === 0 &&
            departamentoSeleccionado.value
          ) {
            return [
              {
                etiqueta: "No hay municipios para este departamento",
                valor: null,
                disabled: true,
              },
            ];
          }

          return municipiosDisponibles
            .filter((municipio) => municipio.is_active !== false)
            .map((municipio) => ({
              etiqueta:
                municipio.nombre_municipio || `Municipio ${municipio.id}`,
              valor: municipio.id,
              datos: municipio,
            }));

        case "ciudades":
          const ciudadesDisponibles = ciudadesFiltradas.value;
          console.log(
            `🌃 Ciudades filtradas (municipio: ${municipioSeleccionado.value}):`,
            ciudadesDisponibles.length
          );

          if (
            ciudadesDisponibles.length === 0 &&
            organizacionStore.ciudades?.length === 0
          ) {
            organizacionStore.loadCiudades();
            return [
              {
                etiqueta: "Cargando ciudades...",
                valor: null,
                disabled: true,
              },
            ];
          }

          if (ciudadesDisponibles.length === 0 && municipioSeleccionado.value) {
            return [
              {
                etiqueta: "No hay ciudades para este municipio",
                valor: null,
                disabled: true,
              },
            ];
          }

          return ciudadesDisponibles
            .filter((ciudad) => ciudad.is_active !== false)
            .map((ciudad) => ({
              etiqueta: ciudad.nombre_ciudad || `Ciudad ${ciudad.id}`,
              valor: ciudad.id,
              datos: ciudad,
            }));

        case "ubicaciones_geograficas":
          const ubicacionesDisponibles = ubicacionesFiltradas.value;
          console.log(
            `📍 Ubicaciones filtradas:`,
            ubicacionesDisponibles.length
          );

          if (
            ubicacionesDisponibles.length === 0 &&
            organizacionStore.ubicacionesGeograficas?.length === 0
          ) {
            organizacionStore.loadUbicacionesGeograficas();
            return [
              {
                etiqueta: "Cargando ubicaciones...",
                valor: null,
                disabled: true,
              },
            ];
          }

          return ubicacionesDisponibles
            .filter((ubicacion) => ubicacion.is_active !== false)
            .map((ubicacion) => ({
              etiqueta:
                ubicacion.nombre_ubicacion || `Ubicación ${ubicacion.id}`,
              valor: ubicacion.id,
              datos: ubicacion,
            }));

        case "estructura_militar":
          const estructuras = organizacionStore.estructuraMilitar || [];
          if (estructuras.length === 0) {
            organizacionStore.loadEstructuraMilitar();
            return [
              {
                etiqueta: "Cargando estructuras...",
                valor: null,
                disabled: true,
              },
            ];
          }
          return estructuras
            .filter((estructura) => estructura.is_active !== false)
            .map((estructura) => ({
              etiqueta: estructura.nombre_unidad || `Unidad ${estructura.id}`,
              valor: estructura.id,
              datos: estructura,
            }));

        default:
          return [];
      }
    };

    // Inicializar formulario ESPECIALIZADO PARA ORGANIZACIÓN
    const inicializarFormulario = async () => {
      cargando.value = true;
      limpiarErrores();
      erroresValidacion.value = {};

      try {
        // ✅ USAR IMPORT DINÁMICO (temporal hasta resolver el estático)
        // Ya está importado al inicio del archivo, usar directamente
        // const { obtenerEsquema } = await import("@/config/esquemaOrganizacion");
        const configuracion = obtenerEsquema(props.esquema);

        if (!configuracion) {
          console.error(
            `❌ Esquema de organización no encontrado: ${props.esquema}`
          );
          return;
        }

        console.log(`✅ Configuración encontrada:`, configuracion);
        configuracionEsquema.value = configuracion;

        // 🔥 CARGA INTELIGENTE POR ESQUEMA - MEJORADA
        await cargarDependenciasEsquema(props.esquema);

        camposConfigurados.value = configuracion.campos
          .map((configuracionCampo) => {
            return analizarConfiguracionCampo(configuracionCampo);
          })
          .filter((config) => config !== null);

        if (props.modo === "crear") {
          datosFormulario.value = obtenerValoresDefecto(configuracion);
        } else {
          datosFormulario.value = { ...props.datosIniciales };

          // 🎯 INICIALIZAR FILTROS PARA MODO EDITAR
          if (props.modo === "editar") {
            paisSeleccionado.value = datosFormulario.value.pais_id || null;
            departamentoSeleccionado.value =
              datosFormulario.value.departamento_id || null;
            municipioSeleccionado.value =
              datosFormulario.value.municipio_id || null;
            ciudadSeleccionada.value = datosFormulario.value.ciudad_id || null;
          }
        }

        console.log(
          `✅ Formulario organización inicializado: ${props.esquema}`
        );
      } catch (error) {
        console.error("❌ Error inicializando formulario organización:", error);
        mostrarNotificacion("errorConexion");
      } finally {
        cargando.value = false;
      }
    };

    // 🚀 CARGA INTELIGENTE DE DEPENDENCIAS - MEJORADA CON VERIFICACIÓN
    const cargarDependenciasEsquema = async (esquema) => {
      try {
        switch (esquema) {
          case "departamentos":
            // Necesita países - CARGA FORZADA
            console.log("🌍 Iniciando carga de países para departamentos...");
            await catalogosStore.loadPaises();

            // Verificar que se cargaron
            const paisesVerificados = catalogosStore.paises || [];
            console.log(
              "✅ Países verificados:",
              paisesVerificados.length,
              paisesVerificados
            );

            if (paisesVerificados.length === 0) {
              console.warn("⚠️ No se cargaron países - reintentando...");
              await catalogosStore.loadPaises();
            }
            break;

          case "municipios":
            // Necesita países y departamentos
            await Promise.all([
              catalogosStore.loadPaises(),
              organizacionStore.loadDepartamentos(),
            ]);
            console.log("🏘️ Países y departamentos cargados para municipios");
            break;

          case "ciudades":
            // Necesita toda la cascada hasta municipios
            await Promise.all([
              catalogosStore.loadPaises(),
              organizacionStore.loadDepartamentos(),
              organizacionStore.loadMunicipios(),
            ]);
            console.log("🏙️ Cascada hasta municipios cargada para ciudades");
            break;

          case "ubicaciones_geograficas":
            // Necesita toda la cascada geográfica
            await Promise.all([
              catalogosStore.loadPaises(),
              organizacionStore.loadDepartamentos(),
              organizacionStore.loadMunicipios(),
              organizacionStore.loadCiudades(),
            ]);
            console.log("🌍 Cascada geográfica completa cargada");
            break;

          case "estructura_militar":
            // Necesita tipos_estructura_militar y ubicaciones
            await Promise.all([
              catalogosStore.loadTiposEstructuraMilitar(),
              organizacionStore.loadUbicacionesGeograficas(),
              catalogosStore.loadPaises(), // Para filtros de ubicaciones
              organizacionStore.loadDepartamentos(),
              organizacionStore.loadMunicipios(),
              organizacionStore.loadCiudades(),
            ]);
            console.log("🏛️ Tipos estructura y cascada geográfica cargados");
            break;

          case "cargos":
            // Necesita estructura_militar
            await organizacionStore.loadEstructuraMilitar();
            console.log("👔 Estructura militar cargada para cargos");
            break;

          case "roles_funcionales":
            // No necesita dependencias externas
            console.log("🎭 Roles funcionales - sin dependencias");
            break;

          default:
            console.log(`ℹ️ Sin dependencias específicas para: ${esquema}`);
        }
      } catch (error) {
        console.error(`❌ Error cargando dependencias para ${esquema}:`, error);
      }
    };

    // 🎯 ACTUALIZAR CAMPO CON FILTRADO JERÁRQUICO
    const actualizarCampo = (nombreCampo, nuevoValor) => {
      console.log(`🔄 Campo actualizado: ${nombreCampo} = ${nuevoValor}`);

      // ✅ ACTUALIZAR EL FORMULARIO
      datosFormulario.value[nombreCampo] = nuevoValor;

      // 🔥 ACTUALIZAR FILTROS JERÁRQUICOS
      actualizarFiltrosJerarquicos(nombreCampo, nuevoValor);

      // ✅ LIMPIAR ERRORES
      if (erroresValidacion.value[nombreCampo]) {
        delete erroresValidacion.value[nombreCampo];
      }
    };

    // 🌍 MANEJO DE EVENTOS ESPECÍFICOS DE ORGANIZACIÓN

    // Auto-llenado cuando selecciona ubicación geográfica
    const manejarUbicacionSeleccionada = (ubicacionData) => {
      console.log(
        "📍 Ubicación seleccionada para auto-llenado:",
        ubicacionData
      );

      const mapaAutollenado = {
        latitud: ubicacionData.latitud,
        longitud: ubicacionData.longitud,
        direccion_referencia: ubicacionData.direccionReferencia,
        altitud_metros: ubicacionData.altitudMetros,
        telefono_principal: ubicacionData.telefonoPrincipal,
        telefono_emergencia: ubicacionData.telefonoEmergencia,
      };

      aplicarAutollenado(mapaAutollenado, `ubicación ${ubicacionData.nombre}`);
    };

    // Auto-llenado cuando selecciona departamento
    const manejarDepartamentoSeleccionado = (departamentoData) => {
      console.log("🏘️ Departamento seleccionado:", departamentoData);

      toast.add({
        severity: "info",
        summary: "Departamento seleccionado",
        detail: `${departamentoData.nombre} configurado correctamente`,
        life: 2500,
      });
    };

    // Auto-llenado cuando selecciona municipio
    const manejarMunicipioSeleccionado = (municipioData) => {
      console.log("🏙️ Municipio seleccionado:", municipioData);

      toast.add({
        severity: "info",
        summary: "Municipio seleccionado",
        detail: `${municipioData.nombre} configurado correctamente`,
        life: 2500,
      });
    };

    // Función helper para aplicar auto-llenado
    const aplicarAutollenado = (mapaAutollenado, nombreReferencia) => {
      Object.keys(mapaAutollenado).forEach((nombreCampo) => {
        if (
          datosFormulario.value.hasOwnProperty(nombreCampo) &&
          mapaAutollenado[nombreCampo]
        ) {
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

      toast.add({
        severity: "success",
        summary: "Auto-llenado completado",
        detail: `Campos llenados automáticamente para ${nombreReferencia}`,
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

          // Actualizar filtros jerárquicos para modo editar
          paisSeleccionado.value = nuevosDatos.pais_id || null;
          departamentoSeleccionado.value = nuevosDatos.departamento_id || null;
          municipioSeleccionado.value = nuevosDatos.municipio_id || null;
          ciudadSeleccionada.value = nuevosDatos.ciudad_id || null;
        }
      },
      { deep: true }
    );

    // Lifecycle
    onMounted(async () => {
      console.log(
        "🛠️ FormularioDinamicoOrganizacion montado para:",
        props.esquema
      );

      // 🔥 DEBUG: Forzar carga de países
      console.log("📡 Estado inicial países:", catalogosStore.paises);

      try {
        await catalogosStore.loadPaises();
        console.log("✅ Países después de cargar:", catalogosStore.paises);
      } catch (error) {
        console.error("❌ Error cargando países:", error);
      }

      // 🔥 DEBUG: Verificar configuración esquema
      console.log(
        "📋 Configuración esquema actual:",
        configuracionEsquema.value
      );

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
      manejarUbicacionSeleccionada,
      manejarDepartamentoSeleccionado,
      manejarMunicipioSeleccionado,
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
.contenedor-formulario-dinamico-organizacion {
  @apply w-full;
}

.formulario-contenido {
  @apply space-y-6;
}

.formulario-dinamico-organizacion {
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

/* Tema púrpura para organización */
.contenedor-formulario-dinamico-organizacion .text-purple-300 {
  color: #d8b4fe;
}

.contenedor-formulario-dinamico-organizacion .bg-purple-800 {
  background-color: #6b21a8;
}

@media (max-width: 768px) {
  .formulario-dinamico-organizacion .grid {
    @apply grid-cols-1 gap-3;
  }
}
</style>
