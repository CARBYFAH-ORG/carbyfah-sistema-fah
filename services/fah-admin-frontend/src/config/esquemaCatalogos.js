// ESQUEMAS DE CATÁLOGOS CARBYFAH
// Configuración ultra-simplificada para 11 tablas + GRADOS INTEGRADO

/**
 * SINTAXIS DE CAMPOS:
 * 'nombre:tipo:opciones'
 * 
 * TIPOS DISPONIBLES:
 * - texto: Campo de texto simple
 * - numero: Campo numérico  
 * - seleccion: Dropdown con opciones
 * - area_texto: Textarea
 * - booleano: Checkbox
 * - fecha: Date picker
 * - foraneo_autocompletado: ✅ NUEVO - Campo con búsqueda inteligente para foráneas
 * 
 * OPCIONES:
 * - requerido: Campo obligatorio
 * - min:N: Valor mínimo (números)
 * - max:N: Valor máximo (números)
 * - longitud:N: Longitud máxima (texto)
 * - referencia:tabla: Para campos de selección que vienen de otra tabla
 */

// Importar funciones desde el composable
const generarEtiquetaAmigable = (nombreCampo) => {
    const mapeoEtiquetas = {
        // Campos comunes
        codigo: 'Código',
        nombre: 'Nombre',
        abreviatura: 'Abreviatura',

        // Categorías personal
        codigo_categoria: 'Código Categoría',
        nombre_categoria: 'Nombre Categoría',
        orden_jerarquico: 'Orden Jerárquico',

        // Especialidades
        codigo_especialidad: 'Código Especialidad',
        nombre_especialidad: 'Nombre Especialidad',
        insignia_url: 'URL Insignia',

        // ✅ GRADOS - campos específicos CORREGIDOS
        categoria_personal_id: 'Categoría Personal',
        codigo_grado: 'Código Grado',
        nombre_grado: 'Nombre Grado',

        // Niveles
        nivel_numerico: 'Nivel Numérico',
        requiere_autorizacion: 'Requiere Autorización',
        tiempo_retencion_anos: 'Tiempo Retención (años)',

        // Países
        nombre_oficial: 'Nombre Oficial',
        codigo_iso3: 'Código ISO3',
        codigo_telefono: 'Código Teléfono',
        moneda_oficial: 'Moneda Oficial',

        // Estados
        permite_operaciones: 'Permite Operaciones',
        es_estado_final: 'Es Estado Final',
        requiere_justificacion: 'Requiere Justificación',

        // Estructura militar
        codigo_tipo: 'Código Tipo',
        nombre_tipo: 'Nombre Tipo',
        nivel_organizacional: 'Nivel Organizacional',
        nivel_autoridad: 'Nivel Autoridad',

        // Eventos
        codigo_evento: 'Código Evento',
        nombre_evento: 'Nombre Evento',
        requiere_aprobacion: 'Requiere Aprobación'
    }

    return mapeoEtiquetas[nombreCampo] || formatearNombreCampo(nombreCampo)
}

const formatearNombreCampo = (nombreCampo) => {
    return nombreCampo
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase())
}

const generarPlaceholder = (nombreCampo, tipoCampo) => {
    const placeholders = {
        // Específicos por campo
        codigo: 'Ej: M, F, OFICIAL',
        nombre: 'Ej: Masculino, Femenino',
        abreviatura: 'Ej: M, F',
        codigo_categoria: 'Ej: OFICIAL, SUBOFICIAL',
        nombre_categoria: 'Ej: Oficial, Suboficial',
        orden_jerarquico: 'Ej: 1, 2, 3...',
        codigo_especialidad: 'Ej: AVI, COM, INT',
        nombre_especialidad: 'Ej: Aviación, Comunicaciones',
        insignia_url: 'Ej: https://ejemplo.com/insignia.png',

        // ✅ GRADOS - placeholders específicos CORREGIDOS
        categoria_personal_id: 'Buscar categoría personal...',
        codigo_grado: 'Ej: SUBTTE, TTE, CTTE',
        nombre_grado: 'Ej: Subteniente, Teniente',

        nivel_numerico: 'Ej: 1, 2, 3...',
        tiempo_retencion_anos: 'Ej: 5, 10, 15...',
        codigo_iso3: 'Ej: HND, USA, GTM',
        codigo_telefono: 'Ej: +504, +1, +502',
        moneda_oficial: 'Ej: Lempira, Dólar',
        codigo_tipo: 'Ej: CMD, BASE, ESC',
        nombre_tipo: 'Ej: Comandancia, Base',
        nivel_organizacional: 'Ej: 1, 2, 3...',
        nivel_autoridad: 'Ej: 1, 2, 3...',
        codigo_evento: 'Ej: CAP, MIS, PER',
        nombre_evento: 'Ej: Capacitación, Misión'
    }

    return placeholders[nombreCampo] || `Ingrese ${generarEtiquetaAmigable(nombreCampo).toLowerCase()}`
}

