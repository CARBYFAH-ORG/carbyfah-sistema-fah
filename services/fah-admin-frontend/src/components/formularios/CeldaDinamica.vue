<!-- services\fah-admin-frontend\src\components\formularios\CeldaDinamica.vue -->
<template>
  <!-- Celda dinamica universal -->
  <div class="celda-dinamica">
    <!-- Relacion foranea -->
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
        <span class="relacion-fallback-text">ID: {{ valor }}</span>
      </div>
    </div>

    <!-- Booleano -->
    <div v-else-if="esTipoBooleano" class="contenido-booleano">
      <span :class="claseBooleano">
        <i :class="iconoBooleano"></i>
        {{ textoBooleano }}
      </span>
    </div>

    <!-- Fecha -->
    <div v-else-if="esFecha" class="contenido-fecha">
      {{ fechaFormateada }}
    </div>

    <!-- Numero -->
    <div v-else-if="esTipoNumero" class="contenido-numero">
      <span :class="claseNumero">{{ numeroFormateado }}</span>
    </div>

    <!-- URL -->
    <div v-else-if="esTipoUrl" class="contenido-url">
      <template v-if="valor && valor !== ''">
        <img
          v-if="esImagenUrl"
          :src="valor"
          :alt="configuracion.etiqueta"
          class="imagen-url"
          @error="$event.target.style.display = 'none'"
        />
        <a
          :href="valor"
          target="_blank"
          rel="noopener noreferrer"
          class="link-url"
        >
          <i class="icono-link">🔗</i>
          Ver enlace
        </a>
      </template>
      <span v-else class="sin-url">Sin URL</span>
    </div>

    <!-- Email -->
    <div v-else-if="esEmail" class="contenido-email">
      <a :href="`mailto:${valor}`" class="link-email">
        <i class="icono-email">📧</i>
        {{ valor }}
      </a>
    </div>

    <!-- Telefono -->
    <div v-else-if="esTelefono" class="contenido-telefono">
      <a :href="`tel:${valor}`" class="link-telefono">
        <i class="icono-telefono">📞</i>
        {{ valor }}
      </a>
    </div>

    <!-- Codigo -->
    <div v-else-if="esTipoCodigo" class="contenido-codigo">
      <span :class="claseCodigo">{{ valor }}</span>
    </div>

    <!-- Texto -->
    <div v-else-if="esTipoTexto" class="contenido-texto">
      <span :class="claseTexto">{{ textoTruncado }}</span>
    </div>

    <!-- Valor por defecto -->
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
    const catalogosStore = useCatalogosStore();
    const organizacionStore = useOrganizacionStore();

    // Detectores de tipo
    const esRelacionForanea = computed(() => {
      const campo = props.configuracion.nombre || "";
      return (
        campo.endsWith("_id") &&
        typeof props.valor === "number" &&
        props.valor > 0
      );
    });

    // Datos de relacion desde backend
    const datosRelacionBackend = computed(() => {
      if (!esRelacionForanea.value) return null;

      const campo = props.configuracion.nombre;
      const nombreTabla = campo.replace("_id", "");

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

    // Resolver desde store
    const valorForaneoResuelto = computed(() => {
      if (!esRelacionForanea.value || datosRelacionBackend.value) return null;

      const campo = props.configuracion.nombre || "";
      const valorId = parseInt(props.valor);

      switch (campo) {
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

    // Valores formateados
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

    // Clases CSS
    const claseBooleano = computed(() => {
      return props.valor ? "badge-success-fah" : "badge-inactive-fah";
    });

    const iconoBooleano = computed(() => {
      return props.valor ? "icono-check-fah" : "icono-times-fah";
    });

    const claseNumero = computed(() => "numero-fah");

    const claseCodigo = computed(() => {
      const campo = props.configuracion.nombre || "";

      if (campo.includes("categoria")) {
        return "codigo-categoria-fah";
      }
      if (campo.includes("especialidad")) {
        return "codigo-especialidad-fah";
      }
      if (campo.includes("grado")) {
        return "codigo-grado-fah";
      }
      if (campo.includes("departamento")) {
        return "codigo-departamento-fah";
      }
      if (campo.includes("municipio")) {
        return "codigo-municipio-fah";
      }

      return "codigo-default-fah";
    });

    const claseForaneo = computed(() => "relacion-foranea-fah");

    const claseTexto = computed(() => "texto-fah");

    return {
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

      datosRelacionBackend,
      valorForaneoResuelto,

      valorMostrar,
      textoBooleano,
      fechaFormateada,
      numeroFormateado,
      textoTruncado,

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
/* Estilos para celda dinamica */

/* Contenedor principal */
.celda-dinamica {
  display: flex;
  align-items: center;
  min-height: 2rem;
  padding: 4px 0;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.4;
}

/* Contenido de relaciones foraneas */
.contenido-relacion {
  width: 100%;
}

.relacion-completa {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nombre-relacion {
  font-size: 14px;
  font-weight: 600;
  color: #1e3a5f;
  line-height: 1.3;
}

.codigo-relacion {
  font-size: 11px;
  color: #6c757d;
  font-family: "Courier New", monospace;
  background: rgba(212, 175, 55, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
  width: fit-content;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.relacion-store {
  display: flex;
  align-items: center;
}

.relacion-foranea-fah {
  background: linear-gradient(135deg, #1e3a5f, #2c4f7a);
  color: #ffffff;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  box-shadow: 0 2px 4px rgba(30, 58, 95, 0.3);
  border: 1px solid rgba(212, 175, 55, 0.3);
  transition: all 0.2s ease;
}

.relacion-foranea-fah:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(30, 58, 95, 0.4);
  background: linear-gradient(135deg, #2c4f7a, #1e3a5f);
}

.relacion-fallback {
  display: flex;
  align-items: center;
}

.relacion-fallback-text {
  color: #6c757d;
  font-size: 11px;
  font-style: italic;
  background: #f8f9fa;
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid #e9ecef;
}

/* Contenido booleano */
.contenido-booleano {
  display: flex;
  align-items: center;
}

.badge-success-fah {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  background: linear-gradient(135deg, #28a745, #20c997);
  color: #ffffff;
  box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
  border: 1px solid rgba(40, 167, 69, 0.5);
}

.badge-inactive-fah {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  background: linear-gradient(135deg, #6c757d, #5a6268);
  color: #ffffff;
  box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
  border: 1px solid rgba(108, 117, 125, 0.5);
}

.icono-check-fah,
.icono-times-fah {
  font-size: 10px;
  font-weight: bold;
}

.icono-check-fah::before {
  content: "✓";
}

.icono-times-fah::before {
  content: "✕";
}

/* Contenido fecha */
.contenido-fecha {
  font-size: 13px;
  color: #495057;
  font-weight: 500;
  background: rgba(212, 175, 55, 0.1);
  padding: 3px 8px;
  border-radius: 4px;
  border-left: 3px solid #d4af37;
}

/* Contenido numerico */
.contenido-numero {
  text-align: right;
}

.numero-fah {
  background: linear-gradient(135deg, #d4af37, #f0c674);
  color: #1e3a5f;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  min-width: 24px;
  text-align: center;
  display: inline-block;
  box-shadow: 0 2px 4px rgba(212, 175, 55, 0.3);
  border: 1px solid rgba(212, 175, 55, 0.5);
  font-family: "Courier New", monospace;
}

/* Contenido URL */
.contenido-url {
  display: flex;
  align-items: center;
  gap: 6px;
}

.imagen-url {
  width: 24px;
  height: 24px;
  object-fit: contain;
  border-radius: 4px;
  border: 1px solid #e9ecef;
}

.link-url,
.link-email,
.link-telefono {
  color: #0ea5e9;
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 6px;
  border-radius: 4px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.link-url:hover,
.link-email:hover,
.link-telefono:hover {
  color: #1e3a5f;
  background: rgba(14, 165, 233, 0.1);
  border-color: rgba(14, 165, 233, 0.3);
  text-decoration: underline;
  transform: translateY(-1px);
}

.icono-link,
.icono-email,
.icono-telefono {
  font-size: 11px;
  opacity: 0.8;
}

.sin-url {
  color: #6c757d;
  font-size: 12px;
  font-style: italic;
}

/* Contenido codigo */
.contenido-codigo {
  display: flex;
  align-items: center;
}

.codigo-categoria-fah {
  background: linear-gradient(135deg, #6c757d, #5a6268);
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  font-family: "Courier New", monospace;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

.codigo-especialidad-fah {
  background: linear-gradient(135deg, #5a9bd4, #4a90c2);
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  font-family: "Courier New", monospace;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(90, 155, 212, 0.3);
}

.codigo-grado-fah {
  background: linear-gradient(135deg, #c1666b, #b55a5f);
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  font-family: "Courier New", monospace;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(193, 102, 107, 0.3);
}

.codigo-departamento-fah {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  font-family: "Courier New", monospace;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.codigo-municipio-fah {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  font-family: "Courier New", monospace;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(14, 165, 233, 0.3);
}

.codigo-default-fah {
  background: linear-gradient(135deg, #1e3a5f, #2c4f7a);
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  font-family: "Courier New", monospace;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(30, 58, 95, 0.3);
}

/* Contenido texto */
.contenido-texto {
  display: flex;
  align-items: center;
}

.texto-fah {
  font-weight: 600;
  color: #343a40;
  font-size: 14px;
  line-height: 1.3;
}

/* Contenido por defecto */
.contenido-defecto {
  color: #495057;
  font-size: 13px;
  font-style: italic;
}

/* Animaciones */
@keyframes fadeInFAH {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.celda-dinamica {
  animation: fadeInFAH 0.3s ease-out;
}

/* Efectos hover globales */
.badge-success-fah:hover,
.badge-inactive-fah:hover,
.numero-fah:hover,
.codigo-categoria-fah:hover,
.codigo-especialidad-fah:hover,
.codigo-grado-fah:hover,
.codigo-departamento-fah:hover,
.codigo-municipio-fah:hover,
.codigo-default-fah:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .celda-dinamica {
    min-height: 1.8rem;
  }

  .nombre-relacion {
    font-size: 12px;
  }

  .codigo-relacion {
    font-size: 10px;
    padding: 1px 4px;
  }

  .relacion-foranea-fah,
  .badge-success-fah,
  .badge-inactive-fah,
  .numero-fah,
  .codigo-categoria-fah,
  .codigo-especialidad-fah,
  .codigo-grado-fah,
  .codigo-departamento-fah,
  .codigo-municipio-fah,
  .codigo-default-fah {
    padding: 2px 6px;
    font-size: 10px;
  }

  .link-url,
  .link-email,
  .link-telefono {
    font-size: 12px;
  }

  .texto-fah {
    font-size: 13px;
  }

  .contenido-fecha {
    font-size: 12px;
    padding: 2px 6px;
  }
}

@media (max-width: 480px) {
  .celda-dinamica {
    min-height: 1.6rem;
  }

  .relacion-completa {
    gap: 1px;
  }

  .nombre-relacion {
    font-size: 11px;
  }

  .codigo-relacion {
    font-size: 9px;
  }

  .relacion-foranea-fah,
  .badge-success-fah,
  .badge-inactive-fah,
  .numero-fah {
    padding: 1px 4px;
    font-size: 9px;
  }

  .texto-fah {
    font-size: 12px;
  }

  .contenido-fecha {
    font-size: 11px;
  }
}

/* Accesibilidad */
.link-url:focus,
.link-email:focus,
.link-telefono:focus {
  outline: 2px solid #d4af37;
  outline-offset: 2px;
  border-radius: 4px;
}

/* Print styles */
@media print {
  .celda-dinamica {
    animation: none;
  }

  .badge-success-fah,
  .badge-inactive-fah,
  .numero-fah,
  .codigo-categoria-fah,
  .codigo-especialidad-fah,
  .codigo-grado-fah,
  .codigo-departamento-fah,
  .codigo-municipio-fah,
  .codigo-default-fah,
  .relacion-foranea-fah {
    background: #f8f9fa !important;
    color: #343a40 !important;
    box-shadow: none !important;
    border: 1px solid #e9ecef !important;
  }

  .link-url,
  .link-email,
  .link-telefono {
    color: #343a40 !important;
    text-decoration: underline !important;
  }
}

/* Modo alto contraste */
@media (prefers-contrast: high) {
  .badge-success-fah,
  .badge-inactive-fah,
  .numero-fah,
  .codigo-categoria-fah,
  .codigo-especialidad-fah,
  .codigo-grado-fah,
  .codigo-departamento-fah,
  .codigo-municipio-fah,
  .codigo-default-fah,
  .relacion-foranea-fah {
    border-width: 2px;
    font-weight: 700;
  }

  .link-url,
  .link-email,
  .link-telefono {
    border-width: 2px;
    font-weight: 600;
  }
}

/* Prefers reduced motion */
@media (prefers-reduced-motion: reduce) {
  .celda-dinamica {
    animation: none;
  }

  .badge-success-fah:hover,
  .badge-inactive-fah:hover,
  .numero-fah:hover,
  .codigo-categoria-fah:hover,
  .codigo-especialidad-fah:hover,
  .codigo-grado-fah:hover,
  .codigo-departamento-fah:hover,
  .codigo-municipio-fah:hover,
  .codigo-default-fah:hover,
  .relacion-foranea-fah:hover,
  .link-url:hover,
  .link-email:hover,
  .link-telefono:hover {
    transform: none;
    transition: none;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .texto-fah {
    color: #e9ecef;
  }

  .contenido-defecto {
    color: #e9ecef;
  }

  .relacion-fallback-text {
    color: #e9ecef;
    background: #343a40;
    border-color: #495057;
  }
}
</style>
