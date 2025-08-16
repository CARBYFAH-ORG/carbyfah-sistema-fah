// COMPOSABLE CRUD DINÁMICO - CARBYFAH
// Operaciones CRUD universales para todos los catálogos

import { ref, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useCatalogosStore } from '@/stores/catalogosStore'
import { obtenerNotificacion, obtenerEsquema } from '@/config/esquemaCatalogos'

/**
 * Composable para operaciones CRUD dinámicas
 * @param {string} esquema - Nombre del esquema del catálogo
 */
export const usarCrudDinamico = (esquema) => {
    // Composables y dependencias
    const toast = useToast()
    const catalogosStore = useCatalogosStore()

    // Estado reactivo
    const cargando = ref(false)
    const error = ref(null)

    // Obtener registros desde el store según el esquema
    const registros = computed(() => {
        switch (esquema) {
            case 'tipos_genero':
                return catalogosStore.tiposGenero || []
            case 'categorias_personal':
                return catalogosStore.categoriasPersonal || []
            case 'especialidades':
                return catalogosStore.especialidades || []
            case 'grados':
                return catalogosStore.grados || []
            case 'niveles_prioridad':
                return catalogosStore.nivelesPrioridad || []
            case 'tipos_estado_general':
                return catalogosStore.tiposEstadoGeneral || []
            case 'niveles_seguridad':
                return catalogosStore.nivelesSeguridad || []
            case 'paises':
                return catalogosStore.paises || []
            case 'tipos_estructura_militar':
                return catalogosStore.tiposEstructuraMilitar || []
            case 'tipos_evento':
                return catalogosStore.tiposEvento || []
            case 'tipos_jerarquia':
                return catalogosStore.tiposJerarquia || []
            default:
                return []
        }
    })

    // Configuración del esquema
    const configuracionEsquema = computed(() => {
        return obtenerEsquema(esquema)
    })

    // Registros activos únicamente
    const registrosActivos = computed(() => {
        return registros.value.filter(registro =>
            registro.is_active !== false
        )
    })

    // Estadísticas de registros
    const estadisticas = computed(() => ({
        total: registros.value.length,
        activos: registrosActivos.value.length,
        inactivos: registros.value.length - registrosActivos.value.length
    }))

    // Cargar datos del esquema
    const cargarDatos = async () => {
        cargando.value = true
        error.value = null

        try {
            // Delegar al store según el esquema
            switch (esquema) {
                case 'tipos_genero':
                    await catalogosStore.loadTiposGenero()
                    break
                case 'categorias_personal':
                    await catalogosStore.loadCategoriasPersonal()
                    break
                case 'grados':
                    await catalogosStore.loadGrados()
                    break
                case 'especialidades':
                    await catalogosStore.loadEspecialidades()
                    break
                case 'niveles_prioridad':
                    await catalogosStore.loadNivelesPrioridad()
                    break
                case 'tipos_estado_general':
                    await catalogosStore.loadTiposEstadoGeneral()
                    break
                case 'niveles_seguridad':
                    await catalogosStore.loadNivelesSeguridad()
                    break
                case 'paises':
                    await catalogosStore.loadPaises()
                    break
                case 'tipos_estructura_militar':
                    await catalogosStore.loadTiposEstructuraMilitar()
                    break
                case 'tipos_jerarquia':
                    await catalogosStore.loadTiposJerarquia()
                    break
                case 'tipos_evento':
                    await catalogosStore.loadTiposEvento()
                    break
                default:
                    if (!catalogosStore.catalogosBasicos) {
                        await catalogosStore.loadCatalogosBasicos()
                    }
            }

        } catch (err) {
            error.value = err.message || 'Error cargando datos'
            toast.add(obtenerNotificacion('errorConexion'))
        } finally {
            cargando.value = false
        }
    }

    // Crear nuevo registro
    const crearRegistro = async (datos) => {
        cargando.value = true
        error.value = null

        try {
            let resultado

            switch (esquema) {
                case 'tipos_genero':
                    resultado = await catalogosStore.createTipoGenero(datos)
                    break
                case 'categorias_personal':
                    resultado = await catalogosStore.createCategoriaPersonal(datos)
                    break
                case 'grados':
                    resultado = await catalogosStore.createGrado(datos)
                    break
                case 'especialidades':
                    resultado = await catalogosStore.createEspecialidad(datos)
                    break
                case 'niveles_prioridad':
                    resultado = await catalogosStore.createNivelPrioridad(datos)
                    break
                case 'tipos_estado_general':
                    resultado = await catalogosStore.createTipoEstadoGeneral(datos)
                    break
                case 'niveles_seguridad':
                    resultado = await catalogosStore.createNivelSeguridad(datos)
                    break
                case 'paises':
                    resultado = await catalogosStore.createPais(datos)
                    break
                case 'tipos_estructura_militar':
                    resultado = await catalogosStore.createTipoEstructuraMilitar(datos)
                    break
                case 'tipos_jerarquia':
                    resultado = await catalogosStore.createTipoJerarquia(datos)
                    break
                case 'tipos_evento':
                    resultado = await catalogosStore.createTipoEvento(datos)
                    break
                default:
                    throw new Error(`Creación no implementada para esquema: ${esquema}`)
            }

            if (resultado.success) {
                const nombreRegistro = obtenerNombreRegistro(datos)
                toast.add(obtenerNotificacion('creado', esquema, nombreRegistro))
                return resultado
            } else {
                throw new Error(resultado.error || 'Error desconocido')
            }

        } catch (err) {
            error.value = err.message
            toast.add(obtenerNotificacion('errorCrear', esquema))
            throw err
        } finally {
            cargando.value = false
        }
    }

    // Actualizar registro existente
    const actualizarRegistro = async (id, datos) => {
        cargando.value = true
        error.value = null

        try {
            let resultado

            switch (esquema) {
                case 'tipos_genero':
                    resultado = await catalogosStore.updateTipoGenero(id, datos)
                    break
                case 'categorias_personal':
                    resultado = await catalogosStore.updateCategoriaPersonal(id, datos)
                    break
                case 'grados':
                    resultado = await catalogosStore.updateGrado(id, datos)
                    break
                case 'especialidades':
                    resultado = await catalogosStore.updateEspecialidad(id, datos)
                    break
                case 'niveles_prioridad':
                    resultado = await catalogosStore.updateNivelPrioridad(id, datos)
                    break
                case 'tipos_estado_general':
                    resultado = await catalogosStore.updateTipoEstadoGeneral(id, datos)
                    break
                case 'niveles_seguridad':
                    resultado = await catalogosStore.updateNivelSeguridad(id, datos)
                    break
                case 'paises':
                    resultado = await catalogosStore.updatePais(id, datos)
                    break
                case 'tipos_estructura_militar':
                    resultado = await catalogosStore.updateTipoEstructuraMilitar(id, datos)
                    break
                case 'tipos_jerarquia':
                    resultado = await catalogosStore.updateTipoJerarquia(id, datos)
                    break
                case 'tipos_evento':
                    resultado = await catalogosStore.updateTipoEvento(id, datos)
                    break
                default:
                    throw new Error(`Actualización no implementada para esquema: ${esquema}`)
            }

            if (resultado.success) {
                const nombreRegistro = obtenerNombreRegistro(datos)
                toast.add(obtenerNotificacion('actualizado', esquema, nombreRegistro))
                return resultado
            } else {
                throw new Error(resultado.error || 'Error desconocido')
            }

        } catch (err) {
            error.value = err.message
            toast.add(obtenerNotificacion('errorActualizar', esquema))
            throw err
        } finally {
            cargando.value = false
        }
    }

    // Eliminar registro
    const eliminarRegistro = async (id) => {
        cargando.value = true
        error.value = null

        try {
            let resultado

            switch (esquema) {
                case 'tipos_genero':
                    resultado = await catalogosStore.deleteTipoGenero(id)
                    break
                case 'categorias_personal':
                    resultado = await catalogosStore.deleteCategoriaPersonal(id)
                    break
                case 'grados':
                    resultado = await catalogosStore.deleteGrado(id)
                    break
                case 'especialidades':
                    resultado = await catalogosStore.deleteEspecialidad(id)
                    break
                case 'niveles_prioridad':
                    resultado = await catalogosStore.deleteNivelPrioridad(id)
                    break
                case 'tipos_estado_general':
                    resultado = await catalogosStore.deleteTipoEstadoGeneral(id)
                    break
                case 'niveles_seguridad':
                    resultado = await catalogosStore.deleteNivelSeguridad(id)
                    break
                case 'paises':
                    resultado = await catalogosStore.deletePais(id)
                    break
                case 'tipos_estructura_militar':
                    resultado = await catalogosStore.deleteTipoEstructuraMilitar(id)
                    break
                case 'tipos_jerarquia':
                    resultado = await catalogosStore.deleteTipoJerarquia(id)
                    break
                case 'tipos_evento':
                    resultado = await catalogosStore.deleteTipoEvento(id)
                    break
                default:
                    throw new Error(`Eliminación no implementada para esquema: ${esquema}`)
            }

            if (resultado.success) {
                toast.add(obtenerNotificacion('eliminado', esquema, 'Registro'))
                return resultado
            } else {
                throw new Error(resultado.error || 'Error desconocido')
            }

        } catch (err) {
            error.value = err.message
            toast.add(obtenerNotificacion('errorEliminar', esquema))
            throw err
        } finally {
            cargando.value = false
        }
    }

    // Buscar registro por ID
    const buscarPorId = (id) => {
        return registros.value.find(registro =>
            registro.id === id || registro.id === parseInt(id)
        )
    }

    // Buscar registros por campo
    const buscarPorCampo = (campo, valor) => {
        return registros.value.filter(registro =>
            registro[campo] === valor
        )
    }

    // Filtrar registros
    const filtrarRegistros = (filtros) => {
        return registros.value.filter(registro => {
            return Object.entries(filtros).every(([campo, valor]) => {
                if (valor === null || valor === undefined || valor === '') {
                    return true
                }

                const valorRegistro = registro[campo]

                if (typeof valor === 'string') {
                    return valorRegistro?.toString().toLowerCase().includes(valor.toLowerCase())
                }

                return valorRegistro === valor
            })
        })
    }

    // Ordenar registros
    const ordenarRegistros = (campo, direccion = 'asc') => {
        return [...registros.value].sort((a, b) => {
            const valorA = a[campo]
            const valorB = b[campo]

            if (valorA < valorB) return direccion === 'asc' ? -1 : 1
            if (valorA > valorB) return direccion === 'asc' ? 1 : -1
            return 0
        })
    }

    // Obtener nombre representativo del registro
    const obtenerNombreRegistro = (registro) => {
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
            if (registro[campo]) {
                return registro[campo]
            }
        }

        return 'Registro'
    }

    // Validar si un registro puede ser eliminado
    const puedeEliminar = (registro) => {
        switch (esquema) {
            case 'categorias_personal':
                return !catalogosStore.grados.some(grado =>
                    grado.categoria_personal_id === registro.id
                )
            default:
                return true
        }
    }

    // Obtener dependencias de un registro
    const obtenerDependencias = (registro) => {
        const dependencias = []

        switch (esquema) {
            case 'categorias_personal':
                const gradosAsociados = catalogosStore.grados.filter(grado =>
                    grado.categoria_personal_id === registro.id
                )
                if (gradosAsociados.length > 0) {
                    dependencias.push({
                        tipo: 'grados',
                        cantidad: gradosAsociados.length,
                        mensaje: `${gradosAsociados.length} grado(s) militar(es)`
                    })
                }
                break
        }

        return dependencias
    }

    // Limpiar errores
    const limpiarErrores = () => {
        error.value = null
    }

    // Refrescar datos
    const refrescar = async () => {
        await cargarDatos()
    }

    return {
        // Estado reactivo
        cargando,
        error,

        // Computed
        registros,
        configuracionEsquema,
        registrosActivos,
        estadisticas,

        // Operaciones CRUD
        cargarDatos,
        crearRegistro,
        actualizarRegistro,
        eliminarRegistro,

        // Búsqueda y filtrado
        buscarPorId,
        buscarPorCampo,
        filtrarRegistros,
        ordenarRegistros,

        // Validaciones y utilidades
        puedeEliminar,
        obtenerDependencias,
        obtenerNombreRegistro,
        limpiarErrores,
        refrescar
    }
}

export default usarCrudDinamico