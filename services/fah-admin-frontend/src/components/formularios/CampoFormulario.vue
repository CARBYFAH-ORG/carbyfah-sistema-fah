<template>
  <!-- COMPONENTE UNIVERSAL DE CAMPO DE FORMULARIO -->
  <!-- Renderiza automáticamente cualquier tipo de campo -->
  <div
    :class="[
      'contenedor-campo',
      `campo-${configuracion.tipo}`,
      configuracion.columnas === 6 ? 'col-span-6' : 'col-span-12',
      { 'campo-con-error': tieneError },
    ]"
  >
    <!-- CAMPO DE TEXTO -->
    <div v-if="esTipoTexto" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <InputText
        :id="idCampo"
        :model-value="valor"
        :placeholder="configuracion.placeholder"
        :maxlength="configuracion.longitudMaxima"
        :disabled="deshabilitado"
        :class="clasesCampoTexto"
        @update:model-value="emitirCambio"
        @blur="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- CAMPO NUMÉRICO -->
    <div v-else-if="esTipoNumero" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <InputNumber
        :id="idCampo"
        :model-value="valor"
        :placeholder="configuracion.placeholder"
        :min="configuracion.minimo"
        :max="configuracion.maximo"
        :disabled="deshabilitado"
        :use-grouping="false"
        :class="clasesCampoNumero"
        @update:model-value="emitirCambio"
        @blur="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- CAMPO DE SELECCIÓN -->
    <div v-else-if="esTipoSeleccion" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <Select
        :id="idCampo"
        :model-value="valor"
        :options="opcionesSeleccion"
        :option-label="'etiqueta'"
        :option-value="'valor'"
        :placeholder="configuracion.placeholder"
        :disabled="deshabilitado"
        :show-clear="!configuracion.requerido"
        :class="clasesCampoSeleccion"
        @update:model-value="emitirCambio"
        @change="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- ✅ CAMPO FORÁNEO AUTOCOMPLETADO - NUEVO Y MEJORADO -->
    <div v-else-if="esTipoForaneoAutocompletado" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <AutoComplete
        :id="idCampo"
        :model-value="valorMostrar"
        :suggestions="sugerenciasAutocompletado"
        :placeholder="configuracion.placeholder"
        :disabled="deshabilitado"
        :class="clasesCampoForaneoAutocompletado"
        option-label="etiqueta"
        option-value="valor"
        force-selection
        complete-on-focus
        :min-length="0"
        @complete="buscarSugerenciasForaneas"
        @update:model-value="manejarCambioForaneo"
        @blur="validarCampo"
      >
        <template #option="slotProps">
          <div class="opcion-foranea-autocompletado">
            <strong>{{ slotProps.option.etiqueta }}</strong>
            <small v-if="slotProps.option.codigo" class="codigo-categoria">
              {{ slotProps.option.codigo }}
            </small>
          </div>
        </template>
      </AutoComplete>

      <div v-if="cargandoDatosForaneos" class="cargando-datos">
        <i class="pi pi-spin pi-spinner text-blue-500"></i>
        <span class="text-sm text-gray-600 ml-2">Cargando opciones...</span>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- ✅ NUEVO: CAMPO AUTOCOMPLETADO API - PARA PAÍSES -->
    <div v-else-if="esTipoAutocompletadoApi" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <AutoComplete
        :id="idCampo"
        :model-value="valorMostrarApi"
        :suggestions="sugerenciasApi"
        :placeholder="configuracion.placeholder"
        :disabled="deshabilitado"
        :class="clasesCampoAutocompletadoApi"
        option-label="nombre"
        :min-length="2"
        :delay="300"
        force-selection
        @complete="buscarPaises"
        @update:model-value="manejarCambioApi"
        @blur="validarCampo"
      >
        <template #option="slotProps">
          <div class="opcion-pais-api">
            <div class="bandera-pais">{{ slotProps.option.bandera }}</div>
            <div class="info-pais">
              <strong>{{ slotProps.option.nombre }}</strong>
              <small class="codigo-pais"
                >{{ slotProps.option.codigo }} -
                {{ slotProps.option.region }}</small
              >
            </div>
          </div>
        </template>
      </AutoComplete>

      <div v-if="cargandoApi" class="cargando-api">
        <i class="pi pi-spin pi-spinner text-blue-500"></i>
        <span class="text-sm text-gray-600 ml-2">Buscando países...</span>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- CAMPO DE ÁREA DE TEXTO -->
    <div v-else-if="esTipoAreaTexto" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <Textarea
        :id="idCampo"
        :model-value="valor"
        :placeholder="configuracion.placeholder"
        :maxlength="configuracion.longitudMaxima"
        :disabled="deshabilitado"
        :rows="configuracion.filas || 3"
        :auto-resize="true"
        :class="clasesCampoAreaTexto"
        @update:model-value="emitirCambio"
        @blur="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- CAMPO BOOLEANO (SWITCH) -->
    <div v-else-if="esTipoBooleano" class="grupo-campo grupo-switch">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <div class="contenedor-switch">
        <InputSwitch
          :id="idCampo"
          :model-value="valor"
          :disabled="deshabilitado"
          :class="clasesCampoSwitch"
          @update:model-value="emitirCambio"
          @change="validarCampo"
        />
        <span class="texto-switch">
          {{ valor ? "Activo" : "Inactivo" }}
        </span>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>
    <!-- CAMPO DE FECHA -->
    <div v-else-if="esTipoFecha" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <Calendar
        :id="idCampo"
        :model-value="valor"
        :placeholder="configuracion.placeholder"
        :disabled="deshabilitado"
        :show-icon="true"
        :date-format="configuracion.formatoFecha || 'dd/mm/yy'"
        :class="clasesCampoFecha"
        @update:model-value="emitirCambio"
        @date-select="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- TIPO DE CAMPO NO SOPORTADO -->
    <div v-else class="grupo-campo">
      <Message severity="warn" :closable="false">
        <strong>Tipo de campo no soportado:</strong>
        <code>{{ configuracion.tipo }}</code>
      </Message>
    </div>
  </div>