export const ESQUEMAS_CATALOGOS = {
    // Tipos de género
    tipos_genero: {
        titulo: 'Tipo de Género',
        icono: '👥',
        tabla: 'tipos_genero',
        ancho: '600px',
        campos: [
            'codigo:texto:requerido:longitud:10',
            'nombre:texto:requerido:longitud:50',
            'abreviatura:texto:longitud:5'
        ],
        mostrarEnTabla: ['codigo', 'nombre', 'abreviatura'],
        ordenarPor: 'nombre'
    },

    // Categorías personal
    categorias_personal: {
        titulo: 'Categoría de Personal',
        icono: '🏢',
        tabla: 'categorias_personal',
        ancho: '700px',
        campos: [
            'codigo_categoria:texto:requerido:longitud:20',
            'nombre_categoria:texto:requerido:longitud:100',
            'orden_jerarquico:texto:requerido:longitud:3:pattern:^[0-9]+$',
        ],
        mostrarEnTabla: ['codigo_categoria', 'nombre_categoria', 'orden_jerarquico'],
        ordenarPor: 'orden_jerarquico'
    },

    // Especialidades
    especialidades: {
        titulo: 'Especialidad Militar',
        icono: '🎖️',
        tabla: 'especialidades',
        ancho: '700px',
        campos: [
            'codigo_especialidad:texto:requerido:longitud:20',
            'nombre_especialidad:texto:requerido:longitud:100',
            'insignia_url:texto:longitud:500'
        ],
        mostrarEnTabla: ['codigo_especialidad', 'nombre_especialidad', 'insignia_url'],
        ordenarPor: 'nombre_especialidad'
    },

    // ✅ GRADOS - CONFIGURACIÓN CORREGIDA PARA SISTEMA DINÁMICO
    grados: {
        titulo: 'Grado Militar',
        icono: '⭐',
        tabla: 'grados',
        ancho: '800px',
        campos: [
            'codigo_grado:texto:requerido:longitud:20',
            'nombre_grado:texto:requerido:longitud:100',
            'orden_jerarquico:numero:requerido:min:1:max:50',
            'categoria_personal_id:foraneo_autocompletado:requerido:referencia:categorias_personal',
            'abreviatura:texto:longitud:20'
        ],
        mostrarEnTabla: ['codigo_grado', 'nombre_grado', 'orden_jerarquico', 'categoria_personal_id', 'abreviatura'],
        ordenarPor: 'orden_jerarquico'
    },

    // Niveles de prioridad
    niveles_prioridad: {
        titulo: 'Nivel de Prioridad',
        icono: '⚡',
        tabla: 'niveles_prioridad',
        ancho: '600px',
        campos: [
            'codigo:texto:requerido:longitud:20',
            'nombre:texto:requerido:longitud:100',
            'nivel_numerico:numero:requerido:min:1:max:10'
        ],
        mostrarEnTabla: ['codigo', 'nombre', 'nivel_numerico'],
        ordenarPor: 'nivel_numerico'
    },

    // Niveles de seguridad
    niveles_seguridad: {
        titulo: 'Nivel de Seguridad',
        icono: '🔒',
        tabla: 'niveles_seguridad',
        ancho: '750px',
        campos: [
            'codigo:texto:requerido:longitud:20',
            'nombre:texto:requerido:longitud:100',
            'nivel_numerico:numero:requerido:min:1:max:5',
            'requiere_autorizacion:booleano',
            'tiempo_retencion_anos:numero:min:1:max:50'
        ],
        mostrarEnTabla: ['codigo', 'nombre', 'nivel_numerico', 'requiere_autorizacion'],
        ordenarPor: 'nivel_numerico'
    },

    // Países
    paises: {
        titulo: 'País',
        icono: '🌍',
        tabla: 'paises',
        ancho: '800px',
        campos: [
            'nombre:autocompletado_api:requerido:api:restcountries',
            'nombre_oficial:texto:longitud:200',
            'codigo_iso3:texto:requerido:longitud:3',
            'codigo_telefono:texto:longitud:10',
            'moneda_oficial:texto:longitud:50'
        ],
        mostrarEnTabla: ['nombre', 'codigo_iso3', 'codigo_telefono'],
        ordenarPor: 'nombre'
    },

    // Tipos estado general
    tipos_estado_general: {
        titulo: 'Tipo de Estado General',
        icono: '📊',
        tabla: 'tipos_estado_general',
        ancho: '750px',
        campos: [
            'codigo:texto:requerido:longitud:20',
            'nombre:texto:requerido:longitud:100',
            'permite_operaciones:booleano',
            'es_estado_final:booleano',
            'requiere_justificacion:booleano'
        ],
        mostrarEnTabla: ['codigo', 'nombre', 'permite_operaciones'],
        ordenarPor: 'nombre'
    },

    // tipos_estado_general: {
    //     titulo: 'Tipo de Estado General',
    //     icono: '📊',
    //     tabla: 'tipos_estado_general',
    //     endpoint: 'catalogos/tipos-estado-general', // ← AGREGAR ESTA LÍNEA
    //     ancho: '750px',
    //     campos: [
    //         'codigo:texto:requerido:longitud:20',
    //         'nombre:texto:requerido:longitud:100',
    //         'permite_operaciones:booleano',
    //         'es_estado_final:booleano',
    //         'requiere_justificacion:booleano'
    //     ],
    //     mostrarEnTabla: ['codigo', 'nombre', 'permite_operaciones'],
    //     ordenarPor: 'nombre'
    // },

    // Tipos estructura militar
    tipos_estructura_militar: {
        titulo: 'Tipo de Estructura Militar',
        icono: '🏛️',
        tabla: 'tipos_estructura_militar',
        ancho: '700px',
        campos: [
            'codigo_tipo:texto:requerido:longitud:20',
            'nombre_tipo:texto:requerido:longitud:100',
            'nivel_organizacional:texto:requerido:longitud:3:pattern:^[0-9]+$'
        ],
        mostrarEnTabla: ['codigo_tipo', 'nombre_tipo', 'nivel_organizacional'],
        ordenarPor: 'nivel_organizacional'
    },

    // Tipos de evento
    tipos_evento: {
        titulo: 'Tipo de Evento',
        icono: '📅',
        tabla: 'tipos_evento',
        ancho: '650px',
        campos: [
            'codigo_evento:texto:requerido:longitud:20',
            'nombre_evento:texto:requerido:longitud:100',
            'requiere_aprobacion:booleano'
        ],
        mostrarEnTabla: ['codigo_evento', 'nombre_evento', 'requiere_aprobacion'],
        ordenarPor: 'nombre_evento'
    },

    // Tipos de jerarquía
    tipos_jerarquia: {
        titulo: 'Tipo de Jerarquía',
        icono: '🎯',
        tabla: 'tipos_jerarquia',
        ancho: '700px',
        campos: [
            'codigo_tipo:texto:requerido:longitud:20',
            'nombre_tipo:texto:requerido:longitud:100',
            'nivel_autoridad:numero:requerido:min:1:max:10'
        ],
        mostrarEnTabla: ['codigo_tipo', 'nombre_tipo', 'nivel_autoridad'],
        ordenarPor: 'nivel_autoridad'
    }
}

