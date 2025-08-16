import axios from 'axios'

// Configuración base axios para catálogos
const API_BASE_URL = process.env.NODE_ENV === 'development'
    ? '/api'
    : 'http://localhost:8008/api'

// Crear instancia axios específica para catálogos
const catalogosAPI = axios.create({
    baseURL: API_BASE_URL,
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

// Interceptor para logging y debug
catalogosAPI.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('fah_token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

// Interceptor para manejar respuestas y errores
catalogosAPI.interceptors.response.use(
    (response) => {
        return response
    },
    (error) => {
        // Si es error 401, redirigir a login
        if (error.response?.status === 401) {
            localStorage.removeItem('fah_token')
            localStorage.removeItem('fah_user')
            window.location.href = '/login'
        }

        return Promise.reject(error)
    }
)

// =====================================================
// SERVICIOS GENERALES
// =====================================================
export const healthCheck = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/health')
        return {
            success: true,
            data: response.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error de conexión'
        }
    }
}

export const pingService = async () => {
    try {
        const response = await catalogosAPI.get('/ping')
        return {
            success: true,
            data: response.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Servicio no disponible'
        }
    }
}

export const getCatalogosBasicos = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/basicos')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo catálogos'
        }
    }
}

export const getEstadisticasCatalogos = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/estadisticas')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo estadísticas'
        }
    }
}

// =====================================================
// SERVICIOS TIPOS GÉNERO
// =====================================================
export const getTiposGenero = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/tipos-genero')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo tipos de género'
        }
    }
}

export const createTipoGenero = async (tipoGenero) => {
    try {
        const response = await catalogosAPI.post('/catalogos/tipos-genero', tipoGenero)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando tipo de género',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateTipoGenero = async (id, tipoGenero) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/tipos-genero/${id}`, tipoGenero)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando tipo de género',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteTipoGenero = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/tipos-genero/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando tipo de género'
        }
    }
}

// =====================================================
// SERVICIOS CATEGORÍAS PERSONAL
// =====================================================
export const getCategoriasPersonal = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/categorias-personal')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo categorías de personal'
        }
    }
}

export const createCategoriaPersonal = async (categoria) => {
    try {
        const response = await catalogosAPI.post('/catalogos/categorias-personal', categoria)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando categoría de personal',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateCategoriaPersonal = async (id, categoria) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/categorias-personal/${id}`, categoria)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando categoría de personal',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteCategoriaPersonal = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/categorias-personal/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando categoría de personal'
        }
    }
}

// =====================================================
// SERVICIOS GRADOS
// =====================================================
export const getGrados = async (categoriaId = null) => {
    try {
        const params = categoriaId ? { categoria_id: categoriaId } : {}
        const response = await catalogosAPI.get('/catalogos/grados', { params })
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo grados'
        }
    }
}

export const getGrado = async (id) => {
    try {
        const response = await catalogosAPI.get(`/catalogos/grados/${id}`)
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo grado'
        }
    }
}

export const getGradosPorCategoria = async (categoriaId) => {
    try {
        const response = await catalogosAPI.get(`/catalogos/grados/por-categoria/${categoriaId}`)
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo grados por categoría'
        }
    }
}

export const createGrado = async (grado) => {
    try {
        const response = await catalogosAPI.post('/catalogos/grados', grado)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando grado',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateGrado = async (id, grado) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/grados/${id}`, grado)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando grado',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteGrado = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/grados/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando grado'
        }
    }
}

// =====================================================
// SERVICIOS ESPECIALIDADES
// =====================================================
export const getEspecialidades = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/especialidades')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo especialidades'
        }
    }
}

export const createEspecialidad = async (especialidad) => {
    try {
        const response = await catalogosAPI.post('/catalogos/especialidades', especialidad)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando especialidad',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateEspecialidad = async (id, especialidad) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/especialidades/${id}`, especialidad)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando especialidad',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteEspecialidad = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/especialidades/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando especialidad'
        }
    }
}

// =====================================================
// SERVICIOS NIVELES PRIORIDAD
// =====================================================
export const getNivelesPrioridad = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/niveles-prioridad')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo niveles de prioridad'
        }
    }
}

export const createNivelPrioridad = async (nivel) => {
    try {
        const response = await catalogosAPI.post('/catalogos/niveles-prioridad', nivel)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando nivel de prioridad',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateNivelPrioridad = async (id, nivel) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/niveles-prioridad/${id}`, nivel)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando nivel de prioridad',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteNivelPrioridad = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/niveles-prioridad/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando nivel de prioridad'
        }
    }
}

// =====================================================
// SERVICIOS TIPOS ESTADO GENERAL
// =====================================================
export const getTiposEstadoGeneral = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/tipos-estado-general')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo tipos de estado general'
        }
    }
}

export const createTipoEstadoGeneral = async (tipo) => {
    try {
        const response = await catalogosAPI.post('/catalogos/tipos-estado-general', tipo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando tipo de estado general',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateTipoEstadoGeneral = async (id, tipo) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/tipos-estado-general/${id}`, tipo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando tipo de estado general',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteTipoEstadoGeneral = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/tipos-estado-general/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando tipo de estado general'
        }
    }
}

