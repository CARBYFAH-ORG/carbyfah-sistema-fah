import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { personalAPI } from '@/services/personalService'

export const usePersonalStore = defineStore('personal', () => {
    // Estado reactivo principal
    const datos = ref([])
    const datoActual = ref(null)
    const isLoading = ref(false)
    const error = ref(null)
    const totalRegistros = ref(0)
    const paginaActual = ref(1)
    const registrosPorPagina = ref(10)

    // Estado específico para Personal FAH
    const filtros = ref({
        busqueda: '',
        categoria_personal_id: null,
        especialidad_id: null,
        grado_id: null,
        unidad_id: null,
        estado: 'ACTIVO'
    })

    // Estadísticas de personal
    const estadisticas = ref({
        total_efectivos: 3644,
        oficiales: 551,
        suboficiales: 711,
        tropa: 1472,
        auxiliares: 460,
        activos: 0,
        en_mision: 0,
        en_licencia: 0,
        baja_temporal: 0
    })

    // Estado para formularios y vistas
    const mostrarFormulario = ref(false)
    const modoFormulario = ref('crear')
    const esquemaActivo = ref('datos_personales')
    const datosFormulario = ref({})

    // Computed properties
    const totalPaginas = computed(() => {
        return Math.ceil(totalRegistros.value / registrosPorPagina.value)
    })

    const hayDatos = computed(() => {
        return datos.value && datos.value.length > 0
    })

    const hayError = computed(() => {
        return error.value !== null
    })

    const personalActivo = computed(() => {
        return datos.value.filter(p => p.estado === 'ACTIVO')
    })

    const personalEnMision = computed(() => {
        return datos.value.filter(p => p.estado === 'EN_MISION')
    })

    // Acciones principales
    const cargarDatos = async (esquema = 'datos_personales', opciones = {}) => {
        try {
            isLoading.value = true
            error.value = null

            const parametros = {
                page: paginaActual.value,
                per_page: registrosPorPagina.value,
                ...filtros.value,
                ...opciones
            }

            let response
            switch (esquema) {
                case 'datos_personales':
                    response = await personalAPI.get('/personal/datos-personales', { params: parametros })
                    break
                case 'perfiles_militares':
                    response = await personalAPI.get('/personal/perfiles-militares', { params: parametros })
                    break
                case 'asignaciones_actuales':
                    response = await personalAPI.get('/personal/asignaciones-actuales', { params: parametros })
                    break
                case 'usuarios_sistema':
                    response = await personalAPI.get('/personal/usuarios-sistema', { params: parametros })
                    break
                default:
                    response = await personalAPI.get('/personal/datos-personales', { params: parametros })
            }

            datos.value = response.data.data || []
            totalRegistros.value = response.data.meta?.total || 0
            esquemaActivo.value = esquema

            console.log(`✅ Datos cargados para esquema: ${esquema}`, {
                registros: datos.value.length,
                total: totalRegistros.value
            })

            return response.data

        } catch (err) {
            console.error(`❌ Error cargando datos de ${esquema}:`, err)
            error.value = err.response?.data?.message || 'Error al cargar datos de personal'
            datos.value = []
            totalRegistros.value = 0
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const cargarEstadisticas = async () => {
        try {
            const response = await personalAPI.get('/personal/datos-personales/estadisticas/generales')
            estadisticas.value = { ...estadisticas.value, ...response.data.data }
            console.log('✅ Estadísticas de personal cargadas:', estadisticas.value)
        } catch (err) {
            console.error('❌ Error cargando estadísticas:', err)
        }
    }

    const crear = async (esquema, datosNuevos) => {
        try {
            isLoading.value = true
            error.value = null

            let endpoint = ''
            switch (esquema) {
                case 'datos_personales':
                    endpoint = '/personal/datos-personales'
                    break
                case 'perfiles_militares':
                    endpoint = '/personal/perfiles-militares'
                    break
                case 'asignaciones_actuales':
                    endpoint = '/personal/asignaciones-actuales'
                    break
                case 'usuarios_sistema':
                    endpoint = '/personal/usuarios-sistema'
                    break
                default:
                    throw new Error(`Esquema no válido: ${esquema}`)
            }

            const response = await personalAPI.post(endpoint, datosNuevos)

            await cargarDatos(esquema)

            console.log(`✅ ${esquema} creado exitosamente:`, response.data)
            return response.data

        } catch (err) {
            console.error(`❌ Error creando ${esquema}:`, err)
            error.value = err.response?.data?.message || `Error al crear ${esquema}`
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const actualizar = async (esquema, id, datosActualizados) => {
        try {
            isLoading.value = true
            error.value = null

            let endpoint = ''
            switch (esquema) {
                case 'datos_personales':
                    endpoint = `/personal/datos-personales/${id}`
                    break
                case 'perfiles_militares':
                    endpoint = `/personal/perfiles-militares/${id}`
                    break
                case 'asignaciones_actuales':
                    endpoint = `/personal/asignaciones-actuales/${id}`
                    break
                case 'usuarios_sistema':
                    endpoint = `/personal/usuarios-sistema/${id}`
                    break
                default:
                    throw new Error(`Esquema no válido: ${esquema}`)
            }

            const response = await personalAPI.put(endpoint, datosActualizados)

            await cargarDatos(esquema)

            console.log(`✅ ${esquema} actualizado exitosamente:`, response.data)
            return response.data

        } catch (err) {
            console.error(`❌ Error actualizando ${esquema}:`, err)
            error.value = err.response?.data?.message || `Error al actualizar ${esquema}`
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const eliminar = async (esquema, id) => {
        try {
            isLoading.value = true
            error.value = null

            let endpoint = ''
            switch (esquema) {
                case 'datos_personales':
                    endpoint = `/personal/datos-personales/${id}`
                    break
                case 'perfiles_militares':
                    endpoint = `/personal/perfiles-militares/${id}`
                    break
                case 'asignaciones_actuales':
                    endpoint = `/personal/asignaciones-actuales/${id}`
                    break
                case 'usuarios_sistema':
                    endpoint = `/personal/usuarios-sistema/${id}`
                    break
                default:
                    throw new Error(`Esquema no válido: ${esquema}`)
            }

            await personalAPI.delete(endpoint)

            await cargarDatos(esquema)

            console.log(`✅ ${esquema} eliminado exitosamente`)
            return true

        } catch (err) {
            console.error(`❌ Error eliminando ${esquema}:`, err)
            error.value = err.response?.data?.message || `Error al eliminar ${esquema}`
            throw err
        } finally {
            isLoading.value = false
        }
    }

    // Acciones específicas para Personal
    const buscarPorIdentidad = async (numeroIdentidad) => {
        try {
            const response = await personalAPI.get(`/personal/datos-personales/por-identidad/${numeroIdentidad}`)
            return response.data.data
        } catch (err) {
            console.error('❌ Error buscando por identidad:', err)
            throw err
        }
    }

    const cambiarEstadoPersonal = async (id, nuevoEstado) => {
        try {
            const response = await personalAPI.patch(`/personal/datos-personales/${id}/estado`, {
                estado: nuevoEstado
            })

            await cargarDatos(esquemaActivo.value)
            await cargarEstadisticas()

            return response.data
        } catch (err) {
            console.error('❌ Error cambiando estado:', err)
            throw err
        }
    }

    // Utilidades
    const limpiarError = () => {
        error.value = null
    }

    const limpiarDatos = () => {
        datos.value = []
        datoActual.value = null
        totalRegistros.value = 0
        error.value = null
    }

    const establecerFiltros = (nuevosFiltros) => {
        filtros.value = { ...filtros.value, ...nuevosFiltros }
        paginaActual.value = 1
    }

    const cambiarPagina = (nuevaPagina) => {
        if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas.value) {
            paginaActual.value = nuevaPagina
        }
    }

    // Configuración de formularios
    const abrirFormulario = (modo = 'crear', datos = null) => {
        modoFormulario.value = modo
        datosFormulario.value = datos ? { ...datos } : {}
        mostrarFormulario.value = true
    }

    const cerrarFormulario = () => {
        mostrarFormulario.value = false
        datosFormulario.value = {}
        modoFormulario.value = 'crear'
    }

    return {
        // Estado
        datos,
        datoActual,
        isLoading,
        error,
        totalRegistros,
        paginaActual,
        registrosPorPagina,
        filtros,
        estadisticas,
        mostrarFormulario,
        modoFormulario,
        esquemaActivo,
        datosFormulario,

        // Computed
        totalPaginas,
        hayDatos,
        hayError,
        personalActivo,
        personalEnMision,

        // Acciones
        cargarDatos,
        cargarEstadisticas,
        crear,
        actualizar,
        eliminar,
        buscarPorIdentidad,
        cambiarEstadoPersonal,

        // Utilidades
        limpiarError,
        limpiarDatos,
        establecerFiltros,
        cambiarPagina,
        abrirFormulario,
        cerrarFormulario
    }
})