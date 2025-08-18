// services\fah-admin-frontend\src\config\esquemaOrganizacion.js

// Importar funciones desde catalogos (reutilizar)
const generarEtiquetaAmigable = (nombreCampo) => {
    const mapeoEtiquetas = {
        // Campos comunes
        codigo: 'Codigo',
        nombre: 'Nombre',

        // Departamentos
        pais_id: 'Pais',
        codigo_departamento: 'Codigo Departamento',
        nombre_departamento: 'Nombre del Departamento',

        // Municipios
        departamento_id: 'Departamento',
        codigo_municipio: 'Codigo Municipio',
        nombre_municipio: 'Nombre del Municipio',

        // Ciudades
        municipio_id: 'Municipio',
        codigo_ciudad: 'Codigo Ciudad',
        nombre_ciudad: 'Nombre de la Ciudad',
        tipo_localidad: 'Tipo de Localidad',

        // Ubicaciones Geograficas
        codigo_ubicacion: 'Codigo Ubicacion',
        nombre_ubicacion: 'Nombre de la Ubicacion',
        ciudad_id: 'Ciudad',
        latitud: 'Latitud',
        longitud: 'Longitud',
        direccion_referencia: 'Direccion de Referencia',
        altitud_metros: 'Altitud (metros)',
        telefono_principal: 'Telefono Principal',
        telefono_emergencia: 'Telefono de Emergencia',

        // Estructura Militar
        codigo_unidad: 'Codigo de Unidad',
        nombre_unidad: 'Nombre de la Unidad',
        tipo_estructura_id: 'Tipo de Estructura',
        ubicacion_geografica_id: 'Ubicacion Geografica',
        unidad_padre_id: 'Unidad Padre',
        nivel_jerarquico: 'Nivel Jerarquico',
        orden_horizontal: 'Orden Horizontal',
        capacidad_personal: 'Capacidad de Personal',
        fecha_activacion: 'Fecha de Activacion',
        fecha_desactivacion: 'Fecha de Desactivacion',
        logo_url: 'URL del Logo',
        mision: 'Mision',
        vision: 'Vision',

        // Cargos
        estructura_militar_id: 'Estructura Militar',
        codigo_cargo: 'Codigo del Cargo',
        nombre_cargo: 'Nombre del Cargo',

        // Roles Funcionales
        codigo_rol: 'Codigo del Rol',
        nombre_rol: 'Nombre del Rol',
        nivel_autoridad: 'Nivel de Autoridad'
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
        // Departamentos
        codigo_departamento: 'Ej: FM, COR, ATL',
        nombre_departamento: 'Ej: Francisco Morazan, Cortes',

        // Municipios
        codigo_municipio: 'Ej: TGU, SPS, LCE',
        nombre_municipio: 'Ej: Tegucigalpa, San Pedro Sula',

        // Ciudades
        codigo_ciudad: 'Ej: TGU01, SPS01',
        nombre_ciudad: 'Ej: Tegucigalpa, Comayaguela',
        tipo_localidad: 'Ej: Ciudad, Aldea, Caserio',

        // Ubicaciones
        codigo_ubicacion: 'Ej: BASE-SOTO-CANO, HAM-TGU',
        nombre_ubicacion: 'Ej: Base Aerea Soto Cano',
        latitud: 'Ej: 14.0583',
        longitud: 'Ej: -87.2068',
        direccion_referencia: 'Ej: 500m norte del parque central',
        altitud_metros: 'Ej: 1000',
        telefono_principal: 'Ej: +504 2234-5678',
        telefono_emergencia: 'Ej: +504 2234-9999',

        // Estructura Militar
        codigo_unidad: 'Ej: FA-1, HAM, JEMGA',
        nombre_unidad: 'Ej: Primera Fuerza Aerea',
        nivel_jerarquico: 'Ej: 1, 2, 3',
        orden_horizontal: 'Ej: 1, 2, 3',
        capacidad_personal: 'Ej: 100, 500, 1000',
        logo_url: 'Ej: https://ejemplo.com/logo.png',
        mision: 'Mision de la unidad...',
        vision: 'Vision de la unidad...',

        // Cargos
        codigo_cargo: 'Ej: CMD, SUBCMD, JEFE-OPS',
        nombre_cargo: 'Ej: Comandante, Jefe de Operaciones',

        // Roles
        codigo_rol: 'Ej: ADMIN, OPERADOR, SUPERVISOR',
        nombre_rol: 'Ej: Administrador, Operador',
        nivel_autoridad: 'Ej: 1, 2, 3, 4, 5'
    }

    return placeholders[nombreCampo] || `Ingrese ${generarEtiquetaAmigable(nombreCampo).toLowerCase()}`
}

