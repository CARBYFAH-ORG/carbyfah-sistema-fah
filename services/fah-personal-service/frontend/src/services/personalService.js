import axios from 'axios'

// Configuración de la instancia axios para el servicio personal
const personalAPI = axios.create({
    baseURL: 'http://localhost:8009/api',
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
})

// Interceptor para agregar token de autenticación
personalAPI.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('fah_personal_token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        console.log(`🔗 API Request: ${config.method?.toUpperCase()} ${config.url}`)
        return config
    },
    (error) => {
        console.error('❌ Error en request interceptor:', error)
        return Promise.reject(error)
    }
)

// Interceptor para manejo de respuestas
personalAPI.interceptors.response.use(
    (response) => {
        console.log(`✅ API Response: ${response.status} ${response.config.url}`)
        return response
    },
    (error) => {
        console.error(`❌ API Error: ${error.response?.status} ${error.config?.url}`, error)

        if (error.response?.status === 401) {
            localStorage.removeItem('fah_personal_token')
            localStorage.removeItem('fah_personal_user')
            window.location.href = '/login'
        }

        return Promise.reject(error)
    }
)

// =====================================================
// SERVICIOS DE SALUD Y INFORMACIÓN
// =====================================================

export const verificarSalud = async () => {
    try {
        const response = await personalAPI.get('/health')
        return response.data
    } catch (error) {
        console.error('Error verificando salud del servicio:', error)
        throw error
    }
}

export const obtenerInfoServicio = async () => {
    try {
        const response = await personalAPI.get('/info')
        return response.data
    } catch (error) {
        console.error('Error obteniendo información del servicio:', error)
        throw error
    }
}

// =====================================================
// SERVICIOS DE DATOS PERSONALES
// =====================================================

export const obtenerDatosPersonales = async (params = {}) => {
    try {
        const response = await personalAPI.get('/personal/datos-personales', { params })
        return response.data
    } catch (error) {
        console.error('Error obteniendo datos personales:', error)
        throw error
    }
}

export const crearDatosPersonales = async (datos) => {
    try {
        const response = await personalAPI.post('/personal/datos-personales', datos)
        return response.data
    } catch (error) {
        console.error('Error creando datos personales:', error)
        throw error
    }
}

export const actualizarDatosPersonales = async (id, datos) => {
    try {
        const response = await personalAPI.put(`/personal/datos-personales/${id}`, datos)
        return response.data
    } catch (error) {
        console.error('Error actualizando datos personales:', error)
        throw error
    }
}

export const eliminarDatosPersonales = async (id) => {
    try {
        const response = await personalAPI.delete(`/personal/datos-personales/${id}`)
        return response.data
    } catch (error) {
        console.error('Error eliminando datos personales:', error)
        throw error
    }
}

export const obtenerPersonaPorIdentidad = async (numeroIdentidad) => {
    try {
        const response = await personalAPI.get(`/personal/datos-personales/por-identidad/${numeroIdentidad}`)
        return response.data
    } catch (error) {
        console.error('Error obteniendo persona por identidad:', error)
        throw error
    }
}

export const obtenerEstadisticasPersonales = async () => {
    try {
        const response = await personalAPI.get('/personal/datos-personales/estadisticas/generales')
        return response.data
    } catch (error) {
        console.error('Error obteniendo estadísticas personales:', error)
        throw error
    }
}

// =====================================================
// SERVICIOS DE PERFILES MILITARES
// =====================================================

export const obtenerPerfilesMilitares = async (params = {}) => {
    try {
        const response = await personalAPI.get('/personal/perfiles-militares', { params })
        return response.data
    } catch (error) {
        console.error('Error obteniendo perfiles militares:', error)
        throw error
    }
}

export const crearPerfilMilitar = async (datos) => {
    try {
        const response = await personalAPI.post('/personal/perfiles-militares', datos)
        return response.data
    } catch (error) {
        console.error('Error creando perfil militar:', error)
        throw error
    }
}