</template>

<script>
import { computed, ref, watch, onMounted } from "vue";

// Componentes PrimeVue
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import Checkbox from "primevue/checkbox";
import InputSwitch from "primevue/inputswitch";
import AutoComplete from "primevue/autocomplete";
import Calendar from "primevue/calendar";
import Message from "primevue/message";

// Componentes auxiliares
import MensajeError from "./MensajeError.vue";
import AyudaCampo from "./AyudaCampo.vue";

// Utilidades
import {
  validarCampoIndividual,
  obtenerOpcionesDinamicas,
} from "@/utils/generadorCampos";

// Imports de catalogo
import { useCatalogosStore } from "@/stores/catalogosStore";
import { usarApiPaises } from "@/composables/usarApiPaises";
import * as catalogosService from "@/services/catalogosService";

// Imports de Organizacion
import { useOrganizacionStore } from "@/stores/organizacionStore";
import * as organizacionService from "@/services/organizacionService";

export default {
  name: "CampoFormulario",

  components: {
    InputText,
    InputNumber,
    Select,
    Textarea,
    Checkbox,
    InputSwitch,
    AutoComplete,
    Calendar,
    Message,
    MensajeError,
    AyudaCampo,
  },

  props: {
    configuracion: {
      type: Object,
      required: true,
      validator: (config) => {
        return (
          config &&
          typeof config.nombre === "string" &&
          typeof config.tipo === "string" &&
          typeof config.etiqueta === "string"
        );
      },
    },
    valor: {
      type: [String, Number, Boolean, Date, Array, Object],
      default: null,
    },
    opcionesExternas: {
      type: Array,
      default: () => [],
    },
    error: {
      type: String,
      default: null,
    },
    deshabilitado: {
      type: Boolean,
      default: false,
    },
  },

  emits: ["actualizar", "pais-seleccionado"],

  setup(props, { emit }) {
    // Estado reactivo
    const validandoCampo = ref(false);
    const sugerenciasAutocompletado = ref([]);
    const cargandoDatosForaneos = ref(false);
    const datosForaneosCache = ref(new Map());
    const catalogosStore = useCatalogosStore();
    const organizacionStore = useOrganizacionStore();

    // ✅ NUEVO: Estado para API de países
    const sugerenciasApi = ref([]);
    const cargandoApi = ref(false);
    const paisSeleccionado = ref(null);
    const { buscarPaisesPorNombre } = usarApiPaises();

    // ✅ CARGAR DATOS AL MONTAR COMPONENTE
    onMounted(async () => {
      console.log("🚀 CampoFormulario montado:", props.configuracion.nombre);

      if (esTipoForaneoAutocompletado.value) {
        await cargarDatosForaneos();
      }
    });

    // Computed properties
    const idCampo = computed(() => {
      return `campo-${props.configuracion.nombre}-${Date.now()}`;
    });

    const tieneError = computed(() => {
      return (
        props.error !== null && props.error !== undefined && props.error !== ""
      );
    });

    // Detectores de tipo
    const esTipoTexto = computed(() => {
      return ["texto", "email", "password"].includes(props.configuracion.tipo);
    });

    const esTipoNumero = computed(() => {
      return props.configuracion.tipo === "numero";
    });

    const esTipoSeleccion = computed(() => {
      return props.configuracion.tipo === "seleccion";
    });

    // ✅ NUEVO DETECTOR PARA FORÁNEO AUTOCOMPLETADO
    const esTipoForaneoAutocompletado = computed(() => {
      return props.configuracion.tipo === "foraneo_autocompletado";
    });

    // ✅ NUEVO DETECTOR PARA AUTOCOMPLETADO API
    const esTipoAutocompletadoApi = computed(() => {
      return props.configuracion.tipo === "autocompletado_api";
    });

    const esTipoAreaTexto = computed(() => {
      return props.configuracion.tipo === "area_texto";
    });

    const esTipoBooleano = computed(() => {
      return props.configuracion.tipo === "booleano";
    });

    const esTipoFecha = computed(() => {
      return props.configuracion.tipo === "fecha";
    });

    // ✅ VALOR A MOSTRAR EN AUTOCOMPLETADO - VERSIÓN MEJORADA
    const valorMostrar = computed(() => {
      if (!esTipoForaneoAutocompletado.value || !props.valor) {
        return props.valor;
      }

      const tablaReferencia = props.configuracion.tablaReferencia;
      const valorId = parseInt(props.valor);

      if (isNaN(valorId)) return props.valor;

      // Buscar en datos según la tabla
      let registro = null;

      switch (tablaReferencia) {
        case "categorias_personal":
          registro = catalogosStore.categoriasPersonal?.find(
            (c) => c.id === valorId
          );
          if (registro) {
            return {
              etiqueta: registro.nombre_categoria || registro.codigo_categoria,
              valor: registro.id,
              codigo: registro.codigo_categoria,
            };
          }
          break;

        case "especialidades":
          registro = catalogosStore.especialidades?.find(
            (e) => e.id === valorId
          );
          if (registro) {
            return {
              etiqueta:
                registro.nombre_especialidad || registro.codigo_especialidad,
              valor: registro.id,
              codigo: registro.codigo_especialidad,
            };
          }
          break;

        case "paises":
          registro = catalogosStore.paises?.find((p) => p.id === valorId);
          if (registro) {
            return {
              etiqueta: registro.nombre,
              valor: registro.id,
              codigo: registro.codigo_iso3,
            };
          }
          break;

        case "departamentos":
          registro = organizacionStore.departamentos?.find(
            (d) => d.id === valorId
          );
          if (registro) {
            return {
              etiqueta: registro.nombre_departamento,
              valor: registro.id,
              codigo: registro.codigo_departamento,
            };
          }
          break;

        case "municipios":
          registro = organizacionStore.municipios?.find(
            (m) => m.id === valorId
          );
          if (registro) {
            return {
              etiqueta: registro.nombre_municipio,
              valor: registro.id,
              codigo: registro.codigo_municipio,
            };
          }
          break;

        case "ciudades":
          registro = organizacionStore.ciudades?.find((c) => c.id === valorId);
          if (registro) {
            return {
              etiqueta: registro.nombre_ciudad,
              valor: registro.id,
              codigo: registro.codigo_ciudad,
            };
          }
          break;
      }

      // Si no se encuentra, devolver el ID como fallback
      return `ID: ${props.valor}`;
    });

    // ✅ VALOR A MOSTRAR EN AUTOCOMPLETADO API
    const valorMostrarApi = computed(() => {
      if (!esTipoAutocompletadoApi.value || !props.valor) {
        return props.valor;
      }

      return paisSeleccionado.value || props.valor;
    });

    // Clases CSS dinámicas
    const clasesCampoTexto = computed(() => [
      "fah-form-control",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoNumero = computed(() => [
      "fah-form-control",
      "fah-form-control-number",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoSeleccion = computed(() => [
      "fah-form-control",
      "fah-form-control-select",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    // ✅ CLASES PARA FORÁNEO AUTOCOMPLETADO - AZUL MILITAR DISTINTIVO
    const clasesCampoForaneoAutocompletado = computed(() => [
      "fah-form-control",
      "fah-form-control-foraneo-autocompletado",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    // ✅ CLASES PARA AUTOCOMPLETADO API - VERDE DISTINTIVO PARA PAÍSES
    const clasesCampoAutocompletadoApi = computed(() => [
      "fah-form-control",
      "fah-form-control-autocompletado-api",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoAreaTexto = computed(() => [
      "fah-form-control",
      "fah-form-control-textarea",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoBooleano = computed(() => [
      "fah-form-check-input",
      {
        "fah-form-check-input-error": tieneError.value,
        "fah-form-check-input-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoFecha = computed(() => [
      "fah-form-control",
      "fah-form-control-date",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoSwitch = computed(() => [
      "fah-form-switch",
      {
        "fah-form-switch-error": tieneError.value,
        "fah-form-switch-disabled": props.deshabilitado,
      },
    ]);

    // ✅ OPCIONES PARA CAMPOS DE SELECCIÓN NORMALES
    const opcionesSeleccion = computed(() => {
      if (!esTipoSeleccion.value) {
        return [];
      }

      if (props.opcionesExternas && props.opcionesExternas.length > 0) {
        return props.opcionesExternas;
      }

      if (
        props.configuracion.opciones &&
        Array.isArray(props.configuracion.opciones)
      ) {
        return props.configuracion.opciones;
      }

      if (props.configuracion.tablaReferencia) {
        switch (props.configuracion.tablaReferencia) {
          case "categorias_personal":
            const categorias = catalogosStore.categoriasPersonal || [];

            if (categorias.length > 0) {
              const opciones = categorias
                .filter((cat) => cat.is_active !== false)
                .map((cat) => ({
                  etiqueta:
                    cat.nombre_categoria ||
                    cat.codigo_categoria ||
                    `Categoría ${cat.id}`,
                  valor: cat.id,
                }));
              return opciones;
            }
            break;

          case "especialidades":
            const especialidades = catalogosStore.especialidades || [];
            if (especialidades.length > 0) {
              return especialidades
                .filter((esp) => esp.is_active !== false)
                .map((esp) => ({
                  etiqueta: esp.nombre_especialidad || esp.codigo_especialidad,
                  valor: esp.id,
                }));
            }
            break;

          case "tipos_genero":
            const tiposGenero = catalogosStore.tiposGenero || [];
            if (tiposGenero.length > 0) {
              return tiposGenero
                .filter((tipo) => tipo.is_active !== false)
                .map((tipo) => ({
                  etiqueta: tipo.nombre || tipo.codigo,
                  valor: tipo.id,
                }));
            }
            break;
        }
      }

      return [];
    });

    // ✅ CARGAR DATOS FORÁNEOS
    const cargarDatosForaneos = async () => {
      if (!props.configuracion.tablaReferencia) return;

      const tablaRef = props.configuracion.tablaReferencia;

      // Verificar cache
      if (datosForaneosCache.value.has(tablaRef)) {
        console.log(`✅ Usando cache para ${tablaRef}`);
        return;
      }

      cargandoDatosForaneos.value = true;

      try {
        console.log(`🔄 Cargando datos foráneos: ${tablaRef}`);

        switch (tablaRef) {
          // ============================================
          // 📊 MÓDULOS DE CATÁLOGOS (EXISTENTES)
          // ============================================
          case "categorias_personal":
            if (!catalogosStore.categoriasPersonal?.length) {
              await catalogosStore.loadCategoriasPersonal();
            }
            datosForaneosCache.value.set(
              tablaRef,
              catalogosStore.categoriasPersonal
            );
            break;

          case "especialidades":
            if (!catalogosStore.especialidades?.length) {
              await catalogosStore.loadEspecialidades();
            }
            datosForaneosCache.value.set(
              tablaRef,
              catalogosStore.especialidades
            );
            break;

          case "tipos_genero":
            if (!catalogosStore.tiposGenero?.length) {
              await catalogosStore.loadTiposGenero();
            }
            datosForaneosCache.value.set(tablaRef, catalogosStore.tiposGenero);
            break;

          // 🌍 PAÍSES (CATÁLOGOS)
          case "paises":
            if (!catalogosStore.paises?.length) {
              await catalogosStore.loadPaises();
            }
            datosForaneosCache.value.set(tablaRef, catalogosStore.paises);
            break;

          // ============================================
          // 🏛️ MÓDULOS DE ORGANIZACIÓN (NUEVOS)
          // ============================================

          // 🗺️ DEPARTAMENTOS
          case "departamentos":
            if (!organizacionStore.departamentos?.length) {
              await organizacionStore.loadDepartamentos();
            }
            datosForaneosCache.value.set(
              tablaRef,
              organizacionStore.departamentos
            );
            break;

          // 🏘️ MUNICIPIOS
          case "municipios":
            if (!organizacionStore.municipios?.length) {
              await organizacionStore.loadMunicipios();
            }
            datosForaneosCache.value.set(
              tablaRef,
              organizacionStore.municipios
            );
            break;

          // 🏙️ CIUDADES
          case "ciudades":
            if (!organizacionStore.ciudades?.length) {
              await organizacionStore.loadCiudades();
            }
            datosForaneosCache.value.set(tablaRef, organizacionStore.ciudades);
            break;

          // 📍 UBICACIONES GEOGRÁFICAS
          case "ubicaciones_geograficas":
            if (!organizacionStore.ubicacionesGeograficas?.length) {
              await organizacionStore.loadUbicacionesGeograficas();
            }
            datosForaneosCache.value.set(
              tablaRef,
              organizacionStore.ubicacionesGeograficas
            );
            break;

          // 🏛️ ESTRUCTURA MILITAR
          case "estructura_militar":
            if (!organizacionStore.estructuraMilitar?.length) {
              await organizacionStore.loadEstructuraMilitar();
            }
            datosForaneosCache.value.set(
              tablaRef,
              organizacionStore.estructuraMilitar
            );
            break;

          // 💼 CARGOS
          case "cargos":
            if (!organizacionStore.cargos?.length) {
              await organizacionStore.loadCargos();
            }
            datosForaneosCache.value.set(tablaRef, organizacionStore.cargos);
            break;

          // 🎭 ROLES FUNCIONALES
          case "roles_funcionales":
            if (!organizacionStore.rolesFuncionales?.length) {
              await organizacionStore.loadRolesFuncionales();
            }
            datosForaneosCache.value.set(
              tablaRef,
              organizacionStore.rolesFuncionales
            );
            break;

          // ============================================
          // 📊 CATÁLOGOS ADICIONALES
          // ============================================
          case "tipos_estructura_militar":
            if (!catalogosStore.tiposEstructuraMilitar?.length) {
              await catalogosStore.loadTiposEstructuraMilitar();
            }
            datosForaneosCache.value.set(
              tablaRef,
              catalogosStore.tiposEstructuraMilitar
            );
            break;

          default:
            console.log("⚠️ Tabla de referencia no soportada:", tablaRef);
            break;
        }

        console.log(`✅ Datos foráneos cargados: ${tablaRef}`);
      } catch (error) {
        console.error(`❌ Error cargando datos foráneos ${tablaRef}:`, error);
      } finally {
        cargandoDatosForaneos.value = false;
      }
    };

    // ✅ BUSCAR SUGERENCIAS FORÁNEAS - SIMPLE Y EFECTIVO
    const buscarSugerenciasForaneas = async (evento) => {
      console.log("🔍 Buscando sugerencias foráneas:", evento.query);

      const query = evento.query.toLowerCase();
      const tablaRef = props.configuracion.tablaReferencia;

      // Si query es muy corto, usar búsqueda en API
      if (query.length >= 2) {
        try {
          console.log(`🌐 Búsqueda API para ${tablaRef}: "${query}"`);

          let resultados = [];

          switch (tablaRef) {
            // ============================================
            // 🌐 BÚSQUEDAS API DINÁMICAS (ORGANIZACION)
            // ============================================
            case "departamentos":
              resultados = await organizacionService.buscarDepartamentos(query);
              break;

            case "municipios":
              resultados = await organizacionService.buscarMunicipios(query);
              break;

            case "ciudades":
              resultados = await organizacionService.buscarCiudades(query);
              break;

            case "ubicaciones_geograficas":
              resultados =
                await organizacionService.buscarUbicacionesGeograficas(query);
              break;

            case "estructura_militar":
              resultados = await organizacionService.buscarEstructuraMilitar(
                query
              );
              break;

            case "cargos":
              resultados = await organizacionService.buscarCargos(query);
              break;

            case "roles_funcionales":
              resultados = await organizacionService.buscarRolesFuncionales(
                query
              );
              break;

            // ============================================
            // 📊 BÚSQUEDAS API DINÁMICAS (CATALOGOS)
            // ============================================
            case "paises":
              resultados = await catalogosService.buscarPaises(query);
              break;

            case "tipos_estructura_militar":
              resultados = await catalogosService.buscarTiposEstructuraMilitar(
                query
              );
              break;

            // ============================================
            // 📋 BÚSQUEDAS EN CACHE LOCAL (SIN API)
            // ============================================
            default:
              console.log(`📋 Búsqueda local para ${tablaRef}`);
              await cargarDatosForaneos();
              resultados = buscarEnCacheLocal(query, tablaRef);
              break;
          }

          // Mapear resultados a formato uniforme
          const sugerencias = mapearResultados(resultados, tablaRef);

          console.log(`✅ ${sugerencias.length} sugerencias encontradas`);
          sugerenciasAutocompletado.value = sugerencias;
        } catch (error) {
          console.error(`❌ Error en búsqueda API ${tablaRef}:`, error);
          // Fallback a búsqueda local
          await cargarDatosForaneos();
          const resultadosLocal = buscarEnCacheLocal(query, tablaRef);
          sugerenciasAutocompletado.value = mapearResultados(
            resultadosLocal,
            tablaRef
          );
        }
      } else {
        // Query muy corto, búsqueda local
        await cargarDatosForaneos();
        const resultados = buscarEnCacheLocal(query, tablaRef);
        sugerenciasAutocompletado.value = mapearResultados(
          resultados,
          tablaRef
        );
      }
    };

    // PASO 6: FUNCIÓN AUXILIAR PARA MAPEAR RESULTADOS
    const mapearResultados = (resultados, tablaRef) => {
      return resultados.map((item) => {
        let etiqueta, codigo;

        switch (tablaRef) {
          // CATÁLOGOS
          case "categorias_personal":
            etiqueta = item.nombre_categoria || item.codigo_categoria;
            codigo = item.codigo_categoria;
            break;
          case "especialidades":
            etiqueta = item.nombre_especialidad || item.codigo_especialidad;
            codigo = item.codigo_especialidad;
            break;
          case "tipos_genero":
            etiqueta = item.nombre || item.codigo;
            codigo = item.codigo;
            break;
          case "paises":
            etiqueta = item.nombre;
            codigo = item.codigo_iso3;
            break;
          case "tipos_estructura_militar":
            etiqueta = item.nombre_tipo || item.codigo_tipo;
            codigo = item.codigo_tipo;
            break;

          // ORGANIZACIÓN
          case "departamentos":
            etiqueta = item.nombre_departamento;
            codigo = item.codigo_departamento;
            break;
          case "municipios":
            etiqueta = item.nombre_municipio;
            codigo = item.codigo_municipio;
            break;
          case "ciudades":
            etiqueta = item.nombre_ciudad;
            codigo = item.codigo_ciudad;
            break;
          case "ubicaciones_geograficas":
            etiqueta = item.nombre_ubicacion;
            codigo = item.codigo_ubicacion;
            break;
          case "estructura_militar":
            etiqueta = item.nombre_unidad;
            codigo = item.codigo_unidad;
            break;
          case "cargos":
            etiqueta = item.nombre_cargo;
            codigo = item.codigo_cargo;
            break;
          case "roles_funcionales":
            etiqueta = item.nombre_rol;
            codigo = item.codigo_rol;
            break;

          default:
            etiqueta = item.nombre || item.codigo || `Item ${item.id}`;
            codigo = item.codigo;
            break;
        }

        return {
          etiqueta,
          valor: item.id,
          codigo,
          datos: item,
        };
      });
    };

    // PASO 5: FUNCIÓN AUXILIAR PARA BÚSQUEDA LOCAL
    const buscarEnCacheLocal = (query, tablaRef) => {
      let datosCompletos = [];

      switch (tablaRef) {
        // CATÁLOGOS
        case "categorias_personal":
          datosCompletos = catalogosStore.categoriasPersonal || [];
          break;
        case "especialidades":
          datosCompletos = catalogosStore.especialidades || [];
          break;
        case "tipos_genero":
          datosCompletos = catalogosStore.tiposGenero || [];
          break;
        case "paises":
          datosCompletos = catalogosStore.paises || [];
          break;
        case "tipos_estructura_militar":
          datosCompletos = catalogosStore.tiposEstructuraMilitar || [];
          break;

        // ORGANIZACIÓN
        case "departamentos":
          datosCompletos = organizacionStore.departamentos || [];
          break;
        case "municipios":
          datosCompletos = organizacionStore.municipios || [];
          break;
        case "ciudades":
          datosCompletos = organizacionStore.ciudades || [];
          break;
        case "ubicaciones_geograficas":
          datosCompletos = organizacionStore.ubicacionesGeograficas || [];
          break;
        case "estructura_militar":
          datosCompletos = organizacionStore.estructuraMilitar || [];
          break;
        case "cargos":
          datosCompletos = organizacionStore.cargos || [];
          break;
        case "roles_funcionales":
          datosCompletos = organizacionStore.rolesFuncionales || [];
          break;

        default:
          console.log("⚠️ Tabla no encontrada en cache:", tablaRef);
          return [];
      }

      return datosCompletos.filter((item) => {
        if (item.is_active === false) return false;

        const textosBusqueda = [
          // Campos de catálogos
          item.nombre_categoria,
          item.codigo_categoria,
          item.nombre_especialidad,
          item.codigo_especialidad,
          item.nombre,
          item.codigo,

          // Campos de organización
          item.nombre_departamento,
          item.codigo_departamento,
          item.nombre_municipio,
          item.codigo_municipio,
          item.nombre_ciudad,
          item.codigo_ciudad,
          item.nombre_ubicacion,
          item.codigo_ubicacion,
          item.nombre_unidad,
          item.codigo_unidad,
          item.nombre_cargo,
          item.codigo_cargo,
          item.nombre_rol,
          item.codigo_rol,
          item.nombre_tipo,
          item.codigo_tipo,
        ]
          .filter(Boolean)
          .map((texto) => texto.toLowerCase());

        return textosBusqueda.some((texto) => texto.includes(query));
      });
    };

    // ✅ BUSCAR PAÍSES EN API
    const buscarPaises = async (evento) => {
      const query = evento.query;

      if (query.length < 2) {
        sugerenciasApi.value = [];
        return;
      }

      cargandoApi.value = true;

      try {
        console.log("🌍 Buscando países:", query);
        const paises = await buscarPaisesPorNombre(query);
        sugerenciasApi.value = paises;
        console.log("✅ Países encontrados:", paises.length);
      } catch (error) {
        console.error("❌ Error buscando países:", error);
        sugerenciasApi.value = [];
      } finally {
        cargandoApi.value = false;
      }
    };

    // ✅ MANEJAR CAMBIO FORÁNEO
    // ✅ MANEJAR CAMBIO FORÁNEO - VERSIÓN CORREGIDA
    const manejarCambioForaneo = (nuevoValor) => {
      console.log("🔄 Cambio foráneo:", nuevoValor);

      if (nuevoValor && typeof nuevoValor === "object" && nuevoValor.valor) {
        // El usuario seleccionó de la lista - EMITIR SOLO EL ID
        console.log("📤 Emitiendo ID:", nuevoValor.valor);
        emitirCambio(nuevoValor.valor);
      } else if (typeof nuevoValor === "number") {
        // Valor numérico directo
        emitirCambio(nuevoValor);
      } else if (typeof nuevoValor === "string") {
        // Si es texto, intentar encontrar coincidencia exacta
        const tablaRef = props.configuracion.tablaReferencia;
        const coincidencia = buscarCoincidenciaExacta(nuevoValor, tablaRef);

        if (coincidencia) {
          console.log(
            "📤 Coincidencia encontrada, emitiendo ID:",
            coincidencia.id
          );
          emitirCambio(coincidencia.id);
        } else {
          // No hay coincidencia, emitir null
          console.log("❌ Sin coincidencia, emitiendo null");
          emitirCambio(null);
        }
      } else {
        // Null o undefined
        emitirCambio(null);
      }
    };

    // ✅ NUEVA FUNCIÓN: Buscar coincidencia exacta por nombre
    const buscarCoincidenciaExacta = (texto, tablaRef) => {
      if (!texto || typeof texto !== "string") return null;

      const textoLimpio = texto.toLowerCase().trim();
      let datosCompletos = [];

      // Obtener datos según la tabla
      switch (tablaRef) {
        case "categorias_personal":
          datosCompletos = catalogosStore.categoriasPersonal || [];
          break;
        case "especialidades":
          datosCompletos = catalogosStore.especialidades || [];
          break;
        case "paises":
          datosCompletos = catalogosStore.paises || [];
          break;
        case "departamentos":
          datosCompletos = organizacionStore.departamentos || [];
          break;
        case "municipios":
          datosCompletos = organizacionStore.municipios || [];
          break;
        case "ciudades":
          datosCompletos = organizacionStore.ciudades || [];
          break;
        default:
          return null;
      }

      // Buscar coincidencia exacta
      return datosCompletos.find((item) => {
        const nombres = [
          item.nombre,
          item.nombre_categoria,
          item.nombre_departamento,
          item.nombre_municipio,
          item.nombre_ciudad,
          item.nombre_ubicacion,
          item.nombre_especialidad,
        ].filter(Boolean);

        return nombres.some(
          (nombre) => nombre.toLowerCase().trim() === textoLimpio
        );
      });
    };

    // ✅ MANEJAR CAMBIO API
    const manejarCambioApi = (nuevoValor) => {
      console.log("🌍 Cambio API países:", nuevoValor);

      if (nuevoValor && typeof nuevoValor === "object") {
        // El usuario seleccionó un país de la lista
        paisSeleccionado.value = nuevoValor;
        emitirCambio(nuevoValor.nombre);

        // ✅ EMITIR EVENTO PARA AUTO-LLENAR OTROS CAMPOS
        emit("pais-seleccionado", nuevoValor);
      } else {
        // Texto libre
        paisSeleccionado.value = null;
        emitirCambio(nuevoValor);
      }
    };

    // Emitir cambio de valor
    const emitirCambio = (nuevoValor) => {
      emit("actualizar", props.configuracion.nombre, nuevoValor);
    };

    // Validar campo individual
    const validarCampo = () => {
      if (validandoCampo.value) return;

      validandoCampo.value = true;

      try {
        const resultado = validarCampoIndividual(
          props.configuracion,
          props.valor
        );
      } catch (error) {
        // Error en validación
      } finally {
        validandoCampo.value = false;
      }
    };

    return {
      // Estado
      validandoCampo,
      sugerenciasAutocompletado,
      cargandoDatosForaneos,

      // ✅ NUEVO: Estado API
      sugerenciasApi,
      cargandoApi,
      valorMostrarApi,

      // Computed
      idCampo,
      tieneError,
      valorMostrar,

      // Detectores de tipo
      esTipoTexto,
      esTipoNumero,
      esTipoSeleccion,
      esTipoForaneoAutocompletado,
      esTipoAutocompletadoApi, // ✅ NUEVO
      esTipoAreaTexto,
      esTipoBooleano,
      esTipoFecha,

      // Clases CSS
      clasesCampoTexto,
      clasesCampoNumero,
      clasesCampoSeleccion,
      clasesCampoForaneoAutocompletado,
      clasesCampoAutocompletadoApi, // ✅ NUEVO
      clasesCampoAreaTexto,
      clasesCampoBooleano,
      clasesCampoSwitch,
      clasesCampoFecha,

      // Opciones
      opcionesSeleccion,

      // Métodos
      emitirCambio,
      validarCampo,
      buscarSugerenciasForaneas,
      manejarCambioForaneo,
      buscarPaises, // ✅ NUEVO
      manejarCambioApi, // ✅ NUEVO
    };
  },
};
</script>

<style>
/* =====================================
   IMPORTAR ESTILOS EXTERNOS PROFESIONALES
   ===================================== */
@import "@/styles/components/formularios/campo-formulario.css";

/* =====================================
   🎯 ESTILOS LOCALES ESPECÍFICOS
   Solo para casos muy específicos que no pueden ir en el CSS externo
   ===================================== */

/* Asegurar que los estilos de dropdown tengan la máxima prioridad */
.campo-formulario
  .fah-form-control-foraneo-autocompletado
  :deep(.p-autocomplete-panel) {
  z-index: 999999 !important;
}

.campo-formulario
  .fah-form-control-autocompletado-api
  :deep(.p-autocomplete-panel) {
  z-index: 999999 !important;
}

/* Fix para asegurar que el contenedor tenga la clase correcta */
.contenedor-campo {
  position: relative;
  z-index: 1;
}

/* =====================================
   VARIABLES CSS CUSTOM PARA CONSISTENCIA
   ===================================== */
:root {
  --fah-dropdown-verde-claro: #f7fee7;
  --fah-dropdown-verde-medio: #ecfdf5;
  --fah-dropdown-verde-oscuro: #166534;
  --fah-dropdown-verde-hover: #16a34a;
  --fah-dropdown-verde-activo: #059669;
  --fah-dropdown-borde: #22c55e;
  --fah-dropdown-sombra: rgba(22, 163, 74, 0.4);
}
</style>
