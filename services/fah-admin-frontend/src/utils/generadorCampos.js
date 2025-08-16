// =====================================
// GENERADOR INTELIGENTE DE CAMPOS - CARBYFAH
// Utilidades para generar opciones dinámicas para campos de selección
// =====================================

import { useCatalogosStore } from '@/stores/catalogosStore'

/**
 * Obtener opciones dinámicas para campos de selección
 * Principalmente usado para referencias a otras tablas
 * @param {Object} configuracionCampo - Configuración del campo
 * @returns {Array} Array de opciones para el dropdown
 */
export const obtenerOpcionesDinamicas = (configuracionCampo) => {
    console.log('🔄 Obteniendo opciones dinámicas para:', configuracionCampo)

    // Si ya tiene opciones estáticas, usarlas
    if (configuracionCampo.opciones && Array.isArray(configuracionCampo.opciones)) {
        return configuracionCampo.opciones
    }

    // Si es campo de referencia, obtener datos de la tabla referenciada
    if (configuracionCampo.tablaReferencia) {
        return obtenerOpcionesDeTablaReferencia(configuracionCampo.tablaReferencia)
    }

    // Campos especiales con opciones predefinidas
    return obtenerOpcionesPredefinidas(configuracionCampo.nombre)
}

/**
 * Obtener opciones desde tabla referenciada (para Foreign Keys)
 * @param {string} tablaReferencia - Nombre de la tabla referenciada
 * @returns {Array} Opciones formateadas para dropdown
 */
const obtenerOpcionesDeTablaReferencia = (tablaReferencia) => {
    const catalogosStore = useCatalogosStore()

    console.log('📋 Obteniendo opciones de tabla:', tablaReferencia)

    switch (tablaReferencia) {
        case 'categorias_personal':
            return catalogosStore.categoriasPersonal.map(categoria => ({
                etiqueta: categoria.nombre_categoria,
                valor: categoria.id,
                datos: categoria
            }))

        case 'especialidades':
            return catalogosStore.especialidades.map(especialidad => ({
                etiqueta: especialidad.nombre_especialidad,
                valor: especialidad.id,
                datos: especialidad
            }))

        case 'tipos_estructura_militar':
            return catalogosStore.tiposEstructuraMilitar.map(estructura => ({
                etiqueta: estructura.nombre_tipo,
                valor: estructura.id,
                datos: estructura
            }))

        case 'niveles_prioridad':
            return catalogosStore.nivelesPrioridad.map(nivel => ({
                etiqueta: `${nivel.nombre} (${nivel.nivel_numerico})`,
                valor: nivel.id,
                datos: nivel
            }))

        case 'niveles_seguridad':
            return catalogosStore.nivelesSeguridad.map(nivel => ({
                etiqueta: `${nivel.nombre} (Nivel ${nivel.nivel_numerico})`,
                valor: nivel.id,
                datos: nivel
            }))

        case 'paises':
            return catalogosStore.paises.map(pais => ({
                etiqueta: `${pais.nombre} (${pais.codigo_iso3})`,
                valor: pais.id,
                datos: pais
            }))

        case 'tipos_evento':
            return catalogosStore.tiposEvento.map(evento => ({
                etiqueta: evento.nombre_evento,
                valor: evento.id,
                datos: evento
            }))

        default:
            console.warn('⚠️ Tabla de referencia no configurada:', tablaReferencia)
            return []
    }
}

/**
 * Obtener opciones predefinidas para campos específicos
 * @param {string} nombreCampo - Nombre del campo
 * @returns {Array} Opciones predefinidas
 */
const obtenerOpcionesPredefinidas = (nombreCampo) => {
    const opcionesPredefinidas = {
        // Campos booleanos comunes
        requiere_autorizacion: [
            { etiqueta: 'Sí, requiere autorización', valor: true },
            { etiqueta: 'No requiere autorización', valor: false }
        ],

        permite_operaciones: [
            { etiqueta: 'Permite operaciones', valor: true },
            { etiqueta: 'No permite operaciones', valor: false }
        ],

        es_estado_final: [
            { etiqueta: 'Es estado final', valor: true },
            { etiqueta: 'No es estado final', valor: false }
        ],

        requiere_justificacion: [
            { etiqueta: 'Requiere justificación', valor: true },
            { etiqueta: 'No requiere justificación', valor: false }
        ],

        requiere_aprobacion: [
            { etiqueta: 'Requiere aprobación', valor: true },
            { etiqueta: 'No requiere aprobación', valor: false }
        ],

        // Niveles numéricos comunes
        nivel_numerico: generarOpcionesNumericas(1, 10, 'Nivel'),
        nivel_organizacional: generarOpcionesNumericas(1, 10, 'Nivel'),
        nivel_autoridad: generarOpcionesNumericas(1, 10, 'Nivel'),
        orden_jerarquico: generarOpcionesNumericas(1, 50, 'Orden'),

        // Tiempo de retención
        tiempo_retencion_anos: [
            { etiqueta: '1 año', valor: 1 },
            { etiqueta: '2 años', valor: 2 },
            { etiqueta: '3 años', valor: 3 },
            { etiqueta: '5 años', valor: 5 },
            { etiqueta: '7 años', valor: 7 },
            { etiqueta: '10 años', valor: 10 },
            { etiqueta: '15 años', valor: 15 },
            { etiqueta: '20 años', valor: 20 },
            { etiqueta: '25 años', valor: 25 },
            { etiqueta: 'Permanente', valor: 999 }
        ]
    }

    return opcionesPredefinidas[nombreCampo] || []
}