export const actualizarPerfilMilitar = async (id, datos) => {
    try {
        const response = await personalAPI.put(`/personal/perfiles-militares/${id}`, datos)
        return response.data
    } catch (error) {
        console.error('Error actualizando perfil militar:', error)
        throw error
    }
}

export const eliminarPerfilMilitar = async (id) => {
    try {
        const response = await personalAPI.delete(`/personal/perfiles-militares/${id}`)
        return response.data
    } catch (error) {
        console.error('Error eliminando perfil militar:', error)
        throw error
    }
}

// =====================================================
// SERVICIOS DE ASIGNACIONES ACTUALES
// =====================================================

export const obtenerAsignacionesActuales = async (params = {}) => {
    try {
        const response = await personalAPI.get('/personal/asignaciones-actuales', { params })
        return response.data
    } catch (error) {
        console.error('Error obteniendo asignaciones actuales:', error)
        throw error
    }
}

export const crearAsignacionActual = async (datos) => {
    try {
        const response = await personalAPI.post('/personal/asignaciones-actuales', datos)
        return response.data
    } catch (error) {
        console.error('Error creando asignación actual:', error)
        throw error
    }
}

export const actualizarAsignacionActual = async (id, datos) => {
    try {
        const response = await personalAPI.put(`/personal/asignaciones-actuales/${id}`, datos)
        return response.data
    } catch (error) {
        console.error('Error actualizando asignación actual:', error)
        throw error
    }
}

export const eliminarAsignacionActual = async (id) => {
    try {
        const response = await personalAPI.delete(`/personal/asignaciones-actuales/${id}`)
        return response.data
    } catch (error) {
        console.error('Error eliminando asignación actual:', error)
        throw error
    }
}

// =====================================================
// SERVICIOS DE USUARIOS DEL SISTEMA
// =====================================================

export const obtenerUsuariosSistema = async (params = {}) => {
    try {
        const response = await personalAPI.get('/personal/usuarios-sistema', { params })
        return response.data
    } catch (error) {
        console.error('Error obteniendo usuarios del sistema:', error)
        throw error
    }
}

export const crearUsuarioSistema = async (datos) => {
    try {
        const response = await personalAPI.post('/personal/usuarios-sistema', datos)
        return response.data
    } catch (error) {
        console.error('Error creando usuario del sistema:', error)
        throw error
    }
}

export const actualizarUsuarioSistema = async (id, datos) => {
    try {
        const response = await personalAPI.put(`/personal/usuarios-sistema/${id}`, datos)
        return response.data
    } catch (error) {
        console.error('Error actualizando usuario del sistema:', error)
        throw error
    }
}

export const eliminarUsuarioSistema = async (id) => {
    try {
        const response = await personalAPI.delete(`/personal/usuarios-sistema/${id}`)
        return response.data
    } catch (error) {
        console.error('Error eliminando usuario del sistema:', error)
        throw error
    }
}

// =====================================================
// SERVICIOS DE ORGANIGRAMAS (EXISTENTE)
// =====================================================

export const obtenerEstructuraFAH = async () => {
    try {
        const response = await personalAPI.get('/organigramas/estructura-fah')
        return response.data
    } catch (error) {
        console.error('Error obteniendo estructura FAH:', error)
        throw error
    }
}

export const obtenerOrganigramaUnidad = async (unidadId) => {
    try {
        const response = await personalAPI.get(`/organigramas/unidad/${unidadId}`)
        return response.data
    } catch (error) {
        console.error('Error obteniendo organigrama de unidad:', error)
        throw error
    }
}

export const exportarOrganigrama = async (formato = 'pdf') => {
    try {
        const response = await personalAPI.get('/organigramas/exportar', {
            params: { formato },
            responseType: 'blob'
        })
        return response.data
    } catch (error) {
        console.error('Error exportando organigrama:', error)
        throw error
    }
}

// =====================================================
// SERVICIOS AUXILIARES
// =====================================================

export const getCurrentUser = () => {
    try {
        const userString = localStorage.getItem('fah_personal_user')
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
export { personalAPI }