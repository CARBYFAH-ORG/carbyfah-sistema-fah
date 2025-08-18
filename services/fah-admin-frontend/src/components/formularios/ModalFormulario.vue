<!-- services\fah-admin-frontend\src\components\formularios\ModalFormulario.vue -->
<template>
  <div>
    <!-- Backdrop del modal -->
    <div
      v-if="esVisible"
      @click="manejarCerrar"
      class="modal-backdrop"
      role="dialog"
      aria-modal="true"
    ></div>

    <!-- Modal principal -->
    <div
      v-if="esVisible"
      class="modal-container"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="idTitulo"
      @click.stop
    >
      <!-- Header personalizado con icono -->
      <div class="modal-header">
        <div class="header-content">
          <div :class="['icono-esquema', claseIconoSegunModo]">
            {{ iconoEsquema }}
          </div>
          <div class="titulo-container">
            <h3 :id="idTitulo" class="titulo-modal">
              {{ tituloModal }}
            </h3>
            <p class="subtitulo-modal">
              {{ subtituloModal }}
            </p>
          </div>
        </div>

        <!-- Boton cerrar personalizado -->
        <button
          @click="manejarCerrar"
          class="boton-cerrar-fah"
          type="button"
          aria-label="Cerrar modal"
        >
          <i class="icono-cerrar">✕</i>
        </button>
      </div>

      <!-- Contenido del formulario dinamico -->
      <div class="contenido-modal">
        <FormularioDinamico
          ref="formularioDinamicoRef"
          :esquema="esquema"
          :modo="modo"
          :datos-iniciales="datosIniciales"
          :tema="tema"
          :solo-lectura="modoSoloLectura"
          @enviado="manejarEnviado"
          @error="manejarError"
          @cancelado="manejarCancelado"
        />
      </div>

      <!-- Footer con botones -->
      <div class="modal-footer">
        <!-- Boton Cancelar -->
        <button
          @click="manejarCancelar"
          :disabled="cargando"
          class="boton-fah boton-cancelar"
          type="button"
        >
          <i class="icono-boton">❌</i>
          <span class="texto-boton">Cancelar</span>
        </button>

        <!-- Boton para Crear -->
        <button
          v-if="modo === 'crear'"
          @click="activarEnvioFormulario"
          :disabled="cargando"
          class="boton-fah boton-crear"
          type="button"
        >
          <div v-if="cargando" class="spinner-fah"></div>
          <i v-else class="icono-boton">➕</i>
          <span class="texto-boton">{{ etiquetaBotonAccion }}</span>
        </button>

        <!-- Boton para Editar -->
        <button
          v-else-if="modo === 'editar'"
          @click="activarEnvioFormulario"
          :disabled="cargando"
          class="boton-fah boton-actualizar"
          type="button"
        >
          <div v-if="cargando" class="spinner-fah"></div>
          <i v-else class="icono-boton">✏️</i>
          <span class="texto-boton">{{ etiquetaBotonAccion }}</span>
        </button>

        <!-- Boton para Eliminar -->
        <button
          v-else-if="modo === 'eliminar'"
          @click="manejarEliminarDirecto"
          :disabled="cargando"
          class="boton-fah boton-eliminar"
          type="button"
        >
          <div v-if="cargando" class="spinner-fah"></div>
          <i v-else class="icono-boton">🗑️</i>
          <span class="texto-boton">{{ etiquetaBotonAccion }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from "vue";

import FormularioDinamico from "./FormularioDinamico.vue";

import { useCatalogosStore } from "@/stores/catalogosStore";
import { obtenerEsquema, obtenerNotificacion } from "@/config/esquemaCatalogos";
import { useToastFAH } from "@/composables/useToastFAH";

export default {
  name: "ModalFormulario",

  components: {
    FormularioDinamico,
  },

  props: {
    esquema: {
      type: String,
      required: true,
    },
    modo: {
      type: String,
      default: "crear",
      validator: (valor) => ["crear", "editar", "eliminar"].includes(valor),
    },
    datosIniciales: {
      type: Object,
      default: () => ({}),
    },
    visible: {
      type: Boolean,
      default: false,
    },
    tema: {
      type: String,
      default: "militar-oscuro",
    },
  },

  emits: ["update:visible", "guardado", "eliminado", "cancelado", "error"],

  setup(props, { emit }) {
    const catalogosStore = useCatalogosStore();
    const toast = useToastFAH();

    const cargando = ref(false);
    const formularioValido = ref(true);
    const datosFormulario = ref({});
    const formularioDinamicoRef = ref(null);

    // ID unico para accesibilidad
    const idTitulo = computed(() => {
      return `modal-titulo-${Date.now()}`;
    });

    // Manejar visibilidad del modal con v-model
    const esVisible = computed({
      get: () => props.visible,
      set: (valor) => emit("update:visible", valor),
    });

    // Obtener configuracion del esquema
    const configuracionEsquema = computed(() => {
      return obtenerEsquema(props.esquema);
    });

    // Titulo principal del modal
    const tituloModal = computed(() => {
      if (!configuracionEsquema.value) return "Formulario";

      const acciones = {
        crear: "Crear Nuevo",
        editar: "Editar",
        eliminar: "Eliminar",
      };

      const accion = acciones[props.modo] || "Formulario";
      return `${accion} ${configuracionEsquema.value.titulo}`;
    });

    // Subtitulo descriptivo del modal
    const subtituloModal = computed(() => {
      const mensajes = {
        crear: "Complete los campos requeridos para crear un nuevo registro",
        editar: "Modifique los campos necesarios y guarde los cambios",
        eliminar: "¿Está seguro de que desea eliminar este registro?",
      };

      return mensajes[props.modo] || "";
    });

    // Icono del esquema
    const iconoEsquema = computed(() => {
      return configuracionEsquema.value?.icono || "📋";
    });

    // Etiqueta del boton de accion principal
    const etiquetaBotonAccion = computed(() => {
      const etiquetas = {
        crear: "Crear",
        editar: "Actualizar",
        eliminar: "Eliminar",
      };

      return etiquetas[props.modo] || "Guardar";
    });

    // Clase CSS del icono segun modo
    const claseIconoSegunModo = computed(() => {
      const clases = {
        crear: "icono-crear",
        editar: "icono-editar",
        eliminar: "icono-eliminar",
      };

      return clases[props.modo] || "icono-crear";
    });

    // Determinar si el formulario debe ser solo lectura
    const modoSoloLectura = computed(() => {
      return props.modo === "eliminar";
    });

    // Manejar cierre del modal
    const manejarCerrar = () => {
      emit("update:visible", false);
      emit("cancelado");
    };

    // Manejar click en boton Cancelar
    const manejarCancelar = () => {
      manejarCerrar();
    };

    // Activar envio del formulario
    const activarEnvioFormulario = () => {
      if (formularioDinamicoRef.value) {
        formularioDinamicoRef.value.manejarEnvio();
      } else {
        toast.error("Error de Sistema", "No se pudo procesar el formulario");
      }
    };

    // Ejecutar eliminacion directa sin formulario
    const manejarEliminarDirecto = async () => {
      cargando.value = true;

      try {
        const resultado = await eliminarRegistro(
          props.esquema,
          props.datosIniciales
        );

        if (resultado.success) {
          const nombreRegistro = obtenerNombreRegistro(props.datosIniciales);
          toast.success(
            "Eliminación Exitosa",
            `${nombreRegistro} ha sido eliminado correctamente`
          );

          emit("eliminado", {
            esquema: props.esquema,
            datos: props.datosIniciales,
            resultado,
          });

          emit("update:visible", false);
        } else {
          toast.error("Error al Eliminar", "No se pudo eliminar el registro");

          emit("error", {
            modo: "eliminar",
            esquema: props.esquema,
            error: resultado.error,
          });
        }
      } catch (error) {
        toast.error("Error de Conexión", "No se pudo conectar al servidor");

        emit("error", {
          modo: "eliminar",
          esquema: props.esquema,
          error: error.message,
        });
      } finally {
        cargando.value = false;
      }
    };

    // Procesar datos enviados desde FormularioDinamico
    const manejarEnviado = async (evento) => {
      cargando.value = true;
      datosFormulario.value = evento.datos;

      try {
        let resultado;

        if (props.modo === "crear") {
          resultado = await ejecutarCreacion(evento.esquema, evento.datos);
        } else if (props.modo === "editar") {
          resultado = await ejecutarActualizacion(evento.esquema, evento.datos);
        } else {
          return;
        }

        if (resultado.success) {
          const nombreRegistro = obtenerNombreRegistro(evento.datos);
          const mensaje =
            props.modo === "crear"
              ? `${nombreRegistro} ha sido creado correctamente`
              : `${nombreRegistro} ha sido actualizado correctamente`;

          toast.success(
            props.modo === "crear"
              ? "Creación Exitosa"
              : "Actualización Exitosa",
            mensaje
          );

          emit("guardado", {
            modo: props.modo,
            esquema: evento.esquema,
            datos: evento.datos,
            resultado,
          });

          emit("update:visible", false);
        } else {
          const tipoError =
            props.modo === "crear" ? "Error al Crear" : "Error al Actualizar";
          toast.error(tipoError, "No se pudo completar la operación");

          emit("error", {
            modo: props.modo,
            esquema: evento.esquema,
            error: resultado.error,
          });
        }
      } catch (error) {
        toast.error("Error de Conexión", "No se pudo conectar al servidor");

        emit("error", {
          modo: props.modo,
          esquema: evento.esquema,
          error: error.message,
        });
      } finally {
        cargando.value = false;
      }
    };

    // Propagar errores del FormularioDinamico
    const manejarError = (evento) => {
      emit("error", evento);
    };

    // Manejar cancelacion del FormularioDinamico
    const manejarCancelado = (evento) => {
      manejarCerrar();
    };

    // Ejecutar creacion segun el tipo de esquema de catalogos
    const ejecutarCreacion = async (esquema, datos) => {
      switch (esquema) {
        case "tipos_genero":
          return await catalogosStore.createTipoGenero(datos);
        case "categorias_personal":
          return await catalogosStore.createCategoriaPersonal(datos);
        case "grados":
          return await catalogosStore.createGrado(datos);
        case "especialidades":
          return await catalogosStore.createEspecialidad(datos);
        case "niveles_prioridad":
          return await catalogosStore.createNivelPrioridad(datos);
        case "tipos_estado_general":
          return await catalogosStore.createTipoEstadoGeneral(datos);
        case "niveles_seguridad":
          return await catalogosStore.createNivelSeguridad(datos);
        case "paises":
          return await catalogosStore.createPais(datos);
        case "tipos_estructura_militar":
          return await catalogosStore.createTipoEstructuraMilitar(datos);
        case "tipos_jerarquia":
          return await catalogosStore.createTipoJerarquia(datos);
        case "tipos_evento":
          return await catalogosStore.createTipoEvento(datos);
        default:
          throw new Error(`Creación no implementada para: ${esquema}`);
      }
    };

    // Ejecutar actualizacion segun el tipo de esquema de catalogos
    const ejecutarActualizacion = async (esquema, datos) => {
      const id = props.datosIniciales.id;

      switch (esquema) {
        case "tipos_genero":
          return await catalogosStore.updateTipoGenero(id, datos);
        case "categorias_personal":
          return await catalogosStore.updateCategoriaPersonal(id, datos);
        case "grados":
          return await catalogosStore.updateGrado(id, datos);
        case "especialidades":
          return await catalogosStore.updateEspecialidad(id, datos);
        case "niveles_prioridad":
          return await catalogosStore.updateNivelPrioridad(id, datos);
        case "tipos_estado_general":
          return await catalogosStore.updateTipoEstadoGeneral(id, datos);
        case "niveles_seguridad":
          return await catalogosStore.updateNivelSeguridad(id, datos);
        case "paises":
          return await catalogosStore.updatePais(id, datos);
        case "tipos_estructura_militar":
          return await catalogosStore.updateTipoEstructuraMilitar(id, datos);
        case "tipos_jerarquia":
          return await catalogosStore.updateTipoJerarquia(id, datos);
        case "tipos_evento":
          return await catalogosStore.updateTipoEvento(id, datos);
        default:
          throw new Error(`Actualización no implementada para: ${esquema}`);
      }
    };

    // Ejecutar eliminacion segun el tipo de esquema de catalogos
    const eliminarRegistro = async (esquema, datos) => {
      const id = datos.id;

      switch (esquema) {
        case "tipos_genero":
          return await catalogosStore.deleteTipoGenero(id);
        case "categorias_personal":
          return await catalogosStore.deleteCategoriaPersonal(id);
        case "grados":
          return await catalogosStore.deleteGrado(id);
        case "especialidades":
          return await catalogosStore.deleteEspecialidad(id);
        case "niveles_prioridad":
          return await catalogosStore.deleteNivelPrioridad(id);
        case "tipos_estado_general":
          return await catalogosStore.deleteTipoEstadoGeneral(id);
        case "niveles_seguridad":
          return await catalogosStore.deleteNivelSeguridad(id);
        case "paises":
          return await catalogosStore.deletePais(id);
        case "tipos_estructura_militar":
          return await catalogosStore.deleteTipoEstructuraMilitar(id);
        case "tipos_jerarquia":
          return await catalogosStore.deleteTipoJerarquia(id);
        case "tipos_evento":
          return await catalogosStore.deleteTipoEvento(id);
        default:
          throw new Error(`Eliminación no implementada para: ${esquema}`);
      }
    };

    // Obtener nombre representativo del registro de catalogos
    const obtenerNombreRegistro = (datos) => {
      const camposPrioridad = [
        "nombre",
        "nombre_categoria",
        "nombre_especialidad",
        "nombre_grado",
        "nombre_evento",
        "nombre_tipo",
        "codigo",
        "codigo_categoria",
        "codigo_especialidad",
        "codigo_grado",
        "codigo_evento",
        "codigo_tipo",
      ];

      for (const campo of camposPrioridad) {
        if (datos[campo]) {
          return datos[campo];
        }
      }

      return "Registro";
    };

    // Resetear estado cuando se cierra el modal
    watch(
      () => props.visible,
      (esVisible) => {
        if (!esVisible) {
          cargando.value = false;
          formularioValido.value = true;
          datosFormulario.value = {};
        }
      }
    );

    return {
      cargando,
      formularioValido,
      datosFormulario,
      formularioDinamicoRef,

      idTitulo,
      esVisible,
      configuracionEsquema,
      tituloModal,
      subtituloModal,
      iconoEsquema,
      etiquetaBotonAccion,
      claseIconoSegunModo,
      modoSoloLectura,

      manejarCerrar,
      manejarCancelar,
      manejarEnviado,
      manejarError,
      manejarCancelado,
      activarEnvioFormulario,
      manejarEliminarDirecto,
    };
  },
};
</script>

<style scoped>
/* Modal Formulario */

/* Backdrop del modal */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(30, 58, 95, 0.8);
  backdrop-filter: blur(4px);
  z-index: 9998;
  animation: backdropFadeIn 0.3s ease;
}

