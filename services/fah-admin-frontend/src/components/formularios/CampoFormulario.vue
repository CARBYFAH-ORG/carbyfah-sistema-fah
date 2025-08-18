<template>
  <!-- Campo universal de formulario -->
  <div
    :class="[
      'contenedor-campo',
      `campo-${configuracion.tipo}`,
      configuracion.columnas === 6 ? 'col-span-6' : 'col-span-12',
      { 'campo-con-error': tieneError },
    ]"
  >
    <!-- Campo de texto -->
    <div v-if="esTipoTexto" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <input
        :id="idCampo"
        :type="
          configuracion.tipo === 'password'
            ? 'password'
            : configuracion.tipo === 'email'
            ? 'email'
            : 'text'
        "
        :value="valor"
        :placeholder="configuracion.placeholder"
        :maxlength="configuracion.longitudMaxima"
        :disabled="deshabilitado"
        :class="clasesCampoTexto"
        @input="emitirCambio($event.target.value)"
        @blur="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo numerico -->
    <div v-else-if="esTipoNumero" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <input
        :id="idCampo"
        type="number"
        :value="valor"
        :placeholder="configuracion.placeholder"
        :min="configuracion.minimo"
        :max="configuracion.maximo"
        :disabled="deshabilitado"
        :class="clasesCampoNumero"
        @input="emitirCambio(parseFloat($event.target.value) || null)"
        @blur="validarCampo"
      />

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo de seleccion -->
    <div v-else-if="esTipoSeleccion" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <div class="select-container">
        <select
          :id="idCampo"
          :value="valor"
          :disabled="deshabilitado"
          :class="clasesCampoSeleccion"
          @change="emitirCambio($event.target.value)"
          @blur="validarCampo"
        >
          <option value="" disabled>
            {{ configuracion.placeholder || "Seleccione una opción" }}
          </option>
          <option
            v-for="opcion in opcionesSeleccion"
            :key="opcion.valor"
            :value="opcion.valor"
          >
            {{ opcion.etiqueta }}
          </option>
        </select>
        <i class="fah-select-arrow">▼</i>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo foraneo autocompletado -->
    <div v-else-if="esTipoForaneoAutocompletado" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <div class="autocomplete-container">
        <input
          :id="idCampo"
          type="text"
          :value="valorTextoMostrar"
          :placeholder="configuracion.placeholder"
          :disabled="deshabilitado"
          :class="clasesCampoForaneoAutocompletado"
          @input="manejarInputAutocompletado"
          @focus="mostrarDropdownForaneo = true"
          @blur="manejarBlurAutocompletado"
          @keydown="manejarTeclasAutocompletado"
          autocomplete="off"
        />

        <!-- Dropdown de sugerencias -->
        <div
          v-if="mostrarDropdownForaneo && sugerenciasAutocompletado.length > 0"
          class="autocomplete-dropdown"
        >
          <div
            v-for="(sugerencia, index) in sugerenciasAutocompletado"
            :key="sugerencia.valor"
            :class="[
              'autocomplete-item',
              { 'autocomplete-item-selected': index === indiceSeleccionado },
            ]"
            @mousedown="seleccionarSugerencia(sugerencia)"
            @mouseenter="indiceSeleccionado = index"
          >
            <strong>{{ sugerencia.etiqueta }}</strong>
            <small v-if="sugerencia.codigo" class="codigo-categoria">
              {{ sugerencia.codigo }}
            </small>
          </div>
        </div>
      </div>

      <div v-if="cargandoDatosForaneos" class="cargando-datos">
        <div class="spinner-fah"></div>
        <span>Cargando opciones...</span>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo autocompletado API para paises -->
    <div v-else-if="esTipoAutocompletadoApi" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <div class="autocomplete-container">
        <input
          :id="idCampo"
          type="text"
          :value="valorMostrarApi"
          :placeholder="configuracion.placeholder"
          :disabled="deshabilitado"
          :class="clasesCampoAutocompletadoApi"
          @input="manejarInputPaises"
          @focus="mostrarDropdownApi = true"
          @blur="manejarBlurPaises"
          @keydown="manejarTeclasPaises"
          autocomplete="off"
        />

        <!-- Dropdown de paises -->
        <div
          v-if="mostrarDropdownApi && sugerenciasApi.length > 0"
          class="autocomplete-dropdown autocomplete-dropdown-paises"
        >
          <div
            v-for="(pais, index) in sugerenciasApi"
            :key="pais.nombre"
            :class="[
              'autocomplete-item',
              'autocomplete-item-pais',
              { 'autocomplete-item-selected': index === indiceSeleccionadoApi },
            ]"
            @mousedown="seleccionarPais(pais)"
            @mouseenter="indiceSeleccionadoApi = index"
          >
            <div class="info-pais">
              <strong>{{ pais.nombre }}</strong>
              <small class="codigo-pais">{{ pais.codigoISO3 }}</small>
            </div>
          </div>
        </div>
      </div>

      <div v-if="cargandoApi" class="cargando-api">
        <div class="spinner-fah"></div>
        <span>Buscando países...</span>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo de area de texto -->
    <div v-else-if="esTipoAreaTexto" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <textarea
        :id="idCampo"
        :value="valor"
        :placeholder="configuracion.placeholder"
        :maxlength="configuracion.longitudMaxima"
        :disabled="deshabilitado"
        :rows="configuracion.filas || 3"
        :class="clasesCampoAreaTexto"
        @input="emitirCambio($event.target.value)"
        @blur="validarCampo"
      ></textarea>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo booleano (switch) -->
    <div v-else-if="esTipoBooleano" class="grupo-campo grupo-switch">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <div class="contenedor-switch">
        <div
          :class="[
            'fah-switch',
            {
              'fah-switch-checked': valor,
              'fah-switch-disabled': deshabilitado,
            },
          ]"
          @click="!deshabilitado && emitirCambio(!valor)"
          role="switch"
          :aria-checked="valor"
          :tabindex="deshabilitado ? -1 : 0"
          @keydown="manejarTeclasSwitch"
        >
          <div class="fah-switch-slider">
            <div class="fah-switch-thumb"></div>
          </div>
        </div>
        <span class="texto-switch">
          {{ valor ? "Activo" : "Inactivo" }}
        </span>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Campo de fecha -->
    <div v-else-if="esTipoFecha" class="grupo-campo">
      <label :for="idCampo" class="etiqueta-campo">
        {{ configuracion.etiqueta }}
        <span v-if="configuracion.requerido" class="marcador-requerido">*</span>
      </label>

      <div class="date-container">
        <input
          :id="idCampo"
          type="date"
          :value="valorFechaFormateado"
          :disabled="deshabilitado"
          :class="clasesCampoFecha"
          @input="emitirCambioFecha($event.target.value)"
          @blur="validarCampo"
        />
        <i class="fah-date-icon">📅</i>
      </div>

      <MensajeError v-if="tieneError" :mensaje="error" />
      <AyudaCampo v-if="configuracion.ayuda" :texto="configuracion.ayuda" />
    </div>

    <!-- Tipo de campo no soportado -->
    <div v-else class="grupo-campo">
      <div class="mensaje-advertencia">
        <strong>Tipo de campo no soportado:</strong>
        <code>{{ configuracion.tipo }}</code>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, watch, onMounted, nextTick } from "vue";

