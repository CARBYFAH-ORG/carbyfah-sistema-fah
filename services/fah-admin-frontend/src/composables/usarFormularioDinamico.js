// services\fah-admin-frontend\src\composables\usarFormularioDinamico.js

// Composable formulario dinamico - CARBYFAH
// Logica reutilizable para todos los formularios CRUD

import { ref, computed } from 'vue'
import {
    obtenerEsquema,
    obtenerNotificacion,
    validarEsquema,
    CONFIGURACION_GLOBAL
} from '@/config/esquemaCatalogos'

// Soporte para esquemas de organizacion
import {
    obtenerEsquema as obtenerEsquemaOrganizacion,
    generarEtiquetaAmigable as generarEtiquetaOrganizacion,
    generarPlaceholder as generarPlaceholderOrganizacion
} from '@/config/esquemaOrganizacion'

import { useCatalogosStore } from '@/stores/catalogosStore'

// Funcion para detectar tipo de esquema
const detectarTipoEsquema = (nombreTabla) => {
    const esquemasOrganizacion = [
        'departamentos',
        'municipios',
        'ciudades',
        'ubicaciones_geograficas',
        'estructura_militar',
        'cargos',
        'roles_funcionales'
    ]

    return esquemasOrganizacion.includes(nombreTabla) ? 'organizacion' : 'catalogos'
}

// Funcion universal para obtener esquema
const obtenerEsquemaUniversal = (nombreTabla) => {
    const tipoEsquema = detectarTipoEsquema(nombreTabla)

    if (tipoEsquema === 'organizacion') {
        return obtenerEsquemaOrganizacion(nombreTabla)
    } else {
        return obtenerEsquema(nombreTabla)
    }
}

