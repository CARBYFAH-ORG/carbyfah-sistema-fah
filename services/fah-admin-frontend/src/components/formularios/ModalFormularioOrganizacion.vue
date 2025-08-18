<!-- services\fah-admin-frontend\src\components\formularios\ModalFormularioOrganizacion.vue -->
<template>
  <div>
    <Dialog
      v-model:visible="esVisible"
      :header="encabezadoModal"
      :style="{ width: anchoModal }"
      :modal="true"
      :closable="true"
      :draggable="false"
      :resizable="false"
      position="center"
      class="modal-formulario-organizacion"
      @hide="manejarCerrar"
    >
      <!-- Header personalizado con icono -->
      <template #header>
        <div class="flex items-center gap-3">
          <div
            :class="[
              'text-3xl w-14 h-14 rounded-xl text-white flex items-center justify-center shadow-lg border-2 border-white/30 transition-all duration-300 hover:scale-105 hover:shadow-xl',
              claseIconoSegunModo,
            ]"
          >
            {{ iconoEsquema }}
          </div>
          <div>
            <h3 class="m-0 text-white text-xl font-semibold">
              {{ tituloModal }}
            </h3>
            <p class="m-0 mt-1 text-gray-300 text-sm font-normal">
              {{ subtituloModal }}
            </p>
          </div>
        </div>
      </template>

      <!-- Contenido del formulario dinamico organizacion -->
      <div class="contenido-modal bg-gray-700 p-0">
        <FormularioDinamicoOrganizacion
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

      <!-- Footer con botones especializados organizacion -->
      <template #footer>
        <div class="flex gap-3 justify-end items-center p-0 bg-gray-800">
          <!-- Boton Cancelar -->
          <Button
            label="Cancelar"
            icon="pi pi-times"
            @click="manejarCancelar"
            :disabled="cargando"
            class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl border-2 border-gray-400 hover:border-gray-300 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-gray-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none uppercase tracking-wide text-sm min-w-[120px]"
            unstyled
          />

          <!-- Boton para Crear (Verde) -->
          <Button
            v-if="modo === 'crear'"
            :label="etiquetaBotonAccion"
            :icon="iconoBotonAccion"
            @click="activarEnvioFormulario"
            :disabled="cargando"
            :loading="cargando"
            class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-xl border-2 border-green-400 hover:border-green-300 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-green-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none uppercase tracking-wide text-sm min-w-[120px]"
            unstyled
          />

          <!-- Boton para Editar (Naranja) -->
          <Button
            v-else-if="modo === 'editar'"
            :label="etiquetaBotonAccion"
            :icon="iconoBotonAccion"
            @click="activarEnvioFormulario"
            :disabled="cargando"
            :loading="cargando"
            class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-3 px-6 rounded-xl border-2 border-orange-400 hover:border-orange-300 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-orange-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none uppercase tracking-wide text-sm min-w-[120px]"
            unstyled
          />

          <!-- Boton para Eliminar (Red) -->
          <Button
            v-else-if="modo === 'eliminar'"
            :label="etiquetaBotonAccion"
            :icon="iconoBotonAccion"
            @click="manejarEliminarDirecto"
            :disabled="cargando"
            :loading="cargando"
            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-6 rounded-xl border-2 border-red-400 hover:border-red-300 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-red-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none uppercase tracking-wide text-sm min-w-[120px]"
            unstyled
          />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script>
import { ref, computed, watch } from "vue";
import { useToastFAH } from "@/composables/useToastFAH";

import Dialog from "primevue/dialog";
import Button from "primevue/button";

import FormularioDinamicoOrganizacion from "./FormularioDinamicoOrganizacion.vue";

import { useOrganizacionStore } from "@/stores/organizacionStore";
import {
  obtenerEsquema,
  obtenerNotificacion,
} from "@/config/esquemaOrganizacion";

