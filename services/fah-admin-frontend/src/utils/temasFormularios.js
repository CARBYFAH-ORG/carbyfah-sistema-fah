// services\fah-admin-frontend\src\utils\temasFormularios.js

/**
 * Configuracion de temas disponibles
 */
export const TEMAS_DISPONIBLES = {
    'militar-oscuro': {
        nombre: 'Militar Oscuro',
        descripcion: 'Tema oscuro corporativo para uso general',
        colores: {
            primario: '#3b82f6',
            secundario: '#6b7280',
            exito: '#10b981',
            error: '#ef4444',
            advertencia: '#f59e0b',
            info: '#06b6d4',
            fondoModal: '#374151',
            fondoCampo: '#ffffff',
            textoClaro: '#f9fafb',
            textoOscuro: '#111827'
        }
    },

    'fah-azul': {
        nombre: 'FAH Azul',
        descripcion: 'Tema oficial de la Fuerza Aerea Hondureña',
        colores: {
            primario: '#1e40af',
            secundario: '#3b82f6',
            exito: '#059669',
            error: '#dc2626',
            advertencia: '#d97706',
            info: '#0284c7',
            fondoModal: '#1e3a8a',
            fondoCampo: '#ffffff',
            textoClaro: '#f0f9ff',
            textoOscuro: '#1e3a8a'
        }
    },

    'ejercito-verde': {
        nombre: 'Ejercito Verde',
        descripcion: 'Tema para el Ejercito de Honduras',
        colores: {
            primario: '#166534',
            secundario: '#22c55e',
            exito: '#15803d',
            error: '#dc2626',
            advertencia: '#ca8a04',
            info: '#0369a1',
            fondoModal: '#14532d',
            fondoCampo: '#ffffff',
            textoClaro: '#f0fdf4',
            textoOscuro: '#14532d'
        }
    },

    'naval-azul-marino': {
        nombre: 'Naval Azul Marino',
        descripcion: 'Tema para la Fuerza Naval Hondureña',
        colores: {
            primario: '#1e3a8a',
            secundario: '#3b82f6',
            exito: '#059669',
            error: '#dc2626',
            advertencia: '#d97706',
            info: '#0891b2',
            fondoModal: '#1e2a47',
            fondoCampo: '#ffffff',
            textoClaro: '#f1f5f9',
            textoOscuro: '#1e2a47'
        }
    }
}

/**
 * Aplicar tema a formulario
 * @param {string} nombreTema - Nombre del tema a aplicar
 */
export const aplicarTemaFormulario = (nombreTema) => {
    const tema = TEMAS_DISPONIBLES[nombreTema]

    if (!tema) {
        return
    }

    // Aplicar variables CSS dinamicamente
    aplicarVariablesCSS(tema.colores)

    // Agregar clase de tema al body
    aplicarClaseTema(nombreTema)
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
 * Obtener configuracion de tema
 * @param {string} nombreTema - Nombre del tema
 * @returns {Object|null} Configuracion del tema
 */
export const obtenerConfiguracionTema = (nombreTema) => {
    return TEMAS_DISPONIBLES[nombreTema] || null
}

/**
 * Obtener lista de todos los temas disponibles
 * @returns {Array} Array con informacion de temas
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
 * Configuracion de estilos personalizados por componente
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
 * Aplicar estilos especificos a un elemento
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
 * Generar clases CSS dinamicas para un tema
 * @param {string} nombreTema - Nombre del tema
 * @returns {string} Clases CSS generadas
 */
export const generarClasesCSS = (nombreTema) => {
    const tema = TEMAS_DISPONIBLES[nombreTema]

    if (!tema) return ''

    // Esta funcion puede expandirse para generar CSS dinamico
    // Por ahora retorna clases basicas
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