// Proporciona toda la logica necesaria para CRUD de catalogos
export const usarFormularioDinamico = () => {
    // Estado reactivo
    const cargandoEsquema = ref(false)
    const esquemaActual = ref(null)
    const erroresValidacionActual = ref({})

    // Computed properties
    const tieneEsquemaCargado = computed(() => {
        return esquemaActual.value !== null
    })

    const esEsquemaValido = computed(() => {
        if (!esquemaActual.value) return false

        // Validar segun el tipo de esquema
        const tipoEsquema = detectarTipoEsquema(esquemaActual.value.tabla)

        if (tipoEsquema === 'organizacion') {
            // Para organizacion, verificar que existe la configuracion
            return esquemaActual.value !== null
        } else {
            // Para catalogos, usar validacion original
            const validacion = validarEsquema(esquemaActual.value.tabla)
            return validacion.valido
        }
    })

    // Configurar esquema desde configuracion
    const configurarEsquema = async (nombreTabla) => {
        cargandoEsquema.value = true

        try {
            // Usar funcion universal
            const esquema = obtenerEsquemaUniversal(nombreTabla)

            if (!esquema) {
                return null
            }

            // Validar segun el tipo
            const tipoEsquema = detectarTipoEsquema(nombreTabla)

            if (tipoEsquema === 'catalogos') {
                const validacion = validarEsquema(nombreTabla)
                if (!validacion.valido) {
                    return null
                }
            }

            esquemaActual.value = esquema

            await cargarDependenciasReferencia(esquema)

            return esquema

        } catch (error) {
            return null
        } finally {
            cargandoEsquema.value = false
        }
    }

    // Esta funcion ahora soporta ambos tipos de esquemas
    const analizarConfiguracionCampo = (configuracionCampo, nombreTabla = null) => {
        // Si ya es un objeto, devolverlo directamente
        if (typeof configuracionCampo === 'object' && configuracionCampo !== null) {
            return configuracionCampo
        }

        // Procesar sintaxis de string
        if (typeof configuracionCampo !== 'string') {
            return null
        }

        const partes = configuracionCampo.split(':')

        if (partes.length < 2) {
            return null
        }

        const [nombre, tipo, ...opciones] = partes

        // Detectar tipo de esquema
        const tipoEsquema = nombreTabla ? detectarTipoEsquema(nombreTabla) : 'catalogos'

        const configuracion = {
            nombre,
            tipo,
            etiqueta: tipoEsquema === 'organizacion'
                ? generarEtiquetaOrganizacion(nombre)
                : generarEtiquetaAmigable(nombre),
            requerido: false,
            placeholder: tipoEsquema === 'organizacion'
                ? generarPlaceholderOrganizacion(nombre, tipo)
                : generarPlaceholder(nombre, tipo, esquemaActual.value?.tabla),
            columnas: 12
        }

        // Procesamiento de opciones
        for (let i = 0; i < opciones.length; i++) {
            const opcion = opciones[i]

            if (opcion === 'requerido') {
                configuracion.requerido = true

            } else if (opcion === 'longitud' && i + 1 < opciones.length && !isNaN(parseInt(opciones[i + 1]))) {
                configuracion.longitudMaxima = parseInt(opciones[i + 1])
                i++

            } else if (opcion.startsWith('longitud:')) {
                configuracion.longitudMaxima = parseInt(opcion.split(':')[1])

            } else if (opcion === 'min' && i + 1 < opciones.length && !isNaN(parseInt(opciones[i + 1]))) {
                configuracion.minimo = parseInt(opciones[i + 1])
                i++

            } else if (opcion.startsWith('min:')) {
                configuracion.minimo = parseInt(opcion.split(':')[1])

            } else if (opcion === 'max' && i + 1 < opciones.length && !isNaN(parseInt(opciones[i + 1]))) {
                configuracion.maximo = parseInt(opciones[i + 1])
                i++

            } else if (opcion.startsWith('max:')) {
                configuracion.maximo = parseInt(opcion.split(':')[1])

            } else if (opcion === 'referencia' && i + 1 < opciones.length) {
                configuracion.tablaReferencia = opciones[i + 1]
                i++

            } else if (opcion.startsWith('referencia:')) {
                configuracion.tablaReferencia = opcion.split(':')[1]

            } else if (opcion.startsWith('pattern:')) {
                configuracion.patron = opcion.split(':')[1]

            } else if (opcion.startsWith('filas:')) {
                configuracion.filas = parseInt(opcion.split(':')[1])

            } else if (opcion.startsWith('formato:')) {
                configuracion.formatoFecha = opcion.split(':')[1]

            } else if (opcion === 'autocompletado') {
                configuracion.tipo = 'autocompletado'

            } else if (opcion === 'medio') {
                configuracion.columnas = 6

            } else if (opcion === 'completo') {
                configuracion.columnas = 12
            }
        }

        // Configurar columnas segun el tipo
        if (tipo === 'area_texto') {
            configuracion.columnas = 12
        } else if (tipo === 'booleano') {
            configuracion.columnas = 12
        } else if (tipo === 'seleccion' || tipo === 'autocompletado') {
            configuracion.columnas = 6
        } else if (tipo === 'fecha') {
            configuracion.columnas = 6
        } else if (tipo === 'numero') {
            configuracion.columnas = 6
        } else {
            configuracion.columnas = 6
        }

        // Configuracion de ayuda automatica
        if (configuracion.tablaReferencia) {
            configuracion.ayuda = "Este es un campo de busqueda inteligente"
        }

        if (configuracion.patron) {
            configuracion.ayuda = (configuracion.ayuda ? configuracion.ayuda + '. ' : '') +
                `Formato requerido: ${configuracion.patron}`
        }

        if (configuracion.minimo !== undefined || configuracion.maximo !== undefined) {
            const rangoTexto = configuracion.minimo !== undefined && configuracion.maximo !== undefined
                ? `Entre ${configuracion.minimo} y ${configuracion.maximo}`
                : configuracion.minimo !== undefined
                    ? `Minimo: ${configuracion.minimo}`
                    : `Maximo: ${configuracion.maximo}`

            configuracion.ayuda = (configuracion.ayuda ? configuracion.ayuda + '. ' : '') + rangoTexto
        }

        configuracion.tipoCampoCompleto = configuracionCampo;

        return configuracion
    }

    // Generar etiqueta amigable desde nombre de campo
    const generarEtiquetaAmigable = (nombreCampo) => {
        const mapeoEtiquetas = {
            codigo: 'Codigo',
            nombre: 'Nombre',
            abreviatura: 'Abreviatura',
            codigo_categoria: 'Codigo Categoria',
            nombre_categoria: 'Nombre Categoria',
            orden_jerarquico: 'Orden Jerarquico',
            codigo_especialidad: 'Codigo Especialidad',
            nombre_especialidad: 'Nombre Especialidad',
            insignia_url: 'URL Insignia',
            categoria_personal_id: 'Categoria Personal',
            codigo_grado: 'Codigo Grado',
            nombre_grado: 'Nombre Grado',
            nivel_numerico: 'Nivel Numerico',
            requiere_autorizacion: 'Requiere Autorizacion',
            tiempo_retencion_anos: 'Tiempo Retencion (anos)',
            nombre_oficial: 'Nombre Oficial',
            codigo_iso3: 'Codigo ISO3',
            codigo_telefono: 'Codigo Telefono',
            moneda_oficial: 'Moneda Oficial',
            permite_operaciones: 'Permite Operaciones',
            es_estado_final: 'Es Estado Final',
            requiere_justificacion: 'Requiere Justificacion',
            codigo_tipo: 'Codigo Tipo',
            nombre_tipo: 'Nombre Tipo',
            nivel_organizacional: 'Nivel Organizacional',
            nivel_autoridad: 'Nivel Autoridad',
            codigo_evento: 'Codigo Evento',
            nombre_evento: 'Nombre Evento',
            requiere_aprobacion: 'Requiere Aprobacion'
        }

        return mapeoEtiquetas[nombreCampo] || formatearNombreCampo(nombreCampo)
    }

    // Formatear nombre de campo como etiqueta
    const formatearNombreCampo = (nombreCampo) => {
        return nombreCampo
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase())
    }

    // Generar placeholder para campo especifico por esquema
    const generarPlaceholder = (nombreCampo, tipoCampo, esquemaTabla = null) => {
        // Los campos booleanos (checkboxes) no necesitan placeholder
        if (tipoCampo === 'booleano') {
            return ''
        }

        // Los campos de autocompletado foraneo tienen placeholder especial
        if (tipoCampo === 'foraneo_autocompletado') {
            return `Buscar y seleccionar ${generarEtiquetaAmigable(nombreCampo).toLowerCase()}...`
        }

        // Placeholders especificos por esquema
        const placeholdersEspecificos = {
            // Tipos de genero
            tipos_genero: {
                codigo: 'Ej: M, F',
                nombre: 'Ej: Masculino, Femenino',
                abreviatura: 'Ej: M, F'
            },

            // Categorias personal
            categorias_personal: {
                codigo_categoria: 'Ej: OFICIAL, SUBOFICIAL, TROPA',
                nombre_categoria: 'Ej: Oficial, Suboficial, Tropa',
                orden_jerarquico: 'Ej: 1 (mas alto), 2, 3...'
            },

            // Especialidades
            especialidades: {
                codigo_especialidad: 'Ej: AVI, COM, INT, LOG',
                nombre_especialidad: 'Ej: Aviacion, Comunicaciones, Inteligencia',
                insignia_url: 'Ej: https://fah.mil.hn/insignias/aviacion.png'
            },

            // Grados
            grados: {
                codigo_grado: 'Ej: GRL, CNL, TTE-CNL, MYR',
                nombre_grado: 'Ej: General, Coronel, Teniente Coronel',
                orden_jerarquico: 'Ej: 1 (General), 2 (Coronel)...',
                abreviatura: 'Ej: GRL, CNL, TTE-CNL',
                categoria_personal_id: 'Seleccionar categoria personal...'
            },

            // Niveles prioridad
            niveles_prioridad: {
                codigo: 'Ej: BAJA, MEDIA, ALTA, CRITICA',
                nombre: 'Ej: Baja, Media, Alta, Critica',
                nivel_numerico: 'Ej: 1 (Baja), 2 (Media), 3 (Alta), 4 (Critica)'
            },

            // Niveles seguridad
            niveles_seguridad: {
                codigo: 'Ej: PUBLICO, RESTRINGIDO, CONFIDENCIAL',
                nombre: 'Ej: Publico, Restringido, Confidencial, Secreto',
                nivel_numerico: 'Ej: 1 (Publico), 2 (Restringido), 3 (Confidencial)',
                tiempo_retencion_anos: 'Ej: 5, 10, 25 anos'
            },

            // Paises
            paises: {
                nombre: 'Ej: Honduras, Guatemala, El Salvador',
                nombre_oficial: 'Ej: Republica de Honduras',
                codigo_iso3: 'Ej: HND, GTM, SLV (3 letras)',
                codigo_telefono: 'Ej: +504, +502, +503',
                moneda_oficial: 'Ej: Lempira, Quetzal, Dolar'
            },

            // Tipos estado general
            tipos_estado_general: {
                codigo: 'Ej: ACTIVO, INACTIVO, SUSPENDIDO',
                nombre: 'Ej: Activo, Inactivo, Suspendido, En Revision'
            },

            // Tipos estructura militar
            tipos_estructura_militar: {
                codigo_tipo: 'Ej: CMD, BASE, ESC, COMP',
                nombre_tipo: 'Ej: Comandancia, Base Aerea, Escuadron',
                nivel_organizacional: 'Ej: 1 (Comandancia), 2 (Base), 3 (Escuadron)'
            },

            // Tipos evento
            tipos_evento: {
                codigo_evento: 'Ej: CAP, MIS, PER, ENT',
                nombre_evento: 'Ej: Capacitacion, Mision, Permiso, Entrenamiento'
            },

            // Tipos jerarquia
            tipos_jerarquia: {
                codigo_tipo: 'Ej: CMD-DIR, CMD-OPE, CMD-ADM',
                nombre_tipo: 'Ej: Comando Directo, Comando Operacional',
                nivel_autoridad: 'Ej: 1 (Maxima), 2 (Alta), 3 (Media)'
            }
        }

        // Si hay esquema especifico, buscar placeholder especifico
        if (esquemaTabla && placeholdersEspecificos[esquemaTabla]) {
            const placeholderEspecifico = placeholdersEspecificos[esquemaTabla][nombreCampo]
            if (placeholderEspecifico) {
                return placeholderEspecifico
            }
        }

        // Fallback a placeholders genericos por tipo de campo
        switch (tipoCampo) {
            case 'texto':
                return `Ingrese ${generarEtiquetaAmigable(nombreCampo).toLowerCase()}`
            case 'numero':
                return `Ingrese valor numerico`
            case 'area_texto':
                return `Ingrese descripcion detallada`
            case 'fecha':
                return 'Seleccionar fecha'
            case 'seleccion':
                return 'Seleccionar opcion'
            default:
                return `Ingrese ${generarEtiquetaAmigable(nombreCampo).toLowerCase()}`
        }
    }

    // Obtener valores por defecto para un esquema
    const obtenerValoresDefecto = (esquema) => {
        const valores = {}

        esquema.campos.forEach(configuracionCampo => {
            const config = analizarConfiguracionCampo(configuracionCampo)

            if (config) {
                switch (config.tipo) {
                    case 'texto':
                    case 'area_texto':
                        valores[config.nombre] = ''
                        break
                    case 'numero':
                        valores[config.nombre] = null
                        break
                    case 'booleano':
                        valores[config.nombre] = false
                        break
                    case 'seleccion':
                        valores[config.nombre] = null
                        break
                    case 'fecha':
                        valores[config.nombre] = null
                        break
                    case 'foraneo_autocompletado':
                        valores[config.nombre] = null
                        break
                    default:
                        valores[config.nombre] = null
                }
            }
        })

        return valores
    }

    // Validar datos del formulario
    const validarDatos = (esquema, datos) => {
        const errores = {}
        let esValido = true

        esquema.campos.forEach(configuracionCampo => {
            const config = analizarConfiguracionCampo(configuracionCampo)

            if (!config) return

            const valor = datos[config.nombre]

            // Validar campo requerido
            if (config.requerido && (valor === null || valor === undefined || valor === '')) {
                errores[config.nombre] = `${config.etiqueta} es obligatorio`
                esValido = false
                return
            }

            // Validaciones adicionales solo si hay valor
            if (valor !== null && valor !== undefined && valor !== '') {
                // Validar longitud maxima
                if (config.longitudMaxima && typeof valor === 'string' && valor.length > config.longitudMaxima) {
                    errores[config.nombre] = `Maximo ${config.longitudMaxima} caracteres`
                    esValido = false
                }

                // Validar rango numerico
                if (config.tipo === 'numero') {
                    const numValor = Number(valor)

                    if (config.minimo && numValor < config.minimo) {
                        errores[config.nombre] = `Valor minimo: ${config.minimo}`
                        esValido = false
                    }

                    if (config.maximo && numValor > config.maximo) {
                        errores[config.nombre] = `Valor maximo: ${config.maximo}`
                        esValido = false
                    }
                }
            }
        })

        erroresValidacionActual.value = errores

        const resultado = { esValido, errores }

        return resultado
    }

    // Formatear datos para enviar al backend
    const formatearParaBackend = (esquema, datos) => {
        const datosFormateados = { ...datos }

        esquema.campos.forEach(configuracionCampo => {
            const config = analizarConfiguracionCampo(configuracionCampo)

            if (!config) return

            const valor = datosFormateados[config.nombre]

            // Formatear segun tipo
            switch (config.tipo) {
                case 'numero':
                    if (valor !== null && valor !== undefined && valor !== '') {
                        datosFormateados[config.nombre] = Number(valor)
                    }
                    break

                case 'booleano':
                    datosFormateados[config.nombre] = Boolean(valor)
                    break

                case 'texto':
                case 'area_texto':
                    if (typeof valor === 'string') {
                        datosFormateados[config.nombre] = valor.trim()
                    }
                    break
            }
        })

        return datosFormateados
    }

    // Esta funcion esta lista para cargar dependencias
    const cargarDependenciasReferencia = async (esquema) => {
        const catalogosStore = useCatalogosStore()

        // Agregar soporte para organizacion
        const { useOrganizacionStore } = await import('@/stores/organizacionStore')
        const organizacionStore = useOrganizacionStore()

        const dependenciasDetectadas = new Set()

        // Analizar cada campo para detectar referencias
        esquema.campos.forEach(configuracionCampo => {
            const config = analizarConfiguracionCampo(configuracionCampo)

            if (config && config.tablaReferencia) {
                dependenciasDetectadas.add(config.tablaReferencia)
            }
        })

        // Cargar dependencias
        for (const tablaReferencia of dependenciasDetectadas) {
            try {
                switch (tablaReferencia) {
                    // Catalogos existentes
                    case 'categorias_personal':
                        await catalogosStore.loadCategoriasPersonal()
                        break
                    case 'especialidades':
                        await catalogosStore.loadEspecialidades()
                        break
                    case 'tipos_genero':
                        await catalogosStore.loadTiposGenero()
                        break
                    case 'niveles_prioridad':
                        await catalogosStore.loadNivelesPrioridad()
                        break
                    case 'paises':
                        await catalogosStore.loadPaises()
                        break
                    case 'tipos_estructura_militar':
                        await catalogosStore.loadTiposEstructuraMilitar()
                        break

                    // Nuevas dependencias de organizacion
                    case 'departamentos':
                        await organizacionStore.loadDepartamentos()
                        break
                    case 'municipios':
                        await organizacionStore.loadMunicipios()
                        break
                    case 'ciudades':
                        await organizacionStore.loadCiudades()
                        break
                    case 'ubicaciones_geograficas':
                        await organizacionStore.loadUbicacionesGeograficas()
                        break
                    case 'estructura_militar':
                        await organizacionStore.loadEstructuraMilitar()
                        break
                    case 'cargos':
                        await organizacionStore.loadCargos()
                        break
                    case 'roles_funcionales':
                        await organizacionStore.loadRolesFuncionales()
                        break

                    default:
                        console.warn(`Tabla de referencia no configurada: ${tablaReferencia}`)
                        break
                }
            } catch (error) {
                console.error(`Error cargando dependencia ${tablaReferencia}:`, error)
            }
        }
    }

    // Limpiar errores de validacion
    const limpiarErrores = () => {
        erroresValidacionActual.value = {}
    }

    // Obtener configuracion de campo especifico
    const obtenerConfiguracionCampo = (esquema, nombreCampo) => {
        const configuracionCampo = esquema.campos.find(campo => {
            const config = analizarConfiguracionCampo(campo)
            return config && config.nombre === nombreCampo
        })

        return configuracionCampo ? analizarConfiguracionCampo(configuracionCampo) : null
    }

    // Generar ID unico para campos
    const generarIdCampo = (nombreCampo) => {
        return `campo-${nombreCampo}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
    }

    // Verificar si un campo es de tipo seleccion con referencia
    const esCampoReferencia = (configuracionCampo) => {
        const config = analizarConfiguracionCampo(configuracionCampo)
        return config && config.tipo === 'seleccion' && config.tablaReferencia
    }

    // Obtener nombre amigable para mostrar en notificaciones
    const obtenerNombreAmigable = (datos, esquema) => {
        const camposPrioridad = [
            'nombre',
            'nombre_categoria',
            'nombre_especialidad',
            'nombre_grado',
            'nombre_evento',
            'nombre_tipo',
            'codigo',
            'codigo_categoria',
            'codigo_especialidad',
            'codigo_grado',
            'codigo_evento',
            'codigo_tipo'
        ]

        for (const campo of camposPrioridad) {
            if (datos[campo]) {
                return datos[campo]
            }
        }

        return 'Registro'
    }

    return {
        // Estado reactivo
        cargandoEsquema,
        esquemaActual,
        erroresValidacionActual,

        // Computed
        tieneEsquemaCargado,
        esEsquemaValido,

        // Funciones principales
        configurarEsquema,
        analizarConfiguracionCampo,
        obtenerValoresDefecto,
        validarDatos,
        formatearParaBackend,
        limpiarErrores,

        // Nuevas funciones de soporte
        detectarTipoEsquema,
        obtenerEsquemaUniversal,

        // Funciones auxiliares
        generarEtiquetaAmigable,
        formatearNombreCampo,
        generarPlaceholder,
        obtenerConfiguracionCampo,
        generarIdCampo,
        esCampoReferencia,
        obtenerNombreAmigable,

        // Configuracion global
        configuracionGlobal: CONFIGURACION_GLOBAL
    }
}

export default usarFormularioDinamico