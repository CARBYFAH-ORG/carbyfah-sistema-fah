<!-- services\fah-admin-frontend\src\components\formularios\TablaDinamica.vue -->
<template>
  <!-- Controles superiores e inferiores -->
  <div class="tabla-dinamica-contenedor">
    <!-- Header de la tabla -->
    <div
      class="flex justify-between items-center px-6 py-5 bg-gradient-to-r from-slate-50 to-slate-100 border-b-2"
      :style="{ borderBottomColor: coloresEsquema.borderColor }"
    >
      <div class="flex items-center gap-3">
        <div
          class="w-11 h-11 rounded-lg flex items-center justify-center text-xl text-white"
          style="background: #1e3a5f"
        >
          {{ configuracionEsquema?.icono || "📋 " }}
        </div>
        <div>
          <h3 class="m-0 text-gray-800 text-lg font-semibold">
            {{ configuracionEsquema?.titulo || "Catálogo" }}
          </h3>
          <p class="m-0 text-sm text-gray-600">
            {{ contadorRegistros }}
          </p>
        </div>
      </div>
    </div>

    <!-- Controles superiores con ordenamiento -->
    <div class="tabla-controles-superiores tabla-controles-con-ordenamiento">
      <!-- Botones izquierda -->
      <div class="controles-izquierda">
        <button
          @click="abrirModalCrear"
          :disabled="cargando"
          class="btn-fah btn-fah-agregar"
        >
          <i class="pi pi-plus"></i>
          Agregar Nuevo
        </button>

        <button
          @click="recargarDatos"
          :disabled="cargando"
          class="btn-fah btn-fah-actualizar"
        >
          <i class="pi pi-refresh" :class="{ 'pi-spin': cargando }"></i>
          Actualizar
        </button>
      </div>

      <!-- Buscador centro -->
      <div class="controles-centro">
        <div class="busqueda-container">
          <input
            type="text"
            class="input-busqueda"
            placeholder="🔍 Buscar en tabla..."
            v-model="filtroTexto"
            @input="filtrarRegistros"
          />
          <i class="pi pi-search busqueda-icon"></i>
        </div>
      </div>

      <!-- Selector registros derecha -->
      <div class="controles-derecha">
        <label for="registrosPorPagina" class="label-registros">Mostrar:</label>
        <select
          id="registrosPorPagina"
          class="select-registros"
          v-model="registrosPorPagina"
          @change="cambiarRegistrosPorPagina"
        >
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="50">50</option>
        </select>
        <span class="label-registros">registros</span>
      </div>

      <!-- Selectores de ordenamiento -->
      <div class="controles-ordenamiento">
        <label class="label-ordenamiento">Ordenar por:</label>
        <select
          v-model="campoOrdenamiento"
          @change="cambiarOrdenamiento"
          class="select-ordenamiento"
        >
          <option
            v-for="opcion in opcionesOrdenamiento"
            :key="opcion.value"
            :value="opcion.value"
          >
            {{ opcion.label }}
          </option>
        </select>

        <select
          v-model="tipoOrdenamiento"
          @change="cambiarOrdenamiento"
          class="select-orden-tipo"
        >
          <option
            v-for="tipo in tiposOrdenamiento"
            :key="tipo.value"
            :value="tipo.value"
          >
            {{ tipo.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="p-6 bg-gray-100 relative">
      <!-- Overlay de carga -->
      <div v-if="cargando" class="loading-overlay">
        <div class="loading-content">
          <ProgressSpinner size="50" strokeWidth="4" />
          <span class="loading-text">Cargando datos...</span>
        </div>
      </div>

      <!-- Estado de error -->
      <div v-else-if="error" class="text-center py-12">
        <Message severity="error" :closable="false">
          <div>
            <strong>⚠️ Error al cargar datos</strong>
            <p class="mt-2">{{ error }}</p>
            <Button
              label="Reintentar"
              icon="pi pi-refresh"
              @click="recargarDatos"
              class="mt-3"
            />
          </div>
        </Message>
      </div>

      <!-- Tabla con datos -->
      <div
        v-else-if="registrosFiltrados?.length > 0"
        class="overflow-x-auto rounded-lg border-2"
        style="border-color: #1e3a5f"
      >
        <table class="tabla-fah">
          <!-- Encabezados dinamicos -->
          <thead>
            <tr>
              <th
                v-for="campo in camposMostrar"
                :key="campo.nombre"
                style="background: #1e3a5f; color: #ffffff"
              >
                {{ campo.etiqueta }}
              </th>
              <th style="background: #1e3a5f; color: #ffffff">Acciones</th>
            </tr>
          </thead>

          <!-- Filas dinamicas -->
          <tbody>
            <tr
              v-for="registro in registrosPaginados"
              :key="registro.id || registro.codigo"
            >
              <!-- Celdas dinamicas -->
              <td v-for="campo in camposMostrar" :key="campo.nombre">
                <!-- Relacion foranea -->
                <div v-if="esRelacionForanea(campo)" class="contenido-relacion">
                  <div
                    v-if="cargandoRelaciones"
                    class="flex items-center gap-2"
                  >
                    <i class="pi pi-spin pi-spinner text-blue-500 text-xs"></i>
                    <span class="text-xs text-gray-500">Cargando...</span>
                  </div>

                  <div v-else class="relacion-datos">
                    <div class="nombre-relacion">
                      {{ obtenerNombreRelacion(registro, campo) }}
                    </div>
                    <div
                      v-if="obtenerCodigoRelacion(registro, campo)"
                      class="codigo-relacion"
                    >
                      {{ obtenerCodigoRelacion(registro, campo) }}
                    </div>
                  </div>
                </div>

                <!-- Booleano -->
                <div
                  v-else-if="esBooleano(campo, registro)"
                  class="contenido-booleano"
                >
                  <span :class="claseBooleano(registro[campo.nombre])">
                    <i :class="iconoBooleano(registro[campo.nombre])"></i>
                    {{ textoBooleano(registro[campo.nombre]) }}
                  </span>
                </div>

                <!-- Fecha -->
                <div
                  v-else-if="esFecha(campo, registro)"
                  class="contenido-fecha"
                >
                  {{ formatearFecha(registro[campo.nombre]) }}
                </div>

                <!-- Numero -->
                <div
                  v-else-if="esNumero(campo, registro)"
                  class="contenido-numero"
                >
                  {{ formatearNumero(registro[campo.nombre]) }}
                </div>

                <!-- URL -->
                <div v-else-if="esUrl(campo, registro)" class="contenido-url">
                  <a
                    :href="registro[campo.nombre]"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="link-url"
                  >
                    <i class="pi pi-external-link mr-1"></i>
                    Ver enlace
                  </a>
                </div>

                <!-- Texto -->
                <div v-else class="contenido-texto">
                  {{ obtenerValorMostrar(registro[campo.nombre]) }}
                </div>
              </td>

              <!-- Celda de acciones -->
              <td class="whitespace-nowrap">
                <div class="flex gap-1">
                  <button
                    @click="abrirModalEditar(registro)"
                    :disabled="cargando"
                    class="btn-fah btn-fah-editar"
                    style="padding: 6px 10px; font-size: 12px"
                    title="Editar"
                  >
                    <i class="pi pi-pencil"></i>
                  </button>

                  <button
                    @click="abrirModalEliminar(registro)"
                    :disabled="cargando"
                    class="btn-fah btn-fah-eliminar"
                    style="padding: 6px 10px; font-size: 12px"
                    title="Eliminar"
                  >
                    <i class="pi pi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estado vacio -->
      <div v-else class="estado-vacio">
        <div class="estado-vacio-icono">
          {{ configuracionEsquema?.icono || "📄" }}
        </div>
        <h4 class="estado-vacio-titulo">Sin registros disponibles</h4>
        <p class="estado-vacio-descripcion">
          No hay
          {{ configuracionEsquema?.titulo?.toLowerCase() || "registros" }} para
          mostrar
          <span v-if="filtroTexto"> que coincidan con "{{ filtroTexto }}"</span>
        </p>
        <button
          @click="limpiarFiltros"
          v-if="filtroTexto"
          class="btn-fah btn-fah-actualizar"
          style="margin-right: 10px"
        >
          <i class="pi pi-filter-slash"></i>
          Limpiar Filtros
        </button>
        <button @click="abrirModalCrear" class="btn-fah btn-fah-agregar">
          <i class="pi pi-plus"></i>
          Crear el primer registro
        </button>
      </div>
    </div>

    <!-- Controles inferiores de paginacion -->
    <div
      v-if="registrosFiltrados?.length > 0"
      class="tabla-controles-inferiores"
    >
      <!-- Info de registros -->
      <div class="info-registros">
        Mostrando <span>{{ registroInicio }}</span> a
        <span>{{ registroFin }}</span> de
        <span>{{ totalRegistrosFiltrados }}</span> registros
        <span v-if="filtroTexto" class="text-sm text-gray-500">
          (filtrados de {{ registrosActuales.length }} totales)
        </span>
      </div>

      <!-- Paginacion -->
      <div class="paginacion" v-if="totalPaginas > 1">
        <button
          class="btn-paginacion"
          @click="irAPagina(paginaActual - 1)"
          :disabled="paginaActual <= 1"
        >
          ⬅️ Anterior
        </button>

        <div class="numeros-pagina">
          <!-- Primera pagina -->
          <button
            v-if="paginaActual > 3"
            class="btn-pagina"
            @click="irAPagina(1)"
          >
            1
          </button>

          <!-- Puntos suspensivos izquierda -->
          <span v-if="paginaActual > 4" class="dots">...</span>

          <!-- Paginas alrededor de la actual -->
          <button
            v-for="pagina in paginasVisibles"
            :key="pagina"
            class="btn-pagina"
            :class="{ activa: pagina === paginaActual }"
            @click="irAPagina(pagina)"
          >
            {{ pagina }}
          </button>

          <!-- Puntos suspensivos derecha -->
          <span v-if="paginaActual < totalPaginas - 3" class="dots">...</span>

          <!-- Ultima pagina -->
          <button
            v-if="paginaActual < totalPaginas - 2"
            class="btn-pagina"
            @click="irAPagina(totalPaginas)"
          >
            {{ totalPaginas }}
          </button>
        </div>

        <button
          class="btn-paginacion"
          @click="irAPagina(paginaActual + 1)"
          :disabled="paginaActual >= totalPaginas"
        >
          Siguiente ➡️
        </button>
      </div>
    </div>

    <!-- Modal para catalogos unicamente -->
    <ModalFormulario
      v-model:visible="modalVisible"
      :esquema="esquema"
      :modo="modalModo"
      :datos-iniciales="modalDatos"
      @guardado="manejarGuardado"
      @eliminado="manejarEliminado"
      @cancelado="manejarCancelado"
      @error="manejarError"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from "vue";
import { useToastFAH } from "@/composables/useToastFAH";

import ProgressSpinner from "primevue/progressspinner";
import Message from "primevue/message";
import Button from "primevue/button";

import ModalFormulario from "./ModalFormulario.vue";
import { useCatalogosStore } from "@/stores/catalogosStore";

import { usarCrudDinamico } from "@/composables/usarCrudDinamico";
import { usarFormularioDinamico } from "@/composables/usarFormularioDinamico";
import { obtenerEsquema } from "@/config/esquemaCatalogos";

export default {
  name: "TablaDinamica",

  components: {
    ProgressSpinner,
    Message,
    Button,
    ModalFormulario,
  },

  props: {
    esquema: {
      type: String,
      required: true,
    },
    datos: {
      type: Array,
      default: () => [],
    },
    cargando: {
      type: Boolean,
      default: false,
    },
    error: {
      type: String,
      default: null,
    },
    tema: {
      type: String,
      default: "militar-oscuro",
    },
    permitirCrud: {
      type: Boolean,
      default: true,
    },
  },

  emits: ["recargar", "creado", "actualizado", "eliminado", "error"],

  setup(props, { emit }) {
    const toast = useToastFAH();
    const catalogosStore = useCatalogosStore();

    const {
      registros,
      cargarDatos,
      crearRegistro,
      actualizarRegistro,
      eliminarRegistro,
    } = usarCrudDinamico(props.esquema);

    const { analizarConfiguracionCampo } = usarFormularioDinamico();

    const modalVisible = ref(false);
    const modalModo = ref("crear");
    const modalDatos = ref({});
    const cargandoRelaciones = ref(false);

    // Estados para filtros y paginacion
    const filtroTexto = ref("");
    const registrosPorPagina = ref(10);
    const paginaActual = ref(1);

    // Variables para ordenamiento
    const campoOrdenamiento = ref("default");
    const tipoOrdenamiento = ref("asc");

    // Configuracion del esquema
    const configuracionEsquema = computed(() => {
      return obtenerEsquema(props.esquema);
    });

    // Opciones de ordenamiento dinamicas por tabla
    const opcionesOrdenamiento = computed(() => {
      const tabla = configuracionEsquema.value?.tabla;

      const opcionesComunes = [
        { label: "Por defecto", value: "default" },
        { label: "Fecha de creación", value: "created_at" },
        { label: "Alfabético por nombre", value: "alfabetico" },
      ];

      // Opciones especificas por tabla
      switch (tabla) {
        case "tipos_estructura_militar":
          return [
            ...opcionesComunes,
            { label: "Nivel Organizacional", value: "nivel_organizacional" },
            { label: "Código Tipo", value: "codigo_tipo" },
          ];

        case "grados":
          return [
            ...opcionesComunes,
            { label: "Orden Jerárquico", value: "orden_jerarquico" },
            { label: "Categoría Personal", value: "categoria_personal_id" },
          ];

        case "categorias_personal":
          return [
            ...opcionesComunes,
            { label: "Orden Jerárquico", value: "orden_jerarquico" },
          ];

        case "paises":
          return [
            ...opcionesComunes,
            { label: "Código ISO3", value: "codigo_iso3" },
            { label: "Código Teléfono", value: "codigo_telefono" },
          ];

        case "niveles_prioridad":
        case "niveles_seguridad":
          return [
            ...opcionesComunes,
            { label: "Nivel Numérico", value: "nivel_numerico" },
          ];

        default:
          return opcionesComunes;
      }
    });

    // Opciones de tipo de ordenamiento
    const tiposOrdenamiento = ref([
      { label: "Menor a Mayor (A-Z, 1-9)", value: "asc" },
      { label: "Mayor a Menor (Z-A, 9-1)", value: "desc" },
    ]);

    // Funcion de ordenamiento inteligente
    const aplicarOrdenamiento = (registros, campo, tipo) => {
      if (campo === "default") {
        const campoDefecto = configuracionEsquema.value?.ordenarPor || "id";
        return [...registros].sort((a, b) => {
          const valorA = a[campoDefecto] || "";
          const valorB = b[campoDefecto] || "";
          return tipo === "asc"
            ? String(valorA).localeCompare(String(valorB))
            : String(valorB).localeCompare(String(valorA));
        });
      }

      if (campo === "alfabetico") {
        const campoNombre =
          Object.keys(registros[0] || {}).find(
            (key) => key.includes("nombre") || key.includes("name")
          ) || "nombre";

        return [...registros].sort((a, b) => {
          const nombreA = (a[campoNombre] || "").toLowerCase();
          const nombreB = (b[campoNombre] || "").toLowerCase();
          return tipo === "asc"
            ? nombreA.localeCompare(nombreB)
            : nombreB.localeCompare(nombreA);
        });
      }

      return [...registros].sort((a, b) => {
        const valorA = a[campo];
        const valorB = b[campo];

        // Ordenamiento numerico
        if (!isNaN(valorA) && !isNaN(valorB)) {
          return tipo === "asc"
            ? Number(valorA) - Number(valorB)
            : Number(valorB) - Number(valorA);
        }

        // Ordenamiento de fechas
        if (campo.includes("fecha") || campo.includes("_at")) {
          const fechaA = new Date(valorA);
          const fechaB = new Date(valorB);
          return tipo === "asc" ? fechaA - fechaB : fechaB - fechaA;
        }

        // Ordenamiento de texto
        return tipo === "asc"
          ? String(valorA || "").localeCompare(String(valorB || ""))
          : String(valorB || "").localeCompare(String(valorA || ""));
      });
    };

    // Colores profesionales
    const coloresEsquema = computed(() => {
      return {
        headerBackground: "#1e3a5f",
        borderColor: "#1e3a5f",
        textColor: "#ffffff",
      };
    });

    // Campos que se muestran en la tabla
    const camposMostrar = computed(() => {
      if (!configuracionEsquema.value) return [];

      const camposConfigurados =
        configuracionEsquema.value.mostrarEnTabla || [];

      return camposConfigurados.map((nombreCampo) => {
        const configuracionCampo = configuracionEsquema.value.campos.find(
          (campo) => {
            const config = analizarConfiguracionCampo(campo);
            return config && config.nombre === nombreCampo;
          }
        );

        if (configuracionCampo) {
          return analizarConfiguracionCampo(configuracionCampo);
        }

        return {
          nombre: nombreCampo,
          etiqueta: formatearEtiqueta(nombreCampo),
          tipo: "texto",
        };
      });
    });

    // Registros actuales
    const registrosActuales = computed(() => {
      return props.datos || registros.value || [];
    });

    // Filtros y busqueda
    const registrosFiltrados = computed(() => {
      if (!filtroTexto.value.trim()) {
        return registrosActuales.value;
      }

      const textoFiltro = filtroTexto.value.toLowerCase().trim();

      return registrosActuales.value.filter((registro) => {
        return camposMostrar.value.some((campo) => {
          const valor = registro[campo.nombre];
          if (valor === null || valor === undefined) return false;

          // Buscar en relaciones foraneas
          if (esRelacionForanea(campo)) {
            const nombreRelacion = obtenerNombreRelacion(registro, campo);
            const codigoRelacion = obtenerCodigoRelacion(registro, campo);
            return (
              nombreRelacion.toLowerCase().includes(textoFiltro) ||
              (codigoRelacion &&
                codigoRelacion.toLowerCase().includes(textoFiltro))
            );
          }

          return String(valor).toLowerCase().includes(textoFiltro);
        });
      });
    });

    // Registros ordenados
    const registrosOrdenados = computed(() => {
      if (!registrosFiltrados.value?.length) return [];

      return aplicarOrdenamiento(
        registrosFiltrados.value,
        campoOrdenamiento.value,
        tipoOrdenamiento.value
      );
    });

    // Paginacion
    const totalRegistrosFiltrados = computed(() => {
      return registrosOrdenados.value?.length || 0;
    });

    const totalPaginas = computed(() =>
      Math.ceil(totalRegistrosFiltrados.value / registrosPorPagina.value)
    );

    const registroInicio = computed(
      () => (paginaActual.value - 1) * registrosPorPagina.value + 1
    );

    const registroFin = computed(() =>
      Math.min(
        paginaActual.value * registrosPorPagina.value,
        totalRegistrosFiltrados.value
      )
    );

    const registrosPaginados = computed(() => {
      const inicio = (paginaActual.value - 1) * registrosPorPagina.value;
      const fin = inicio + registrosPorPagina.value;
      return registrosOrdenados.value.slice(inicio, fin);
    });

    const paginasVisibles = computed(() => {
      const total = totalPaginas.value;
      const actual = paginaActual.value;
      const paginas = [];

      let inicio = Math.max(1, actual - 2);
      let fin = Math.min(total, actual + 2);

      if (fin - inicio < 4) {
        if (inicio === 1) {
          fin = Math.min(total, inicio + 4);
        } else if (fin === total) {
          inicio = Math.max(1, fin - 4);
        }
      }

      for (let i = inicio; i <= fin; i++) {
        paginas.push(i);
      }

      return paginas;
    });

    // Contador de registros
    const contadorRegistros = computed(() => {
      const cantidad = totalRegistrosFiltrados.value;
      const total = registrosActuales.value.length;
      const singular = configuracionEsquema.value?.titulo || "registro";
      const plural = cantidad === 1 ? singular : `${singular}s`;

      if (filtroTexto.value && cantidad !== total) {
        return `${cantidad} de ${total} ${plural.toLowerCase()}`;
      }

      return `${cantidad} ${plural.toLowerCase()}`;
    });

    // Metodos de filtros y paginacion
    const filtrarRegistros = () => {
      paginaActual.value = 1;
    };

    const limpiarFiltros = () => {
      filtroTexto.value = "";
      paginaActual.value = 1;
    };

    const cambiarRegistrosPorPagina = () => {
      paginaActual.value = 1;
    };

    const irAPagina = (pagina) => {
      if (pagina >= 1 && pagina <= totalPaginas.value) {
        paginaActual.value = pagina;
      }
    };

    const cambiarOrdenamiento = () => {
      paginaActual.value = 1;
    };

    // Metodos para relaciones
    const esRelacionForanea = (campo) => {
      return campo.nombre.endsWith("_id") && campo.tipo !== "numero";
    };

    const obtenerNombreRelacion = (registro, campo) => {
      const nombreCampo = campo.nombre;
      const nombreTabla = nombreCampo.replace("_id", "");

      if (registro[nombreTabla]) {
        const datosRelacion = registro[nombreTabla];
        return (
          datosRelacion.nombre ||
          datosRelacion.nombre_categoria ||
          datosRelacion.nombre_especialidad ||
          datosRelacion.nombre_grado ||
          datosRelacion.nombre_tipo ||
          datosRelacion.nombre_evento ||
          `ID: ${registro[nombreCampo]}`
        );
      }

      return `ID: ${registro[nombreCampo]}`;
    };

    const obtenerCodigoRelacion = (registro, campo) => {
      const nombreCampo = campo.nombre;
      const nombreTabla = nombreCampo.replace("_id", "");

      if (registro[nombreTabla]) {
        const datosRelacion = registro[nombreTabla];
        return (
          datosRelacion.codigo_iso3 ||
          datosRelacion.codigo_categoria ||
          datosRelacion.codigo_especialidad ||
          datosRelacion.codigo_grado ||
          datosRelacion.codigo_tipo ||
          datosRelacion.codigo_evento ||
          datosRelacion.codigo
        );
      }

      return null;
    };

    // Metodos para formateo
    const esBooleano = (campo, registro) => {
      return typeof registro[campo.nombre] === "boolean";
    };

    const esFecha = (campo, registro) => {
      const valor = registro[campo.nombre];
      if (!valor) return false;
      const fechaRegex = /^\d{4}-\d{2}-\d{2}/;
      return typeof valor === "string" && fechaRegex.test(valor);
    };

    const esNumero = (campo, registro) => {
      return (
        typeof registro[campo.nombre] === "number" && !esRelacionForanea(campo)
      );
    };

    const esUrl = (campo, registro) => {
      const valor = registro[campo.nombre];
      if (!valor || typeof valor !== "string") return false;
      return (
        valor.includes("http") ||
        valor.includes("www.") ||
        valor.includes(".com")
      );
    };

    const claseBooleano = (valor) => {
      return valor ? "badge-success" : "badge-inactive";
    };

    const iconoBooleano = (valor) => {
      return valor ? "pi pi-check-circle" : "pi pi-times-circle";
    };

    const textoBooleano = (valor) => {
      return valor ? "Activo" : "Inactivo";
    };

    const formatearFecha = (valor) => {
      try {
        const fecha = new Date(valor);
        return fecha.toLocaleDateString("es-HN", {
          year: "numeric",
          month: "short",
          day: "numeric",
        });
      } catch {
        return valor;
      }
    };

    const formatearNumero = (valor) => {
      return new Intl.NumberFormat("es-HN").format(valor);
    };

    const obtenerValorMostrar = (valor) => {
      if (valor === null || valor === undefined) return "-";
      if (typeof valor === "string" && valor.trim() === "") return "-";
      return valor;
    };

    // Metodos principales
    const abrirModalCrear = async () => {
      modalVisible.value = false;

      if (props.esquema === "grados") {
        if (!catalogosStore.categoriasPersonal?.length) {
          await catalogosStore.loadCategoriasPersonal();
        }
        while (!catalogosStore.categoriasPersonal?.length) {
          await new Promise((resolve) => setTimeout(resolve, 100));
        }
      }

      modalModo.value = "crear";
      modalDatos.value = {};
      modalVisible.value = true;
    };

    const abrirModalEditar = (registro) => {
      modalModo.value = "editar";
      modalDatos.value = { ...registro };
      modalVisible.value = true;
    };

    const abrirModalEliminar = (registro) => {
      modalModo.value = "eliminar";
      modalDatos.value = { ...registro };
      modalVisible.value = true;
    };

    const recargarDatos = async () => {
      try {
        emit("recargar");

        if (
          catalogosStore &&
          typeof catalogosStore[
            `load${
              props.esquema.charAt(0).toUpperCase() + props.esquema.slice(1)
            }`
          ] === "function"
        ) {
          await catalogosStore[
            `load${
              props.esquema.charAt(0).toUpperCase() + props.esquema.slice(1)
            }`
          ]();
        }

        toast.success(
          "Datos actualizados",
          "La tabla ha sido recargada exitosamente"
        );
      } catch (error) {
        toast.error("Error al actualizar", "No se pudieron recargar los datos");
      }
    };

    const manejarGuardado = (evento) => {
      emit(evento.modo === "crear" ? "creado" : "actualizado", evento);
      emit("recargar");
    };

    const manejarEliminado = (evento) => {
      emit("eliminado", evento);
      emit("recargar");
    };

    const manejarCancelado = () => {
      modalVisible.value = false;
    };

    const manejarError = (evento) => {
      emit("error", evento);
    };

    const formatearEtiqueta = (nombreCampo) => {
      return nombreCampo
        .replace(/_/g, " ")
        .replace(/\b\w/g, (l) => l.toUpperCase());
    };

    // Watchers
    watch(
      () => props.esquema,
      (nuevoEsquema) => {
        filtroTexto.value = "";
        paginaActual.value = 1;
      }
    );

    watch(
      () => props.datos,
      () => {
        paginaActual.value = 1;
      }
    );

    // Lifecycle
    onMounted(() => {
      console.log(
        `📋 TablaDinamica FAH montada para esquema: ${props.esquema}`
      );
    });

    return {
      modalVisible,
      modalModo,
      modalDatos,
      cargandoRelaciones,
      filtroTexto,
      registrosPorPagina,
      paginaActual,

      // Variables de ordenamiento
      campoOrdenamiento,
      tipoOrdenamiento,
      opcionesOrdenamiento,
      tiposOrdenamiento,
      cambiarOrdenamiento,

      configuracionEsquema,
      coloresEsquema,
      camposMostrar,
      registrosActuales,
      registrosFiltrados,
      totalRegistrosFiltrados,
      totalPaginas,
      registroInicio,
      registroFin,
      registrosPaginados,
      paginasVisibles,
      contadorRegistros,

      abrirModalCrear,
      abrirModalEditar,
      abrirModalEliminar,
      recargarDatos,
      manejarGuardado,
      manejarEliminado,
      manejarCancelado,
      manejarError,

      filtrarRegistros,
      limpiarFiltros,
      cambiarRegistrosPorPagina,
      irAPagina,

      esRelacionForanea,
      obtenerNombreRelacion,
      obtenerCodigoRelacion,

      esBooleano,
      esFecha,
      esNumero,
      esUrl,
      claseBooleano,
      iconoBooleano,
      textoBooleano,
      formatearFecha,
      formatearNumero,
      obtenerValorMostrar,
    };
  },
};
</script>

<style scoped>
/* Estilos autocontenidos - Paleta FAH */

/* Contenedor principal */
.tabla-dinamica-contenedor {
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s;
  background: #ffffff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 2px solid #1e3a5f;
}

/* Controles superiores */
.tabla-controles-superiores {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  gap: 20px;
}

.controles-izquierda {
  display: flex;
  gap: 12px;
}

.controles-centro {
  flex: 1;
  max-width: 400px;
  margin: 0 20px;
}

.controles-derecha {
  display: flex;
  align-items: center;
  gap: 10px;
  white-space: nowrap;
}

/* Selectores de ordenamiento */
.tabla-controles-con-ordenamiento {
  flex-wrap: wrap;
}

.controles-ordenamiento {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #ffffff;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.label-ordenamiento {
  font-size: 14px;
  font-weight: 600;
  color: #495057;
  white-space: nowrap;
}

.select-ordenamiento,
.select-orden-tipo {
  padding: 6px 12px;
  border: 1px solid #e9ecef;
  border-radius: 4px;
  background: #ffffff;
  font-size: 14px;
  color: #495057;
  cursor: pointer;
  transition: border-color 0.2s;
  min-width: 120px;
}

.select-ordenamiento:focus,
.select-orden-tipo:focus {
  outline: none;
  border-color: #1e3a5f;
  box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

/* Botones */
.btn-fah {
  padding: 10px 18px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-fah-agregar {
  background: #5a9bd4;
  color: #ffffff;
}

.btn-fah-agregar:hover {
  background: #4a90c2;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(90, 155, 212, 0.2);
}

.btn-fah-actualizar {
  background: #5a9bd4;
  color: #ffffff;
}

.btn-fah-actualizar:hover {
  background: #4a90c2;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(90, 155, 212, 0.2);
}

.btn-fah-editar {
  background: #d4af37;
  color: #1e3a5f;
  font-weight: 600;
  padding: 6px 10px;
  font-size: 12px;
}

.btn-fah-editar:hover {
  background: #c19b26;
  transform: translateY(-1px);
}

.btn-fah-eliminar {
  background: #c1666b;
  color: #ffffff;
  padding: 6px 10px;
  font-size: 12px;
}

.btn-fah-eliminar:hover {
  background: #b55a5f;
  transform: translateY(-1px);
}

/* Buscador */
.busqueda-container {
  position: relative;
  width: 100%;
}

.input-busqueda {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-radius: 6px;
  font-size: 14px;
  color: #495057;
  background-color: #ffffff;
  transition: border-color 0.2s ease;
}

.input-busqueda:focus {
  outline: none;
  border-color: #1e3a5f;
  box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

.input-busqueda::placeholder {
  color: #6c757d;
}

.busqueda-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #d4af37;
  pointer-events: none;
}

/* Selector de registros */
.label-registros {
  color: #495057;
  font-weight: 500;
  font-size: 14px;
}

.select-registros {
  padding: 10px 15px;
  border: 2px solid #e9ecef;
  border-radius: 6px;
  font-size: 14px;
  color: #495057;
  background-color: #ffffff;
  cursor: pointer;
  transition: border-color 0.2s ease;
  min-width: 80px;
}

.select-registros:focus {
  outline: none;
  border-color: #1e3a5f;
  box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

/* Tabla */
.tabla-fah {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  background: #ffffff;
}

.tabla-fah thead {
  background: #1e3a5f;
}

.tabla-fah th {
  padding: 15px 12px;
  text-align: left;
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  color: #ffffff;
  border-bottom: 2px solid #d4af37;
}

.tabla-fah td {
  padding: 12px;
  border-bottom: 1px solid #e9ecef;
  color: #343a40;
  font-weight: 500;
  vertical-align: middle;
}

.tabla-fah tbody tr:nth-child(even) {
  background-color: #f8f9fa;
}

.tabla-fah tbody tr:nth-child(odd) {
  background-color: #ffffff;
}

.tabla-fah tbody tr:hover {
  background-color: rgba(212, 175, 55, 0.1) !important;
  transition: background-color 0.15s ease;
}

/* Controles inferiores */
.tabla-controles-inferiores {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
}

.info-registros {
  color: #6c757d;
  font-size: 14px;
  font-weight: 500;
}

.info-registros span {
  color: #1e3a5f;
  font-weight: 700;
}

/* Paginacion */
.paginacion {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-paginacion {
  padding: 8px 16px;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  background: #ffffff;
  color: #495057;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
}

.btn-paginacion:hover:not(:disabled) {
  background: #1e3a5f;
  border-color: #1e3a5f;
  color: #ffffff;
}

.btn-paginacion:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f8f9fa;
}

.numeros-pagina {
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-pagina {
  width: 40px;
  height: 40px;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  background: #ffffff;
  color: #495057;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.btn-pagina:hover {
  background: #f8f9fa;
  border-color: #1e3a5f;
  color: #1e3a5f;
}

.btn-pagina.activa {
  background: #1e3a5f;
  border-color: #1e3a5f;
  color: #ffffff;
}

.dots {
  color: #6c757d;
  padding: 0 8px;
  font-weight: bold;
}

/* Estados */
.badge-success {
  display: inline-flex;
  align-items: center;
  padding: 3px 8px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  background: linear-gradient(135deg, #28a745, #20c997);
  color: #ffffff;
  box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.badge-inactive {
  display: inline-flex;
  align-items: center;
  padding: 3px 8px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  background: linear-gradient(135deg, #6c757d, #5a6268);
  color: #ffffff;
  box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

/* Relaciones foraneas */
.contenido-relacion {
  width: 100%;
}

.relacion-datos {
  display: flex;
  flex-direction: column;
}

.nombre-relacion {
  font-size: 13px;
  font-weight: 600;
  color: #1e3a5f;
}

.codigo-relacion {
  font-size: 10px;
  font-family: "Courier New", monospace;
  color: #6c757d;
  background: rgba(212, 175, 55, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  margin-top: 2px;
  display: inline-block;
}

/* Contenido */
.contenido-numero {
  text-align: right;
  font-family: "Courier New", monospace;
  font-size: 14px;
  font-weight: 600;
  color: #d4af37;
}

.contenido-fecha {
  font-size: 12px;
  color: #495057;
  background: rgba(212, 175, 55, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  border-left: 3px solid #d4af37;
}

.contenido-texto {
  font-size: 14px;
  font-weight: 500;
  color: #343a40;
}

.contenido-url {
  font-size: 14px;
}

.link-url {
  color: #5a9bd4;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s ease;
}

.link-url:hover {
  color: #1e3a5f;
  text-decoration: underline;
}

/* Estado vacio */
.estado-vacio {
  text-align: center;
  padding: 60px 20px;
  background: #f8f9fa;
  border: 2px dashed #e9ecef;
  border-radius: 8px;
  margin: 20px;
}

.estado-vacio-icono {
  font-size: 3rem;
  opacity: 0.4;
  margin-bottom: 20px;
  color: #d4af37;
}

.estado-vacio-titulo {
  color: #1e3a5f;
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 10px;
}

.estado-vacio-descripcion {
  color: #6c757d;
  font-size: 1rem;
  margin-bottom: 30px;
}

/* Loading */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 20;
  backdrop-filter: blur(1px);
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
  padding: 30px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(30, 58, 95, 0.1);
  border: 2px solid #d4af37;
}

.loading-text {
  color: #1e3a5f;
  font-weight: 600;
  font-size: 16px;
}

/* Responsive */
@media (max-width: 768px) {
  .tabla-controles-superiores {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }

  .controles-centro {
    margin: 0;
    max-width: none;
  }

  .controles-derecha {
    justify-content: center;
  }

  .controles-ordenamiento {
    width: 100%;
    justify-content: center;
  }

  .select-ordenamiento,
  .select-orden-tipo {
    flex: 1;
    min-width: auto;
  }

  .tabla-controles-inferiores {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }

  .paginacion {
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-fah {
    padding: 8px 12px;
    font-size: 12px;
  }

  .tabla-fah {
    font-size: 12px;
  }

  .tabla-fah th,
  .tabla-fah td {
    padding: 8px 6px;
  }
}

/* Animacion */
.tabla-dinamica-contenedor tbody tr {
  animation: fadeInRow 0.3s ease-out;
}

@keyframes fadeInRow {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