export const CONFIGURACION_NOTIFICACIONES = {
    // Duraciones (milisegundos)
    duraciones: {
        exito: 3000,
        error: 5000,
        advertencia: 4000,
        informacion: 2500
    },

    // Posición en pantalla
    posicion: 'top-right',

    // Plantillas de mensajes
    mensajes: {
        // Operaciones exitosas
        creado: (tabla, nombre) => ({
            severity: 'success',
            summary: 'Creado Exitosamente',
            detail: `${obtenerTituloTabla(tabla)} "${nombre}" creado correctamente`,
            life: 3000
        }),

        actualizado: (tabla, nombre) => ({
            severity: 'success',
            summary: 'Actualizado',
            detail: `${obtenerTituloTabla(tabla)} "${nombre}" actualizado exitosamente`,
            life: 3000
        }),

        eliminado: (tabla, nombre) => ({
            severity: 'success',
            summary: 'Eliminado',
            detail: `${obtenerTituloTabla(tabla)} "${nombre}" eliminado correctamente`,
            life: 3000
        }),

        // Errores
        errorCrear: (tabla) => ({
            severity: 'error',
            summary: 'Error al Crear',
            detail: `No se pudo crear el ${obtenerTituloTabla(tabla).toLowerCase()}`,
            life: 5000
        }),

        errorActualizar: (tabla) => ({
            severity: 'error',
            summary: 'Error al Actualizar',
            detail: `No se pudo actualizar el ${obtenerTituloTabla(tabla).toLowerCase()}`,
            life: 5000
        }),

        errorEliminar: (tabla) => ({
            severity: 'error',
            summary: 'Error al Eliminar',
            detail: `No se pudo eliminar el ${obtenerTituloTabla(tabla).toLowerCase()}`,
            life: 5000
        }),

        errorValidacion: () => ({
            severity: 'error',
            summary: 'Error de Validación',
            detail: 'Por favor corrija los errores en el formulario',
            life: 5000
        }),

        errorConexion: () => ({
            severity: 'error',
            summary: 'Error de Conexión',
            detail: 'No se pudo conectar con el servidor',
            life: 5000
        }),

        // Advertencias
        confirmacionEliminar: (tabla, nombre) => ({
            mensaje: `¿Está seguro de eliminar el ${obtenerTituloTabla(tabla).toLowerCase()} "${nombre}"?`,
            header: 'Confirmar Eliminación',
            icon: 'pi pi-exclamation-triangle',
            acceptClass: 'p-button-danger',
            acceptLabel: 'Sí, Eliminar',
            rejectLabel: 'Cancelar'
        }),

        // Información  
        cargando: (accion) => ({
            severity: 'info',
            summary: 'Procesando',
            detail: `${accion}...`,
            life: 2500
        }),

        registrosEncontrados: (cantidad) => ({
            severity: 'info',
            summary: 'Resultados',
            detail: `${cantidad} registro${cantidad !== 1 ? 's' : ''} encontrado${cantidad !== 1 ? 's' : ''}`,
            life: 2500
        })
    }
}

