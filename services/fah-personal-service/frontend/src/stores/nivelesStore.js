// C:\FAH\services\fah-personal-service\frontend\src\stores\nivelesStore.js

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
    NIVELES_ACCESO,
    CONFIGURACION_NIVELES,
    obtenerConfiguracionNivel,
    tienePermiso,
    obtenerUnidadesVisibles,
    obtenerSeccionesVisibles
} from '@/config/nivelesJerarquicos'

export const useNivelesStore = defineStore('niveles', () => {
    // Estado reactivo
    const usuarioActual = ref(null)
    const configuracionNivel = ref(null)
    const filtrosActivos = ref({})
    const cargandoNiveles = ref(false)
    const errorNiveles = ref(null)

    // Computadas para nivel actual
    const nivelAcceso = computed(() => {
        return usuarioActual.value?.nivel_acceso || NIVELES_ACCESO.OPERADOR
    })

    const esJefeFA1 = computed(() => {
        return nivelAcceso.value === NIVELES_ACCESO.FA1
    })

    const esJefeS1 = computed(() => {
        return nivelAcceso.value === NIVELES_ACCESO.S1
    })

    const esJefeSeccion = computed(() => {
        return nivelAcceso.value === NIVELES_ACCESO.SECCION
    })

    const esOperador = computed(() => {
        return nivelAcceso.value === NIVELES_ACCESO.OPERADOR
    })

    // Computadas para datos visibles
    const unidadesVisibles = computed(() => {
        if (!usuarioActual.value) return []
        return obtenerUnidadesVisibles(usuarioActual.value)
    })

    const seccionesVisibles = computed(() => {
        if (!usuarioActual.value) return []
        return obtenerSeccionesVisibles(usuarioActual.value)
    })

    const puedeVerOtrasUnidades = computed(() => {
        return configuracionNivel.value?.puede_ver_otras_unidades || false
    })

    const puedeTomarDecisionesEstrategicas = computed(() => {
        return configuracionNivel.value?.puede_tomar_decisiones_estrategicas || false
    })

    const nivelDatosSensibles = computed(() => {
        return configuracionNivel.value?.nivel_datos_sensibles || 'MINIMO'
    })

    // Computadas para título y descripción adaptativa
    const tituloNivel = computed(() => {
        if (!usuarioActual.value) return 'Sistema Personal FAH'

        switch (nivelAcceso.value) {
            case NIVELES_ACCESO.FA1:
                return 'FA-1: Recursos Humanos Fuerza Aérea Hondureña'
            case NIVELES_ACCESO.S1:
                const unidad = usuarioActual.value.unidad_principal || 'Unidad'
                return `S-1: Recursos Humanos ${unidad}`
            case NIVELES_ACCESO.SECCION:
                const seccion = usuarioActual.value.seccion_principal || 'Sección'
                return `Jefe ${seccion}`
            default:
                return 'Sistema Personal FAH'
        }
    })

    const descripcionAlcance = computed(() => {
        if (!usuarioActual.value) return ''

        switch (nivelAcceso.value) {
            case NIVELES_ACCESO.FA1:
                return '3,644 efectivos - Todas las unidades FAH'
            case NIVELES_ACCESO.S1:
                const unidad = usuarioActual.value.unidad_principal || 'Unidad'
                const totalUnidad = usuarioActual.value.total_personal_unidad || 0
                return `${totalUnidad} efectivos - ${unidad}`
            case NIVELES_ACCESO.SECCION:
                const seccion = usuarioActual.value.seccion_principal || 'Sección'
                const totalSeccion = usuarioActual.value.total_personal_seccion || 0
                return `${totalSeccion} efectivos - ${seccion}`
            default:
                return 'Acceso limitado'
        }
    })

    // Acciones
    const cargarUsuarioActual = async () => {
        try {
            cargandoNiveles.value = true
            errorNiveles.value = null

            // Obtener datos del usuario desde el token o API
            const token = localStorage.getItem('fah_token')
            if (!token) {
                throw new Error('Token no encontrado')
            }

            // Simulación de carga de usuario - reemplazar por API real
            const datosUsuario = await obtenerDatosUsuarioDesdeAPI(token)

            usuarioActual.value = datosUsuario
            configuracionNivel.value = obtenerConfiguracionNivel(datosUsuario.nivel_acceso)

            // Configurar filtros automáticos
            configurarFiltrosAutomaticos()

            console.log('✅ Usuario cargado:', {
                nivel: nivelAcceso.value,
                unidades: unidadesVisibles.value,
                alcance: descripcionAlcance.value
            })

        } catch (error) {
            console.error('❌ Error cargando usuario:', error)
            errorNiveles.value = error.message

            // Usuario por defecto en caso de error
            usuarioActual.value = {
                id: 0,
                nombre: 'Usuario Invitado',
                nivel_acceso: NIVELES_ACCESO.OPERADOR,
                unidad_principal: null,
                unidades_asignadas: [],
                secciones_asignadas: []
            }
            configuracionNivel.value = obtenerConfiguracionNivel(NIVELES_ACCESO.OPERADOR)
        } finally {
            cargandoNiveles.value = false
        }
    }

    const configurarFiltrosAutomaticos = () => {
        if (!usuarioActual.value) return

        const filtros = {}

        switch (nivelAcceso.value) {
            case NIVELES_ACCESO.FA1:
                // Sin filtros - ve todo
                filtros.unidades = null
                filtros.secciones = null
                filtros.personal = null
                break

            case NIVELES_ACCESO.S1:
                // Solo su unidad
                filtros.unidades = usuarioActual.value.unidades_asignadas
                filtros.secciones = null // Ve todas las secciones de su unidad
                filtros.personal = 'UNIDAD_PROPIA'
                break

            case NIVELES_ACCESO.SECCION:
                // Solo su sección
                filtros.unidades = usuarioActual.value.unidades_asignadas
                filtros.secciones = usuarioActual.value.secciones_asignadas
                filtros.personal = 'SECCION_PROPIA'
                break

            case NIVELES_ACCESO.OPERADOR:
                // Acceso muy limitado
                filtros.unidades = usuarioActual.value.unidades_asignadas
                filtros.secciones = usuarioActual.value.secciones_asignadas
                filtros.personal = 'LIMITADO'
                break
        }

        filtrosActivos.value = filtros

        console.log('🔍 Filtros configurados:', filtros)
    }

    // Función para verificar permisos específicos
    const verificarPermiso = (modulo, accion) => {
        if (!usuarioActual.value) return false
        return tienePermiso(nivelAcceso.value, modulo, accion)
    }

    // Función para obtener filtros SQL/Query
    const obtenerFiltrosQuery = () => {
        const filtros = { ...filtrosActivos.value }

        // Agregar ID de usuario para auditoría
        filtros.usuario_consulta = usuarioActual.value?.id
        filtros.nivel_acceso = nivelAcceso.value
        filtros.timestamp = new Date().toISOString()

        return filtros
    }

    // Función para cambiar contexto (útil para testing o demostración)
    const cambiarContextoUsuario = (nuevoUsuario) => {
        usuarioActual.value = nuevoUsuario
        configuracionNivel.value = obtenerConfiguracionNivel(nuevoUsuario.nivel_acceso)
        configurarFiltrosAutomaticos()

        console.log('🔄 Contexto cambiado a:', nuevoUsuario.nivel_acceso)
    }

    // Función simulada para obtener datos del usuario desde API
    const obtenerDatosUsuarioDesdeAPI = async (token) => {
        // Simulación - reemplazar por llamada real a la API
        await new Promise(resolve => setTimeout(resolve, 500))

        // Ejemplos de usuarios para testing
        const usuariosEjemplo = {
            'token_jefe_fa1': {
                id: 1,
                nombre: 'General Juan Carlos Mendoza',
                grado: 'General de Brigada',
                cargo: 'Jefe FA-1',
                nivel_acceso: NIVELES_ACCESO.FA1,
                unidad_principal: 'JEMGA',
                unidades_asignadas: ['HAM', 'HCM', 'AEE', 'JESC', 'AMA'],
                secciones_asignadas: [],
                total_personal_unidad: 3644,
                email: 'jefe.fa1@fah.mil.hn',
                telefono: '+504 2234-5678'
            },
            'token_jefe_s1_ham': {
                id: 2,
                nombre: 'Mayor José Antonio Rodríguez',
                grado: 'Mayor',
                cargo: 'Jefe S-1 HAM',
                nivel_acceso: NIVELES_ACCESO.S1,
                unidad_principal: 'HAM',
                unidades_asignadas: ['HAM'],
                secciones_asignadas: [],
                total_personal_unidad: 406,
                total_personal_seccion: 0,
                email: 's1.ham@fah.mil.hn',
                telefono: '+504 2234-1234'
            },
            'token_jefe_mantenimiento': {
                id: 3,
                nombre: 'Capitán Carlos Alberto Sánchez',
                grado: 'Capitán',
                cargo: 'Jefe Mantenimiento HAM',
                nivel_acceso: NIVELES_ACCESO.SECCION,
                unidad_principal: 'HAM',
                unidades_asignadas: ['HAM'],
                secciones_asignadas: ['MANTENIMIENTO'],
                total_personal_unidad: 406,
                total_personal_seccion: 45,
                seccion_principal: 'Mantenimiento',
                email: 'mant.ham@fah.mil.hn',
                telefono: '+504 2234-5555'
            }
        }

        // En desarrollo, usar token específico
        // En producción, decodificar JWT real
        return usuariosEjemplo[token] || usuariosEjemplo['token_jefe_s1_ham']
    }

    return {
        // Estado
        usuarioActual,
        configuracionNivel,
        filtrosActivos,
        cargandoNiveles,
        errorNiveles,

        // Computadas
        nivelAcceso,
        esJefeFA1,
        esJefeS1,
        esJefeSeccion,
        esOperador,
        unidadesVisibles,
        seccionesVisibles,
        puedeVerOtrasUnidades,
        puedeTomarDecisionesEstrategicas,
        nivelDatosSensibles,
        tituloNivel,
        descripcionAlcance,

        // Acciones
        cargarUsuarioActual,
        verificarPermiso,
        obtenerFiltrosQuery,
        cambiarContextoUsuario
    }
})