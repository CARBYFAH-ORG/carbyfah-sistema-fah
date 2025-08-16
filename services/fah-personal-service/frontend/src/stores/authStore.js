import { defineStore } from 'pinia'
import { ref, computed, readonly } from 'vue'

// Store de autenticación que usa el microservicio fah-auth-service
export const useAuthStore = defineStore('auth', () => {
    // Estado reactivo
    const user = ref(null)
    const token = ref(null)
    const isLoading = ref(false)
    const error = ref(null)
    const isInitialized = ref(false)

    // Estados computados
    const isAuthenticated = computed(() => {
        return !!(user.value && token.value)
    })

    const userRole = computed(() => {
        return user.value?.rol_funcional || null
    })

    const userPermissions = computed(() => {
        return user.value?.permissions || []
    })

    const nivelAutoridad = computed(() => {
        return user.value?.rol_funcional?.nivel_autoridad || 0
    })

    // Acciones
    const inicializarAutenticacion = async () => {
        if (isInitialized.value) return

        try {
            isLoading.value = true

            // Intentar recuperar datos del localStorage
            const storedToken = localStorage.getItem('fah_token')
            const storedUser = localStorage.getItem('fah_user')

            if (storedToken && storedUser) {
                token.value = storedToken
                user.value = JSON.parse(storedUser)

                // Verificar si el token sigue siendo válido con fah-auth-service
                const isValid = await verificarToken()
                if (!isValid) {
                    await logout()
                }
            }
        } catch (error) {
            console.error('Error al inicializar autenticación:', error)
            await logout()
        } finally {
            isLoading.value = false
            isInitialized.value = true
        }
    }

    const login = async (credentials) => {
        try {
            isLoading.value = true
            error.value = null

            // Login contra fah-auth-service
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(credentials)
            })

            const data = await response.json()

            if (!response.ok) {
                throw new Error(data.message || 'Error en el login')
            }

            if (data.success) {
                // Extraer datos del usuario y token
                token.value = data.data.token
                user.value = data.data.usuario

                // Guardar en localStorage
                localStorage.setItem('fah_token', token.value)
                localStorage.setItem('fah_user', JSON.stringify(user.value))

                return { success: true, user: user.value }
            } else {
                throw new Error(data.message || 'Login fallido')
            }
        } catch (err) {
            error.value = err.message || 'Error de conexión'
            return { success: false, error: error.value }
        } finally {
            isLoading.value = false
        }
    }

    const logout = async () => {
        try {
            // Intentar logout en el servidor si hay token
            if (token.value) {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token.value}`
                    }
                })
            }
        } catch (error) {
            console.error('Error al hacer logout en servidor:', error)
        } finally {
            // Limpiar estado local siempre
            user.value = null
            token.value = null
            error.value = null

            // Limpiar localStorage
            localStorage.removeItem('fah_token')
            localStorage.removeItem('fah_user')
        }
    }

    const verificarToken = async () => {
        if (!token.value) return false

        try {
            const response = await fetch('/api/me', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token.value}`
                }
            })

            if (response.ok) {
                const data = await response.json()
                if (data.success) {
                    // Actualizar datos del usuario si es necesario
                    user.value = { ...user.value, ...data.data.usuario }
                    return true
                }
            }

            return false
        } catch (error) {
            console.error('Error al verificar token:', error)
            return false
        }
    }

    const obtenerDatosUsuario = async () => {
        if (!token.value) return null

        try {
            const response = await fetch('/api/me', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token.value}`
                }
            })

            const data = await response.json()

            if (data.success) {
                user.value = { ...user.value, ...data.data.usuario }
                localStorage.setItem('fah_user', JSON.stringify(user.value))
                return user.value
            }
        } catch (error) {
            console.error('Error al obtener datos del usuario:', error)
        }

        return null
    }

    const verificarPermisos = (permisoRequerido) => {
        if (!user.value || !userPermissions.value) return false
        return userPermissions.value.includes(permisoRequerido)
    }

    const verificarNivelAutoridad = (nivelMinimo) => {
        return nivelAutoridad.value >= nivelMinimo
    }

    const esJefeFA1 = computed(() => {
        return userRole.value?.codigo_rol?.includes('JEFE-FA-1') || false
    })

    const esEncargadoS1 = computed(() => {
        return userRole.value?.codigo_rol?.includes('ENC-S-1') || false
    })

    const esComandanteBase = computed(() => {
        return userRole.value?.codigo_rol?.includes('CMDTE-BASE') || false
    })

    const codigoBase = computed(() => {
        if (!userRole.value?.codigo_rol) return null

        const rol = userRole.value.codigo_rol.toUpperCase()
        const partes = rol.split('-')
        return partes[partes.length - 1] || null
    })

    // Función para obtener configuración de acceso según rol
    const obtenerConfiguracionAcceso = () => {
        if (!userRole.value) return { acceso: 'limitado', scope: 'ninguno' }

        const rol = userRole.value.codigo_rol.toUpperCase()

        if (rol.includes('JEFE-FA-1')) {
            return {
                acceso: 'completo',
                scope: 'fah_completa',
                descripcion: 'Acceso completo a toda la FAH'
            }
        } else if (rol.includes('CMDTE-BASE')) {
            return {
                acceso: 'base_completa',
                scope: codigoBase.value,
                descripcion: `Acceso completo a Base ${codigoBase.value}`
            }
        } else if (rol.includes('ENC-S-1')) {
            return {
                acceso: 'seccion_s1',
                scope: codigoBase.value,
                descripcion: `Acceso a S-1 de Base ${codigoBase.value}`
            }
        } else if (rol.includes('BIENESTAR')) {
            return {
                acceso: 'area_especifica',
                scope: 'bienestar',
                descripcion: 'Acceso a área de Bienestar de Personal'
            }
        } else if (rol.includes('ADMIN-PERSONAL')) {
            return {
                acceso: 'area_especifica',
                scope: 'administracion',
                descripcion: 'Acceso a Administración de Personal'
            }
        } else if (rol.includes('DISCIPLINA')) {
            return {
                acceso: 'area_especifica',
                scope: 'disciplina',
                descripcion: 'Acceso a Disciplina y Orden'
            }
        } else {
            return {
                acceso: 'limitado',
                scope: 'consulta',
                descripcion: 'Acceso de consulta limitado'
            }
        }
    }

    // Retornar estado y acciones
    return {
        // Estado
        user: readonly(user),
        token: readonly(token),
        isLoading: readonly(isLoading),
        error: readonly(error),
        isInitialized: readonly(isInitialized),

        // Computados
        isAuthenticated,
        userRole,
        userPermissions,
        nivelAutoridad,
        esJefeFA1,
        esEncargadoS1,
        esComandanteBase,
        codigoBase,

        // Acciones
        inicializarAutenticacion,
        login,
        logout,
        verificarToken,
        obtenerDatosUsuario,
        verificarPermisos,
        verificarNivelAutoridad,
        obtenerConfiguracionAcceso
    }
}, {
    persist: false // No persistir con plugin, manejamos manualmente
})