import MensajeError from "./MensajeError.vue";
import AyudaCampo from "./AyudaCampo.vue";

import {
  validarCampoIndividual,
  obtenerOpcionesDinamicas,
} from "@/utils/generadorCampos";

import { useCatalogosStore } from "@/stores/catalogosStore";
import { usarApiPaises } from "@/composables/usarApiPaises";
import * as catalogosService from "@/services/catalogosService";

import { useOrganizacionStore } from "@/stores/organizacionStore";
import * as organizacionService from "@/services/organizacionService";

export default {
  name: "CampoFormulario",

  components: {
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
    const validandoCampo = ref(false);
    const sugerenciasAutocompletado = ref([]);
    const cargandoDatosForaneos = ref(false);
    const datosForaneosCache = ref(new Map());
    const catalogosStore = useCatalogosStore();
    const organizacionStore = useOrganizacionStore();

    // Estados para autocompletado foraneo
    const mostrarDropdownForaneo = ref(false);
    const indiceSeleccionado = ref(-1);
    const valorTextoMostrar = ref("");
    const timeoutBusqueda = ref(null);

    // Estados para API de paises
    const sugerenciasApi = ref([]);
    const cargandoApi = ref(false);
    const paisSeleccionado = ref(null);
    const mostrarDropdownApi = ref(false);
    const indiceSeleccionadoApi = ref(-1);
    const { buscarPaisesPorNombre } = usarApiPaises();

    // Cargar datos al montar componente
    onMounted(async () => {
      if (esTipoForaneoAutocompletado.value) {
        await cargarDatosForaneos();
        actualizarValorTextoMostrar();
      }
    });

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

    const esTipoForaneoAutocompletado = computed(() => {
      return props.configuracion.tipo === "foraneo_autocompletado";
    });

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

    // Valor API para mostrar
    const valorMostrarApi = computed(() => {
      if (!esTipoAutocompletadoApi.value || !props.valor) {
        return props.valor;
      }
      return paisSeleccionado.value?.nombre || props.valor;
    });

    // Formateo de fecha
    const valorFechaFormateado = computed(() => {
      if (!props.valor) return "";
      if (props.valor instanceof Date) {
        return props.valor.toISOString().split("T")[0];
      }
      if (typeof props.valor === "string") {
        // Convertir dd/mm/yyyy a yyyy-mm-dd
        if (props.valor.includes("/")) {
          const partes = props.valor.split("/");
          if (partes.length === 3) {
            return `${partes[2]}-${partes[1].padStart(
              2,
              "0"
            )}-${partes[0].padStart(2, "0")}`;
          }
        }
        return props.valor;
      }
      return "";
    });

    // Clases CSS dinamicas
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

    const clasesCampoForaneoAutocompletado = computed(() => [
      "fah-form-control",
      "fah-form-control-foraneo-autocompletado",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

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

    const clasesCampoFecha = computed(() => [
      "fah-form-control",
      "fah-form-control-date",
      {
        "fah-form-control-error": tieneError.value,
        "fah-form-control-disabled": props.deshabilitado,
      },
    ]);

    // Opciones para seleccion
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
              return categorias
                .filter((cat) => cat.is_active !== false)
                .map((cat) => ({
                  etiqueta:
                    cat.nombre_categoria ||
                    cat.codigo_categoria ||
                    `Categoría ${cat.id}`,
                  valor: cat.id,
                }));
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

    // Cargar datos foraneos
    const cargarDatosForaneos = async () => {
      if (!props.configuracion.tablaReferencia) return;

      const tablaRef = props.configuracion.tablaReferencia;

      if (datosForaneosCache.value.has(tablaRef)) {
        return;
      }

      cargandoDatosForaneos.value = true;

      try {
        switch (tablaRef) {
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

          case "paises":
            if (!catalogosStore.paises?.length) {
              await catalogosStore.loadPaises();
            }
            datosForaneosCache.value.set(tablaRef, catalogosStore.paises);
            break;

          case "departamentos":
            if (!organizacionStore.departamentos?.length) {
              await organizacionStore.loadDepartamentos();
            }
            datosForaneosCache.value.set(
              tablaRef,
              organizacionStore.departamentos
            );
            break;

          case "municipios":
            if (!organizacionStore.municipios?.length) {
              await organizacionStore.loadMunicipios();
            }
            datosForaneosCache.value.set(
              tablaRef,
              organizacionStore.municipios
            );
            break;

          case "ciudades":
            if (!organizacionStore.ciudades?.length) {
              await organizacionStore.loadCiudades();
            }
            datosForaneosCache.value.set(tablaRef, organizacionStore.ciudades);
            break;

          default:
            break;
        }
      } catch (error) {
        console.error("Error cargando datos foraneos:", error);
      } finally {
        cargandoDatosForaneos.value = false;
      }
    };

    // Actualizar valor texto mostrar
    const actualizarValorTextoMostrar = () => {
      if (!esTipoForaneoAutocompletado.value || !props.valor) {
        valorTextoMostrar.value = props.valor || "";
        return;
      }

      const tablaReferencia = props.configuracion.tablaReferencia;
      const valorId = parseInt(props.valor);

      if (isNaN(valorId)) {
        valorTextoMostrar.value = props.valor || "";
        return;
      }

      let registro = null;
      let datosCompletos = [];

      switch (tablaReferencia) {
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
      }

      registro = datosCompletos.find((item) => item.id === valorId);

      if (registro) {
        const etiqueta =
          registro.nombre_categoria ||
          registro.nombre_especialidad ||
          registro.nombre ||
          registro.nombre_departamento ||
          registro.nombre_municipio ||
          registro.nombre_ciudad ||
          `Item ${registro.id}`;
        valorTextoMostrar.value = etiqueta;
      } else {
        valorTextoMostrar.value = `ID: ${props.valor}`;
      }
    };

    // Buscar sugerencias
    const buscarSugerencias = async (query) => {
      if (!query || query.length < 1) {
        sugerenciasAutocompletado.value = [];
        return;
      }

      const tablaRef = props.configuracion.tablaReferencia;
      await cargarDatosForaneos();

      let datosCompletos = [];
      switch (tablaRef) {
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
        case "departamentos":
          datosCompletos = organizacionStore.departamentos || [];
          break;
        case "municipios":
          datosCompletos = organizacionStore.municipios || [];
          break;
        case "ciudades":
          datosCompletos = organizacionStore.ciudades || [];
          break;
      }

      const resultados = datosCompletos.filter((item) => {
        if (item.is_active === false) return false;

        const textosBusqueda = [
          item.nombre_categoria,
          item.codigo_categoria,
          item.nombre_especialidad,
          item.codigo_especialidad,
          item.nombre,
          item.codigo,
          item.nombre_departamento,
          item.codigo_departamento,
          item.nombre_municipio,
          item.codigo_municipio,
          item.nombre_ciudad,
          item.codigo_ciudad,
        ]
          .filter(Boolean)
          .map((texto) => texto.toLowerCase());

        return textosBusqueda.some((texto) =>
          texto.includes(query.toLowerCase())
        );
      });

      sugerenciasAutocompletado.value = resultados.map((item) => {
        let etiqueta, codigo;

        switch (tablaRef) {
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

      indiceSeleccionado.value = -1;
    };

    // Manejar input autocompletado
    const manejarInputAutocompletado = (evento) => {
      const valor = evento.target.value;
      valorTextoMostrar.value = valor;

      if (timeoutBusqueda.value) {
        clearTimeout(timeoutBusqueda.value);
      }

      timeoutBusqueda.value = setTimeout(() => {
        buscarSugerencias(valor);
      }, 300);

      if (!valor) {
        emitirCambio(null);
      }
    };

    // Manejar blur autocompletado
    const manejarBlurAutocompletado = () => {
      setTimeout(() => {
        mostrarDropdownForaneo.value = false;
      }, 200);
    };

    // Seleccionar sugerencia
    const seleccionarSugerencia = (sugerencia) => {
      valorTextoMostrar.value = sugerencia.etiqueta;
      emitirCambio(sugerencia.valor);
      mostrarDropdownForaneo.value = false;
      indiceSeleccionado.value = -1;
    };

    // Manejar teclas autocompletado
    const manejarTeclasAutocompletado = (evento) => {
      if (
        !mostrarDropdownForaneo.value ||
        sugerenciasAutocompletado.value.length === 0
      )
        return;

      switch (evento.key) {
        case "ArrowDown":
          evento.preventDefault();
          indiceSeleccionado.value = Math.min(
            indiceSeleccionado.value + 1,
            sugerenciasAutocompletado.value.length - 1
          );
          break;
        case "ArrowUp":
          evento.preventDefault();
          indiceSeleccionado.value = Math.max(indiceSeleccionado.value - 1, -1);
          break;
        case "Enter":
          evento.preventDefault();
          if (indiceSeleccionado.value >= 0) {
            seleccionarSugerencia(
              sugerenciasAutocompletado.value[indiceSeleccionado.value]
            );
          }
          break;
        case "Escape":
          mostrarDropdownForaneo.value = false;
          indiceSeleccionado.value = -1;
          break;
      }
    };

    // Manejar input paises
    const manejarInputPaises = async (evento) => {
      const valor = evento.target.value;

      emit("actualizar", props.configuracion.nombre, valor);

      if (valor.length < 2) {
        sugerenciasApi.value = [];
        mostrarDropdownApi.value = false;
        return;
      }

      cargandoApi.value = true;

      try {
        const paises = await buscarPaisesPorNombre(valor);
        sugerenciasApi.value = paises;
        mostrarDropdownApi.value = true;
      } catch (error) {
        console.error("Error buscando paises:", error);
        sugerenciasApi.value = [];
      } finally {
        cargandoApi.value = false;
      }
    };

    // Manejar blur paises
    const manejarBlurPaises = () => {
      setTimeout(() => {
        mostrarDropdownApi.value = false;
      }, 200);
    };

    // Seleccionar pais
    const seleccionarPais = (pais) => {
      paisSeleccionado.value = pais;
      emitirCambio(pais.nombre);
      emit("pais-seleccionado", pais);
      mostrarDropdownApi.value = false;
      indiceSeleccionadoApi.value = -1;
    };

    // Manejar teclas paises
    const manejarTeclasPaises = (evento) => {
      if (!mostrarDropdownApi.value || sugerenciasApi.value.length === 0)
        return;

      switch (evento.key) {
        case "ArrowDown":
          evento.preventDefault();
          indiceSeleccionadoApi.value = Math.min(
            indiceSeleccionadoApi.value + 1,
            sugerenciasApi.value.length - 1
          );
          break;
        case "ArrowUp":
          evento.preventDefault();
          indiceSeleccionadoApi.value = Math.max(
            indiceSeleccionadoApi.value - 1,
            -1
          );
          break;
        case "Enter":
          evento.preventDefault();
          if (indiceSeleccionadoApi.value >= 0) {
            seleccionarPais(sugerenciasApi.value[indiceSeleccionadoApi.value]);
          }
          break;
        case "Escape":
          mostrarDropdownApi.value = false;
          indiceSeleccionadoApi.value = -1;
          break;
      }
    };

    // Manejar teclas switch
    const manejarTeclasSwitch = (evento) => {
      if (evento.key === "Enter" || evento.key === " ") {
        evento.preventDefault();
        if (!props.deshabilitado) {
          emitirCambio(!props.valor);
        }
      }
    };

    // Emitir cambio fecha
    const emitirCambioFecha = (valor) => {
      if (!valor) {
        emitirCambio(null);
        return;
      }

      const fecha = new Date(valor);
      emitirCambio(fecha);
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
        const resultado = validarCampoIndividual(
          props.configuracion,
          props.valor
        );
      } catch (error) {
        // Error en validacion
      } finally {
        validandoCampo.value = false;
      }
    };

    // Watch para actualizar el texto mostrado cuando cambie el valor
    watch(
      () => props.valor,
      () => {
        if (esTipoForaneoAutocompletado.value) {
          actualizarValorTextoMostrar();
        }
      }
    );

    return {
      validandoCampo,
      sugerenciasAutocompletado,
      cargandoDatosForaneos,
      mostrarDropdownForaneo,
      indiceSeleccionado,
      valorTextoMostrar,
      sugerenciasApi,
      cargandoApi,
      mostrarDropdownApi,
      indiceSeleccionadoApi,
      valorMostrarApi,

      idCampo,
      tieneError,
      valorFechaFormateado,

      esTipoTexto,
      esTipoNumero,
      esTipoSeleccion,
      esTipoForaneoAutocompletado,
      esTipoAutocompletadoApi,
      esTipoAreaTexto,
      esTipoBooleano,
      esTipoFecha,

      clasesCampoTexto,
      clasesCampoNumero,
      clasesCampoSeleccion,
      clasesCampoForaneoAutocompletado,
      clasesCampoAutocompletadoApi,
      clasesCampoAreaTexto,
      clasesCampoFecha,

      opcionesSeleccion,

      emitirCambio,
      emitirCambioFecha,
      validarCampo,
      manejarInputAutocompletado,
      manejarBlurAutocompletado,
      seleccionarSugerencia,
      manejarTeclasAutocompletado,
      manejarInputPaises,
      manejarBlurPaises,
      seleccionarPais,
      manejarTeclasPaises,
      manejarTeclasSwitch,
    };
  },
};
</script>

<style scoped>
/* Estilos para campo de formulario */

/* Contenedor principal */
.contenedor-campo {
  position: relative;
  margin-bottom: 24px;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.contenedor-campo.col-span-6 {
  width: 100%;
  display: block;
  margin-right: 0;
}

.contenedor-campo.col-span-12 {
  width: 100%;
}

/* Grupo de campo */
.grupo-campo {
  display: flex;
  flex-direction: column;
  width: 100%;
}

/* Etiquetas de campo */
.etiqueta-campo {
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
  line-height: 1.4;
  user-select: none;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
}

.marcador-requerido {
  color: #c1666b;
  font-weight: 700;
  font-size: 16px;
  margin-left: 2px;
}

/* Estilos base para controles */
.fah-form-control {
  width: 100%;
  padding: 12px 16px;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.5;
  color: #343a40;
  background: #ffffff;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  outline: none;
  font-family: inherit;
}

.fah-form-control:hover {
  border-color: #d4af37;
  box-shadow: 0 4px 8px rgba(212, 175, 55, 0.15);
  transform: translateY(-1px);
}

.fah-form-control:focus {
  border-color: #d4af37;
  box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3),
    inset 0 1px 0 rgba(212, 175, 55, 0.2);
  transform: translateY(-1px);
}

.fah-form-control::placeholder {
  color: #6c757d;
  font-style: italic;
  opacity: 0.8;
}

/* Estados de error */
.fah-form-control-error {
  border-color: #c1666b !important;
  background: rgba(193, 102, 107, 0.05);
  box-shadow: 0 2px 8px rgba(193, 102, 107, 0.2) !important;
}

.fah-form-control-error:hover,
.fah-form-control-error:focus {
  border-color: #c1666b !important;
  box-shadow: 0 4px 12px rgba(193, 102, 107, 0.3) !important;
}

.campo-con-error .etiqueta-campo {
  color: #ffffff;
  text-shadow: 1px 1px 2px rgba(193, 102, 107, 0.5);
}

/* Estados deshabilitados */
.fah-form-control-disabled {
  background: #f8f9fa !important;
  border-color: #e9ecef !important;
  color: #6c757d !important;
  cursor: not-allowed;
  opacity: 0.7;
}

.fah-form-control-disabled:hover {
  transform: none !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
}

/* Campo numerico */
.fah-form-control-number {
  text-align: right;
  padding-right: 20px;
}

/* Campo select personalizado */
.select-container {
  position: relative;
}

.fah-form-control-select {
  appearance: none;
  cursor: pointer;
  padding-right: 40px;
  background-image: none;
}

.fah-select-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #d4af37;
  font-size: 12px;
  pointer-events: none;
  transition: all 0.3s ease;
}

.select-container:hover .fah-select-arrow {
  color: #1e3a5f;
  transform: translateY(-50%) scale(1.1);
}

/* Campo textarea */
.fah-form-control-textarea {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

/* Autocompletado personalizado */
.autocomplete-container {
  position: relative;
}

/* Campos foraneos */
.fah-form-control-foraneo-autocompletado {
  border-left: 4px solid #1e3a5f;
  background: linear-gradient(
    90deg,
    rgba(30, 58, 95, 0.02),
    rgba(255, 255, 255, 1) 8%
  );
}

.fah-form-control-foraneo-autocompletado:hover {
  border-left-color: #d4af37;
  background: linear-gradient(
    90deg,
    rgba(212, 175, 55, 0.05),
    rgba(255, 255, 255, 1) 8%
  );
}

.fah-form-control-foraneo-autocompletado:focus {
  border-left-color: #d4af37;
  box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3),
    inset 4px 0 0 rgba(212, 175, 55, 0.3);
}

/* Campos API */
.fah-form-control-autocompletado-api {
  border-left: 4px solid #0ea5e9;
  background: linear-gradient(
    90deg,
    rgba(14, 165, 233, 0.02),
    rgba(255, 255, 255, 1) 8%
  );
}

.fah-form-control-autocompletado-api:hover {
  border-left-color: #0ea5e9;
  background: linear-gradient(
    90deg,
    rgba(14, 165, 233, 0.05),
    rgba(255, 255, 255, 1) 8%
  );
}

.fah-form-control-autocompletado-api:focus {
  border-left-color: #0ea5e9;
  box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3),
    inset 4px 0 0 rgba(14, 165, 233, 0.3);
}

/* Dropdown autocompletado */
.autocomplete-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #ffffff;
  border: 2px solid #d4af37;
  border-radius: 8px;
  box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25), 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  max-height: 200px;
  overflow-y: auto;
  margin-top: 4px;
}

