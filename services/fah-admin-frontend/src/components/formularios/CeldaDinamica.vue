<template>
  <!-- ============================================ -->
  <!-- CELDA DINÁMICA UNIVERSAL MEJORADA -->
  <!-- ✅ SOPORTE COMPLETO PARA RELACIONES BACKEND -->
  <!-- Renderiza valores según el tipo de campo automáticamente -->
  <!-- ============================================ -->
  <div class="celda-dinamica">
    <!-- 🔗 RELACIÓN FORÁNEA: Aprovecha datos del backend -->
    <div v-if="esRelacionForanea" class="contenido-relacion">
      <div v-if="datosRelacionBackend" class="relacion-completa">
        <div class="nombre-relacion">{{ datosRelacionBackend.nombre }}</div>
        <div v-if="datosRelacionBackend.codigo" class="codigo-relacion">
          {{ datosRelacionBackend.codigo }}
        </div>
      </div>

      <div v-else-if="valorForaneoResuelto" class="relacion-store">
        <span :class="claseForaneo">{{ valorForaneoResuelto }}</span>
      </div>

      <div v-else class="relacion-fallback">
        <span class="text-gray-400 text-xs">ID: {{ valor }}</span>
      </div>
    </div>

    <!-- ✅ BOOLEANO: Badge visual mejorado -->
    <div v-else-if="esTipoBooleano" class="contenido-booleano">
      <span :class="claseBooleano">
        <i :class="iconoBooleano"></i>
        {{ textoBooleano }}
      </span>
    </div>

    <!-- 📅 FECHA: Formato legible -->
    <div v-else-if="esFecha" class="contenido-fecha">
      {{ fechaFormateada }}
    </div>

    <!-- 🔢 NÚMERO: Con separadores -->
    <div v-else-if="esTipoNumero" class="contenido-numero">
      <span :class="claseNumero">{{ numeroFormateado }}</span>
    </div>

    <!-- 🌐 URL: Link clicable mejorado -->
    <div v-else-if="esTipoUrl" class="contenido-url">
      <template v-if="valor && valor !== ''">
        <img
          v-if="esImagenUrl"
          :src="valor"
          :alt="configuracion.etiqueta"
          class="w-6 h-6 object-contain rounded mr-2"
          @error="$event.target.style.display = 'none'"
        />
        <a
          :href="valor"
          target="_blank"
          rel="noopener noreferrer"
          class="link-url"
        >
          <i class="pi pi-external-link mr-1"></i>
          Ver enlace
        </a>
      </template>
      <span v-else class="sin-url">Sin URL</span>
    </div>

    <!-- 📧 EMAIL: Link clicable -->
    <div v-else-if="esEmail" class="contenido-email">
      <a :href="`mailto:${valor}`" class="link-email">
        <i class="pi pi-envelope mr-1"></i>
        {{ valor }}
      </a>
    </div>

    <!-- 📱 TELÉFONO: Link clicable -->
    <div v-else-if="esTelefono" class="contenido-telefono">
      <a :href="`tel:${valor}`" class="link-telefono">
        <i class="pi pi-phone mr-1"></i>
        {{ valor }}
      </a>
    </div>

    <!-- 🏷️ CÓDIGO: Badge colorido -->
    <div v-else-if="esTipoCodigo" class="contenido-codigo">
      <span :class="claseCodigo">{{ valor }}</span>
    </div>

    <!-- 📝 TEXTO: Simple -->
    <div v-else-if="esTipoTexto" class="contenido-texto">
      <span :class="claseTexto">{{ textoTruncado }}</span>
    </div>

    <!-- 📄 VALOR POR DEFECTO -->
    <div v-else class="contenido-defecto">
      {{ valorMostrar }}
    </div>
  </div>
</template>

<script>
import { computed } from "vue";
import { useCatalogosStore } from "@/stores/catalogosStore";
import { useOrganizacionStore } from "@/stores/organizacionStore";

