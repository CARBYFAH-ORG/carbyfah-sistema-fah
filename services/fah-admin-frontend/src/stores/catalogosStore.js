// services\fah-admin-frontend\src\stores\catalogosStore.js

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import * as catalogosService from '@/services/catalogosService'

// Store principal de catalogos
export const useCatalogosStore = defineStore('catalogos', () => {

    // Estado reactivo
    const loading = ref(false)
    const loadingTiposGenero = ref(false)
    const loadingCategoriasPersonal = ref(false)
    const loadingGrados = ref(false)

    // Estados de error
    const error = ref(null)
    const errors = ref({})

    // Datos de catalogos
    const tiposGenero = ref([])
    const categoriasPersonal = ref([])
    const grados = ref([])
    const catalogosBasicos = ref(null)
    const estadisticas = ref(null)

    // Nuevos catalogos
    const tiposEstadoGeneral = ref([])
    const nivelesPrioridad = ref([])
    const nivelesSeguridad = ref([])
    const tiposEvento = ref([])
    const tiposEstructuraMilitar = ref([])
    const tiposJerarquia = ref([])
    const especialidades = ref([])
    const paises = ref([])

    // Estados de modales y UI
    const modalVisible = ref(false)
    const modalMode = ref('create')
    const currentItem = ref(null)
    const currentTable = ref('tipos_genero')

    // Filtros y busquedas
    const filtroCategoria = ref(null)
    const searchTerm = ref('')

    // Computed propiedades recibidas
    const gradosFiltrados = computed(() => {
        if (!filtroCategoria.value) return grados.value
        return grados.value.filter(grado =>
            grado.categoria_personal_id === filtroCategoria.value
        )
    })

    const tiposGeneroActivos = computed(() =>
        tiposGenero.value.filter(tipo => tipo.is_active)
    )

    const categoriasPersonalActivas = computed(() =>
        categoriasPersonal.value
            .filter(categoria => categoria.is_active)
            .sort((a, b) => a.orden_jerarquico - b.orden_jerarquico)
    )

    const gradosActivos = computed(() =>
        grados.value
            .filter(grado => grado.is_active)
            .sort((a, b) => a.orden_jerarquico - b.orden_jerarquico)
    )

    const totalRegistros = computed(() => ({
        tiposGenero: tiposGenero.value.length,
        categoriasPersonal: categoriasPersonal.value.length,
        grados: grados.value.length,
        tiposEstadoGeneral: tiposEstadoGeneral.value.length,
        nivelesPrioridad: nivelesPrioridad.value.length,
        nivelesSeguridad: nivelesSeguridad.value.length,
        tiposEvento: tiposEvento.value.length,
        tiposEstructuraMilitar: tiposEstructuraMilitar.value.length,
        tiposJerarquia: tiposJerarquia.value.length,
        especialidades: especialidades.value.length,
        paises: paises.value.length,
        total: tiposGenero.value.length + categoriasPersonal.value.length + grados.value.length +
            tiposEstadoGeneral.value.length + nivelesPrioridad.value.length + nivelesSeguridad.value.length +
            tiposEvento.value.length + tiposEstructuraMilitar.value.length + tiposJerarquia.value.length +
            especialidades.value.length + paises.value.length
    }))

    const isLoading = computed(() =>
        loading.value || loadingTiposGenero.value ||
        loadingCategoriasPersonal.value || loadingGrados.value
    )

    // Metodos generales
    const clearErrors = () => {
        error.value = null
        errors.value = {}
    }

    const showModal = (mode = 'create', item = null, table = 'tipos_genero') => {
        modalMode.value = mode
        currentItem.value = item
        currentTable.value = table
        modalVisible.value = true
        clearErrors()
    }

    const hideModal = () => {
        modalVisible.value = false
        currentItem.value = null
        clearErrors()
    }

    // Esta funcion esta configurada para cargar catalogos basicos
    const loadCatalogosBasicos = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getCatalogosBasicos()

            if (response && response.success && response.data) {
                const data = response.data
                catalogosBasicos.value = data

                tiposGenero.value = Array.isArray(data.tipos_genero) ? data.tipos_genero : []
                categoriasPersonal.value = Array.isArray(data.categorias_personal) ? data.categorias_personal : []

                // Extraer grados de grados_por_categoria
                const todosLosGrados = []

                if (data.grados_por_categoria) {
                    Object.values(data.grados_por_categoria).forEach(categoria => {
                        if (categoria.grados && Array.isArray(categoria.grados)) {
                            todosLosGrados.push(...categoria.grados)
                        }
                    })
                }

                grados.value = todosLosGrados.map(grado => ({
                    id: grado.id,
                    categoria_personal_id: grado.categoria_personal_id,
                    codigo_grado: grado.codigo_grado || grado.codigo,
                    nombre_grado: grado.nombre_grado || grado.nombre,
                    abreviatura: grado.abreviatura,
                    orden_jerarquico: grado.orden_jerarquico || grado.orden,
                    insignia_url: grado.insignia_url,
                    is_active: grado.is_active !== false,
                    created_at: grado.created_at,
                    updated_at: grado.updated_at,
                    categoria_personal: grado.categoria_personal || null
                }))

                tiposEstadoGeneral.value = Array.isArray(data.tipos_estado_general) ? data.tipos_estado_general : []
                nivelesPrioridad.value = Array.isArray(data.niveles_prioridad) ? data.niveles_prioridad : []
                nivelesSeguridad.value = Array.isArray(data.niveles_seguridad) ? data.niveles_seguridad : []
                tiposEvento.value = Array.isArray(data.tipos_evento) ? data.tipos_evento : []
                tiposEstructuraMilitar.value = Array.isArray(data.tipos_estructura_militar) ? data.tipos_estructura_militar : []
                tiposJerarquia.value = Array.isArray(data.tipos_jerarquia) ? data.tipos_jerarquia : []
                especialidades.value = Array.isArray(data.especialidades) ? data.especialidades : []
                paises.value = Array.isArray(data.paises) ? data.paises : []

            } else {
                error.value = response?.error || 'Datos no validos recibidos'
            }
        } catch (err) {
            error.value = 'Error cargando catalogos basicos: ' + (err.message || err)
        } finally {
            loading.value = false
        }
    }

    // Metodo individual para cargar grados
    const cargarGrados = async (categoriaId = null) => {
        loadingGrados.value = true
        clearErrors()

        try {
            const response = await catalogosService.getGrados(categoriaId)

            if (response.success) {
                grados.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando grados'
        } finally {
            loadingGrados.value = false
        }
    }

    // Cargar estadisticas
    const loadEstadisticas = async () => {
        try {
            const response = await catalogosService.getEstadisticasCatalogos()

            if (response.success) {
                estadisticas.value = response.data
            }
        } catch (err) {
            // Error cargando estadisticas
        }
    }

    // Actions tipos genero
    const loadTiposGenero = async () => {
        loadingTiposGenero.value = true
        clearErrors()

        try {
            const response = await catalogosService.getTiposGenero()

            if (response.success) {
                tiposGenero.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando tipos de genero'
        } finally {
            loadingTiposGenero.value = false
        }
    }

    const createTipoGenero = async (tipoGenero) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createTipoGenero(tipoGenero)

            if (response.success) {
                tiposGenero.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando tipo de genero'
            return { success: false, error: 'Error creando tipo de genero' }
        } finally {
            loading.value = false
        }
    }

    const updateTipoGenero = async (id, tipoGenero) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateTipoGenero(id, tipoGenero)

            if (response.success) {
                const index = tiposGenero.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    tiposGenero.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando tipo de genero'
            return { success: false, error: 'Error actualizando tipo de genero' }
        } finally {
            loading.value = false
        }
    }

    const deleteTipoGenero = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteTipoGenero(id)

            if (response.success) {
                tiposGenero.value = tiposGenero.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando tipo de genero'
            return { success: false, error: 'Error eliminando tipo de genero' }
        } finally {
            loading.value = false
        }
    }

    // Esta funcion esta lista para cargar categorias
    const loadCategoriasPersonal = async () => {
        loadingCategoriasPersonal.value = true
        clearErrors()

        try {
            const response = await catalogosService.getCategoriasPersonal()

            if (response.success) {
                categoriasPersonal.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando categorias de personal'
        } finally {
            loadingCategoriasPersonal.value = false
        }
    }

    const createCategoriaPersonal = async (categoria) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createCategoriaPersonal(categoria)

            if (response.success) {
                categoriasPersonal.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando categoria de personal'
            return { success: false, error: 'Error creando categoria de personal' }
        } finally {
            loading.value = false
        }
    }

    const updateCategoriaPersonal = async (id, categoria) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateCategoriaPersonal(id, categoria)

            if (response.success) {
                const index = categoriasPersonal.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    categoriasPersonal.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando categoria de personal'
            return { success: false, error: 'Error actualizando categoria de personal' }
        } finally {
            loading.value = false
        }
    }

    const deleteCategoriaPersonal = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteCategoriaPersonal(id)

            if (response.success) {
                categoriasPersonal.value = categoriasPersonal.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando categoria de personal'
            return { success: false, error: 'Error eliminando categoria de personal' }
        } finally {
            loading.value = false
        }
    }

    // Actions grados
    const loadGrados = async (categoriaId = null) => {
        loadingGrados.value = true
        clearErrors()

        try {
            const response = await catalogosService.getGrados(categoriaId)

            if (response.success) {
                grados.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando grados'
        } finally {
            loadingGrados.value = false
        }
    }

    const createGrado = async (grado) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createGrado(grado)

            if (response.success) {
                await cargarGrados()
                hideModal()
                return { success: true, message: response.message, data: response.data }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando grado'
            return { success: false, error: 'Error creando grado' }
        } finally {
            loading.value = false
        }
    }

    const updateGrado = async (id, grado) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateGrado(id, grado)

            if (response.success) {
                const index = grados.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    grados.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando grado'
            return { success: false, error: 'Error actualizando grado' }
        } finally {
            loading.value = false
        }
    }

    const deleteGrado = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteGrado(id)

            if (response.success) {
                grados.value = grados.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando grado'
            return { success: false, error: 'Error eliminando grado' }
        } finally {
            loading.value = false
        }
    }

    // Actions especialidades
    const loadEspecialidades = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getEspecialidades()

            if (response.success) {
                especialidades.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando especialidades'
        } finally {
            loading.value = false
        }
    }

    const createEspecialidad = async (especialidad) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createEspecialidad(especialidad)

            if (response.success) {
                especialidades.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando especialidad'
            return { success: false, error: 'Error creando especialidad' }
        } finally {
            loading.value = false
        }
    }

    const updateEspecialidad = async (id, especialidad) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateEspecialidad(id, especialidad)

            if (response.success) {
                const index = especialidades.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    especialidades.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando especialidad'
            return { success: false, error: 'Error actualizando especialidad' }
        } finally {
            loading.value = false
        }
    }

    const deleteEspecialidad = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteEspecialidad(id)

            if (response.success) {
                especialidades.value = especialidades.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando especialidad'
            return { success: false, error: 'Error eliminando especialidad' }
        } finally {
            loading.value = false
        }
    }

    // Actions niveles prioridad
    const loadNivelesPrioridad = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getNivelesPrioridad()

            if (response.success) {
                nivelesPrioridad.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando niveles de prioridad'
        } finally {
            loading.value = false
        }
    }

    const createNivelPrioridad = async (nivel) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createNivelPrioridad(nivel)

            if (response.success) {
                nivelesPrioridad.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando nivel de prioridad'
            return { success: false, error: 'Error creando nivel de prioridad' }
        } finally {
            loading.value = false
        }
    }

    const updateNivelPrioridad = async (id, nivel) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateNivelPrioridad(id, nivel)

            if (response.success) {
                const index = nivelesPrioridad.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    nivelesPrioridad.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando nivel de prioridad'
            return { success: false, error: 'Error actualizando nivel de prioridad' }
        } finally {
            loading.value = false
        }
    }

    const deleteNivelPrioridad = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteNivelPrioridad(id)

            if (response.success) {
                nivelesPrioridad.value = nivelesPrioridad.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando nivel de prioridad'
            return { success: false, error: 'Error eliminando nivel de prioridad' }
        } finally {
            loading.value = false
        }
    }

    // Actions tipos estado general 
    const loadTiposEstadoGeneral = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getTiposEstadoGeneral()

            if (response.success) {
                tiposEstadoGeneral.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando tipos de estado general'
        } finally {
            loading.value = false
        }
    }

    const createTipoEstadoGeneral = async (tipo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createTipoEstadoGeneral(tipo)

            if (response.success) {
                tiposEstadoGeneral.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando tipo de estado general'
            return { success: false, error: 'Error creando tipo de estado general' }
        } finally {
            loading.value = false
        }
    }

    const updateTipoEstadoGeneral = async (id, tipo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateTipoEstadoGeneral(id, tipo)

            if (response.success) {
                const index = tiposEstadoGeneral.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    tiposEstadoGeneral.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando tipo de estado general'
            return { success: false, error: 'Error actualizando tipo de estado general' }
        } finally {
            loading.value = false
        }
    }

    const deleteTipoEstadoGeneral = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteTipoEstadoGeneral(id)

            if (response.success) {
                tiposEstadoGeneral.value = tiposEstadoGeneral.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando tipo de estado general'
            return { success: false, error: 'Error eliminando tipo de estado general' }
        } finally {
            loading.value = false
        }
    }

    // Actions niveles seguridad 
    const loadNivelesSeguridad = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getNivelesSeguridad()

            if (response.success) {
                nivelesSeguridad.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando niveles de seguridad'
        } finally {
            loading.value = false
        }
    }

    const createNivelSeguridad = async (nivel) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createNivelSeguridad(nivel)

            if (response.success) {
                nivelesSeguridad.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando nivel de seguridad'
            return { success: false, error: 'Error creando nivel de seguridad' }
        } finally {
            loading.value = false
        }
    }

    const updateNivelSeguridad = async (id, nivel) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateNivelSeguridad(id, nivel)

            if (response.success) {
                const index = nivelesSeguridad.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    nivelesSeguridad.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando nivel de seguridad'
            return { success: false, error: 'Error actualizando nivel de seguridad' }
        } finally {
            loading.value = false
        }
    }

    const deleteNivelSeguridad = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteNivelSeguridad(id)

            if (response.success) {
                nivelesSeguridad.value = nivelesSeguridad.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando nivel de seguridad'
            return { success: false, error: 'Error eliminando nivel de seguridad' }
        } finally {
            loading.value = false
        }
    }

    // Actions paises 
    const loadPaises = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getPaises()

            if (response.success) {
                paises.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando paises'
        } finally {
            loading.value = false
        }
    }

    const createPais = async (pais) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createPais(pais)

            if (response.success) {
                paises.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando pais'
            return { success: false, error: 'Error creando pais' }
        } finally {
            loading.value = false
        }
    }

    const updatePais = async (id, pais) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updatePais(id, pais)

            if (response.success) {
                const index = paises.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    paises.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando pais'
            return { success: false, error: 'Error actualizando pais' }
        } finally {
            loading.value = false
        }
    }

    const deletePais = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deletePais(id)

            if (response.success) {
                paises.value = paises.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando pais'
            return { success: false, error: 'Error eliminando pais' }
        } finally {
            loading.value = false
        }
    }

    // Actions tipos estructura militar 
    const loadTiposEstructuraMilitar = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getTiposEstructuraMilitar()

            if (response.success) {
                tiposEstructuraMilitar.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando tipos de estructura militar'
        } finally {
            loading.value = false
        }
    }

    const createTipoEstructuraMilitar = async (tipo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createTipoEstructuraMilitar(tipo)

            if (response.success) {
                tiposEstructuraMilitar.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando tipo de estructura militar'
            return { success: false, error: 'Error creando tipo de estructura militar' }
        } finally {
            loading.value = false
        }
    }

    const updateTipoEstructuraMilitar = async (id, tipo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateTipoEstructuraMilitar(id, tipo)

            if (response.success) {
                const index = tiposEstructuraMilitar.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    tiposEstructuraMilitar.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando tipo de estructura militar'
            return { success: false, error: 'Error actualizando tipo de estructura militar' }
        } finally {
            loading.value = false
        }
    }

    const deleteTipoEstructuraMilitar = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteTipoEstructuraMilitar(id)

            if (response.success) {
                tiposEstructuraMilitar.value = tiposEstructuraMilitar.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando tipo de estructura militar'
            return { success: false, error: 'Error eliminando tipo de estructura militar' }
        } finally {
            loading.value = false
        }
    }

    // Actions tipos jerarquia 
    const loadTiposJerarquia = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getTiposJerarquia()

            if (response.success) {
                tiposJerarquia.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando tipos de jerarquia'
        } finally {
            loading.value = false
        }
    }

    const createTipoJerarquia = async (tipo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createTipoJerarquia(tipo)

            if (response.success) {
                tiposJerarquia.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando tipo de jerarquia'
            return { success: false, error: 'Error creando tipo de jerarquia' }
        } finally {
            loading.value = false
        }
    }

    const updateTipoJerarquia = async (id, tipo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateTipoJerarquia(id, tipo)

            if (response.success) {
                const index = tiposJerarquia.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    tiposJerarquia.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando tipo de jerarquia'
            return { success: false, error: 'Error actualizando tipo de jerarquia' }
        } finally {
            loading.value = false
        }
    }

    const deleteTipoJerarquia = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteTipoJerarquia(id)

            if (response.success) {
                tiposJerarquia.value = tiposJerarquia.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando tipo de jerarquia'
            return { success: false, error: 'Error eliminando tipo de jerarquia' }
        } finally {
            loading.value = false
        }
    }

    // Actions tipos evento 
    const loadTiposEvento = async () => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.getTiposEvento()

            if (response.success) {
                tiposEvento.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando tipos de evento'
        } finally {
            loading.value = false
        }
    }

    const createTipoEvento = async (tipo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.createTipoEvento(tipo)

            if (response.success) {
                tiposEvento.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando tipo de evento'
            return { success: false, error: 'Error creando tipo de evento' }
        } finally {
            loading.value = false
        }
    }

    const updateTipoEvento = async (id, tipo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.updateTipoEvento(id, tipo)

            if (response.success) {
                const index = tiposEvento.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    tiposEvento.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando tipo de evento'
            return { success: false, error: 'Error actualizando tipo de evento' }
        } finally {
            loading.value = false
        }
    }

    const deleteTipoEvento = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await catalogosService.deleteTipoEvento(id)

            if (response.success) {
                tiposEvento.value = tiposEvento.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando tipo de evento'
            return { success: false, error: 'Error eliminando tipo de evento' }
        } finally {
            loading.value = false
        }
    }

    // Metodos auxiliares
    const getCategoriaById = (id) => {
        return categoriasPersonal.value.find(categoria => categoria.id === id)
    }

    const getGradosByCategoria = (categoriaId) => {
        return grados.value.filter(grado => grado.categoria_personal_id === categoriaId)
    }

    const canDeleteCategoria = (categoriaId) => {
        return getGradosByCategoria(categoriaId).length === 0
    }

    const resetStore = () => {
        tiposGenero.value = []
        categoriasPersonal.value = []
        grados.value = []
        tiposEstadoGeneral.value = []
        nivelesPrioridad.value = []
        nivelesSeguridad.value = []
        tiposEvento.value = []
        tiposEstructuraMilitar.value = []
        tiposJerarquia.value = []
        especialidades.value = []
        paises.value = []
        catalogosBasicos.value = null
        estadisticas.value = null
        clearErrors()
        hideModal()
    }

    return {
        // Estado
        loading,
        loadingTiposGenero,
        loadingCategoriasPersonal,
        loadingGrados,
        error,
        errors,

        // Datos de catalogos
        tiposGenero,
        categoriasPersonal,
        grados,
        catalogosBasicos,
        estadisticas,

        // Nuevos catalogos
        tiposEstadoGeneral,
        nivelesPrioridad,
        nivelesSeguridad,
        tiposEvento,
        tiposEstructuraMilitar,
        tiposJerarquia,
        especialidades,
        paises,

        // Estados UI
        modalVisible,
        modalMode,
        currentItem,
        currentTable,
        filtroCategoria,
        searchTerm,

        // Computed
        gradosFiltrados,
        tiposGeneroActivos,
        categoriasPersonalActivas,
        gradosActivos,
        totalRegistros,
        isLoading,

        // Actions generales
        clearErrors,
        showModal,
        hideModal,
        loadCatalogosBasicos,
        loadEstadisticas,

        // Actions tipos genero
        loadTiposGenero,
        createTipoGenero,
        updateTipoGenero,
        deleteTipoGenero,

        // Actions categorias personal
        loadCategoriasPersonal,
        createCategoriaPersonal,
        updateCategoriaPersonal,
        deleteCategoriaPersonal,

        // Actions grados
        loadGrados,
        cargarGrados,
        createGrado,
        updateGrado,
        deleteGrado,

        // Actions especialidades
        loadEspecialidades,
        createEspecialidad,
        updateEspecialidad,
        deleteEspecialidad,

        // Actions niveles prioridad
        loadNivelesPrioridad,
        createNivelPrioridad,
        updateNivelPrioridad,
        deleteNivelPrioridad,

        // Actions tipos estado general
        loadTiposEstadoGeneral,
        createTipoEstadoGeneral,
        updateTipoEstadoGeneral,
        deleteTipoEstadoGeneral,

        // Actions niveles seguridad
        loadNivelesSeguridad,
        createNivelSeguridad,
        updateNivelSeguridad,
        deleteNivelSeguridad,

        // Actions paises
        loadPaises,
        createPais,
        updatePais,
        deletePais,

        // Actions tipos estructura militar
        loadTiposEstructuraMilitar,
        createTipoEstructuraMilitar,
        updateTipoEstructuraMilitar,
        deleteTipoEstructuraMilitar,

        // Actions tipos jerarquia
        loadTiposJerarquia,
        createTipoJerarquia,
        updateTipoJerarquia,
        deleteTipoJerarquia,

        // Actions tipos evento
        loadTiposEvento,
        createTipoEvento,
        updateTipoEvento,
        deleteTipoEvento,

        // Utilidades
        getCategoriaById,
        getGradosByCategoria,
        canDeleteCategoria,
        resetStore
    }
})