.autocomplete-item {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #e9ecef;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.autocomplete-item:last-child {
  border-bottom: none;
}

.autocomplete-item:hover,
.autocomplete-item-selected {
  background: rgba(212, 175, 55, 0.1);
  transform: translateX(2px);
}

.autocomplete-item strong {
  color: #1e3a5f;
  font-size: 14px;
  font-weight: 600;
}

.codigo-categoria {
  color: #6c757d;
  font-size: 12px;
  font-style: italic;
  background: rgba(212, 175, 55, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
  width: fit-content;
}

/* Dropdown paises */
.autocomplete-dropdown-paises {
  border-color: #0ea5e9;
  box-shadow: 0 8px 25px rgba(14, 165, 233, 0.25), 0 4px 12px rgba(0, 0, 0, 0.1);
}

.autocomplete-item-pais {
  flex-direction: row;
  align-items: center;
  gap: 12px;
}

.bandera-pais {
  font-size: 20px;
  width: 28px;
  text-align: center;
  flex-shrink: 0;
}

.info-pais {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.codigo-pais {
  color: #6c757d;
  font-size: 12px;
  background: rgba(14, 165, 233, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
  width: fit-content;
}

/* Switch personalizado */
.grupo-switch {
  flex-direction: row;
  align-items: center;
  gap: 12px;
}

.contenedor-switch {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
}

.fah-switch {
  position: relative;
  width: 50px;
  height: 24px;
  cursor: pointer;
  outline: none;
  border-radius: 24px;
  transition: all 0.3s ease;
}

.fah-switch-slider {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #e9ecef;
  border: 2px solid #e9ecef;
  border-radius: 24px;
  transition: all 0.3s ease;
}

.fah-switch-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 16px;
  height: 16px;
  background: #ffffff;
  border-radius: 50%;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.fah-switch-checked .fah-switch-slider {
  background: #d4af37;
  border-color: #d4af37;
}

.fah-switch-checked .fah-switch-thumb {
  transform: translateX(26px);
}

.fah-switch:hover .fah-switch-slider {
  background: #6c757d;
  border-color: #6c757d;
}

.fah-switch-checked:hover .fah-switch-slider {
  background: #d4af37;
  border-color: #d4af37;
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
}

.fah-switch-disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

.fah-switch-disabled:hover .fah-switch-slider {
  background: #e9ecef !important;
  border-color: #e9ecef !important;
  box-shadow: none !important;
}

.texto-switch {
  font-size: 14px;
  font-weight: 500;
  color: #495057;
  user-select: none;
}

/* Campo fecha */
.date-container {
  position: relative;
}

.fah-form-control-date {
  cursor: pointer;
}

.fah-date-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #d4af37;
  font-size: 16px;
  pointer-events: none;
}

/* Indicadores de carga */
.cargando-datos,
.cargando-api {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  padding: 8px 12px;
  background: rgba(30, 58, 95, 0.05);
  border: 1px solid rgba(30, 58, 95, 0.1);
  border-radius: 6px;
  font-size: 13px;
  color: #1e3a5f;
}

.spinner-fah {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(212, 175, 55, 0.3);
  border-top: 2px solid #d4af37;
  border-radius: 50%;
  animation: fahSpin 1s linear infinite;
}

@keyframes fahSpin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Mensaje advertencia */
.mensaje-advertencia {
  background: rgba(255, 193, 7, 0.1);
  border: 1px solid #ffc107;
  border-radius: 6px;
  padding: 12px 16px;
  color: #1e3a5f;
  font-size: 14px;
}

.mensaje-advertencia code {
  background: rgba(255, 193, 7, 0.2);
  padding: 2px 6px;
  border-radius: 4px;
  font-family: "Courier New", monospace;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
  .contenedor-campo.col-span-6 {
    width: 100%;
    margin-right: 0;
    margin-bottom: 16px;
  }

  .fah-form-control {
    padding: 10px 14px;
    font-size: 16px;
  }

  .etiqueta-campo {
    font-size: 15px;
  }
}

@media (max-width: 480px) {
  .contenedor-campo {
    margin-bottom: 16px;
  }

  .grupo-switch {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .contenedor-switch {
    margin-top: 4px;
  }

  .autocomplete-item-pais {
    padding: 12px;
  }

  .bandera-pais {
    font-size: 18px;
    width: 24px;
  }
}

/* Animaciones */
@keyframes fahFormFadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fahFormPulseGold {
  0%,
  100% {
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
  }
  50% {
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
  }
}

@keyframes fahFormShakeError {
  0%,
  100% {
    transform: translateX(0);
  }
  10%,
  30%,
  50%,
  70%,
  90% {
    transform: translateX(-2px);
  }
  20%,
  40%,
  60%,
  80% {
    transform: translateX(2px);
  }
}

.contenedor-campo {
  animation: fahFormFadeIn 0.3s ease forwards;
}

.fah-form-control:focus {
  animation: fahFormPulseGold 2s ease-in-out infinite;
}

.fah-form-control-error {
  animation: fahFormShakeError 0.5s ease-in-out;
}

/* Accesibilidad */
.fah-form-control:focus-visible {
  outline: 3px solid rgba(212, 175, 55, 0.5);
  outline-offset: 2px;
}

.fah-switch:focus-visible {
  outline: 3px solid rgba(212, 175, 55, 0.5);
  outline-offset: 2px;
}

.etiqueta-campo:focus-visible {
  outline: 2px solid rgba(212, 175, 55, 0.5);
  outline-offset: 1px;
  border-radius: 4px;
}

/* Scrollbar personalizado */
.autocomplete-dropdown::-webkit-scrollbar {
  width: 6px;
}

.autocomplete-dropdown::-webkit-scrollbar-track {
  background: #f8f9fa;
}

.autocomplete-dropdown::-webkit-scrollbar-thumb {
  background: #d4af37;
  border-radius: 3px;
}

.autocomplete-dropdown::-webkit-scrollbar-thumb:hover {
  background: #1e3a5f;
}

/* Print styles */
@media print {
  .fah-form-control {
    border: 1px solid #343a40 !important;
    box-shadow: none !important;
    background: transparent !important;
  }

  .cargando-datos,
  .cargando-api,
  .spinner-fah {
    display: none !important;
  }

  .autocomplete-dropdown {
    display: none !important;
  }
}
</style>