/**
 * Generar opciones numéricas para rangos
 * @param {number} inicio - Valor inicial
 * @param {number} fin - Valor final
 * @param {string} prefijo - Prefijo para la etiqueta
 * @returns {Array} Opciones numéricas
 */
const generarOpcionesNumericas = (inicio, fin, prefijo = '') => {
    const opciones = []

    for (let i = inicio; i <= fin; i++) {
        opciones.push({
            etiqueta: prefijo ? `${prefijo} ${i}` : i.toString(),
            valor: i
        })
    }

    return opciones
}

/**
 * Validar campo individual
 * @param {Object} configuracionCampo - Configuración del campo
 * @param {any} valor - Valor a validar
 * @returns {Object} Resultado de validación
 */
export const validarCampoIndividual = (configuracionCampo, valor) => {
    console.log('🔍 Validando campo individual:', {
        campo: configuracionCampo.nombre,
        valor,
        tipo: configuracionCampo.tipo
    })

    const errores = []
    let esValido = true

    // Validar requerido
    if (configuracionCampo.requerido) {
        if (valor === null || valor === undefined || valor === '') {
            errores.push(`${configuracionCampo.etiqueta} es obligatorio`)
            esValido = false
        }
    }

    // Validaciones específicas por tipo
    if (valor !== null && valor !== undefined && valor !== '') {
        switch (configuracionCampo.tipo) {
            case 'texto':
                esValido = validarCampoTexto(configuracionCampo, valor, errores) && esValido
                break

            case 'numero':
                esValido = validarCampoNumero(configuracionCampo, valor, errores) && esValido
                break

            case 'seleccion':
                esValido = validarCampoSeleccion(configuracionCampo, valor, errores) && esValido
                break

            case 'area_texto':
                esValido = validarCampoAreaTexto(configuracionCampo, valor, errores) && esValido
                break

            case 'booleano':
                esValido = validarCampoBooleano(configuracionCampo, valor, errores) && esValido
                break

            case 'foraneo_autocompletado':
                esValido = validarCampoForaneoAutocompletado(configuracionCampo, valor, errores) && esValido
                break

            case 'fecha':
                esValido = validarCampoFecha(configuracionCampo, valor, errores) && esValido
                break

            default:
                console.warn(`⚠️ Tipo de campo no soportado: ${configuracionCampo.tipo}`)
                break
        }
    }

    return {
        esValido,
        errores
    }
}

/**
 * Validar campo de texto
 */
const validarCampoTexto = (config, valor, errores) => {
    let valido = true

    // Validar longitud máxima
    if (config.longitudMaxima && valor.length > config.longitudMaxima) {
        errores.push(`Máximo ${config.longitudMaxima} caracteres`)
        valido = false
    }

    // Validaciones específicas por campo
    if (config.nombre === 'codigo_iso3' && valor.length !== 3) {
        errores.push('El código ISO3 debe tener exactamente 3 caracteres')
        valido = false
    }

    return valido
}

/**
 * Validar campo numérico
 */
const validarCampoNumero = (config, valor, errores) => {
    let valido = true
    const numValor = Number(valor)

    // Verificar que sea un número válido
    if (isNaN(numValor)) {
        errores.push('Debe ser un número válido')
        return false
    }

    // Validar rango mínimo
    if (config.minimo && numValor < config.minimo) {
        errores.push(`Valor mínimo: ${config.minimo}`)
        valido = false
    }

    // Validar rango máximo
    if (config.maximo && numValor > config.maximo) {
        errores.push(`Valor máximo: ${config.maximo}`)
        valido = false
    }

    return valido
}

/**
 * Validar campo de selección
 */
const validarCampoSeleccion = (config, valor, errores) => {
    // Para campos de selección, solo validar que tenga un valor válido
    if (config.requerido && (valor === null || valor === undefined)) {
        errores.push(`Debe seleccionar una opción`)
        return false
    }

    return true
}

/**
 * Validar área de texto
 */
