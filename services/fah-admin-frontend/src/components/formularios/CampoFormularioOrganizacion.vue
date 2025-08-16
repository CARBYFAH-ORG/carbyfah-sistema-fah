<template>
  <!-- COMPONENTE CAMPO FORMULARIO ESPECIALIZADO ORGANIZACIÓN -->
  <!-- Versión específica para módulos de organización -->
  <div
    :class="[
      'contenedor-campo-organizacion',
      `campo-${configuracion.tipo}`,
      configuracion.columnas === 6 ? 'col-span-6' : 'col-span-12',
      { 'campo-con-error': tieneError },
    ]"
  >
    <!-- CAMPO DE TEXTO ORGANIZACIÓN -->
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

    <!-- CAMPO NUMÉRICO ORGANIZACIÓN -->
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

    <!-- CAMPO DE SELECCIÓN ORGANIZACIÓN -->
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

    <!-- ✅ CAMPO FORÁNEO AUTOCOMPLETADO ORGANIZACIÓN -->
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

    <!-- CAMPO DE ÁREA DE TEXTO ORGANIZACIÓN -->
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

    <!-- CAMPO BOOLEANO ORGANIZACIÓN -->
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

    <!-- CAMPO DE FECHA ORGANIZACIÓN -->
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

    <!-- TIPO DE CAMPO NO SOPORTADO -->
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

// Componentes PrimeVue
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import InputSwitch from "primevue/inputswitch";
import AutoComplete from "primevue/autocomplete";
import Calendar from "primevue/calendar";
import Message from "primevue/message";

// Componentes auxiliares
import MensajeError from "./MensajeError.vue";
import AyudaCampo from "./AyudaCampo.vue";

// Stores específicos de organización
import { useOrganizacionStore } from "@/stores/organizacionStore";
import { useCatalogosStore } from "@/stores/catalogosStore";

// Services específicos de organización
import * as organizacionService from "@/services/organizacionService";

