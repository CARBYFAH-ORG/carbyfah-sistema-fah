<!-- services\fah-admin-frontend\src\components\formularios\CampoFormularioOrganizacion.vue -->
<template>
  <div
    :class="[
      'contenedor-campo-organizacion',
      `campo-${configuracion.tipo}`,
      configuracion.columnas === 6 ? 'col-span-6' : 'col-span-12',
      { 'campo-con-error': tieneError },
    ]"
  >
    <!-- Campo de texto organizacion -->
    <div v-if="esTipoTexto" class="grupo-campo-org">
      <label :for="idCampo" class="etiqueta-campo-org">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido-org"
          >*</span
        >
      </label>

      <InputText
        :id="idCampo"
        :model-value="valor"
        :placeholder="configuracion.placeholder"
        :maxlength="configuracion.longitudMaxima"
        :disabled="deshabilitado"
        :class="clasesCampoTextoOrg"
        @update:model-value="emitirCambio"
        @blur="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo numerico organizacion -->
    <div v-else-if="esTipoNumero" class="grupo-campo-org">
      <label :for="idCampo" class="etiqueta-campo-org">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido-org"
          >*</span
        >
      </label>

      <InputNumber
        :id="idCampo"
        :model-value="valor"
        :placeholder="configuracion.placeholder"
        :min="configuracion.minimo"
        :max="configuracion.maximo"
        :disabled="deshabilitado"
        :use-grouping="false"
        :class="clasesCampoNumeroOrg"
        @update:model-value="emitirCambio"
        @blur="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo de seleccion organizacion -->
    <div v-else-if="esTipoSeleccion" class="grupo-campo-org">
      <label :for="idCampo" class="etiqueta-campo-org">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido-org"
          >*</span
        >
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
        :class="clasesCampoSeleccionOrg"
        @update:model-value="emitirCambio"
        @change="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo foraneo autocompletado organizacion -->
    <div v-else-if="esTipoForaneoAutocompletado" class="grupo-campo-org">
      <label :for="idCampo" class="etiqueta-campo-org">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido-org"
          >*</span
        >
      </label>

      <AutoComplete
        :id="idCampo"
        :model-value="valorMostrarOrg"
        :suggestions="sugerenciasAutocompletadoOrg"
        :placeholder="configuracion.placeholder"
        :disabled="deshabilitado"
        :class="clasesCampoForaneoOrg"
        option-label="etiqueta"
        option-value="valor"
        force-selection
        complete-on-focus
        :min-length="0"
        @complete="buscarSugerenciasForaneasOrg"
        @update:model-value="manejarCambioForaneoOrg"
        @blur="validarCampo"
      >
        <template #option="slotProps">
          <div class="opcion-foranea-organizacion">
            <strong>{{ slotProps.option.etiqueta }}</strong>
            <small v-if="slotProps.option.codigo" class="codigo-organizacion">
              {{ slotProps.option.codigo }}
            </small>
          </div>
        </template>
      </AutoComplete>

      <div v-if="cargandoDatosForaneosOrg" class="cargando-datos-org">
        <i class="pi pi-spin pi-spinner text-purple-500"></i>
        <span class="text-sm text-gray-600 ml-2">Cargando opciones...</span>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo de area de texto organizacion -->
    <div v-else-if="esTipoAreaTexto" class="grupo-campo-org">
      <label :for="idCampo" class="etiqueta-campo-org">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido-org"
          >*</span
        >
      </label>

      <Textarea
        :id="idCampo"
        :model-value="valor"
        :placeholder="configuracion.placeholder"
        :maxlength="configuracion.longitudMaxima"
        :disabled="deshabilitado"
        :rows="configuracion.filas || 3"
        :auto-resize="true"
        :class="clasesCampoAreaTextoOrg"
        @update:model-value="emitirCambio"
        @blur="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo booleano organizacion -->
    <div v-else-if="esTipoBooleano" class="grupo-campo-org grupo-switch-org">
      <label :for="idCampo" class="etiqueta-campo-org">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido-org"
          >*</span
        >
      </label>

      <div class="contenedor-switch-org">
        <InputSwitch
          :id="idCampo"
          :model-value="valor"
          :disabled="deshabilitado"
          :class="clasesCampoSwitchOrg"
          @update:model-value="emitirCambio"
          @change="validarCampo"
        />
        <span class="texto-switch-org">
          {{ valor ? "Activo" : "Inactivo" }}
        </span>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo de fecha organizacion -->
    <div v-else-if="esTipoFecha" class="grupo-campo-org">
      <label :for="idCampo" class="etiqueta-campo-org">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido-org"
          >*</span
        >
      </label>

      <Calendar
        :id="idCampo"
        :model-value="valor"
        :placeholder="configuracion.placeholder"
        :disabled="deshabilitado"
        :show-icon="true"
        :date-format="configuracion.formatoFecha || 'dd/mm/yy'"
        :class="clasesCampoFechaOrg"
        @update:model-value="emitirCambio"
        @date-select="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Tipo de campo no soportado -->
    <div v-else class="grupo-campo-org">
      <Message severity="warn" :closable="false">
        <strong>Tipo de campo no soportado:</strong>
        <code>{{ configuracion.tipo }}</code>
      </Message>
    </div>
  </div>
