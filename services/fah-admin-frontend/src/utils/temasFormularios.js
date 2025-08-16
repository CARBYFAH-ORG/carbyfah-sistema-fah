// =====================================
// TEMAS Y ESTILOS PARA FORMULARIOS - CARBYFAH
// Sistema de temas dinámicos para diferentes fuerzas militares
// =====================================

/**
 * Configuración de temas disponibles
 */
export const TEMAS_DISPONIBLES = {
    'militar-oscuro': {
        nombre: 'Militar Oscuro',
        descripcion: 'Tema oscuro corporativo para uso general',
        colores: {
            primario: '#3b82f6',      // Azul
            secundario: '#6b7280',    // Gris
            exito: '#10b981',         // Verde
            error: '#ef4444',         // Rojo
            advertencia: '#f59e0b',   // Amarillo
            info: '#06b6d4',          // Cyan
            fondoModal: '#374151',    // Gris oscuro
            fondoCampo: '#ffffff',    // Blanco
            textoClaro: '#f9fafb',    // Gris muy claro
            textoOscuro: '#111827'    // Gris muy oscuro
        }
    },

    'fah-azul': {
        nombre: 'FAH Azul',
        descripcion: 'Tema oficial de la Fuerza Aérea Hondureña',
        colores: {
            primario: '#1e40af',      // Azul FAH
            secundario: '#3b82f6',    // Azul claro
            exito: '#059669',         // Verde militar
            error: '#dc2626',         // Rojo militar
            advertencia: '#d97706',   // Naranja
            info: '#0284c7',          // Azul info
            fondoModal: '#1e3a8a',    // Azul oscuro FAH
            fondoCampo: '#ffffff',    // Blanco
            textoClaro: '#f0f9ff',    // Azul muy claro
            textoOscuro: '#1e3a8a'    // Azul oscuro
        }
    },

    'ejercito-verde': {
        nombre: 'Ejército Verde',
        descripcion: 'Tema para el Ejército de Honduras',
        colores: {
            primario: '#166534',      // Verde ejército
            secundario: '#22c55e',    // Verde claro
            exito: '#15803d',         // Verde éxito
            error: '#dc2626',         // Rojo
            advertencia: '#ca8a04',   // Amarillo ejército
            info: '#0369a1',          // Azul info
            fondoModal: '#14532d',    // Verde muy oscuro
            fondoCampo: '#ffffff',    // Blanco
            textoClaro: '#f0fdf4',    // Verde muy claro
            textoOscuro: '#14532d'    // Verde muy oscuro
        }
    },

    'naval-azul-marino': {
        nombre: 'Naval Azul Marino',
        descripcion: 'Tema para la Fuerza Naval Hondureña',
        colores: {
            primario: '#1e3a8a',      // Azul marino
            secundario: '#3b82f6',    // Azul claro
            exito: '#059669',         // Verde agua
            error: '#dc2626',         // Rojo
            advertencia: '#d97706',   // Naranja
            info: '#0891b2',          // Azul turquesa
            fondoModal: '#1e2a47',    // Azul marino oscuro
            fondoCampo: '#ffffff',    // Blanco
            textoClaro: '#f1f5f9',    // Azul muy claro
            textoOscuro: '#1e2a47'    // Azul marino oscuro
        }
    }
}

/**
 * Aplicar tema a formulario
 * @param {string} nombreTema - Nombre del tema a aplicar
 */
export const aplicarTemaFormulario = (nombreTema) => {
    console.log('🎨 Aplicando tema:', nombreTema)

    const tema = TEMAS_DISPONIBLES[nombreTema]

    if (!tema) {
        console.warn(`⚠️ Tema no encontrado: ${nombreTema}`)
        return
    }

    // Aplicar variables CSS dinámicamente
    aplicarVariablesCSS(tema.colores)

    // Agregar clase de tema al body
    aplicarClaseTema(nombreTema)

    console.log('✅ Tema aplicado exitosamente:', tema.nombre)
}

/**
 * Aplicar variables CSS personalizadas
 * @param {Object} colores - Objeto con colores del tema
 */
const aplicarVariablesCSS = (colores) => {
    const root = document.documentElement

    // Aplicar cada color como variable CSS
    Object.entries(colores).forEach(([nombre, valor]) => {
        const nombreVariable = `--carbyfah-${convertirCamelAKebab(nombre)}`
        root.style.setProperty(nombreVariable, valor)
    })
}