const validarCampoAreaTexto = (config, valor, errores) => {
    let valido = true

    // Validar longitud máxima
    if (config.longitudMaxima && valor.length > config.longitudMaxima) {
        errores.push(`Máximo ${config.longitudMaxima} caracteres`)
        valido = false
    }

    return valido
}

/**
 * ✅ NUEVA: Validar campo booleano
 */
const validarCampoBooleano = (config, valor, errores) => {
    console.log('✅ Validando campo booleano:', {
        campo: config.nombre,
        valor,
        tipo: typeof valor
    })

    // Los campos booleanos siempre son válidos si tienen un valor
    // true/false son ambos valores válidos
    if (typeof valor === 'boolean') {
        return true
    }

    // Si el valor no es booleano, intentar convertirlo
    if (valor === 'true' || valor === 1 || valor === '1') {
        return true
    }

    if (valor === 'false' || valor === 0 || valor === '0') {
        return true
    }

    // Si llega aquí y es requerido, es inválido
    if (config.requerido) {
        errores.push(`${config.etiqueta} debe tener un valor válido (Sí/No)`)
        return false
    }

    return true
}

/**
 * ✅ NUEVA: Validar campo foráneo autocompletado
 */
const validarCampoForaneoAutocompletado = (config, valor, errores) => {
    console.log('🔗 Validando campo foráneo autocompletado:', {
        campo: config.nombre,
        valor,
        tablaReferencia: config.tablaReferencia
    })

    // Si es requerido y no tiene valor
    if (config.requerido && (valor === null || valor === undefined || valor === '')) {
        errores.push(`Debe seleccionar un ${config.etiqueta.toLowerCase()}`)
        return false
    }

    // Si tiene valor, verificar que sea un ID válido (número)
    if (valor !== null && valor !== undefined && valor !== '') {
        const numValor = Number(valor)
        if (isNaN(numValor) || numValor <= 0) {
            errores.push(`Debe seleccionar una opción válida`)
            return false
        }
    }

    return true
}

/**
 * ✅ NUEVA: Validar campo de fecha
 */
const validarCampoFecha = (config, valor, errores) => {
    console.log('📅 Validando campo fecha:', {
        campo: config.nombre,
        valor,
        tipo: typeof valor
    })

    // Si es requerido y no tiene valor
    if (config.requerido && (valor === null || valor === undefined || valor === '')) {
        errores.push(`${config.etiqueta} es obligatorio`)
        return false
    }

    // Si tiene valor, verificar que sea una fecha válida
    if (valor !== null && valor !== undefined && valor !== '') {
        const fecha = new Date(valor)
        if (isNaN(fecha.getTime())) {
            errores.push(`Debe ser una fecha válida`)
            return false
        }
    }

    return true
}

/**
 * Formatear valor para mostrar en tabla
 * @param {any} valor - Valor a formatear
 * @param {Object} configuracionCampo - Configuración del campo
 * @returns {string} Valor formateado
 */
export const formatearValorParaTabla = (valor, configuracionCampo) => {
    if (valor === null || valor === undefined) {
        return '-'
    }

    switch (configuracionCampo.tipo) {
        case 'booleano':
            return valor ? '✅ Sí' : '❌ No'

        case 'numero':
            return valor.toLocaleString('es-HN')

        case 'texto':
            // Truncar textos muy largos
            if (typeof valor === 'string' && valor.length > 50) {
                return valor.substring(0, 47) + '...'
            }
            return valor

        case 'fecha':
            if (valor instanceof Date) {
                return valor.toLocaleDateString('es-HN')
            }
            return new Date(valor).toLocaleDateString('es-HN')

        default:
            return valor.toString()
    }
}

/**
 * Obtener icono para tipo de campo
 * @param {string} tipoCampo - Tipo del campo
 * @returns {string} Clase de icono
 */
export const obtenerIconoCampo = (tipoCampo) => {
    const iconos = {
        texto: 'pi pi-pencil',
        numero: 'pi pi-hashtag',
        seleccion: 'pi pi-list',
        area_texto: 'pi pi-align-left',
        booleano: 'pi pi-check-square',
        fecha: 'pi pi-calendar',
        foraneo_autocompletado: 'pi pi-search'
    }

    return iconos[tipoCampo] || 'pi pi-question'
}

// =====================================
// UTILIDADES PARA TEMAS Y ESTILOS
// =====================================

/**
 * Aplicar tema a formulario (placeholder para futuras implementaciones)
 * @param {string} nombreTema - Nombre del tema
 */
export const aplicarTemaFormulario = (nombreTema) => {
    console.log('🎨 Aplicando tema:', nombreTema)

    // Por ahora solo log, en el futuro se pueden aplicar clases CSS dinámicas
    // o cambiar variables CSS personalizadas
}

export default {
    obtenerOpcionesDinamicas,
    validarCampoIndividual,
    formatearValorParaTabla,
    obtenerIconoCampo,
    aplicarTemaFormulario
}