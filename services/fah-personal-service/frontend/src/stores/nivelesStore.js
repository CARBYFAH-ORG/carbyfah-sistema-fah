import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

// Store básico para niveles jerárquicos
export const useNivelesStore = defineStore('niveles', () => {
    // Estado básico
    const loading = ref(false)
    const error = ref(null)
    const niveles = ref([])
    const token = ref(null)

    // Simulación de datos de niveles jerárquicos FAH
    const nivelesEstaticos = ref([
        {
            id: 1,
            codigo: 'JEMGA',
            nombre: 'Jefatura del Estado Mayor General Aéreo',
            nivel: 1,
            padre_id: null
        },
        {
            id: 2,
            codigo: 'FA-1',
            nombre: 'FA-1: Departamento de Recursos Humanos',
            nivel: 2,
            padre_id: 1
        },
        {
            id: 3,
            codigo: 'S-1',
            nombre: 'S-1: Sección de Recursos Humanos',
            nivel: 3,
            padre_id: 2
        }
    ])

    // Computados
    const nivelesActivos = computed(() => {
        return niveles.value.length > 0 ? niveles.value : nivelesEstaticos.value
    })

    // Acciones básicas
    const cargarUsuarioActual = async () => {
        try {
            loading.value = true

            // Obtener token del localStorage
            const storedToken = localStorage.getItem('fah_token')
            if (storedToken) {
                token.value = storedToken
                console.log('Token cargado desde localStorage')
            } else {
                console.log('No se encontró token en localStorage')
            }

            return token.value
        } catch (err) {
            error.value = err.message
            console.error('Error cargando usuario actual:', err)
        } finally {
            loading.value = false
        }
    }

    const cargarNiveles = async () => {
        try {
            loading.value = true

            // Por ahora usar datos estáticos
            niveles.value = nivelesEstaticos.value
            console.log('Niveles jerárquicos cargados')

        } catch (err) {
            error.value = err.message
            console.error('Error cargando niveles:', err)
        } finally {
            loading.value = false
        }
    }

    return {
        // Estado
        loading,
        error,
        niveles,
        token,
        nivelesEstaticos,

        // Computados
        nivelesActivos,

        // Acciones
        cargarUsuarioActual,
        cargarNiveles
    }
})