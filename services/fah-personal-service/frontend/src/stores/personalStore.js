import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

// Store básico para personal - versión inicial
export const usePersonalStore = defineStore('personal', () => {
    // Estado básico
    const loading = ref(false)
    const error = ref(null)
    const personal = ref([])
    const estadisticas = ref({
        total: 3644,
        ocupacion: 87,
        vacantes: 518,
        deficit: 13,
        alertas: 12
    })

    // Datos de configuración por rol
    const configuracionRol = ref(null)
    const dashboardData = ref(null)

    // Computados básicos
    const totalPersonal = computed(() => estadisticas.value.total)
    const personalOperativo = computed(() => Math.round(estadisticas.value.total * 0.92))

    // Acciones básicas
    const cargarConfiguracionRol = async (rolFuncional) => {
        try {
            loading.value = true

            // Simulación de carga - reemplazar con API real
            configuracionRol.value = {
                codigo_rol: rolFuncional?.codigo_rol || 'GENERAL',
                nombre_rol: rolFuncional?.nombre_rol || 'Usuario General',
                nivel_autoridad: rolFuncional?.nivel_autoridad || 1
            }

            console.log('Configuración del rol cargada:', configuracionRol.value)
        } catch (err) {
            error.value = err.message
            console.error('Error cargando configuración del rol:', err)
        } finally {
            loading.value = false
        }
    }

    const cargarDashboardJefeFA1 = async () => {
        console.log('Cargando dashboard para Jefe FA-1')
        dashboardData.value = { tipo: 'jefe_fa1' }
    }

    const cargarDashboardEncS1 = async (codigoBase) => {
        console.log('Cargando dashboard para Enc S-1 de base:', codigoBase)
        dashboardData.value = { tipo: 'enc_s1', base: codigoBase }
    }

    const cargarDashboardComandanteBase = async (baseComandante) => {
        console.log('Cargando dashboard para Comandante de base:', baseComandante)
        dashboardData.value = { tipo: 'comandante_base', base: baseComandante }
    }

    const cargarDashboardAreaEspecifica = async (rolFuncional) => {
        console.log('Cargando dashboard para área específica:', rolFuncional)
        dashboardData.value = { tipo: 'area_especifica', area: rolFuncional }
    }

    const cargarDashboardGeneral = async () => {
        console.log('Cargando dashboard general')
        dashboardData.value = { tipo: 'general' }
    }

    const actualizarEstadoPersonalEnDashboard = (personal, estadoNuevo) => {
        console.log('Actualizando estado en dashboard:', personal, estadoNuevo)
    }

    const actualizarUbicacionPersonal = (personalId, ubicacion) => {
        console.log('Actualizando ubicación de personal:', personalId, ubicacion)
    }

    const cargarEstadisticas = async () => {
        try {
            loading.value = true

            // Simular carga de estadísticas - reemplazar con API real
            estadisticas.value = {
                total: 3644,
                ocupacion: 87,
                vacantes: 518,
                deficit: 13,
                alertas: 12,
                operativo: Math.round(3644 * 0.92)
            }

            console.log('Estadísticas cargadas:', estadisticas.value)
        } catch (err) {
            error.value = err.message
            console.error('Error cargando estadísticas:', err)
        } finally {
            loading.value = false
        }
    }

    return {
        // Estado
        loading,
        error,
        personal,
        estadisticas,
        configuracionRol,
        dashboardData,

        // Computados
        totalPersonal,
        personalOperativo,

        // Acciones
        cargarConfiguracionRol,
        cargarDashboardJefeFA1,
        cargarDashboardEncS1,
        cargarDashboardComandanteBase,
        cargarDashboardAreaEspecifica,
        cargarDashboardGeneral,
        actualizarEstadoPersonalEnDashboard,
        actualizarUbicacionPersonal,
        cargarEstadisticas
    }
})