@keyframes backdropFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Contenedor principal del modal */
.modal-container {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #1e3a5f;
  border-radius: 16px;
  box-shadow: 0 25px 50px rgba(30, 58, 95, 0.4),
    0 0 0 3px rgba(212, 175, 55, 0.3);
  z-index: 9999;
  width: 90vw;
  max-width: 600px;
  max-height: 90vh;
  overflow: hidden;
  animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  border: 2px solid #d4af37;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translate(-50%, -60%) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
}

/* Header del modal */
.modal-header {
  background: linear-gradient(135deg, #1e3a5f 0%, #2c4f7a 100%);
  color: #ffffff;
  padding: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 3px solid #d4af37;
  position: relative;
}

.modal-header::after {
  content: "";
  position: absolute;
  bottom: -3px;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #d4af37, #f0c674, #d4af37);
  animation: doradoPulse 2s ease-in-out infinite;
}

@keyframes doradoPulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.header-content {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
}

/* Icono del esquema */
.icono-esquema {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #ffffff;
  border: 2px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.icono-esquema:hover {
  transform: scale(1.1);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.icono-crear {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
}

.icono-editar {
  background: linear-gradient(135deg, #5a9bd4, #4a90c2);
  box-shadow: 0 4px 15px rgba(90, 155, 212, 0.4);
}

.icono-eliminar {
  background: linear-gradient(135deg, #c1666b, #b55a5f);
  box-shadow: 0 4px 15px rgba(193, 102, 107, 0.4);
}

/* Titulos */
.titulo-container {
  flex: 1;
}

.titulo-modal {
  margin: 0;
  color: #ffffff;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.2;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
}

.subtitulo-modal {
  margin: 4px 0 0 0;
  color: #e9ecef;
  font-size: 14px;
  font-weight: 400;
  opacity: 0.9;
  line-height: 1.3;
}

/* Boton cerrar personalizado */
.boton-cerrar-fah {
  background: linear-gradient(
    135deg,
    rgba(193, 102, 107, 0.2),
    rgba(193, 102, 107, 0.1)
  );
  border: 2px solid #c1666b;
  color: #c1666b;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(193, 102, 107, 0.2);
  flex-shrink: 0;
}

.boton-cerrar-fah:hover {
  background: linear-gradient(
    135deg,
    rgba(193, 102, 107, 0.3),
    rgba(193, 102, 107, 0.15)
  );
  border-color: #d47479;
  color: #d47479;
  transform: scale(1.1);
  box-shadow: 0 4px 15px rgba(193, 102, 107, 0.4);
}

.boton-cerrar-fah:active {
  transform: scale(0.95);
}

.icono-cerrar {
  font-size: 16px;
  font-weight: bold;
  line-height: 1;
}

/* Contenido del modal */
.contenido-modal {
  background: #1e3a5f;
  padding: 24px;
  max-height: 60vh;
  overflow-y: auto;
  border-bottom: 1px solid #495057;
}

.contenido-modal::-webkit-scrollbar {
  width: 8px;
}

.contenido-modal::-webkit-scrollbar-track {
  background: #e9ecef;
  border-radius: 4px;
}

.contenido-modal::-webkit-scrollbar-thumb {
  background: #d4af37;
  border-radius: 4px;
}

.contenido-modal::-webkit-scrollbar-thumb:hover {
  background: #c19b26;
}

/* Footer del modal */
.modal-footer {
  background: linear-gradient(135deg, #343a40 0%, #495057 100%);
  padding: 20px 24px;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  align-items: center;
  border-top: 1px solid #495057;
}

/* Botones autocontenidos */
.boton-fah {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: 2px solid;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 120px;
  justify-content: center;
  position: relative;
  overflow: hidden;
  outline: none;
  user-select: none;
  font-family: inherit;
}

.boton-fah::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.2),
    transparent
  );
  transition: left 0.5s ease;
}

.boton-fah:hover::before {
  left: 100%;
}

.boton-fah:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.boton-fah:active {
  transform: translateY(0);
}

.boton-fah:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* Boton cancelar */
.boton-cancelar {
  background: linear-gradient(135deg, #6c757d, #5a6268);
  border-color: #6c757d;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.boton-cancelar:hover {
  background: linear-gradient(135deg, #5a6268, #495057);
  border-color: #495057;
  box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
}

/* Boton crear */
.boton-crear {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  border-color: #0ea5e9;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.boton-crear:hover {
  background: linear-gradient(135deg, #0284c7, #0369a1);
  border-color: #0284c7;
  box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
}

/* Boton actualizar */
.boton-actualizar {
  background: linear-gradient(135deg, #5a9bd4, #4a90c2);
  border-color: #5a9bd4;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(90, 155, 212, 0.3);
}

.boton-actualizar:hover {
  background: linear-gradient(135deg, #4a90c2, #3a80b2);
  border-color: #4a90c2;
  box-shadow: 0 8px 20px rgba(90, 155, 212, 0.4);
}

/* Boton eliminar */
.boton-eliminar {
  background: linear-gradient(135deg, #c1666b, #b55a5f);
  border-color: #c1666b;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(193, 102, 107, 0.3);
}

.boton-eliminar:hover {
  background: linear-gradient(135deg, #b55a5f, #a94e53);
  border-color: #b55a5f;
  box-shadow: 0 8px 20px rgba(193, 102, 107, 0.4);
}

/* Iconos y texto de botones */
.icono-boton {
  font-size: 16px;
  line-height: 1;
}

.texto-boton {
  font-weight: 600;
  font-size: 13px;
}

/* Spinner de carga */
.spinner-fah {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid #ffffff;
  border-radius: 50%;
  animation: spinFAH 1s linear infinite;
}

@keyframes spinFAH {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Estados de focus para accesibilidad */
.boton-fah:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.5);
}

.boton-cerrar-fah:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(193, 102, 107, 0.5);
}

/* Responsive */
@media (max-width: 768px) {
  .modal-container {
    width: 95vw;
    max-height: 95vh;
    top: 50%;
    transform: translate(-50%, -50%);
  }

  .modal-header {
    padding: 20px;
  }

  .header-content {
    gap: 12px;
  }

  .icono-esquema {
    width: 48px;
    height: 48px;
    font-size: 20px;
  }

  .titulo-modal {
    font-size: 18px;
  }

  .subtitulo-modal {
    font-size: 13px;
  }

  .contenido-modal {
    padding: 20px;
    max-height: 50vh;
  }

  .modal-footer {
    padding: 16px 20px;
    flex-direction: column-reverse;
    gap: 8px;
  }

  .boton-fah {
    width: 100%;
    min-width: auto;
  }
}

@media (max-width: 480px) {
  .modal-container {
    width: 98vw;
    max-height: 98vh;
  }

  .modal-header {
    padding: 16px;
  }

  .icono-esquema {
    width: 40px;
    height: 40px;
    font-size: 18px;
  }

  .titulo-modal {
    font-size: 16px;
  }

  .subtitulo-modal {
    font-size: 12px;
  }

  .contenido-modal {
    padding: 16px;
  }

  .modal-footer {
    padding: 12px 16px;
  }
}

/* Animaciones adicionales */
@keyframes modalShake {
  0%,
  100% {
    transform: translate(-50%, -50%);
  }
  10%,
  30%,
  50%,
  70%,
  90% {
    transform: translate(-51%, -50%);
  }
  20%,
  40%,
  60%,
  80% {
    transform: translate(-49%, -50%);
  }
}

.modal-container.error {
  animation: modalShake 0.5s ease-in-out;
}

/* Print styles */
@media print {
  .modal-backdrop,
  .modal-container {
    display: none !important;
  }
}

/* Modo alto contraste */
@media (prefers-contrast: high) {
  .modal-container {
    border: 4px solid #000000;
  }

  .boton-fah {
    border-width: 3px;
  }
}

/* Prefers reduced motion */
@media (prefers-reduced-motion: reduce) {
  .modal-container,
  .modal-backdrop,
  .boton-fah,
  .icono-esquema {
    animation: none;
    transition: none;
  }

  .boton-fah:hover {
    transform: none;
  }
}
</style>