import { usarCrudDinamico } from "@/composables/usarCrudDinamico.js";

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
    // =====================================
    // STORES ESPECÍFICOS ORGANIZACIÓN
    // =====================================
    const organizacionStore = useOrganizacionStore();
    const catalogosStore = useCatalogosStore();

    // =====================================
    // ESTADO REACTIVO
    // =====================================
    const validandoCampo = ref(false);
    const sugerenciasAutocompletadoOrg = ref([]);
    const cargandoDatosForaneosOrg = ref(false);
    const datosForaneosCacheOrg = ref(new Map());

    // =====================================
    // CARGAR DATOS AL MONTAR
    // =====================================
    onMounted(async () => {
      console.log(
        "🏛️ CampoFormularioOrganizacion montado:",
        props.configuracion.nombre
      );

      if (esTipoForaneoAutocompletado.value) {
        await cargarDatosForaneosOrg();
      }
    });

    // =====================================
    // COMPUTED PROPERTIES
    // =====================================
    const idCampo = computed(() => {
      return `campo-org-${props.configuracion.nombre}-${Date.now()}`;
    });

    const tieneError = computed(() => {
      return (
        props.error !== null && props.error !== undefined && props.error !== ""
      );
    });

    // Detectores de tipo específicos organización
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

    // ✅ VALOR A MOSTRAR EN AUTOCOMPLETADO ORGANIZACIÓN
    const valorMostrarOrg = computed(() => {
      if (!esTipoForaneoAutocompletado.value || !props.valor) {
        return props.valor;
      }

      const tablaReferencia = props.configuracion.tablaReferencia;
      const valorId = parseInt(props.valor);

      if (isNaN(valorId)) return props.valor;

      // Buscar en datos según la tabla de organización
      let registro = null;

      switch (tablaReferencia) {
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

        // También soportar algunos catálogos
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

      return `ID: ${props.valor}`;
    });

    // Clases CSS específicas de organización (tema purple)
    const clasesCampoTextoOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-org-texto",
      {
        "fah-form-control-org-error": tieneError.value,
        "fah-form-control-org-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoNumeroOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-org-numero",
      {
        "fah-form-control-org-error": tieneError.value,
        "fah-form-control-org-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoSeleccionOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-org-select",
      {
        "fah-form-control-org-error": tieneError.value,
        "fah-form-control-org-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoForaneoOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-org-foraneo",
      {
        "fah-form-control-org-error": tieneError.value,
        "fah-form-control-org-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoAreaTextoOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-org-textarea",
      {
        "fah-form-control-org-error": tieneError.value,
        "fah-form-control-org-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoSwitchOrg = computed(() => [
      "fah-form-switch-org",
      {
        "fah-form-switch-org-error": tieneError.value,
        "fah-form-switch-org-disabled": props.deshabilitado,
      },
    ]);

    const clasesCampoFechaOrg = computed(() => [
      "fah-form-control-org",
      "fah-form-control-org-fecha",
      {
        "fah-form-control-org-error": tieneError.value,
        "fah-form-control-org-disabled": props.deshabilitado,
      },
    ]);

    // ✅ OPCIONES PARA SELECCIÓN ORGANIZACIÓN
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

    // =====================================
    // MÉTODOS ESPECÍFICOS ORGANIZACIÓN
    // =====================================

    // Cargar datos foráneos específicos de organización
    const cargarDatosForaneosOrg = async () => {
      if (!props.configuracion.tablaReferencia) return;

      const tablaRef = props.configuracion.tablaReferencia;

      // ✅ NUEVO: Detectar si requiere servicio externo (catalogos)
      const configuracionCompleta = props.configuracion.tipoCampoCompleto || "";
      const requiereCatalogos =
        configuracionCompleta.includes("servicio:catalogos");

      console.log(
        `🛡️ Cargando datos foráneos: ${tablaRef}, requiere catálogos: ${requiereCatalogos}`
      );

      console.log("🔍 DEBUG configuración completa:", props.configuracion);
      console.log(
        "🔍 DEBUG tipoCampoCompleto:",
        props.configuracion.tipoCampoCompleto
      );
      console.log("🔍 DEBUG configuracionCompleta:", configuracionCompleta);

      if (datosForaneosCacheOrg.value.has(tablaRef)) {
        console.log(`✅ Usando cache organización para ${tablaRef}`);
        return;
      }

      cargandoDatosForaneosOrg.value = true;

      try {
        // ✅ NUEVO: Si requiere catálogos, usar servicio de catálogos
        if (requiereCatalogos && tablaRef === "paises") {
          console.log(`🌍 Cargando países desde servicio de catálogos`);

          if (!catalogosStore.paises?.length) {
            await catalogosStore.loadPaises();
          }

          console.log(
            `✅ Países cargados desde catálogos:`,
            catalogosStore.paises
          );

          datosForaneosCacheOrg.value.set(
            tablaRef,
            catalogosStore.paises || []
          );
          return;
        }

        console.log(`🏛️ Cargando datos foráneos organización: ${tablaRef}`);

        switch (tablaRef) {
          case "paises":
            console.log(`🌍 Cargando países desde servicio de catálogos`);

            if (!catalogosStore.paises?.length) {
              await catalogosStore.loadPaises();
            }

            console.log(
              `✅ Países cargados desde catálogos:`,
              catalogosStore.paises
            );

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
            console.log(
              "⚠️ Tabla de referencia organización no soportada:",
              tablaRef
            );
            break;
        }

        console.log(`✅ Datos foráneos organización cargados: ${tablaRef}`);
      } catch (error) {
        console.error(
          `❌ Error cargando datos foráneos organización ${tablaRef}:`,
          error
        );
      } finally {
        cargandoDatosForaneosOrg.value = false;
      }
    };

    // Buscar sugerencias foráneas organización
    const buscarSugerenciasForaneasOrg = async (evento) => {
      console.log("🏛️ Buscando sugerencias organización:", evento.query);

      const query = evento.query.toLowerCase();
      const tablaRef = props.configuracion.tablaReferencia;

      if (query.length >= 2) {
        try {
          console.log(
            `🌐 Búsqueda API organización para ${tablaRef}: "${query}"`
          );

          let resultados = [];

          switch (tablaRef) {
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

            default:
              console.log(`📋 Búsqueda local organización para ${tablaRef}`);
              await cargarDatosForaneosOrg();
              resultados = buscarEnCacheLocalOrg(query, tablaRef);
              break;
          }

          const sugerencias = mapearResultadosOrg(resultados, tablaRef);
          console.log(
            `✅ ${sugerencias.length} sugerencias organización encontradas`
          );
          sugerenciasAutocompletadoOrg.value = sugerencias;
        } catch (error) {
          console.error(
            `❌ Error en búsqueda API organización ${tablaRef}:`,
            error
          );
          await cargarDatosForaneosOrg();
          const resultadosLocal = buscarEnCacheLocalOrg(query, tablaRef);
          sugerenciasAutocompletadoOrg.value = mapearResultadosOrg(
            resultadosLocal,
            tablaRef
          );
        }
      } else {
        await cargarDatosForaneosOrg();
        const resultados = buscarEnCacheLocalOrg(query, tablaRef);
        sugerenciasAutocompletadoOrg.value = mapearResultadosOrg(
          resultados,
          tablaRef
        );
      }
    };

    // Mapear resultados organización
    const mapearResultadosOrg = (resultados, tablaRef) => {
      return resultados.map((item) => {
        let etiqueta, codigo;

        switch (tablaRef) {
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
            etiqueta = item.nombre_tipo || item.codigo_tipo;
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

    // Búsqueda en cache local organización
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
          console.log(
            "⚠️ Tabla no encontrada en cache organización:",
            tablaRef
          );
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
        ]
          .filter(Boolean)
          .map((texto) => texto.toLowerCase());

        return textosBusqueda.some((texto) => texto.includes(query));
      });
    };

    // Manejar cambio foráneo organización
    const manejarCambioForaneoOrg = (nuevoValor) => {
      console.log("🏛️ Cambio foráneo organización:", nuevoValor);

      if (nuevoValor && typeof nuevoValor === "object" && nuevoValor.valor) {
        console.log("📤 Emitiendo ID organización:", nuevoValor.valor);
        emitirCambio(nuevoValor.valor);
      } else if (typeof nuevoValor === "number") {
        emitirCambio(nuevoValor);
      } else if (typeof nuevoValor === "string") {
        const tablaRef = props.configuracion.tablaReferencia;
        const coincidencia = buscarCoincidenciaExactaOrg(nuevoValor, tablaRef);

        if (coincidencia) {
          console.log(
            "📤 Coincidencia organización encontrada, emitiendo ID:",
            coincidencia.id
          );
          emitirCambio(coincidencia.id);
        } else {
          console.log("❌ Sin coincidencia organización, emitiendo null");
          emitirCambio(null);
        }
      } else {
        emitirCambio(null);
      }
    };

    // Buscar coincidencia exacta organización
    const buscarCoincidenciaExactaOrg = (texto, tablaRef) => {
      if (!texto || typeof texto !== "string") return null;

      const textoLimpio = texto.toLowerCase().trim();
      let datosCompletos = [];

      switch (tablaRef) {
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
          return null;
      }

      return datosCompletos.find((item) => {
        const nombres = [
          item.nombre_departamento,
          item.nombre_municipio,
          item.nombre_ciudad,
          item.nombre_ubicacion,
          item.nombre_unidad,
          item.nombre_cargo,
          item.nombre_rol,
          item.nombre,
        ].filter(Boolean);

        return nombres.some(
          (nombre) => nombre.toLowerCase().trim() === textoLimpio
        );
      });
    };

    // Emitir cambio
    const emitirCambio = (nuevoValor) => {
      emit("actualizar", props.configuracion.nombre, nuevoValor);
    };

    // Validar campo
    const validarCampo = () => {
      if (validandoCampo.value) return;
      validandoCampo.value = true;

      try {
        // Validación específica organización
      } finally {
        validandoCampo.value = false;
      }
    };

    return {
      // Estado
      validandoCampo,
      sugerenciasAutocompletadoOrg,
      cargandoDatosForaneosOrg,

      // Computed
      idCampo,
      tieneError,
      valorMostrarOrg,

      // Detectores de tipo
      esTipoTexto,
      esTipoNumero,
      esTipoSeleccion,
      esTipoForaneoAutocompletado,
      esTipoAreaTexto,
      esTipoBooleano,
      esTipoFecha,

      // Clases CSS específicas organización
      clasesCampoTextoOrg,
      clasesCampoNumeroOrg,
      clasesCampoSeleccionOrg,
      clasesCampoForaneoOrg,
      clasesCampoAreaTextoOrg,
      clasesCampoSwitchOrg,
      clasesCampoFechaOrg,

      // Opciones
      opcionesSeleccion,

      // Métodos
      emitirCambio,
      validarCampo,
      buscarSugerenciasForaneasOrg,
      manejarCambioForaneoOrg,
    };
  },
};
</script>

<style>
/* =====================================
   IMPORTAR ESTILOS EXTERNOS ORGANIZACIÓN
   ===================================== */
@import "@/styles/components/formularios/campo-formulario-organizacion.css";

/* =====================================
   🎯 ESTILOS LOCALES ESPECÍFICOS ORGANIZACIÓN
   Solo para casos muy específicos que no pueden ir en el CSS externo
   ===================================== */

/* Asegurar que los estilos de dropdown organización tengan la máxima prioridad */
.contenedor-campo-organizacion
  .fah-form-control-org-foraneo
  :deep(.p-autocomplete-panel) {
  z-index: 999999 !important;
}

/* Fix para asegurar que el contenedor organización tenga la clase correcta */
.contenedor-campo-organizacion {
  position: relative;
  z-index: 1;
}

/* =====================================
   ✅ TÍTULOS BLANCOS COMO EL ORIGINAL
   ===================================== */
.etiqueta-campo-org {
  @apply block mb-2 text-sm font-medium text-white !important;
}

/* =====================================
   VARIABLES CSS CUSTOM PARA CONSISTENCIA ORGANIZACIÓN
   ===================================== */
:root {
  --fah-dropdown-purple-claro: var(--fah-org-purple-50);
  --fah-dropdown-purple-medio: var(--fah-org-purple-100);
  --fah-dropdown-purple-oscuro: var(--fah-org-purple-800);
  --fah-dropdown-purple-hover: var(--fah-org-purple-500);
  --fah-dropdown-purple-activo: var(--fah-org-purple-600);
  --fah-dropdown-purple-borde: var(--fah-org-purple-400);
  --fah-dropdown-purple-sombra: rgba(168, 85, 247, 0.4);
}
</style>