export default {
  name: "ModalFormularioOrganizacion",

  components: {
    Dialog,
    Button,
    FormularioDinamicoOrganizacion,
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
      default: "organizacion-purple",
    },
  },

  emits: ["update:visible", "guardado", "eliminado", "cancelado", "error"],

  setup(props, { emit }) {
    const organizacionStore = useOrganizacionStore();
    const toast = useToastFAH();

    const cargando = ref(false);
    const formularioValido = ref(true);
    const datosFormulario = ref({});
    const formularioDinamicoRef = ref(null);

    // Manejar visibilidad del modal con v-model
    const esVisible = computed({
      get: () => props.visible,
      set: (valor) => emit("update:visible", valor),
    });

    // Obtener configuracion del esquema
    const configuracionEsquema = computed(() => {
      return obtenerEsquema(props.esquema);
    });

    // Generar encabezado del modal segun modo
    const encabezadoModal = computed(() => {
      if (!configuracionEsquema.value) return "Formulario";

      const acciones = {
        crear: "Crear Nuevo",
        editar: "Editar",
        eliminar: "Eliminar",
      };

      const accion = acciones[props.modo] || "Formulario";
      return `${accion} ${configuracionEsquema.value.titulo}`;
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

    // Ancho del modal
    const anchoModal = computed(() => {
      return configuracionEsquema.value?.ancho || "600px";
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

    // Icono del boton de accion principal
    const iconoBotonAccion = computed(() => {
      const iconos = {
        crear: "pi pi-plus",
        editar: "pi pi-check",
        eliminar: "pi pi-trash",
      };

      return iconos[props.modo] || "pi pi-save";
    });

    // Clase CSS del icono segun modo
    const claseIconoSegunModo = computed(() => {
      const clases = {
        crear:
          "bg-gradient-to-br from-green-500 to-green-700 shadow-green-500/40 hover:shadow-green-500/50",
        editar:
          "bg-gradient-to-br from-blue-500 to-blue-700 shadow-blue-500/40 hover:shadow-blue-500/50",
        eliminar:
          "bg-gradient-to-br from-red-500 to-red-700 shadow-red-500/40 hover:shadow-red-500/50",
      };

      return (
        clases[props.modo] ||
        "bg-gradient-to-br from-blue-500 to-blue-700 shadow-blue-500/40"
      );
    });

    // Determinar si el formulario debe ser solo lectura
    const modoSoloLectura = computed(() => {
      return props.modo === "eliminar";
    });

    // Manejar cierre del modal desde X o Escape
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
        toast.error(
          "Error",
          "No se pudo procesar el formulario de organización"
        );
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
            "Registro Eliminado",
            `${nombreRegistro} eliminado exitosamente`
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
        toast.error("Error de Conexión", "No se pudo conectar con el servidor");

        emit("error", {
          modo: "eliminar",
          esquema: props.esquema,
          error: error.message,
        });
      } finally {
        cargando.value = false;
      }
    };

    // Procesar datos enviados desde FormularioDinamicoOrganizacion
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
          const tipoNotificacion =
            props.modo === "crear" ? "creado" : "actualizado";

          const mensaje =
            props.modo === "crear"
              ? "creado exitosamente"
              : "actualizado exitosamente";
          const titulo =
            props.modo === "crear" ? "Registro Creado" : "Registro Actualizado";
          toast.success(titulo, `${nombreRegistro} ${mensaje}`);

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
        toast.error("Error de Conexión", "No se pudo conectar con el servidor");

        emit("error", {
          modo: props.modo,
          esquema: evento.esquema,
          error: error.message,
        });
      } finally {
        cargando.value = false;
      }
    };

    // Propagar errores del FormularioDinamicoOrganizacion
    const manejarError = (evento) => {
      emit("error", evento);
    };

    // Manejar cancelacion del FormularioDinamicoOrganizacion
    const manejarCancelado = (evento) => {
      manejarCerrar();
    };

    // Ejecutar creacion segun el tipo de esquema de organizacion
    const ejecutarCreacion = async (esquema, datos) => {
      switch (esquema) {
        case "departamentos":
          return await organizacionStore.createDepartamento(datos);
        case "municipios":
          return await organizacionStore.createMunicipio(datos);
        case "ciudades":
          return await organizacionStore.createCiudad(datos);
        case "ubicaciones_geograficas":
          return await organizacionStore.createUbicacionGeografica(datos);
        case "estructura_militar":
          return await organizacionStore.createEstructuraMilitar(datos);
        case "cargos":
          return await organizacionStore.createCargo(datos);
        case "roles_funcionales":
          return await organizacionStore.createRolFuncional(datos);
        default:
          throw new Error(`Creación no implementada para: ${esquema}`);
      }
    };

    // Ejecutar actualizacion segun el tipo de esquema de organizacion
    const ejecutarActualizacion = async (esquema, datos) => {
      const id = props.datosIniciales.id;

      switch (esquema) {
        case "departamentos":
          return await organizacionStore.updateDepartamento(id, datos);
        case "municipios":
          return await organizacionStore.updateMunicipio(id, datos);
        case "ciudades":
          return await organizacionStore.updateCiudad(id, datos);
        case "ubicaciones_geograficas":
          return await organizacionStore.updateUbicacionGeografica(id, datos);
        case "estructura_militar":
          return await organizacionStore.updateEstructuraMilitar(id, datos);
        case "cargos":
          return await organizacionStore.updateCargo(id, datos);
        case "roles_funcionales":
          return await organizacionStore.updateRolFuncional(id, datos);
        default:
          throw new Error(`Actualización no implementada para: ${esquema}`);
      }
    };

    // Ejecutar eliminacion segun el tipo de esquema de organizacion
    const eliminarRegistro = async (esquema, datos) => {
      const id = datos.id;

      switch (esquema) {
        case "departamentos":
          return await organizacionStore.deleteDepartamento(id);
        case "municipios":
          return await organizacionStore.deleteMunicipio(id);
        case "ciudades":
          return await organizacionStore.deleteCiudad(id);
        case "ubicaciones_geograficas":
          return await organizacionStore.deleteUbicacionGeografica(id);
        case "estructura_militar":
          return await organizacionStore.deleteEstructuraMilitar(id);
        case "cargos":
          return await organizacionStore.deleteCargo(id);
        case "roles_funcionales":
          return await organizacionStore.deleteRolFuncional(id);
        default:
          throw new Error(`Eliminación no implementada para: ${esquema}`);
      }
    };

    // Obtener nombre representativo del registro de organizacion
    const obtenerNombreRegistro = (datos) => {
      const camposPrioridad = [
        "nombre_departamento",
        "nombre_municipio",
        "nombre_ciudad",
        "nombre_ubicacion",
        "nombre_unidad",
        "nombre_cargo",
        "nombre_rol",
        "codigo_departamento",
        "codigo_municipio",
        "codigo_ciudad",
        "codigo_ubicacion",
        "codigo_unidad",
        "codigo_cargo",
        "codigo_rol",
        "nombre",
        "codigo",
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

      esVisible,
      configuracionEsquema,
      encabezadoModal,
      tituloModal,
      subtituloModal,
      iconoEsquema,
      anchoModal,
      etiquetaBotonAccion,
      iconoBotonAccion,
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
/* Estilos para modal formulario organizacion */

/* Modal principal con fondo oscuro como el original */
.modal-formulario-organizacion :global(.p-dialog-mask) {
  @apply bg-slate-600 !important;
  backdrop-filter: none !important;
}

.modal-formulario-organizacion :global(.p-dialog) {
  background: white !important;
  z-index: 999999 !important;
  position: fixed !important;
  top: 10% !important;
  left: 50% !important;
  transform: translateX(-50%) !important;
  max-width: 90vw !important;
  border: 3px solid #f4d03f !important;
}

.modal-formulario-organizacion :global(.p-dialog-header) {
  @apply bg-gray-700 text-white border-b border-gray-700 rounded-t-xl px-6 py-5 !important;
}

.modal-formulario-organizacion :global(.p-dialog-content) {
  @apply bg-gray-700 p-6 !important;
  border-radius: 0 !important;
}

.modal-formulario-organizacion :global(.p-dialog-footer) {
  @apply bg-gray-800 border-t border-gray-700 rounded-b-xl px-6 py-4 !important;
}

/* Animacion de entrada */
.modal-formulario-organizacion :global(.p-dialog) {
  animation: modalFadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1.05) translateY(0);
  }
}

/* Boton cerrar */
.modal-formulario-organizacion :global(.p-dialog-header-close) {
  @apply bg-transparent text-gray-300 border-none w-8 h-8 rounded-full transition-all duration-300 !important;
}

.modal-formulario-organizacion :global(.p-dialog-header-close:hover) {
  @apply bg-white/10 text-red-500 !important;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-formulario-organizacion :global(.p-dialog) {
    @apply m-2 max-h-[calc(100vh-1rem)] !important;
  }
}
</style>
