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
          <!-- Generar campos dinámicamente CON SOPORTE PARA MAPA -->
          <template
            v-for="(configuracionCampo, index) in camposConfigurados"
            :key="configuracionCampo.nombre"
          >
            <!-- Botón de mapa JUSTO ANTES del campo Latitud -->
            <div
              v-if="
                configuracionCampo.nombre === 'latitud' &&
                mostrarBotonMapaGlobal
              "
              class="col-span-12 contenedor-boton-mapa-antes-latitud"
            >
              <Button
                icon="pi pi-map-marker"
                label="📍 Seleccionar en Mapa"
                class="boton-seleccionar-mapa-antes-latitud"
                @click="abrirModalMapa"
                :disabled="cargando"
              />
            </div>

            <!-- Campo normal CORREGIDO -->
            <CampoFormularioOrganizacion
              :configuracion="configuracionCampo"
              :valor="datosFormulario[configuracionCampo.nombre]"
              :error="erroresValidacion[configuracionCampo.nombre]"
              :deshabilitado="cargando"
              :opciones-externas="obtenerOpcionesParaCampo(configuracionCampo)"
              @actualizar="
                (valor) => actualizarCampo(configuracionCampo.nombre, valor)
              "
              @coordenadas-mapa-seleccionadas="actualizarTodasLasCoordenadas"
              @ubicacion-seleccionada="manejarUbicacionSeleccionada"
              @departamento-seleccionado="manejarDepartamentoSeleccionado"
              @municipio-seleccionado="manejarMunicipioSeleccionado"
              @abrir-modal-mapa="abrirModalMapa"
              class="campo-wrapper"
            />
          </template>
        </div>
      </form>
    </div>

    <!-- Modal de mapa para coordenadas -->
    <ModalMapaCoordenadas
      v-model="modalMapaVisible"
      :coordenadas-iniciales="{
        latitud: datosFormulario.latitud || 14.0818,
        longitud: datosFormulario.longitud || -87.2068,
        altitud: datosFormulario.altitud_metros || 0,
      }"
      @coordenadas-seleccionadas="manejarCoordenadasModalMapa"
    />
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
import ModalMapaCoordenadas from "./ModalMapaCoordenadas.vue";

// Composables y utilidades para organización
import { usarFormularioDinamico } from "@/composables/usarFormularioDinamico";
import { useOrganizacionStore } from "@/stores/organizacionStore";
import { useCatalogosStore } from "@/stores/catalogosStore";

// Configuracion de esquemas
import { obtenerEsquema } from "@/config/esquemaOrganizacion";