export const ESQUEMAS_ORGANIZACION = {
    // Estructura geografica

    // Departamentos
    departamentos: {
        titulo: 'Departamento',
        icono: '🗺️',
        tabla: 'departamentos',
        ancho: '700px',
        servicio: 'organizacion',
        campos: [
            'pais_id:foraneo_autocompletado:requerido:referencia:paises:servicio:catalogos',
            'codigo_departamento:texto:requerido:longitud:10',
            'nombre_departamento:texto:requerido:longitud:100'
        ],
        mostrarEnTabla: ['codigo_departamento', 'nombre_departamento', 'pais_id'],
        ordenarPor: 'nombre_departamento'
    },

    // Municipios
    municipios: {
        titulo: 'Municipio',
        icono: '🏘️',
        tabla: 'municipios',
        ancho: '700px',
        servicio: 'organizacion',
        campos: [
            'departamento_id:foraneo_autocompletado:requerido:referencia:departamentos',
            'codigo_municipio:texto:requerido:longitud:10',
            'nombre_municipio:texto:requerido:longitud:100'
        ],
        mostrarEnTabla: ['codigo_municipio', 'nombre_municipio', 'departamento_id'],
        ordenarPor: 'nombre_municipio'
    },

    // Ciudades
    ciudades: {
        titulo: 'Ciudad',
        icono: '🏙️',
        tabla: 'ciudades',
        ancho: '750px',
        servicio: 'organizacion',
        campos: [
            'municipio_id:foraneo_autocompletado:requerido:referencia:municipios',
            'codigo_ciudad:texto:requerido:longitud:10',
            'nombre_ciudad:texto:requerido:longitud:100',
            'tipo_localidad:texto:longitud:20'
        ],
        mostrarEnTabla: ['codigo_ciudad', 'nombre_ciudad', 'tipo_localidad'],
        ordenarPor: 'nombre_ciudad'
    },

    // Ubicaciones Geograficas
    ubicaciones_geograficas: {
        titulo: 'Ubicacion Geografica',
        icono: '📍',
        tabla: 'ubicaciones_geograficas',
        ancho: '900px',
        servicio: 'organizacion',
        campos: [
            'codigo_ubicacion:texto:requerido:longitud:30',
            'nombre_ubicacion:texto:requerido:longitud:200',
            'pais_id:foraneo_autocompletado:requerido:referencia:paises',
            'departamento_id:foraneo_autocompletado:requerido:referencia:departamentos',
            'municipio_id:foraneo_autocompletado:requerido:referencia:municipios',
            'ciudad_id:foraneo_autocompletado:referencia:ciudades',
            'latitud:numero:requerido:min:-90:max:90',
            'longitud:numero:requerido:min:-180:max:180',
            'direccion_referencia:area_texto:longitud:500',
            'altitud_metros:numero:min:0:max:10000',
            'telefono_principal:texto:longitud:20',
            'telefono_emergencia:texto:longitud:20'
        ],
        mostrarEnTabla: ['codigo_ubicacion', 'nombre_ubicacion', 'latitud', 'longitud'],
        ordenarPor: 'nombre_ubicacion'
    },

    // Estructura militar

    // Estructura Militar
    estructura_militar: {
        titulo: 'Estructura Militar',
        icono: '🏛️',
        tabla: 'estructura_militar',
        ancho: '950px',
        servicio: 'organizacion',
        campos: [
            'codigo_unidad:texto:requerido:longitud:50',
            'nombre_unidad:texto:requerido:longitud:200',
            'tipo_estructura_id:foraneo_autocompletado:requerido:referencia:tipos_estructura_militar',
            'ubicacion_geografica_id:foraneo_autocompletado:referencia:ubicaciones_geograficas',
            'unidad_padre_id:foraneo_autocompletado:referencia:estructura_militar',
            'nivel_jerarquico:numero:requerido:min:1:max:10',
            'orden_horizontal:numero:min:1:max:100',
            'capacidad_personal:numero:min:1:max:10000',
            'fecha_activacion:fecha',
            'fecha_desactivacion:fecha',
            'logo_url:texto:longitud:500',
            'mision:area_texto',
            'vision:area_texto'
        ],
        mostrarEnTabla: ['codigo_unidad', 'nombre_unidad', 'nivel_jerarquico', 'capacidad_personal'],
        ordenarPor: 'nivel_jerarquico'
    },

    // Cargos
    cargos: {
        titulo: 'Cargo',
        icono: '👔',
        tabla: 'cargos',
        ancho: '750px',
        servicio: 'organizacion',
        campos: [
            'estructura_militar_id:foraneo_autocompletado:requerido:referencia:estructura_militar',
            'codigo_cargo:texto:requerido:longitud:50',
            'nombre_cargo:texto:requerido:longitud:200',
            'nivel_jerarquico:numero:requerido:min:1:max:10'
        ],
        mostrarEnTabla: ['codigo_cargo', 'nombre_cargo', 'nivel_jerarquico'],
        ordenarPor: 'nivel_jerarquico'
    },

    // Roles Funcionales
    roles_funcionales: {
        titulo: 'Rol Funcional',
        icono: '🎭',
        tabla: 'roles_funcionales',
        ancho: '700px',
        servicio: 'organizacion',
        campos: [
            'codigo_rol:texto:requerido:longitud:50',
            'nombre_rol:texto:requerido:longitud:200',
            'nivel_autoridad:numero:min:1:max:10'
        ],
        mostrarEnTabla: ['codigo_rol', 'nombre_rol', 'nivel_autoridad'],
        ordenarPor: 'nivel_autoridad'
    }
}

