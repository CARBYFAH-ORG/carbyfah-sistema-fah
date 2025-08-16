<template>
  <!-- ============================================ -->
  <!-- TABLA DINÁMICA CATÁLOGOS FAH - VERSIÓN COMPLETA -->
  <!-- ✅ CONTROLES SUPERIORES E INFERIORES + PALETA FAH -->
  <!-- ============================================ -->
  <div class="tabla-dinamica-contenedor">
    <!-- =====================================
         HEADER DE LA TABLA FAH
         ===================================== -->
    <div
      class="flex justify-between items-center px-6 py-5 bg-gradient-to-r from-slate-50 to-slate-100 border-b-2"
      :style="{ borderBottomColor: coloresEsquema.borderColor }"
    >
      <div class="flex items-center gap-3">
        <div
          class="w-11 h-11 rounded-lg flex items-center justify-center text-xl text-white"
          style="background: #1e3a8a"
        >
          {{ configuracionEsquema?.icono || "📋" }}
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

    <!-- =====================================
         CONTROLES SUPERIORES FAH
         ===================================== -->
    <div class="tabla-controles-superiores">
      <!-- BOTONES IZQUIERDA -->
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

      <!-- BUSCADOR CENTRO -->
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

      <!-- SELECTOR REGISTROS DERECHA -->
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
    </div>

    <!-- =====================================
         CONTENIDO PRINCIPAL
         ===================================== -->
    <div class="p-6 bg-gray-100 relative">
      <!-- OVERLAY DE CARGA -->
      <div v-if="cargando" class="loading-overlay">
        <div class="loading-content">
          <ProgressSpinner size="50" strokeWidth="4" />
          <span class="loading-text">Cargando datos...</span>
        </div>
      </div>

      <!-- ESTADO DE ERROR -->
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

      <!-- TABLA CON DATOS -->
      <div
        v-else-if="registrosFiltrados?.length > 0"
        class="overflow-x-auto rounded-lg border-2"
        style="border-color: #1e3a8a"
      >
        <table class="tabla-fah">
          <!-- ENCABEZADOS DINÁMICOS -->
          <thead>
            <tr>
              <th
                v-for="campo in camposMostrar"
                :key="campo.nombre"
                style="background: #1e3a8a; color: #ffffff"
              >
                {{ campo.etiqueta }}
              </th>
              <th style="background: #1e3a8a; color: #ffffff">Acciones</th>
            </tr>
          </thead>

          <!-- FILAS DINÁMICAS -->
          <tbody>
            <tr
              v-for="registro in registrosPaginados"
              :key="registro.id || registro.codigo"
            >
              <!-- CELDAS DINÁMICAS -->
              <td v-for="campo in camposMostrar" :key="campo.nombre">
                <!-- 🔗 RELACIÓN FORÁNEA -->
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

                <!-- ✅ BOOLEANO -->
                <div
                  v-else-if="esBooleano(campo, registro)"
                  class="contenido-booleano"
                >
                  <span :class="claseBooleano(registro[campo.nombre])">
                    <i :class="iconoBooleano(registro[campo.nombre])"></i>
                    {{ textoBooleano(registro[campo.nombre]) }}
                  </span>
                </div>

                <!-- 📅 FECHA -->
                <div
                  v-else-if="esFecha(campo, registro)"
                  class="contenido-fecha"
                >
                  {{ formatearFecha(registro[campo.nombre]) }}
                </div>

                <!-- 🔢 NÚMERO -->
                <div
                  v-else-if="esNumero(campo, registro)"
                  class="contenido-numero"
                >
                  {{ formatearNumero(registro[campo.nombre]) }}
                </div>

                <!-- 🌐 URL -->
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

                <!-- 📝 TEXTO -->
                <div v-else class="contenido-texto">
                  {{ obtenerValorMostrar(registro[campo.nombre]) }}
                </div>
              </td>

              <!-- CELDA DE ACCIONES -->
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

      <!-- ESTADO VACÍO -->
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

    <!-- =====================================
         CONTROLES INFERIORES DE PAGINACIÓN
         ===================================== -->
    <div
      v-if="registrosFiltrados?.length > 0"
      class="tabla-controles-inferiores"
    >
      <!-- INFO DE REGISTROS -->
      <div class="info-registros">
        Mostrando <span>{{ registroInicio }}</span> a
        <span>{{ registroFin }}</span> de
        <span>{{ totalRegistrosFiltrados }}</span> registros
        <span v-if="filtroTexto" class="text-sm text-gray-500">
          (filtrados de {{ registrosActuales.length }} totales)
        </span>
      </div>

      <!-- PAGINACIÓN -->
      <div class="paginacion" v-if="totalPaginas > 1">
        <button
          class="btn-paginacion"
          @click="irAPagina(paginaActual - 1)"
          :disabled="paginaActual <= 1"
        >
          ⬅️ Anterior
        </button>

        <div class="numeros-pagina">
          <!-- Primera página -->
          <button
            v-if="paginaActual > 3"
            class="btn-pagina"
            @click="irAPagina(1)"
          >
            1
          </button>

          <!-- Puntos suspensivos izquierda -->
          <span v-if="paginaActual > 4" class="dots">...</span>

          <!-- Páginas alrededor de la actual -->
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

          <!-- Última página -->
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

    <!-- MODAL PARA CATÁLOGOS ÚNICAMENTE -->
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
import { useToast } from "primevue/usetoast";

// Componentes PrimeVue
import ProgressSpinner from "primevue/progressspinner";
import Message from "primevue/message";
import Button from "primevue/button";

// Componentes propios
import ModalFormulario from "./ModalFormulario.vue";
import { useCatalogosStore } from "@/stores/catalogosStore";