</template>
<script>
import { computed, ref, watch, onMounted } from "vue";

import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import InputSwitch from "primevue/inputswitch";
import AutoComplete from "primevue/autocomplete";
import Calendar from "primevue/calendar";
import Message from "primevue/message";

import MensajeError from "./MensajeError.vue";
import AyudaCampo from "./AyudaCampo.vue";

import { useOrganizacionStore } from "@/stores/organizacionStore";
import { useCatalogosStore } from "@/stores/catalogosStore";

import * as organizacionService from "@/services/organizacionService";
import * as catalogosService from "@/services/catalogosService";

export default {
  name: "CampoFormularioOrganizacion",

  components: {
    InputText,
    InputNumber,
    Select,
    Textarea,
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

  emits: ["actualizar"],

  setup(props, { emit }) {
    const organizacionStore = useOrganizacionStore();
    const catalogosStore = useCatalogosStore();

    const validandoCampo = ref(false);
    const sugerenciasAutocompletadoOrg = ref([]);
    const cargandoDatosForaneosOrg = ref(false);
    const datosForaneosCacheOrg = ref(new Map());

    onMounted(async () => {
      if (esTipoForaneoAutocompletado.value) {
        await cargarDatosForaneosOrg();
      }
    });

    const idCampo = computed(() => {
      return `campo-org-${props.configuracion.nombre}-${Date.now()}`;
    });

    const tieneError = computed(() => {
      return (
        props.error !== null && props.error !== undefined && props.error !== ""
      );
    });

    // Detectores de tipo especificos organizacion
    const esTipoTexto = computed(() => {
      return ["texto", "email", "password"].includes(props.configuracion.tipo);
    });

    const esTipoNumero = computed(() => {
      return props.configuracion.tipo === "numero";
    });

    const esTipoSeleccion = computed(() => {
      return props.configuracion.tipo === "seleccion";
    });

    const esTipoForaneoAutocompletado = computed(() => {
      return props.configuracion.tipo === "foraneo_autocompletado";
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

    // Clases CSS dinamicas organizacion
    const clasesCampoTextoOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-texto-org",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoNumeroOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-numero-org",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoSeleccionOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-seleccion-org",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoForaneoOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-foraneo-autocompletado-org",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoAreaTextoOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-area-texto-org",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoSwitchOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-switch-org",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoFechaOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-fecha-org",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    // Opciones para seleccion organizacion
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
          case "tipos_estructura_militar":
            const tiposEstructura = catalogosStore.tiposEstructuraMilitar || [];
            if (tiposEstructura.length > 0) {
              return tiposEstructura
                .filter((tipo) => tipo.is_active !== false)
                .map((tipo) => ({
                  etiqueta: tipo.nombre_tipo || tipo.codigo_tipo,
                  valor: tipo.id,
                }));
            }
            break;
        }
      }

      return [];
    });

    // Valor a mostrar en autocompletado organizacion
    const valorMostrarOrg = computed(() => {
      if (!esTipoForaneoAutocompletado.value || !props.valor) {
        return props.valor;
      }

      const tablaReferencia = props.configuracion.tablaReferencia;
      const valorId = parseInt(props.valor);

      if (isNaN(valorId)) return props.valor;

      let registro = null;

      switch (tablaReferencia) {
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

        case "ubicaciones_geograficas":
          registro = organizacionStore.ubicacionesGeograficas?.find(
            (u) => u.id === valorId
          );
          if (registro) {
            return {
              etiqueta: registro.nombre_ubicacion,
              valor: registro.id,
              codigo: registro.codigo_ubicacion,
            };
          }
          break;

        case "estructura_militar":
          registro = organizacionStore.estructuraMilitar?.find(
            (e) => e.id === valorId
          );
          if (registro) {
            return {
              etiqueta: registro.nombre_unidad,
              valor: registro.id,
              codigo: registro.codigo_unidad,
            };
          }
          break;

        case "cargos":
          registro = organizacionStore.cargos?.find((c) => c.id === valorId);
          if (registro) {
            return {
              etiqueta: registro.nombre_cargo,
              valor: registro.id,
              codigo: registro.codigo_cargo,
            };
          }
          break;

        case "roles_funcionales":
          registro = organizacionStore.rolesFuncionales?.find(
            (r) => r.id === valorId
          );
          if (registro) {
            return {
              etiqueta: registro.nombre_rol,
              valor: registro.id,
              codigo: registro.codigo_rol,
            };
          }
          break;

        case "tipos_estructura_militar":
          registro = catalogosStore.tiposEstructuraMilitar?.find(
            (t) => t.id === valorId
          );
          if (registro) {
            return {
              etiqueta: registro.nombre_tipo,
              valor: registro.id,
              codigo: registro.codigo_tipo,
            };
          }
          break;
      }

      return props.valor;
    });

    // Cargar datos foraneos especificos de organizacion
    const cargarDatosForaneosOrg = async () => {
      if (!props.configuracion.tablaReferencia) return;

      const tablaRef = props.configuracion.tablaReferencia;

      const configuracionCompleta = props.configuracion.tipoCampoCompleto || "";
      const requiereCatalogos =
        configuracionCompleta.includes("servicio:catalogos");

      if (datosForaneosCacheOrg.value.has(tablaRef)) {
        return;
      }

      cargandoDatosForaneosOrg.value = true;

      try {
        if (requiereCatalogos && tablaRef === "paises") {
          if (!catalogosStore.paises?.length) {
            await catalogosStore.loadPaises();
          }

          datosForaneosCacheOrg.value.set(
            tablaRef,
            catalogosStore.paises || []
          );
          return;
        }

        switch (tablaRef) {
          case "paises":
            if (!catalogosStore.paises?.length) {
              await catalogosStore.loadPaises();
            }

            datosForaneosCacheOrg.value.set(
              tablaRef,
              catalogosStore.paises || []
            );
            return;

          case "departamentos":
            if (!organizacionStore.departamentos?.length) {
              await organizacionStore.loadDepartamentos();
            }
            datosForaneosCacheOrg.value.set(
              tablaRef,
              organizacionStore.departamentos
            );
            break;

          case "municipios":
            if (!organizacionStore.municipios?.length) {
              await organizacionStore.loadMunicipios();
            }
            datosForaneosCacheOrg.value.set(
              tablaRef,
              organizacionStore.municipios
            );
            break;

          case "ciudades":
            if (!organizacionStore.ciudades?.length) {
              await organizacionStore.loadCiudades();
            }
            datosForaneosCacheOrg.value.set(
              tablaRef,
              organizacionStore.ciudades
            );
            break;

          case "ubicaciones_geograficas":
            if (!organizacionStore.ubicacionesGeograficas?.length) {
              await organizacionStore.loadUbicacionesGeograficas();
            }
            datosForaneosCacheOrg.value.set(
              tablaRef,
              organizacionStore.ubicacionesGeograficas
            );
            break;

          case "estructura_militar":
            if (!organizacionStore.estructuraMilitar?.length) {
              await organizacionStore.loadEstructuraMilitar();
            }
            datosForaneosCacheOrg.value.set(
              tablaRef,
              organizacionStore.estructuraMilitar
            );
            break;

          case "cargos":
            if (!organizacionStore.cargos?.length) {
              await organizacionStore.loadCargos();
            }
            datosForaneosCacheOrg.value.set(tablaRef, organizacionStore.cargos);
            break;

          case "roles_funcionales":
            if (!organizacionStore.rolesFuncionales?.length) {
              await organizacionStore.loadRolesFuncionales();
            }
            datosForaneosCacheOrg.value.set(
              tablaRef,
              organizacionStore.rolesFuncionales
            );
            break;

          case "tipos_estructura_militar":
            if (!catalogosStore.tiposEstructuraMilitar?.length) {
              await catalogosStore.loadTiposEstructuraMilitar();
            }
            datosForaneosCacheOrg.value.set(
              tablaRef,
              catalogosStore.tiposEstructuraMilitar
            );
            break;

          default:
            break;
        }
      } catch (error) {
        console.error("Error cargando datos foraneos organizacion:", error);
      } finally {
        cargandoDatosForaneosOrg.value = false;
      }
    };

    // Formatear sugerencias organizacion
    const formatearSugerenciasOrg = (resultados) => {
      if (!Array.isArray(resultados)) return [];

      return resultados.slice(0, 10).map((item) => {
        let etiqueta, codigo;

        switch (props.configuracion.tablaReferencia) {
          case "paises":
            etiqueta = item.nombre;
            codigo = item.codigo_iso3;
            break;
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
          case "tipos_estructura_militar":
            etiqueta = item.nombre_tipo;
            codigo = item.codigo_tipo;
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

    // Busqueda en cache local organizacion
    const buscarEnCacheLocalOrg = (query, tablaRef) => {
      let datosCompletos = [];

      switch (tablaRef) {
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
        case "tipos_estructura_militar":
          datosCompletos = catalogosStore.tiposEstructuraMilitar || [];
          break;
        default:
          return [];
      }

      return datosCompletos.filter((item) => {
        if (item.is_active === false) return false;

        const textosBusqueda = [
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
          item.nombre,
          item.codigo,
          item.codigo_iso3,
        ]
          .filter(Boolean)
          .map((texto) => texto.toLowerCase());

        return textosBusqueda.some((texto) => texto.includes(query));
      });
    };

    // Buscar sugerencias foraneas organizacion
    const buscarSugerenciasForaneasOrg = async (evento) => {
      const query = evento.query.toLowerCase();
      const tablaRef = props.configuracion.tablaReferencia;

      if (query.length >= 2) {
        try {
          let resultados = [];

          switch (tablaRef) {
            case "paises":
              resultados = await catalogosService.buscarPaises(query);
              break;

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

            case "tipos_estructura_militar":
              resultados = await catalogosService.buscarTiposEstructuraMilitar(
                query
              );
              break;

            default:
              resultados = [];
              break;
          }

          sugerenciasAutocompletadoOrg.value =
            formatearSugerenciasOrg(resultados);
        } catch (error) {
          console.error(`Error buscando ${tablaRef}:`, error);
          sugerenciasAutocompletadoOrg.value = [];
        }
      } else {
        const resultadosLocales = buscarEnCacheLocalOrg(query, tablaRef);
        sugerenciasAutocompletadoOrg.value =
          formatearSugerenciasOrg(resultadosLocales);
      }
    };

    // Manejar cambio foraneo organizacion
    const manejarCambioForaneoOrg = (nuevoValor) => {
      if (nuevoValor && typeof nuevoValor === "object" && nuevoValor.valor) {
        emitirCambio(nuevoValor.valor);
      } else if (typeof nuevoValor === "number") {
        emitirCambio(nuevoValor);
      } else if (typeof nuevoValor === "string") {
        const tablaRef = props.configuracion.tablaReferencia;
        const coincidencia = buscarCoincidenciaExactaOrg(nuevoValor, tablaRef);

        if (coincidencia) {
          emitirCambio(coincidencia.id);
        } else {
          emitirCambio(null);
        }
      } else {
        emitirCambio(null);
      }
    };

    // Buscar coincidencia exacta organizacion
    const buscarCoincidenciaExactaOrg = (textoCompleto, tablaRef) => {
      const resultadosLocales = buscarEnCacheLocalOrg(
        textoCompleto.toLowerCase(),
        tablaRef
      );

      return resultadosLocales.find((item) => {
        const nombres = [
          item.nombre,
          item.nombre_departamento,
          item.nombre_municipio,
          item.nombre_ciudad,
          item.nombre_ubicacion,
          item.nombre_unidad,
          item.nombre_cargo,
          item.nombre_rol,
          item.nombre_tipo,
        ].filter(Boolean);

        return nombres.some(
          (nombre) => nombre.toLowerCase() === textoCompleto.toLowerCase()
        );
      });
    };

    // Emitir cambio con validacion
    const emitirCambio = (nuevoValor) => {
      emit("actualizar", nuevoValor);
    };

    // Validar campo individual
    const validarCampo = () => {
      validandoCampo.value = true;

      setTimeout(() => {
        validandoCampo.value = false;
      }, 500);
    };

    watch(
      () => props.configuracion.tablaReferencia,
      () => {
        if (esTipoForaneoAutocompletado.value) {
          cargarDatosForaneosOrg();
        }
      }
    );

    return {
      validandoCampo,
      sugerenciasAutocompletadoOrg,
      cargandoDatosForaneosOrg,

      idCampo,
      tieneError,
      esTipoTexto,
      esTipoNumero,
      esTipoSeleccion,
      esTipoForaneoAutocompletado,
      esTipoAreaTexto,
      esTipoBooleano,
      esTipoFecha,
      clasesCampoTextoOrg,
      clasesCampoNumeroOrg,
      clasesCampoSeleccionOrg,
      clasesCampoForaneoOrg,
      clasesCampoAreaTextoOrg,
      clasesCampoSwitchOrg,
      clasesCampoFechaOrg,
      opcionesSeleccion,
      valorMostrarOrg,

      cargarDatosForaneosOrg,
      formatearSugerenciasOrg,
      buscarEnCacheLocalOrg,
      buscarSugerenciasForaneasOrg,
      manejarCambioForaneoOrg,
      buscarCoincidenciaExactaOrg,
      emitirCambio,
      validarCampo,
    };
  },
};
</script>

<style scoped>
/* Estilos locales especificos organizacion */
.contenedor-campo-organizacion {
  @apply mb-4;
}

.grupo-campo-org {
  @apply space-y-2;
}

.etiqueta-campo-org {
  @apply block text-sm font-medium text-purple-200 mb-1;
}

.marcador-requerido-org {
  @apply text-red-400 ml-1;
}

.grupo-switch-org {
  @apply flex items-center space-x-4;
}

.contenedor-switch-org {
  @apply flex items-center space-x-3;
}

.texto-switch-org {
  @apply text-sm text-gray-300;
}

.cargando-datos-org {
  @apply flex items-center mt-2 text-purple-300;
}

.opcion-foranea-organizacion {
  @apply block;
}

.codigo-organizacion {
  @apply text-xs text-gray-500 mt-1 block;
}

.campo-con-error {
  @apply ring-2 ring-red-500;
}

@media (max-width: 768px) {
  .grupo-switch-org {
    @apply flex-col items-start space-x-0 space-y-2;
  }
}
</style>