// =====================================================
// SERVICIOS NIVELES SEGURIDAD
// =====================================================
export const getNivelesSeguridad = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/niveles-seguridad')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo niveles de seguridad'
        }
    }
}

export const createNivelSeguridad = async (nivel) => {
    try {
        const response = await catalogosAPI.post('/catalogos/niveles-seguridad', nivel)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando nivel de seguridad',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateNivelSeguridad = async (id, nivel) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/niveles-seguridad/${id}`, nivel)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando nivel de seguridad',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteNivelSeguridad = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/niveles-seguridad/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando nivel de seguridad'
        }
    }
}

// =====================================================
// SERVICIOS PAÍSES
// =====================================================
export const getPaises = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/paises')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo países'
        }
    }
}

export const createPais = async (pais) => {
    try {
        const response = await catalogosAPI.post('/catalogos/paises', pais)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando país',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updatePais = async (id, pais) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/paises/${id}`, pais)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando país',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deletePais = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/paises/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando país'
        }
    }
}

// =====================================================
// SERVICIOS TIPOS ESTRUCTURA MILITAR
// =====================================================
export const getTiposEstructuraMilitar = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/tipos-estructura-militar')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo tipos de estructura militar'
        }
    }
}

export const createTipoEstructuraMilitar = async (tipo) => {
    try {
        const response = await catalogosAPI.post('/catalogos/tipos-estructura-militar', tipo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando tipo de estructura militar',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateTipoEstructuraMilitar = async (id, tipo) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/tipos-estructura-militar/${id}`, tipo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando tipo de estructura militar',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteTipoEstructuraMilitar = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/tipos-estructura-militar/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando tipo de estructura militar'
        }
    }
}

// =====================================================
// SERVICIOS TIPOS JERARQUÍA
// =====================================================
export const getTiposJerarquia = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/tipos-jerarquia')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo tipos de jerarquía'
        }
    }
}

export const createTipoJerarquia = async (tipo) => {
    try {
        const response = await catalogosAPI.post('/catalogos/tipos-jerarquia', tipo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando tipo de jerarquía',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateTipoJerarquia = async (id, tipo) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/tipos-jerarquia/${id}`, tipo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando tipo de jerarquía',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteTipoJerarquia = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/tipos-jerarquia/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando tipo de jerarquía'
        }
    }
}

// =====================================================
// SERVICIOS TIPOS EVENTO
// =====================================================
export const getTiposEvento = async () => {
    try {
        const response = await catalogosAPI.get('/catalogos/tipos-evento')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo tipos de evento'
        }
    }
}

export const createTipoEvento = async (tipo) => {
    try {
        const response = await catalogosAPI.post('/catalogos/tipos-evento', tipo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando tipo de evento',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateTipoEvento = async (id, tipo) => {
    try {
        const response = await catalogosAPI.put(`/catalogos/tipos-evento/${id}`, tipo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando tipo de evento',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteTipoEvento = async (id) => {
    try {
        const response = await catalogosAPI.delete(`/catalogos/tipos-evento/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando tipo de evento'
        }
    }
}

// =====================================================
// MÉTODOS DE BÚSQUEDA PARA CATÁLOGOS
// =====================================================

// BÚSQUEDAS PAÍSES
export const buscarPaises = async (query) => {
    try {
        const response = await catalogosAPI.get(`/catalogos/paises/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando países:', error)
        return []
    }
}

// BÚSQUEDAS TIPOS ESTRUCTURA MILITAR
export const buscarTiposEstructuraMilitar = async (query) => {
    try {
        const response = await catalogosAPI.get(`/catalogos/tipos-estructura-militar/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando tipos estructura militar:', error)
        return []
    }
}

// BÚSQUEDAS CATEGORÍAS PERSONAL
export const buscarCategoriasPersonal = async (query) => {
    try {
        const response = await catalogosAPI.get(`/catalogos/categorias-personal/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando categorías personal:', error)
        return []
    }
}

// BÚSQUEDAS ESPECIALIDADES
export const buscarEspecialidades = async (query) => {
    try {
        const response = await catalogosAPI.get(`/catalogos/especialidades/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando especialidades:', error)
        return []
    }
}

// BÚSQUEDAS GRADOS
export const buscarGrados = async (query) => {
    try {
        const response = await catalogosAPI.get(`/catalogos/grados/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando grados:', error)
        return []
    }
}

// =====================================================
// SERVICIOS AUXILIARES
// =====================================================
export const getCurrentUser = () => {
    try {
        const userString = localStorage.getItem('fah_user')
        if (userString) {
            return JSON.parse(userString)
        }
        return null
    } catch (error) {
        return null
    }
}

export const handleApiError = (error, defaultMessage = 'Error en la operación') => {
    if (error.response) {
        return {
            message: error.response.data?.message || defaultMessage,
            errors: error.response.data?.errors || {},
            status: error.response.status
        }
    } else if (error.request) {
        return {
            message: 'Error de conexión con el servidor',
            errors: {},
            status: 0
        }
    } else {
        return {
            message: error.message || defaultMessage,
            errors: {},
            status: 0
        }
    }
}

// Exportar instancia axios por si se necesita para casos especiales
export { catalogosAPI }