export default {
  name: "FormularioDinamicoOrganizacion",

  components: {
    ProgressSpinner,
    Message,
    CampoFormularioOrganizacion,
    ModalMapaCoordenadas,
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

    // Estados para modal de mapa
    const modalMapaVisible = ref(false);

    // Refs para filtrado jerarquico
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

    // Computed para mostrar botón de mapa solo en ubicaciones geográficas
    const mostrarBotonMapaGlobal = computed(() => {
      return props.esquema === "ubicaciones_geograficas";
    });

    // Abrir modal de mapa para seleccionar coordenadas
    const abrirModalMapa = () => {
      console.log("Abriendo modal de mapa para seleccionar coordenadas");
      modalMapaVisible.value = true;
    };

    // Manejar coordenadas seleccionadas desde el modal de mapa
    const manejarCoordenadasModalMapa = (coordenadas) => {
      console.log("Coordenadas recibidas del modal de mapa:", coordenadas);

      // Actualizar formulario con las coordenadas seleccionadas
      datosFormulario.value.latitud = coordenadas.latitud;
      datosFormulario.value.longitud = coordenadas.longitud;
      datosFormulario.value.altitud_metros = coordenadas.altitud;

      // Limpiar errores
      delete erroresValidacion.value.latitud;
      delete erroresValidacion.value.longitud;
      delete erroresValidacion.value.altitud_metros;

      toast.add({
        severity: "success",
        summary: "Coordenadas actualizadas",
        detail: `Lat: ${coordenadas.latitud}, Lng: ${coordenadas.longitud}`,
        life: 3000,
      });

      // Cerrar modal
      modalMapaVisible.value = false;
    };

    // Actualizar todas las coordenadas desde el mapa
    const actualizarTodasLasCoordenadas = (coordenadas) => {
      console.log(
        "Actualizando todas las coordenadas desde mapa:",
        coordenadas
      );

      // Actualizar los campos en el formulario
      datosFormulario.value.latitud = coordenadas.latitud;
      datosFormulario.value.longitud = coordenadas.longitud;
      datosFormulario.value.altitud_metros = coordenadas.altitud_metros;

      // Limpiar errores de coordenadas
      delete erroresValidacion.value.latitud;
      delete erroresValidacion.value.longitud;
      delete erroresValidacion.value.altitud_metros;

      // Mostrar notificación de éxito
      toast.add({
        severity: "success",
        summary: "Coordenadas actualizadas",
        detail: `Latitud: ${coordenadas.latitud}, Longitud: ${coordenadas.longitud}, Altitud: ${coordenadas.altitud_metros}m`,
        life: 4000,
      });

      console.log(
        "Formulario actualizado con coordenadas:",
        datosFormulario.value
      );
    };

    // Computed para filtrado jerarquico
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

    // Funcion para detectar cambios en selecciones y filtrar - CORREGIDA
    const actualizarFiltrosJerarquicos = (nombreCampo, valor) => {
      console.log(`Actualizando filtros: ${nombreCampo} = ${valor}`);

      switch (nombreCampo) {
        case "pais_id":
          paisSeleccionado.value = valor;
          // Reset cascada
          departamentoSeleccionado.value = null;
          municipioSeleccionado.value = null;
          ciudadSeleccionada.value = null;

          // Limpiar campos dependientes en el formulario
          if (datosFormulario.value.departamento_id) {
            datosFormulario.value.departamento_id = null;
          }
          if (datosFormulario.value.municipio_id) {
            datosFormulario.value.municipio_id = null;
          }
          if (datosFormulario.value.ciudad_id) {
            datosFormulario.value.ciudad_id = null;
          }
          console.log("País seleccionado, campos dependientes reseteados");
          break;

        case "departamento_id":
          departamentoSeleccionado.value = valor;
          // Reset cascada siguiente
          municipioSeleccionado.value = null;
          ciudadSeleccionada.value = null;

          // Limpiar campos dependientes
          if (datosFormulario.value.municipio_id) {
            datosFormulario.value.municipio_id = null;
          }
          if (datosFormulario.value.ciudad_id) {
            datosFormulario.value.ciudad_id = null;
          }
          console.log(
            "Departamento seleccionado, municipios/ciudades reseteados"
          );
          break;

        case "municipio_id":
          municipioSeleccionado.value = valor;
          // Reset cascada siguiente
          ciudadSeleccionada.value = null;

          // Limpiar campos dependientes
          if (datosFormulario.value.ciudad_id) {
            datosFormulario.value.ciudad_id = null;
          }
          console.log("Municipio seleccionado, ciudades reseteadas");
          break;

        case "ciudad_id":
          ciudadSeleccionada.value = valor;
          console.log("Ciudad seleccionada");
          break;
      }

      // Forzar actualizacion de la UI
      nextTick(() => {
        console.log("UI actualizada con nuevos filtros");
      });
    };

    // Funcion mejorada para obtener opciones CORREGIDA FINAL
    const obtenerOpcionesParaCampo = (configuracionCampo) => {
      console.log(
        "Obteniendo opciones para:",
        configuracionCampo.nombre,
        configuracionCampo.tablaReferencia
      );
      console.log("Esquema actual:", props.esquema);

      if (
        (configuracionCampo.tipo !== "seleccion" &&
          configuracionCampo.tipo !== "foraneo_autocompletado") ||
        !configuracionCampo.tablaReferencia
      ) {
        console.log("Campo no válido para opciones");
        return [];
      }

      // Detectar si estamos en ubicaciones geograficas
      const esUbicacionesGeograficas =
        props.esquema === "ubicaciones_geograficas";
      console.log("Es ubicaciones geográficas:", esUbicacionesGeograficas);

      switch (configuracionCampo.tablaReferencia) {
        // Paises - siempre disponibles
        case "paises":
          const paises = catalogosStore.paises || [];
          console.log("Países disponibles:", paises.length);

          if (paises.length === 0) {
            catalogosStore.loadPaises();
            return [
              { etiqueta: "Cargando países...", valor: null, disabled: true },
            ];
          }

          return paises
            .filter((pais) => pais.is_active !== false)
            .map((pais) => ({
              etiqueta: pais.nombre || `País ${pais.id}`,
              valor: pais.id,
              datos: pais,
            }));

        // Departamentos - Filtrado solo en ubicaciones geograficas
        case "departamentos":
          console.log("Procesando departamentos...");

          // Solo aplicar filtrado en ubicaciones geograficas
          if (esUbicacionesGeograficas) {
            console.log("Aplicando filtrado jerárquico para departamentos");
            console.log(
              "País seleccionado actualmente:",
              paisSeleccionado.value
            );

            if (!paisSeleccionado.value) {
              return [
                {
                  etiqueta: "Primero seleccione un país",
                  valor: null,
                  disabled: true,
                },
              ];
            }

            const departamentosDisponibles = departamentosFiltrados.value;
            console.log(
              `Departamentos filtrados para país ${paisSeleccionado.value}:`,
              departamentosDisponibles.length
            );

            if (departamentosDisponibles.length === 0) {
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
                etiqueta:
                  depto.nombre_departamento || `Departamento ${depto.id}`,
                valor: depto.id,
                datos: depto,
              }));
          } else {
            // Para otros esquemas: mostrar todos los departamentos
            console.log("Mostrando TODOS los departamentos (sin filtrado)");

            const todosDepartamentos = organizacionStore.departamentos || [];

            if (todosDepartamentos.length === 0) {
              organizacionStore.loadDepartamentos();
              return [
                {
                  etiqueta: "Cargando departamentos...",
                  valor: null,
                  disabled: true,
                },
              ];
            }

            return todosDepartamentos
              .filter((depto) => depto.is_active !== false)
              .map((depto) => ({
                etiqueta:
                  depto.nombre_departamento || `Departamento ${depto.id}`,
                valor: depto.id,
                datos: depto,
              }));
          }

        // Municipios - Filtrado solo en ubicaciones geograficas
        case "municipios":
          console.log("Procesando municipios...");

          if (esUbicacionesGeograficas) {
            console.log("Aplicando filtrado jerárquico para municipios");
            console.log(
              "Departamento seleccionado actualmente:",
              departamentoSeleccionado.value
            );

            if (!departamentoSeleccionado.value) {
              return [
                {
                  etiqueta: "Primero seleccione un departamento",
                  valor: null,
                  disabled: true,
                },
              ];
            }

            const municipiosDisponibles = municipiosFiltrados.value;
            console.log(
              `Municipios filtrados para departamento ${departamentoSeleccionado.value}:`,
              municipiosDisponibles.length
            );

            if (municipiosDisponibles.length === 0) {
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
          } else {
            // Para otros esquemas: mostrar todos los municipios
            console.log("Mostrando TODOS los municipios (sin filtrado)");

            const todosMunicipios = organizacionStore.municipios || [];

            if (todosMunicipios.length === 0) {
              organizacionStore.loadMunicipios();
              return [
                {
                  etiqueta: "Cargando municipios...",
                  valor: null,
                  disabled: true,
                },
              ];
            }

            return todosMunicipios
              .filter((municipio) => municipio.is_active !== false)
              .map((municipio) => ({
                etiqueta:
                  municipio.nombre_municipio || `Municipio ${municipio.id}`,
                valor: municipio.id,
                datos: municipio,
              }));
          }

        // Ciudades - Filtrado solo en ubicaciones geograficas
        case "ciudades":
          console.log("Procesando ciudades...");

          if (esUbicacionesGeograficas) {
            console.log("Aplicando filtrado jerárquico para ciudades");
            console.log(
              "Municipio seleccionado actualmente:",
              municipioSeleccionado.value
            );

            if (!municipioSeleccionado.value) {
              return [
                {
                  etiqueta: "Primero seleccione un municipio",
                  valor: null,
                  disabled: true,
                },
              ];
            }

            const ciudadesDisponibles = ciudadesFiltradas.value;
            console.log(
              `Ciudades filtradas para municipio ${municipioSeleccionado.value}:`,
              ciudadesDisponibles.length
            );

            if (ciudadesDisponibles.length === 0) {
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
          } else {
            // Para otros esquemas: mostrar todas las ciudades
            console.log("Mostrando TODAS las ciudades (sin filtrado)");

            const todasCiudades = organizacionStore.ciudades || [];

            if (todasCiudades.length === 0) {
              organizacionStore.loadCiudades();
              return [
                {
                  etiqueta: "Cargando ciudades...",
                  valor: null,
                  disabled: true,
                },
              ];
            }

            return todasCiudades
              .filter((ciudad) => ciudad.is_active !== false)
              .map((ciudad) => ({
                etiqueta: ciudad.nombre_ciudad || `Ciudad ${ciudad.id}`,
                valor: ciudad.id,
                datos: ciudad,
              }));
          }

        // Tipos estructura militar - sin filtrado
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

        // NUEVO: Ubicaciones geograficas - todas disponibles
        case "ubicaciones_geograficas":
          const ubicacionesGeo = organizacionStore.ubicacionesGeograficas || [];
          if (ubicacionesGeo.length === 0) {
            organizacionStore.loadUbicacionesGeograficas();
            return [
              {
                etiqueta: "Cargando ubicaciones...",
                valor: null,
                disabled: true,
              },
            ];
          }
          return ubicacionesGeo
            .filter((ubicacion) => ubicacion.is_active !== false)
            .map((ubicacion) => ({
              etiqueta:
                ubicacion.nombre_ubicacion || `Ubicación ${ubicacion.id}`,
              valor: ubicacion.id,
              datos: ubicacion,
            }));

        // NUEVO: Estructura militar padre - todas las unidades existentes
        case "estructura_militar":
          const estructurasMilitares =
            organizacionStore.estructuraMilitar || [];
          if (estructurasMilitares.length === 0) {
            organizacionStore.loadEstructuraMilitar();
            return [
              {
                etiqueta: "Cargando unidades...",
                valor: null,
                disabled: true,
              },
            ];
          }
          return estructurasMilitares
            .filter((estructura) => estructura.is_active !== false)
            .map((estructura) => ({
              etiqueta:
                estructura.nombre_unidad ||
                `Unidad ${estructura.codigo_unidad}`,
              valor: estructura.id,
              datos: estructura,
            }));

        default:
          console.log(
            "Tabla de referencia no reconocida:",
            configuracionCampo.tablaReferencia
          );
          return [];
      }
    };

    // Inicializar formulario especializado para organizacion
    const inicializarFormulario = async () => {
      cargando.value = true;
      limpiarErrores();
      erroresValidacion.value = {};

      try {
        // Usar import estatico que ya existe arriba
        const configuracion = obtenerEsquema(props.esquema);

        if (!configuracion) {
          console.error(
            `Esquema de organización no encontrado: ${props.esquema}`
          );
          return;
        }

        console.log(`Configuración encontrada:`, configuracion);
        configuracionEsquema.value = configuracion;

        // Cargar dependencias
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

          // Inicializar filtros para modo editar
          if (props.modo === "editar") {
            paisSeleccionado.value = datosFormulario.value.pais_id || null;
            departamentoSeleccionado.value =
              datosFormulario.value.departamento_id || null;
            municipioSeleccionado.value =
              datosFormulario.value.municipio_id || null;
            ciudadSeleccionada.value = datosFormulario.value.ciudad_id || null;
          }
        }

        console.log(`Formulario organización inicializado: ${props.esquema}`);
      } catch (error) {
        console.error("Error inicializando formulario organización:", error);
        mostrarNotificacion("errorConexion");
      } finally {
        cargando.value = false;
      }
    };

    // Carga inteligente de dependencias mejorada con verificacion
    const cargarDependenciasEsquema = async (esquema) => {
      try {
        switch (esquema) {
          case "departamentos":
            // Necesita países - carga forzada
            console.log("Iniciando carga de países para departamentos...");
            await catalogosStore.loadPaises();

            // Verificar que se cargaron
            const paisesVerificados = catalogosStore.paises || [];
            console.log(
              "Países verificados:",
              paisesVerificados.length,
              paisesVerificados
            );

            if (paisesVerificados.length === 0) {
              console.warn("No se cargaron países - reintentando...");
              await catalogosStore.loadPaises();
            }
            break;

          case "municipios":
            // Necesita países y departamentos
            await Promise.all([
              catalogosStore.loadPaises(),
              organizacionStore.loadDepartamentos(),
            ]);
            console.log("Países y departamentos cargados para municipios");
            break;

          case "ciudades":
            // Necesita toda la cascada hasta municipios
            await Promise.all([
              catalogosStore.loadPaises(),
              organizacionStore.loadDepartamentos(),
              organizacionStore.loadMunicipios(),
            ]);
            console.log("Cascada hasta municipios cargada para ciudades");
            break;

          case "ubicaciones_geograficas":
            // Necesita toda la cascada geográfica
            await Promise.all([
              catalogosStore.loadPaises(),
              organizacionStore.loadDepartamentos(),
              organizacionStore.loadMunicipios(),
              organizacionStore.loadCiudades(),
            ]);
            console.log("Cascada geográfica completa cargada");
            break;

          case "estructura_militar":
            // ACTUALIZADO: Necesita tipos_estructura_militar, ubicaciones Y estructura militar existente
            await Promise.all([
              catalogosStore.loadTiposEstructuraMilitar(),
              organizacionStore.loadUbicacionesGeograficas(),
              organizacionStore.loadEstructuraMilitar(), // AGREGADO: Para unidad padre
              catalogosStore.loadPaises(), // Para filtros de ubicaciones
              organizacionStore.loadDepartamentos(),
              organizacionStore.loadMunicipios(),
              organizacionStore.loadCiudades(),
            ]);
            console.log(
              "Tipos estructura, ubicaciones y unidades padre cargados"
            );
            break;

          case "cargos":
            // Necesita estructura_militar
            await organizacionStore.loadEstructuraMilitar();
            console.log("Estructura militar cargada para cargos");
            break;

          case "roles_funcionales":
            // No necesita dependencias externas
            console.log("Roles funcionales - sin dependencias");
            break;

          default:
            console.log(`Sin dependencias específicas para: ${esquema}`);
        }
      } catch (error) {
        console.error(`Error cargando dependencias para ${esquema}:`, error);
      }
    };

    // Actualizar campo con filtrado jerarquico - CORREGIDA
    const actualizarCampo = (nombreCampo, nuevoValor) => {
      console.log(`Campo actualizado: ${nombreCampo} = ${nuevoValor}`);

      // Actualizar el formulario
      datosFormulario.value[nombreCampo] = nuevoValor;

      // IMPORTANTE: Solo aplicar filtros jerárquicos en ubicaciones geográficas
      if (props.esquema === "ubicaciones_geograficas") {
        actualizarFiltrosJerarquicos(nombreCampo, nuevoValor);
      }

      // Limpiar errores
      if (erroresValidacion.value[nombreCampo]) {
        delete erroresValidacion.value[nombreCampo];
      }
    };

    // Manejo de eventos especificos de organizacion

    // Auto-llenado cuando selecciona ubicacion geografica
    const manejarUbicacionSeleccionada = (ubicacionData) => {
      console.log("Ubicación seleccionada para auto-llenado:", ubicacionData);

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
      console.log("Departamento seleccionado:", departamentoData);

      toast.add({
        severity: "info",
        summary: "Departamento seleccionado",
        detail: `${departamentoData.nombre} configurado correctamente`,
        life: 2500,
      });
    };

    // Auto-llenado cuando selecciona municipio
    const manejarMunicipioSeleccionado = (municipioData) => {
      console.log("Municipio seleccionado:", municipioData);

      toast.add({
        severity: "info",
        summary: "Municipio seleccionado",
        detail: `${municipioData.nombre} configurado correctamente`,
        life: 2500,
      });
    };

    // Funcion helper para aplicar auto-llenado
    const aplicarAutollenado = (mapaAutollenado, nombreReferencia) => {
      Object.keys(mapaAutollenado).forEach((nombreCampo) => {
        if (
          datosFormulario.value.hasOwnProperty(nombreCampo) &&
          mapaAutollenado[nombreCampo]
        ) {
          datosFormulario.value[nombreCampo] = mapaAutollenado[nombreCampo];
          console.log(
            `Auto-llenado: ${nombreCampo} = ${mapaAutollenado[nombreCampo]}`
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
        "FormularioDinamicoOrganizacion montado para:",
        props.esquema
      );

      // Debug forzar carga de países
      console.log("Estado inicial países:", catalogosStore.paises);

      try {
        await catalogosStore.loadPaises();
        console.log("Países después de cargar:", catalogosStore.paises);
      } catch (error) {
        console.error("Error cargando países:", error);
      }

      // Debug verificar configuracion esquema
      console.log("Configuración esquema actual:", configuracionEsquema.value);

      inicializarFormulario();
    });

    return {
      // Estado
      cargando,
      datosFormulario,
      erroresValidacion,
      configuracionEsquema,
      camposConfigurados,
      modalMapaVisible,

      // Computed
      nombreEsquema,
      esquemaValido,
      tieneErrores,
      mensajeCarga,
      mostrarBotonMapaGlobal,

      // Métodos
      actualizarCampo,
      actualizarTodasLasCoordenadas,
      abrirModalMapa,
      manejarCoordenadasModalMapa,
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

/* Tema purpura para organizacion */
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

  .contenedor-boton-mapa-antes-latitud {
    margin-bottom: 15px;
  }

  .boton-seleccionar-mapa-antes-latitud {
    font-size: 13px !important;
    padding: 10px 20px !important;
  }
}
</style>