/**
 * Aplicar clase de tema al body
 * @param {string} nombreTema - Nombre del tema
 */
const aplicarClaseTema = (nombreTema) => {
    // Remover clases de tema anteriores
    document.body.classList.forEach(clase => {
        if (clase.startsWith('tema-')) {
            document.body.classList.remove(clase)
        }
    })

    // Agregar nueva clase de tema
    document.body.classList.add(`tema-${nombreTema}`)
}

/**
 * Convertir camelCase a kebab-case
 * @param {string} texto - Texto en camelCase
 * @returns {string} Texto en kebab-case
 */
const convertirCamelAKebab = (texto) => {
    return texto.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2').toLowerCase()
}

/**
 * Obtener tema por defecto para una fuerza
 * @param {string} fuerza - Nombre de la fuerza ('fah', 'ejercito', 'naval')
 * @returns {string} Nombre del tema
 */
export const obtenerTemaPorFuerza = (fuerza) => {
    const mapeoTemas = {
        'fah': 'fah-azul',
        'ejercito': 'ejercito-verde',
        'naval': 'naval-azul-marino',
        'general': 'militar-oscuro'
    }

    return mapeoTemas[fuerza] || 'militar-oscuro'
}

/**
 * Obtener configuración de tema
 * @param {string} nombreTema - Nombre del tema
 * @returns {Object|null} Configuración del tema
 */
export const obtenerConfiguracionTema = (nombreTema) => {
    return TEMAS_DISPONIBLES[nombreTema] || null
}

/**
 * Obtener lista de todos los temas disponibles
 * @returns {Array} Array con información de temas
 */
export const obtenerListaTemas = () => {
    return Object.entries(TEMAS_DISPONIBLES).map(([clave, tema]) => ({
        clave,
        ...tema
    }))
}

/**
 * Restablecer tema por defecto
 */
export const restablecerTema = () => {
    aplicarTemaFormulario('militar-oscuro')
}

/**
 * Configuración de estilos personalizados por componente
 */
export const ESTILOS_COMPONENTES = {
    formulario: {
        'militar-oscuro': {
            backgroundColor: 'var(--carbyfah-fondo-modal)',
            color: 'var(--carbyfah-texto-claro)'
        },
        'fah-azul': {
            backgroundColor: 'var(--carbyfah-fondo-modal)',
            color: 'var(--carbyfah-texto-claro)'
        }
    },

    campo: {
        'militar-oscuro': {
            backgroundColor: 'var(--carbyfah-fondo-campo)',
            borderColor: 'var(--carbyfah-secundario)',
            color: 'var(--carbyfah-texto-oscuro)'
        }
    },

    boton: {
        primario: {
            backgroundColor: 'var(--carbyfah-primario)',
            borderColor: 'var(--carbyfah-primario)',
            color: 'white'
        },
        secundario: {
            backgroundColor: 'var(--carbyfah-secundario)',
            borderColor: 'var(--carbyfah-secundario)',
            color: 'white'
        }
    }
}

/**
 * Aplicar estilos específicos a un elemento
 * @param {HTMLElement} elemento - Elemento DOM
 * @param {string} componente - Tipo de componente
 * @param {string} variante - Variante del estilo
 * @param {string} tema - Tema actual
 */
export const aplicarEstilosElemento = (elemento, componente, variante = 'default', tema = 'militar-oscuro') => {
    const estilos = ESTILOS_COMPONENTES[componente]?.[variante] ||
        ESTILOS_COMPONENTES[componente]?.[tema]

    if (estilos && elemento) {
        Object.entries(estilos).forEach(([propiedad, valor]) => {
            elemento.style[propiedad] = valor
        })
    }
}

/**
 * Generar clases CSS dinámicas para un tema
 * @param {string} nombreTema - Nombre del tema
 * @returns {string} Clases CSS generadas
 */
export const generarClasesCSS = (nombreTema) => {
    const tema = TEMAS_DISPONIBLES[nombreTema]

    if (!tema) return ''

    // Esta función puede expandirse para generar CSS dinámico
    // Por ahora retorna clases básicas
    return `tema-${nombreTema}`
}

export default {
    TEMAS_DISPONIBLES,
    ESTILOS_COMPONENTES,
    aplicarTemaFormulario,
    obtenerTemaPorFuerza,
    obtenerConfiguracionTema,
    obtenerListaTemas,
    restablecerTema,
    aplicarEstilosElemento,
    generarClasesCSS
}