export default {
  name: "CeldaDinamica",

  props: {
    valor: {
      type: [String, Number, Boolean, Date, Object],
      default: null,
    },
    configuracion: {
      type: Object,
      default: () => ({}),
    },
    registroCompleto: {
      type: Object,
      default: () => ({}),
    },
  },

  setup(props) {
    // =====================================
    // STORES
    // =====================================
    const catalogosStore = useCatalogosStore();
    const organizacionStore = useOrganizacionStore();

    // =====================================
    // DETECTORES DE TIPO
    // =====================================

    // 🔗 DETECTOR MEJORADO PARA RELACIONES FORÁNEAS
    const esRelacionForanea = computed(() => {
      const campo = props.configuracion.nombre || "";
      return (
        campo.endsWith("_id") &&
        typeof props.valor === "number" &&
        props.valor > 0
      );
    });

    // 🔗 DATOS DE RELACIÓN DESDE BACKEND (PRIORITARIO)
    const datosRelacionBackend = computed(() => {
      if (!esRelacionForanea.value) return null;

      const campo = props.configuracion.nombre;
      const nombreTabla = campo.replace("_id", "");

      // Buscar en los datos relacionados que vienen del backend
      const relacion = props.registroCompleto[nombreTabla];
      if (!relacion) return null;

      return {
        nombre:
          relacion.nombre ||
          relacion.nombre_departamento ||
          relacion.nombre_municipio ||
          relacion.nombre_ciudad ||
          relacion.nombre_ubicacion ||
          relacion.nombre_unidad ||
          relacion.nombre_cargo ||
          relacion.nombre_rol ||
          relacion.nombre_categoria ||
          relacion.nombre_especialidad ||
          relacion.nombre_grado ||
          relacion.nombre_tipo ||
          relacion.nombre_evento ||
          "Sin nombre",
        codigo:
          relacion.codigo_iso3 ||
          relacion.codigo_departamento ||
          relacion.codigo_municipio ||
          relacion.codigo_ciudad ||
          relacion.codigo_ubicacion ||
          relacion.codigo_unidad ||
          relacion.codigo_cargo ||
          relacion.codigo_rol ||
          relacion.codigo_categoria ||
          relacion.codigo_especialidad ||
          relacion.codigo_grado ||
          relacion.codigo_tipo ||
          relacion.codigo_evento ||
          relacion.codigo ||
          null,
      };
    });

    // 🔗 RESOLVER DESDE STORE (FALLBACK)
    const valorForaneoResuelto = computed(() => {
      if (!esRelacionForanea.value || datosRelacionBackend.value) return null;

      const campo = props.configuracion.nombre || "";
      const valorId = parseInt(props.valor);

      switch (campo) {
        // CATÁLOGOS
        case "categoria_personal_id":
          const categoria = catalogosStore.categoriasPersonal?.find(
            (c) => c.id === valorId
          );
          return (
            categoria?.nombre_categoria || categoria?.codigo_categoria || null
          );

        case "especialidad_id":
          const especialidad = catalogosStore.especialidades?.find(
            (e) => e.id === valorId
          );
          return (
            especialidad?.nombre_especialidad ||
            especialidad?.codigo_especialidad ||
            null
          );

        case "tipo_genero_id":
          const genero = catalogosStore.tiposGenero?.find(
            (g) => g.id === valorId
          );
          return genero?.nombre || genero?.codigo || null;

        case "pais_id":
          const pais = catalogosStore.paises?.find((p) => p.id === valorId);
          return pais?.nombre || null;

        case "tipo_estructura_id":
          const tipoEst = catalogosStore.tiposEstructuraMilitar?.find(
            (t) => t.id === valorId
          );
          return tipoEst?.nombre_tipo || tipoEst?.codigo_tipo || null;

        // ORGANIZACIÓN
        case "departamento_id":
          const depto = organizacionStore.departamentos?.find(
            (d) => d.id === valorId
          );
          return depto?.nombre_departamento || null;

        case "municipio_id":
          const muni = organizacionStore.municipios?.find(
            (m) => m.id === valorId
          );
          return muni?.nombre_municipio || null;

        case "ciudad_id":
          const ciudad = organizacionStore.ciudades?.find(
            (c) => c.id === valorId
          );
          return ciudad?.nombre_ciudad || null;

        default:
          return null;
      }
    });

    // Otros detectores
    const esTipoBooleano = computed(() => {
      return (
        typeof props.valor === "boolean" ||
        [
          "requiere_autorizacion",
          "permite_operaciones",
          "es_estado_final",
          "requiere_justificacion",
          "requiere_aprobacion",
        ].includes(props.configuracion.nombre || "")
      );
    });

    const esFecha = computed(() => {
      if (!props.valor) return false;
      const fechaRegex = /^\d{4}-\d{2}-\d{2}/;
      return typeof props.valor === "string" && fechaRegex.test(props.valor);
    });

    const esTipoNumero = computed(() => {
      return (
        (typeof props.valor === "number" && !esRelacionForanea.value) ||
        [
          "orden_jerarquico",
          "nivel_numerico",
          "nivel_organizacional",
          "nivel_autoridad",
          "tiempo_retencion_anos",
        ].includes(props.configuracion.nombre || "")
      );
    });

    const esTipoUrl = computed(() => {
      const campo = props.configuracion.nombre || "";
      return (
        campo.includes("url") ||
        campo.includes("_url") ||
        (typeof props.valor === "string" &&
          (props.valor.includes("http") ||
            props.valor.includes("www.") ||
            props.valor.includes(".com")))
      );
    });

    const esEmail = computed(() => {
      if (!props.valor || typeof props.valor !== "string") return false;
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(props.valor);
    });

    const esTelefono = computed(() => {
      const campo = props.configuracion.nombre || "";
      return (
        campo.includes("telefono") ||
        campo.includes("phone") ||
        (typeof props.valor === "string" &&
          /^[\+]?[\d\s\-\(\)]{8,}$/.test(props.valor))
      );
    });

    const esTipoCodigo = computed(() => {
      const campo = props.configuracion.nombre || "";
      return (
        campo.includes("codigo") ||
        campo === "abreviatura" ||
        [
          "codigo",
          "codigo_categoria",
          "codigo_especialidad",
          "codigo_grado",
          "codigo_evento",
          "codigo_tipo",
          "codigo_iso3",
          "abreviatura",
        ].includes(campo)
      );
    });

    const esTipoTexto = computed(() => {
      const campo = props.configuracion.nombre || "";
      return [
        "nombre",
        "nombre_categoria",
        "nombre_especialidad",
        "nombre_grado",
        "nombre_evento",
        "nombre_tipo",
        "nombre_departamento",
        "nombre_municipio",
        "nombre_ciudad",
        "nombre_ubicacion",
        "nombre_unidad",
        "nombre_cargo",
        "nombre_rol",
      ].includes(campo);
    });

    const esImagenUrl = computed(() => {
      if (!props.valor || typeof props.valor !== "string") return false;
      const extensionesImagen = [
        ".jpg",
        ".jpeg",
        ".png",
        ".gif",
        ".webp",
        ".svg",
      ];
      return extensionesImagen.some((ext) =>
        props.valor.toLowerCase().includes(ext)
      );
    });

    // =====================================
    // VALORES FORMATEADOS
    // =====================================

    const valorMostrar = computed(() => {
      if (props.valor === null || props.valor === undefined) return "-";
      if (typeof props.valor === "string" && props.valor.trim() === "")
        return "-";
      return props.valor;
    });

    const textoBooleano = computed(() => {
      return props.valor ? "Activo" : "Inactivo";
    });

    const fechaFormateada = computed(() => {
      try {
        const fecha = new Date(props.valor);
        return fecha.toLocaleDateString("es-HN", {
          year: "numeric",
          month: "short",
          day: "numeric",
        });
      } catch {
        return props.valor;
      }
    });

    const numeroFormateado = computed(() => {
      if (typeof props.valor === "number") {
        return props.valor.toLocaleString("es-HN");
      }
      return props.valor;
    });

    const textoTruncado = computed(() => {
      if (typeof props.valor === "string" && props.valor.length > 30) {
        return props.valor.substring(0, 27) + "...";
      }
      return props.valor;
    });

    // =====================================
    // CLASES CSS
    // =====================================

    const claseBooleano = computed(() => {
      return props.valor
        ? "badge-success inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
        : "badge-inactive inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600";
    });

    const iconoBooleano = computed(() => {
      return props.valor
        ? "pi pi-check-circle mr-1"
        : "pi pi-times-circle mr-1";
    });

    const claseNumero = computed(() => [
      "bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-2 py-1 rounded text-xs font-semibold min-w-6 text-center inline-block",
    ]);

    const claseCodigo = computed(() => {
      const campo = props.configuracion.nombre || "";

      if (campo.includes("categoria")) {
        return "bg-gradient-to-r from-gray-500 to-gray-600 text-white px-2 py-1 rounded text-xs font-semibold font-mono uppercase";
      }
      if (campo.includes("especialidad")) {
        return "bg-gradient-to-r from-purple-500 to-purple-600 text-white px-2 py-1 rounded text-xs font-semibold font-mono uppercase";
      }
      if (campo.includes("grado")) {
        return "bg-gradient-to-r from-orange-500 to-orange-600 text-white px-2 py-1 rounded text-xs font-semibold font-mono uppercase";
      }
      if (campo.includes("departamento")) {
        return "bg-gradient-to-r from-green-500 to-green-600 text-white px-2 py-1 rounded text-xs font-semibold font-mono uppercase";
      }
      if (campo.includes("municipio")) {
        return "bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1 rounded text-xs font-semibold font-mono uppercase";
      }

      return "bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1 rounded text-xs font-semibold font-mono uppercase";
    });

    const claseForaneo = computed(() => [
      "bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-2 py-1 rounded text-xs font-semibold uppercase shadow-sm",
    ]);

    const claseTexto = computed(() => ["font-semibold text-gray-800"]);

    // =====================================
    // RETURN
    // =====================================
    return {
      // Detectores
      esRelacionForanea,
      esTipoBooleano,
      esFecha,
      esTipoNumero,
      esTipoUrl,
      esEmail,
      esTelefono,
      esTipoCodigo,
      esTipoTexto,
      esImagenUrl,

      // Datos
      datosRelacionBackend,
      valorForaneoResuelto,

      // Valores formateados
      valorMostrar,
      textoBooleano,
      fechaFormateada,
      numeroFormateado,
      textoTruncado,

      // Clases
      claseBooleano,
      iconoBooleano,
      claseNumero,
      claseCodigo,
      claseForaneo,
      claseTexto,
    };
  },
};
</script>

