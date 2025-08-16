// C:\FAH\services\fah-personal-service\frontend\src\composables\usarFiltrosJerarquicos.js

import { computed, ref } from 'vue'
import { useNivelesStore } from '@/stores/nivelesStore'
import { NIVELES_ACCESO } from '@/config/nivelesJerarquicos'

export function usarFiltrosJerarquicos() {
    const nivelesStore = useNivelesStore()

    // Estado local
    const aplicandoFiltros = ref(false)
    const errorFiltros = ref(null)

    // Filtros computados según el nivel del usuario
    const filtrosPorNivel = computed(() => {
        const filtros = {}
        const usuario = nivelesStore.usuarioActual

        if (!usuario) return filtros

        switch (nivelesStore.nivelAcceso) {
            case NIVELES_ACCESO.FA1:
                // Jefe FA-1 ve TODO - sin filtros
                break

            case NIVELES_ACCESO.S1:
                // Jefe S-1 solo ve su unidad
                filtros.unidad = usuario.unidades_asignadas
                break

            case NIVELES_ACCESO.SECCION:
                // Jefe Sección solo ve su sección
                filtros.unidad = usuario.unidades_asignadas
                filtros.seccion = usuario.secciones_asignadas
                break

            case NIVELES_ACCESO.OPERADOR:
                // Operador - acceso muy limitado
                filtros.unidad = usuario.unidades_asignadas
                filtros.seccion = usuario.secciones_asignadas
                filtros.solo_lectura = true
                break
        }

        return filtros
    })

    // Función para aplicar filtros a consultas de personal
    const filtrarPersonal = (personal) => {
        if (!personal || !Array.isArray(personal)) return []

        const filtros = filtrosPorNivel.value

        return personal.filter(persona => {
            // Si es Jefe FA-1, ve todo
            if (nivelesStore.esJefeFA1) return true

            // Filtrar por unidad
            if (filtros.unidad && filtros.unidad.length > 0) {
                if (!filtros.unidad.includes(persona.unidad)) {
                    return false
                }
            }

            // Filtrar por sección
            if (filtros.seccion && filtros.seccion.length > 0) {
                if (!filtros.seccion.includes(persona.seccion)) {
                    return false
                }
            }

            return true
        })
    }

    // Función para aplicar filtros a consultas de unidades
    const filtrarUnidades = (unidades) => {
        if (!unidades || !Array.isArray(unidades)) return []

        const unidadesVisibles = nivelesStore.unidadesVisibles

        if (nivelesStore.esJefeFA1) return unidades

        return unidades.filter(unidad =>
            unidadesVisibles.includes(unidad.codigo || unidad.id)
        )
    }

    // Función para obtener parámetros de query con filtros
    const obtenerParametrosQuery = (parametrosBase = {}) => {
        const filtros = filtrosPorNivel.value
        const parametros = { ...parametrosBase }

        // Agregar filtros según nivel
        if (filtros.unidad && filtros.unidad.length > 0) {
            parametros.unidades = filtros.unidad.join(',')
        }

        if (filtros.seccion && filtros.seccion.length > 0) {
            parametros.secciones = filtros.seccion.join(',')
        }

        // Agregar metadatos de usuario para auditoría
        parametros.nivel_acceso = nivelesStore.nivelAcceso
        parametros.usuario_id = nivelesStore.usuarioActual?.id

        return parametros
    }

    // Función para verificar si puede ver/editar un registro específico
    const puedeAccederRegistro = (registro) => {
        if (!registro) return false

        // Jefe FA-1 puede ver todo
        if (nivelesStore.esJefeFA1) return true

        const filtros = filtrosPorNivel.value

        // Verificar unidad
        if (filtros.unidad && filtros.unidad.length > 0) {
            if (!filtros.unidad.includes(registro.unidad)) {
                return false
            }
        }

        // Verificar sección
        if (filtros.seccion && filtros.seccion.length > 0) {
            if (!filtros.seccion.includes(registro.seccion)) {
                return false
            }
        }

        return true
    }

    // Función para filtrar métricas/estadísticas
    const filtrarMetricas = (metricas) => {
        if (!metricas) return null

        if (nivelesStore.esJefeFA1) return metricas

        const filtros = filtrosPorNivel.value
        const metricasFiltradas = { ...metricas }

        // Para S-1: solo métricas de su unidad
        if (nivelesStore.esJefeS1 && filtros.unidad) {
            const unidadesPropias = filtros.unidad
            metricasFiltradas.por_unidad = metricasFiltradas.por_unidad?.filter(
                unidad => unidadesPropias.includes(unidad.codigo)
            )
        }

        // Para Sección: solo métricas de su sección
        if (nivelesStore.esJefeSeccion && filtros.seccion) {
            const seccionesPropias = filtros.seccion
            metricasFiltradas.por_seccion = metricasFiltradas.por_seccion?.filter(
                seccion => seccionesPropias.includes(seccion.codigo)
            )
        }

        return metricasFiltradas
    }

    // Función para obtener título contextual
    const obtenerTituloContextual = (tituloBase) => {
        const usuario = nivelesStore.usuarioActual
        if (!usuario) return tituloBase

        switch (nivelesStore.nivelAcceso) {
            case NIVELES_ACCESO.FA1:
                return `${tituloBase} - Fuerza Aérea Hondureña`

            case NIVELES_ACCESO.S1:
                return `${tituloBase} - ${usuario.unidad_principal}`

            case NIVELES_ACCESO.SECCION:
                return `${tituloBase} - ${usuario.seccion_principal || 'Mi Sección'}`

            default:
                return tituloBase
        }
    }

    // Función para obtener breadcrumbs contextuales
    const obtenerBreadcrumbsContextuales = (breadcrumbsBase = []) => {
        const usuario = nivelesStore.usuarioActual
        if (!usuario) return breadcrumbsBase

        const breadcrumbs = [
            { label: 'Inicio', to: '/dashboard' },
            ...breadcrumbsBase
        ]

        // Agregar contexto según nivel
        switch (nivelesStore.nivelAcceso) {
            case NIVELES_ACCESO.S1:
                breadcrumbs.splice(1, 0, {
                    label: usuario.unidad_principal || 'Mi Unidad',
                    to: `/unidad/${usuario.unidad_principal?.toLowerCase()}`
                })
                break

            case NIVELES_ACCESO.SECCION:
                breadcrumbs.splice(1, 0,
                    {
                        label: usuario.unidad_principal || 'Mi Unidad',
                        to: `/unidad/${usuario.unidad_principal?.toLowerCase()}`
                    },
                    {
                        label: usuario.seccion_principal || 'Mi Sección',
                        to: `/seccion/${usuario.seccion_principal?.toLowerCase()}`
                    }
                )
                break
        }

        return breadcrumbs
    }

    // Función para verificar permisos de acción
    const puedeEjecutarAccion = (accion, objeto = null) => {
        // Verificar permiso general
        if (!nivelesStore.verificarPermiso('ADMINISTRACION_PERSONAL', accion)) {
            return false
        }

        // Verificar acceso específico al objeto
        if (objeto && !puedeAccederRegistro(objeto)) {
            return false
        }

        return true
    }

    // Función de utilidad para logging con contexto
    const logConContexto = (mensaje, datos = {}) => {
        const contexto = {
            usuario: nivelesStore.usuarioActual?.nombre,
            nivel: nivelesStore.nivelAcceso,
            unidad: nivelesStore.usuarioActual?.unidad_principal,
            timestamp: new Date().toISOString(),
            ...datos
        }

        console.log(`[${nivelesStore.nivelAcceso}] ${mensaje}`, contexto)
    }

    return {
        // Estado
        aplicandoFiltros,
        errorFiltros,

        // Computadas
        filtrosPorNivel,

        // Funciones de filtrado
        filtrarPersonal,
        filtrarUnidades,
        filtrarMetricas,

        // Funciones de permisos
        puedeAccederRegistro,
        puedeEjecutarAccion,

        // Funciones de query
        obtenerParametrosQuery,

        // Funciones de UI
        obtenerTituloContextual,
        obtenerBreadcrumbsContextuales,

        // Utilidades
        logConContexto
    }
}