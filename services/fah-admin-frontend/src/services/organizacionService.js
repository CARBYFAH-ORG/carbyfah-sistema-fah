import axios from 'axios'

// Configuración base axios para organización
const API_BASE_URL = process.env.NODE_ENV === 'development'
    ? '/api'
    : 'http://localhost:8010/api'  // ← Puerto del fah-organizacion-service

// Crear instancia axios específica para organización
const organizacionAPI = axios.create({
    baseURL: API_BASE_URL,
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

// Interceptor para logging y debug
organizacionAPI.interceptors.request.use(
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
organizacionAPI.interceptors.response.use(
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
        const response = await organizacionAPI.get('/organizacion/health')
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
        const response = await organizacionAPI.get('/ping')
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

// =====================================================
// SERVICIOS DEPARTAMENTOS
// =====================================================
export const getDepartamentos = async () => {
    try {
        const response = await organizacionAPI.get('/organizacion/departamentos')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo departamentos'
        }
    }
}

export const createDepartamento = async (departamento) => {
    try {
        const response = await organizacionAPI.post('/organizacion/departamentos', departamento)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando departamento',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateDepartamento = async (id, departamento) => {
    try {
        const response = await organizacionAPI.put(`/organizacion/departamentos/${id}`, departamento)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando departamento',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteDepartamento = async (id) => {
    try {
        const response = await organizacionAPI.delete(`/organizacion/departamentos/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando departamento'
        }
    }
}

// =====================================================
// SERVICIOS MUNICIPIOS
// =====================================================
export const getMunicipios = async () => {
    try {
        const response = await organizacionAPI.get('/organizacion/municipios')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo municipios'
        }
    }
}

export const createMunicipio = async (municipio) => {
    try {
        const response = await organizacionAPI.post('/organizacion/municipios', municipio)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando municipio',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateMunicipio = async (id, municipio) => {
    try {
        const response = await organizacionAPI.put(`/organizacion/municipios/${id}`, municipio)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando municipio',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteMunicipio = async (id) => {
    try {
        const response = await organizacionAPI.delete(`/organizacion/municipios/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando municipio'
        }
    }
}

// =====================================================
// SERVICIOS CIUDADES
// =====================================================
export const getCiudades = async () => {
    try {
        const response = await organizacionAPI.get('/organizacion/ciudades')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo ciudades'
        }
    }
}

export const createCiudad = async (ciudad) => {
    try {
        const response = await organizacionAPI.post('/organizacion/ciudades', ciudad)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando ciudad',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateCiudad = async (id, ciudad) => {
    try {
        const response = await organizacionAPI.put(`/organizacion/ciudades/${id}`, ciudad)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando ciudad',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteCiudad = async (id) => {
    try {
        const response = await organizacionAPI.delete(`/organizacion/ciudades/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando ciudad'
        }
    }
}

// =====================================================
// SERVICIOS UBICACIONES GEOGRÁFICAS
// =====================================================
export const getUbicacionesGeograficas = async () => {
    try {
        const response = await organizacionAPI.get('/organizacion/ubicaciones-geograficas')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo ubicaciones geográficas'
        }
    }
}

export const createUbicacionGeografica = async (ubicacion) => {
    try {
        const response = await organizacionAPI.post('/organizacion/ubicaciones-geograficas', ubicacion)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando ubicación geográfica',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateUbicacionGeografica = async (id, ubicacion) => {
    try {
        const response = await organizacionAPI.put(`/organizacion/ubicaciones-geograficas/${id}`, ubicacion)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando ubicación geográfica',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteUbicacionGeografica = async (id) => {
    try {
        const response = await organizacionAPI.delete(`/organizacion/ubicaciones-geograficas/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando ubicación geográfica'
        }
    }
}

// =====================================================
// SERVICIOS ESTRUCTURA MILITAR
// =====================================================
export const getEstructuraMilitar = async () => {
    try {
        const response = await organizacionAPI.get('/organizacion/estructura-militar')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo estructura militar'
        }
    }
}

export const createEstructuraMilitar = async (estructura) => {
    try {
        const response = await organizacionAPI.post('/organizacion/estructura-militar', estructura)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando estructura militar',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateEstructuraMilitar = async (id, estructura) => {
    try {
        const response = await organizacionAPI.put(`/organizacion/estructura-militar/${id}`, estructura)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando estructura militar',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteEstructuraMilitar = async (id) => {
    try {
        const response = await organizacionAPI.delete(`/organizacion/estructura-militar/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando estructura militar'
        }
    }
}

// =====================================================
// SERVICIOS CARGOS
// =====================================================
export const getCargos = async () => {
    try {
        const response = await organizacionAPI.get('/organizacion/cargos')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo cargos'
        }
    }
}

export const createCargo = async (cargo) => {
    try {
        const response = await organizacionAPI.post('/organizacion/cargos', cargo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando cargo',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateCargo = async (id, cargo) => {
    try {
        const response = await organizacionAPI.put(`/organizacion/cargos/${id}`, cargo)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando cargo',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteCargo = async (id) => {
    try {
        const response = await organizacionAPI.delete(`/organizacion/cargos/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando cargo'
        }
    }
}

// =====================================================
// SERVICIOS ROLES FUNCIONALES
// =====================================================
export const getRolesFuncionales = async () => {
    try {
        const response = await organizacionAPI.get('/organizacion/roles-funcionales')
        return {
            success: true,
            data: response.data.data
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error obteniendo roles funcionales'
        }
    }
}

export const createRolFuncional = async (rol) => {
    try {
        const response = await organizacionAPI.post('/organizacion/roles-funcionales', rol)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error creando rol funcional',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const updateRolFuncional = async (id, rol) => {
    try {
        const response = await organizacionAPI.put(`/organizacion/roles-funcionales/${id}`, rol)
        return {
            success: true,
            data: response.data.data,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error actualizando rol funcional',
            errors: error.response?.data?.errors || {}
        }
    }
}

export const deleteRolFuncional = async (id) => {
    try {
        const response = await organizacionAPI.delete(`/organizacion/roles-funcionales/${id}`)
        return {
            success: true,
            message: response.data.message
        }
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.message || 'Error eliminando rol funcional'
        }
    }
}

// =====================================================
// SERVICIOS AUXILIARES (COPIADOS DE CATÁLOGOS)
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

// =====================================================
// 🔍 MÉTODOS DE BÚSQUEDA PARA RELACIONES
// Agregar estos métodos al final de organizacionService.js
// =====================================================

// =====================================================
// BÚSQUEDAS DEPARTAMENTOS
// =====================================================
export const buscarDepartamentos = async (query) => {
    try {
        const response = await organizacionAPI.get(`/organizacion/departamentos/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando departamentos:', error)
        return []
    }
}

// =====================================================
// BÚSQUEDAS MUNICIPIOS
// =====================================================
export const buscarMunicipios = async (query) => {
    try {
        const response = await organizacionAPI.get(`/organizacion/municipios/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando municipios:', error)
        return []
    }
}

// =====================================================
// BÚSQUEDAS CIUDADES
// =====================================================
export const buscarCiudades = async (query) => {
    try {
        const response = await organizacionAPI.get(`/organizacion/ciudades/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando ciudades:', error)
        return []
    }
}

// =====================================================
// BÚSQUEDAS UBICACIONES GEOGRÁFICAS
// =====================================================
export const buscarUbicacionesGeograficas = async (query) => {
    try {
        const response = await organizacionAPI.get(`/organizacion/ubicaciones-geograficas/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando ubicaciones geográficas:', error)
        return []
    }
}

// =====================================================
// BÚSQUEDAS ESTRUCTURA MILITAR
// =====================================================
export const buscarEstructuraMilitar = async (query) => {
    try {
        const response = await organizacionAPI.get(`/organizacion/estructura-militar/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando estructura militar:', error)
        return []
    }
}

// =====================================================
// BÚSQUEDAS CARGOS
// =====================================================
export const buscarCargos = async (query) => {
    try {
        const response = await organizacionAPI.get(`/organizacion/cargos/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando cargos:', error)
        return []
    }
}

// =====================================================
// BÚSQUEDAS ROLES FUNCIONALES
// =====================================================
export const buscarRolesFuncionales = async (query) => {
    try {
        const response = await organizacionAPI.get(`/organizacion/roles-funcionales/buscar?q=${encodeURIComponent(query)}`)
        return response.data.data || []
    } catch (error) {
        console.error('Error buscando roles funcionales:', error)
        return []
    }
}

// Exportar instancia axios por si se necesita para casos especiales
export { organizacionAPI }