// Composables y configuración (SOLO CATÁLOGOS)
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
    // =====================================
    // COMPOSABLES Y STORES
    // =====================================
    const toast = useToast();
    const catalogosStore = useCatalogosStore();

    const {
      registros,
      cargarDatos,
      crearRegistro,
      actualizarRegistro,
      eliminarRegistro,
    } = usarCrudDinamico(props.esquema);

    const { analizarConfiguracionCampo } = usarFormularioDinamico();

    // =====================================
    // ESTADO REACTIVO
    // =====================================
    const modalVisible = ref(false);
    const modalModo = ref("crear");
    const modalDatos = ref({});
    const cargandoRelaciones = ref(false);

    // Estados para filtros y paginación
    const filtroTexto = ref("");
    const registrosPorPagina = ref(10);
    const paginaActual = ref(1);

    // =====================================
    // COMPUTED PROPERTIES
    // =====================================

    // Configuración del esquema
    const configuracionEsquema = computed(() => {
      return obtenerEsquema(props.esquema);
    });

    // 🎨 COLORES PROFESIONALES FAH - SÓLIDOS Y SOBRIOS
    const coloresEsquema = computed(() => {
      // TODOS usan el mismo azul FAH profesional - sin variaciones excesivas
      return {
        headerBackground: "#1e3a8a", // Azul FAH sólido
        borderColor: "#1e3a8a", // Mismo azul para consistencia
        textColor: "#ffffff", // Texto blanco legible
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

    // 🔍 FILTROS Y BÚSQUEDA
    const registrosFiltrados = computed(() => {
      if (!filtroTexto.value.trim()) {
        return registrosActuales.value;
      }

      const textoFiltro = filtroTexto.value.toLowerCase().trim();

      return registrosActuales.value.filter((registro) => {
        return camposMostrar.value.some((campo) => {
          const valor = registro[campo.nombre];
          if (valor === null || valor === undefined) return false;

          // Buscar en relaciones foráneas
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

    // 📄 PAGINACIÓN
    const totalRegistrosFiltrados = computed(
      () => registrosFiltrados.value.length
    );
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
      return registrosFiltrados.value.slice(inicio, fin);
    });

    const paginasVisibles = computed(() => {
      const total = totalPaginas.value;
      const actual = paginaActual.value;
      const paginas = [];

      let inicio = Math.max(1, actual - 2);
      let fin = Math.min(total, actual + 2);

      // Ajustar para mostrar siempre 5 páginas si es posible
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

    // =====================================
    // MÉTODOS DE FILTROS Y PAGINACIÓN
    // =====================================
    const filtrarRegistros = () => {
      paginaActual.value = 1; // Resetear a primera página al filtrar
    };

    const limpiarFiltros = () => {
      filtroTexto.value = "";
      paginaActual.value = 1;
    };

    const cambiarRegistrosPorPagina = () => {
      paginaActual.value = 1; // Resetear a primera página al cambiar cantidad
    };

    const irAPagina = (pagina) => {
      if (pagina >= 1 && pagina <= totalPaginas.value) {
        paginaActual.value = pagina;
      }
    };

    // =====================================
    // MÉTODOS PARA RELACIONES (sin cambios)
    // =====================================
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

    // =====================================
    // MÉTODOS PARA FORMATEO (sin cambios)
    // =====================================
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

    // =====================================
    // MÉTODOS PRINCIPALES (sin cambios)
    // =====================================
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

    // Método para recargar datos - FUNCIÓN COMPLETA ORIGINAL
    const recargarDatos = async () => {
      console.log("🔄 Recargando datos para esquema:", props.esquema);

      try {
        // Emit para que el padre recargue los datos
        emit("recargar");

        // También recargar desde el store si está disponible
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

        // Mostrar toast de éxito
        toast.add({
          severity: "success",
          summary: "Datos actualizados",
          detail: "La tabla ha sido recargada exitosamente",
          life: 3000,
        });
      } catch (error) {
        console.error("Error al recargar datos:", error);
        toast.add({
          severity: "error",
          summary: "Error al actualizar",
          detail: "No se pudieron recargar los datos",
          life: 5000,
        });
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

    // =====================================
    // WATCHERS
    // =====================================
    watch(
      () => props.esquema,
      (nuevoEsquema) => {
        console.log(`🔄 Esquema cambió a: ${nuevoEsquema}`);
        // Resetear filtros y paginación
        filtroTexto.value = "";
        paginaActual.value = 1;
      }
    );

    // Resetear paginación cuando cambien los datos
    watch(
      () => props.datos,
      () => {
        paginaActual.value = 1;
      }
    );

    // =====================================
    // LIFECYCLE
    // =====================================
    onMounted(() => {
      console.log(
        `📋 TablaDinamica FAH montada para esquema: ${props.esquema}`
      );
      console.log(`🎨 Colores aplicados:`, coloresEsquema.value);
    });

    // =====================================
    // RETURN
    // =====================================
    return {
      // Estado
      modalVisible,
      modalModo,
      modalDatos,
      cargandoRelaciones,
      filtroTexto,
      registrosPorPagina,
      paginaActual,

      // Computed
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

      // Métodos principales
      abrirModalCrear,
      abrirModalEditar,
      abrirModalEliminar,
      recargarDatos,
      manejarGuardado,
      manejarEliminado,
      manejarCancelado,
      manejarError,

      // Métodos de filtros y paginación
      filtrarRegistros,
      limpiarFiltros,
      cambiarRegistrosPorPagina,
      irAPagina,

      // Métodos para relaciones
      esRelacionForanea,
      obtenerNombreRelacion,
      obtenerCodigoRelacion,

      // Métodos para formateo
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

<style>
/* Importar estilos externos organizados */
@import "@/styles/components/formularios/tabla-dinamica.css";
</style>
