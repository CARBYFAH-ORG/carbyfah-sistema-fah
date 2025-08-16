// C:\FAH\services\fah-personal-service\frontend\src\config\nivelesJerarquicos.js

export const NIVELES_ACCESO = {
    FA1: 'FA-1',
    S1: 'S-1',
    SECCION: 'SECCION',
    OPERADOR: 'OPERADOR'
}

export const UNIDADES_FAH = {
    // Bases Aéreas Principales
    HAM: 'Base Aérea HAM',
    HCM: 'Base Aérea HCM',
    AEE: 'Base Aérea AEE',
    JESC: 'Base Aérea JESC',

    // Centros de Educación
    AMA: 'Academia Militar de Aviación',
    ECMI: 'Escuela de Capacitación de Mandos Intermedios',
    EFSOFAH: 'Escuela de Formación de Suboficiales',
    ECSOFAH: 'Escuela de Capacitación de Suboficiales',

    // Unidades Especiales
    COSECGFAH: 'Comando de Seguridad del Complejo General FAH',
    PEDA: 'Primer Escuadrón de Defensa Aérea',
    EAIVR: 'Escuadrón Aéreo de Inteligencia',

    // Comandancia
    COMANDANCIA: 'Comandancia General FAH',
    JEMGA: 'Jefatura del Estado Mayor General Aéreo'
}

export const SECCIONES_MILITARES = {
    // Secciones S (Bases/Unidades)
    S1: 'S-1 Recursos Humanos',
    S2: 'S-2 Información y Análisis',
    S3: 'S-3 Operaciones y Adiestramiento',
    S4: 'S-4 Logística',
    S5: 'S-5 Planes y Políticas',
    S6: 'S-6 Comunicaciones',

    // Secciones Operativas
    VUELO: 'Personal de Vuelo',
    MANTENIMIENTO: 'Mantenimiento de Aeronaves',
    COMUNICACIONES: 'Comunicaciones y Electrónica',
    INTELIGENCIA: 'Inteligencia Militar',
    SEGURIDAD: 'Seguridad y Vigilancia',
    ADMINISTRACION: 'Administración y Finanzas',
    SANIDAD: 'Sanidad Militar',
    LOGISTICA: 'Logística y Abastecimiento'
}

export const CONFIGURACION_NIVELES = {
    [NIVELES_ACCESO.FA1]: {
        nombre: 'Jefe FA-1',
        descripcion: 'Acceso total a toda la Fuerza Aérea Hondureña',
        alcance: 'NACIONAL',
        unidades_visibles: Object.keys(UNIDADES_FAH),
        secciones_visibles: Object.keys(SECCIONES_MILITARES),
        personal_visible: 'TODOS',
        puede_ver_otras_unidades: true,
        puede_tomar_decisiones_estrategicas: true,
        nivel_datos_sensibles: 'ALTO',
        reporta_a: null,
        supervisa: [NIVELES_ACCESO.S1]
    },

    [NIVELES_ACCESO.S1]: {
        nombre: 'Jefe S-1 Unidad',
        descripcion: 'Acceso solo a su unidad/base asignada',
        alcance: 'UNIDAD',
        unidades_visibles: [], // Se configura dinámicamente por usuario
        secciones_visibles: Object.keys(SECCIONES_MILITARES),
        personal_visible: 'UNIDAD_PROPIA',
        puede_ver_otras_unidades: false,
        puede_tomar_decisiones_estrategicas: false,
        nivel_datos_sensibles: 'MEDIO',
        reporta_a: NIVELES_ACCESO.FA1,
        supervisa: [NIVELES_ACCESO.SECCION, NIVELES_ACCESO.OPERADOR]
    },

    [NIVELES_ACCESO.SECCION]: {
        nombre: 'Jefe de Sección',
        descripcion: 'Acceso solo a su sección específica',
        alcance: 'SECCION',
        unidades_visibles: [], // Se configura dinámicamente
        secciones_visibles: [], // Se configura dinámicamente
        personal_visible: 'SECCION_PROPIA',
        puede_ver_otras_unidades: false,
        puede_tomar_decisiones_estrategicas: false,
        nivel_datos_sensibles: 'BAJO',
        reporta_a: NIVELES_ACCESO.S1,
        supervisa: [NIVELES_ACCESO.OPERADOR]
    },

    [NIVELES_ACCESO.OPERADOR]: {
        nombre: 'Operador Básico',
        descripcion: 'Acceso limitado solo a funciones básicas',
        alcance: 'LIMITADO',
        unidades_visibles: [],
        secciones_visibles: [],
        personal_visible: 'LIMITADO',
        puede_ver_otras_unidades: false,
        puede_tomar_decisiones_estrategicas: false,
        nivel_datos_sensibles: 'MINIMO',
        reporta_a: NIVELES_ACCESO.SECCION,
        supervisa: []
    }
}