<style scoped>
/* =====================================
   ESTILOS PARA CELDA DINÁMICA MEJORADA
   ===================================== */

.celda-dinamica {
  @apply flex items-center min-h-[2rem];
}

/* 🔗 RELACIONES */
.contenido-relacion {
  @apply w-full;
}

.relacion-completa {
  @apply flex flex-col;
}

.nombre-relacion {
  @apply text-sm font-medium text-gray-800;
}

.codigo-relacion {
  @apply text-xs text-gray-500 font-mono;
}

.relacion-fallback {
  @apply text-gray-400 text-xs italic;
}

/* 🌐 ENLACES */
.link-url,
.link-email,
.link-telefono {
  @apply text-blue-600 hover:text-blue-800 text-sm;
  @apply transition-colors duration-200;
  @apply no-underline hover:underline;
}

/* 📝 CONTENIDO */
.contenido-numero {
  @apply text-right;
}

.contenido-fecha {
  @apply text-sm text-gray-700;
}

.contenido-texto {
  @apply text-sm;
}

.sin-url {
  @apply text-gray-500 text-xs;
}

/* Animaciones */
.celda-dinamica {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .nombre-relacion {
    @apply text-xs;
  }

  .codigo-relacion {
    @apply text-xs;
  }

  .celda-dinamica .bg-gradient-to-r {
    @apply px-1 py-0.5 text-xs;
  }
}
</style>