// Obtener configuración de esquema por nombre
export const obtenerEsquema = (nombreTabla) => {
    const esquema = ESQUEMAS_CATALOGOS[nombreTabla]
    if (!esquema) {
        return null
    }
    return esquema
}

// Obtener título amigable de tabla
export const obtenerTituloTabla = (nombreTabla) => {
    const esquema = obtenerEsquema(nombreTabla)
    return esquema ? esquema.titulo : nombreTabla
}

// Validar si existe configuración para una tabla
export const tieneEsquema = (nombreTabla) => {
    return ESQUEMAS_CATALOGOS[nombreTabla] !== undefined
}

// Obtener todos los nombres de tablas configuradas
export const obtenerNombresTablas = () => {
    return Object.keys(ESQUEMAS_CATALOGOS)
}

// Obtener configuración de notificación
export const obtenerNotificacion = (tipo, tabla, nombre = null) => {
    const mensaje = CONFIGURACION_NOTIFICACIONES.mensajes[tipo]

    if (typeof mensaje === 'function') {
        return mensaje(tabla, nombre)
    }

    return mensaje || null
}

// Validar configuración de esquema
export const validarEsquema = (nombreTabla) => {
    const esquema = obtenerEsquema(nombreTabla)

    if (!esquema) {
        return { valido: false, errores: ['Esquema no encontrado'] }
    }

    const errores = []

    // Validar campos requeridos
    if (!esquema.titulo) errores.push('Falta título')
    if (!esquema.tabla) errores.push('Falta nombre de tabla')
    if (!esquema.campos || !Array.isArray(esquema.campos)) {
        errores.push('Falta configuración de campos')
    }

    return {
        valido: errores.length === 0,
        errores
    }
}

export const CONFIGURACION_GLOBAL = {
    // Prefijo para clases CSS
    prefijoCSS: 'carbyfah',

    // Tema por defecto
    temaPorDefecto: 'militar-oscuro',

    // Configuración de formularios
    formularios: {
        validacionEnTiempoReal: true,
        mostrarAyuda: true,
        animacionesCampos: true
    },

    // Configuración de tablas
    tablas: {
        filasPorPagina: 10,
        paginacionTamaños: [5, 10, 25, 50],
        filtrosGlobales: true
    }
}

// Exportar funciones adicionales
export { generarEtiquetaAmigable, formatearNombreCampo, generarPlaceholder }

export default {
    ESQUEMAS_CATALOGOS,
    CONFIGURACION_NOTIFICACIONES,
    CONFIGURACION_GLOBAL,
    obtenerEsquema,
    obtenerTituloTabla,
    tieneEsquema,
    obtenerNombresTablas,
    obtenerNotificacion,
    validarEsquema,
    generarEtiquetaAmigable,
    formatearNombreCampo,
    generarPlaceholder
}