export const PERMISOS_POR_MODULO = {
    DASHBOARD: {
        [NIVELES_ACCESO.FA1]: ['ver_todo', 'metricas_consolidadas', 'alertas_criticas'],
        [NIVELES_ACCESO.S1]: ['ver_unidad', 'metricas_unidad', 'alertas_unidad'],
        [NIVELES_ACCESO.SECCION]: ['ver_seccion', 'metricas_seccion', 'alertas_seccion'],
        [NIVELES_ACCESO.OPERADOR]: ['ver_limitado', 'metricas_basicas']
    },

    ADMINISTRACION_PERSONAL: {
        [NIVELES_ACCESO.FA1]: ['crud_total', 'transferencias', 'promociones', 'datos_sensibles'],
        [NIVELES_ACCESO.S1]: ['crud_unidad', 'transferencias_internas', 'datos_unidad'],
        [NIVELES_ACCESO.SECCION]: ['ver_seccion', 'editar_limitado'],
        [NIVELES_ACCESO.OPERADOR]: ['solo_lectura']
    },

    DISCIPLINA: {
        [NIVELES_ACCESO.FA1]: ['ver_todos_casos', 'tribunales_honor', 'estadisticas_consolidadas'],
        [NIVELES_ACCESO.S1]: ['casos_unidad', 'expedientes_unidad', 'reportes_unidad'],
        [NIVELES_ACCESO.SECCION]: ['casos_seccion', 'reportes_basicos'],
        [NIVELES_ACCESO.OPERADOR]: ['solo_consulta']
    },

    SOLICITUDES: {
        [NIVELES_ACCESO.FA1]: ['aprobar_todas', 'rechazar_todas', 'delegar', 'estadisticas'],
        [NIVELES_ACCESO.S1]: ['aprobar_unidad', 'rechazar_unidad', 'escalar'],
        [NIVELES_ACCESO.SECCION]: ['aprobar_seccion', 'recomendar'],
        [NIVELES_ACCESO.OPERADOR]: ['crear_solicitud', 'ver_propias']
    },

    CARBYCHAT: {
        [NIVELES_ACCESO.FA1]: ['grupos_todos', 'mensajes_oficiales', 'alertas_sistema'],
        [NIVELES_ACCESO.S1]: ['grupos_unidad', 'mensajes_unidad', 'reportes_unidad'],
        [NIVELES_ACCESO.SECCION]: ['grupos_seccion', 'mensajes_seccion'],
        [NIVELES_ACCESO.OPERADOR]: ['grupos_limitados', 'mensajes_basicos']
    }
}

// Función para obtener configuración de usuario
export const obtenerConfiguracionNivel = (nivel) => {
    return CONFIGURACION_NIVELES[nivel] || CONFIGURACION_NIVELES[NIVELES_ACCESO.OPERADOR]
}

// Función para verificar permisos
export const tienePermiso = (nivel, modulo, accion) => {
    const permisos = PERMISOS_POR_MODULO[modulo]?.[nivel] || []
    return permisos.includes(accion)
}

// Función para obtener unidades visibles por usuario
export const obtenerUnidadesVisibles = (usuario) => {
    const config = obtenerConfiguracionNivel(usuario.nivel_acceso)

    if (usuario.nivel_acceso === NIVELES_ACCESO.FA1) {
        return Object.keys(UNIDADES_FAH)
    }

    return usuario.unidades_asignadas || []
}

// Función para obtener secciones visibles por usuario  
export const obtenerSeccionesVisibles = (usuario) => {
    const config = obtenerConfiguracionNivel(usuario.nivel_acceso)

    if (usuario.nivel_acceso === NIVELES_ACCESO.FA1 || usuario.nivel_acceso === NIVELES_ACCESO.S1) {
        return Object.keys(SECCIONES_MILITARES)
    }

    return usuario.secciones_asignadas || []
}