// Configuracion de notificaciones

export const CONFIGURACION_NOTIFICACIONES = {
    // Duraciones (milisegundos)
    duraciones: {
        exito: 3000,
        error: 5000,
        advertencia: 4000,
        informacion: 2500
    },

    // Posicion en pantalla
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
            summary: 'Error de Validacion',
            detail: 'Por favor corrija los errores en el formulario',
            life: 5000
        }),

        errorConexion: () => ({
            severity: 'error',
            summary: 'Error de Conexion',
            detail: 'No se pudo conectar con el servidor',
            life: 5000
        }),

        // Advertencias
        confirmacionEliminar: (tabla, nombre) => ({
            mensaje: `¿Esta seguro de eliminar el ${obtenerTituloTabla(tabla).toLowerCase()} "${nombre}"?`,
            header: 'Confirmar Eliminacion',
            icon: 'pi pi-exclamation-triangle',
            acceptClass: 'p-button-danger',
            acceptLabel: 'Si, Eliminar',
            rejectLabel: 'Cancelar'
        }),

        // Informacion
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

// Funciones auxiliares

// Obtener configuracion de esquema por nombre
export const obtenerEsquema = (nombreTabla) => {
    const esquema = ESQUEMAS_ORGANIZACION[nombreTabla]
    if (!esquema) {
        return null
    }
    return esquema
}

// Obtener titulo amigable de tabla
export const obtenerTituloTabla = (nombreTabla) => {
    const esquema = obtenerEsquema(nombreTabla)
    return esquema ? esquema.titulo : nombreTabla
}

// Validar si existe configuracion para una tabla
export const tieneEsquema = (nombreTabla) => {
    return ESQUEMAS_ORGANIZACION[nombreTabla] !== undefined
}

// Obtener todos los nombres de tablas configuradas
export const obtenerNombresTablas = () => {
    return Object.keys(ESQUEMAS_ORGANIZACION)
}

// Obtener configuracion de notificacion
export const obtenerNotificacion = (tipo, tabla, nombre = null) => {
    const mensaje = CONFIGURACION_NOTIFICACIONES.mensajes[tipo]

    if (typeof mensaje === 'function') {
        return mensaje(tabla, nombre)
    }

    return mensaje || null
}

// Validar configuracion de esquema
export const validarEsquema = (nombreTabla) => {
    const esquema = obtenerEsquema(nombreTabla)

    if (!esquema) {
        return { valido: false, errores: ['Esquema no encontrado'] }
    }

    const errores = []

    // Validar campos requeridos
    if (!esquema.titulo) errores.push('Falta titulo')
    if (!esquema.tabla) errores.push('Falta nombre de tabla')
    if (!esquema.campos || !Array.isArray(esquema.campos)) {
        errores.push('Falta configuracion de campos')
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

    // Configuracion de formularios
    formularios: {
        validacionEnTiempoReal: true,
        mostrarAyuda: true,
        animacionesCampos: true
    },

    // Configuracion de tablas
    tablas: {
        filasPorPagina: 10,
        paginacionTamaños: [5, 10, 25, 50],
        filtrosGlobales: true
    }
}

// Exportar funciones adicionales
export { generarEtiquetaAmigable, formatearNombreCampo, generarPlaceholder }

export default {
    ESQUEMAS_ORGANIZACION,
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