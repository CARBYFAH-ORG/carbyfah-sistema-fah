// services\fah-admin-frontend\src\stores\organizacionStore.js

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import * as organizacionService from '@/services/organizacionService'

// Store principal de organizacion
export const useOrganizacionStore = defineStore('organizacion', () => {

    // Estado reactivo
    const loading = ref(false)
    const loadingDepartamentos = ref(false)
    const loadingMunicipios = ref(false)
    const loadingCiudades = ref(false)
    const loadingUbicacionesGeograficas = ref(false)
    const loadingEstructuraMilitar = ref(false)
    const loadingCargos = ref(false)
    const loadingRolesFuncionales = ref(false)

    // Estados de error
    const error = ref(null)
    const errors = ref({})

    // Datos de organizacion
    const departamentos = ref([])
    const municipios = ref([])
    const ciudades = ref([])
    const ubicacionesGeograficas = ref([])
    const estructuraMilitar = ref([])
    const cargos = ref([])
    const rolesFuncionales = ref([])

    // Estados de modales y UI
    const modalVisible = ref(false)
    const modalMode = ref('create')
    const currentItem = ref(null)
    const currentTable = ref('departamentos')

    // Filtros y busquedas
    const filtroPais = ref(null)
    const filtroDepartamento = ref(null)
    const filtroMunicipio = ref(null)
    const searchTerm = ref('')

    // Computed properties
    const departamentosActivos = computed(() =>
        departamentos.value.filter(depto => depto.is_active)
    )

    const municipiosActivos = computed(() =>
        municipios.value.filter(municipio => municipio.is_active)
    )

    const ciudadesActivas = computed(() =>
        ciudades.value.filter(ciudad => ciudad.is_active)
    )

    const ubicacionesGeograficasActivas = computed(() =>
        ubicacionesGeograficas.value.filter(ubicacion => ubicacion.is_active)
    )

    const estructuraMilitarActiva = computed(() =>
        estructuraMilitar.value
            .filter(estructura => estructura.is_active)
            .sort((a, b) => a.nivel_jerarquico - b.nivel_jerarquico)
    )

    const cargosActivos = computed(() =>
        cargos.value
            .filter(cargo => cargo.is_active)
            .sort((a, b) => a.nivel_jerarquico - b.nivel_jerarquico)
    )

    const rolesFuncionalesActivos = computed(() =>
        rolesFuncionales.value
            .filter(rol => rol.is_active)
            .sort((a, b) => (b.nivel_autoridad || 0) - (a.nivel_autoridad || 0))
    )

    const totalRegistros = computed(() => ({
        departamentos: departamentos.value.length,
        municipios: municipios.value.length,
        ciudades: ciudades.value.length,
        ubicacionesGeograficas: ubicacionesGeograficas.value.length,
        estructuraMilitar: estructuraMilitar.value.length,
        cargos: cargos.value.length,
        rolesFuncionales: rolesFuncionales.value.length,
        total: departamentos.value.length + municipios.value.length + ciudades.value.length +
            ubicacionesGeograficas.value.length + estructuraMilitar.value.length +
            cargos.value.length + rolesFuncionales.value.length
    }))

    const isLoading = computed(() =>
        loading.value || loadingDepartamentos.value || loadingMunicipios.value ||
        loadingCiudades.value || loadingUbicacionesGeograficas.value ||
        loadingEstructuraMilitar.value || loadingCargos.value || loadingRolesFuncionales.value
    )

    // Metodos generales
    const clearErrors = () => {
        error.value = null
        errors.value = {}
    }

    const showModal = (mode = 'create', item = null, table = 'departamentos') => {
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

    /* Actions departamentos */
    const loadDepartamentos = async () => {
        if (typeof organizacionService.getDepartamentos !== 'function') {
            return;
        }

        loadingDepartamentos.value = true
        clearErrors()

        try {
            const response = await organizacionService.getDepartamentos()

            if (response.success) {
                departamentos.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando departamentos'
        } finally {
            loadingDepartamentos.value = false
        }
    }

    const createDepartamento = async (departamento) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.createDepartamento(departamento)

            if (response.success) {
                departamentos.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando departamento'
            return { success: false, error: 'Error creando departamento' }
        } finally {
            loading.value = false
        }
    }

    const updateDepartamento = async (id, departamento) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.updateDepartamento(id, departamento)

            if (response.success) {
                const index = departamentos.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    departamentos.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando departamento'
            return { success: false, error: 'Error actualizando departamento' }
        } finally {
            loading.value = false
        }
    }

    const deleteDepartamento = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.deleteDepartamento(id)

            if (response.success) {
                departamentos.value = departamentos.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando departamento'
            return { success: false, error: 'Error eliminando departamento' }
        } finally {
            loading.value = false
        }
    }

    /* Actions municipios */
    const loadMunicipios = async () => {
        loadingMunicipios.value = true
        clearErrors()

        try {
            const response = await organizacionService.getMunicipios()

            if (response.success) {
                municipios.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando municipios'
        } finally {
            loadingMunicipios.value = false
        }
    }

    const createMunicipio = async (municipio) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.createMunicipio(municipio)

            if (response.success) {
                municipios.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando municipio'
            return { success: false, error: 'Error creando municipio' }
        } finally {
            loading.value = false
        }
    }

    const updateMunicipio = async (id, municipio) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.updateMunicipio(id, municipio)

            if (response.success) {
                const index = municipios.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    municipios.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando municipio'
            return { success: false, error: 'Error actualizando municipio' }
        } finally {
            loading.value = false
        }
    }

    const deleteMunicipio = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.deleteMunicipio(id)

            if (response.success) {
                municipios.value = municipios.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando municipio'
            return { success: false, error: 'Error eliminando municipio' }
        } finally {
            loading.value = false
        }
    }

    /* Actions ciudades */
    const loadCiudades = async () => {
        loadingCiudades.value = true
        clearErrors()

        try {
            const response = await organizacionService.getCiudades()

            if (response.success) {
                ciudades.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando ciudades'
        } finally {
            loadingCiudades.value = false
        }
    }

    const createCiudad = async (ciudad) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.createCiudad(ciudad)

            if (response.success) {
                ciudades.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando ciudad'
            return { success: false, error: 'Error creando ciudad' }
        } finally {
            loading.value = false
        }
    }

    const updateCiudad = async (id, ciudad) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.updateCiudad(id, ciudad)

            if (response.success) {
                const index = ciudades.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    ciudades.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando ciudad'
            return { success: false, error: 'Error actualizando ciudad' }
        } finally {
            loading.value = false
        }
    }

    const deleteCiudad = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.deleteCiudad(id)

            if (response.success) {
                ciudades.value = ciudades.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando ciudad'
            return { success: false, error: 'Error eliminando ciudad' }
        } finally {
            loading.value = false
        }
    }

    /* Actions ubicaciones geograficas */
    const loadUbicacionesGeograficas = async () => {
        loadingUbicacionesGeograficas.value = true
        clearErrors()

        try {
            const response = await organizacionService.getUbicacionesGeograficas()

            if (response.success) {
                ubicacionesGeograficas.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando ubicaciones geograficas'
        } finally {
            loadingUbicacionesGeograficas.value = false
        }
    }

    const createUbicacionGeografica = async (ubicacion) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.createUbicacionGeografica(ubicacion)

            if (response.success) {
                ubicacionesGeograficas.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando ubicacion geografica'
            return { success: false, error: 'Error creando ubicacion geografica' }
        } finally {
            loading.value = false
        }
    }

    const updateUbicacionGeografica = async (id, ubicacion) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.updateUbicacionGeografica(id, ubicacion)

            if (response.success) {
                const index = ubicacionesGeograficas.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    ubicacionesGeograficas.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando ubicacion geografica'
            return { success: false, error: 'Error actualizando ubicacion geografica' }
        } finally {
            loading.value = false
        }
    }

    const deleteUbicacionGeografica = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.deleteUbicacionGeografica(id)

            if (response.success) {
                ubicacionesGeograficas.value = ubicacionesGeograficas.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando ubicacion geografica'
            return { success: false, error: 'Error eliminando ubicacion geografica' }
        } finally {
            loading.value = false
        }
    }

    /* Actions estructura militar */
    const loadEstructuraMilitar = async () => {
        loadingEstructuraMilitar.value = true
        clearErrors()

        try {
            const response = await organizacionService.getEstructuraMilitar()

            if (response.success) {
                estructuraMilitar.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando estructura militar'
        } finally {
            loadingEstructuraMilitar.value = false
        }
    }

    const createEstructuraMilitar = async (estructura) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.createEstructuraMilitar(estructura)

            if (response.success) {
                estructuraMilitar.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando estructura militar'
            return { success: false, error: 'Error creando estructura militar' }
        } finally {
            loading.value = false
        }
    }

    const updateEstructuraMilitar = async (id, estructura) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.updateEstructuraMilitar(id, estructura)

            if (response.success) {
                const index = estructuraMilitar.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    estructuraMilitar.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando estructura militar'
            return { success: false, error: 'Error actualizando estructura militar' }
        } finally {
            loading.value = false
        }
    }

    const deleteEstructuraMilitar = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.deleteEstructuraMilitar(id)

            if (response.success) {
                estructuraMilitar.value = estructuraMilitar.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando estructura militar'
            return { success: false, error: 'Error eliminando estructura militar' }
        } finally {
            loading.value = false
        }
    }

    /* Actions cargos */
    const loadCargos = async () => {
        loadingCargos.value = true
        clearErrors()

        try {
            const response = await organizacionService.getCargos()

            if (response.success) {
                cargos.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando cargos'
        } finally {
            loadingCargos.value = false
        }
    }

    const createCargo = async (cargo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.createCargo(cargo)

            if (response.success) {
                cargos.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando cargo'
            return { success: false, error: 'Error creando cargo' }
        } finally {
            loading.value = false
        }
    }

    const updateCargo = async (id, cargo) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.updateCargo(id, cargo)

            if (response.success) {
                const index = cargos.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    cargos.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando cargo'
            return { success: false, error: 'Error actualizando cargo' }
        } finally {
            loading.value = false
        }
    }

    const deleteCargo = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.deleteCargo(id)

            if (response.success) {
                cargos.value = cargos.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando cargo'
            return { success: false, error: 'Error eliminando cargo' }
        } finally {
            loading.value = false
        }
    }

    /* Actions roles funcionales */
    const loadRolesFuncionales = async () => {
        loadingRolesFuncionales.value = true
        clearErrors()

        try {
            const response = await organizacionService.getRolesFuncionales()

            if (response.success) {
                rolesFuncionales.value = response.data
            } else {
                error.value = response.error
            }
        } catch (err) {
            error.value = 'Error cargando roles funcionales'
        } finally {
            loadingRolesFuncionales.value = false
        }
    }

    const createRolFuncional = async (rol) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.createRolFuncional(rol)

            if (response.success) {
                rolesFuncionales.value.push(response.data)
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error creando rol funcional'
            return { success: false, error: 'Error creando rol funcional' }
        } finally {
            loading.value = false
        }
    }

    const updateRolFuncional = async (id, rol) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.updateRolFuncional(id, rol)

            if (response.success) {
                const index = rolesFuncionales.value.findIndex(item => item.id === id)
                if (index !== -1) {
                    rolesFuncionales.value[index] = response.data
                }
                hideModal()
                return { success: true, message: response.message }
            } else {
                errors.value = response.errors || {}
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error actualizando rol funcional'
            return { success: false, error: 'Error actualizando rol funcional' }
        } finally {
            loading.value = false
        }
    }

    const deleteRolFuncional = async (id) => {
        loading.value = true
        clearErrors()

        try {
            const response = await organizacionService.deleteRolFuncional(id)

            if (response.success) {
                rolesFuncionales.value = rolesFuncionales.value.filter(item => item.id !== id)
                return { success: true, message: response.message }
            } else {
                return { success: false, error: response.error }
            }
        } catch (err) {
            error.value = 'Error eliminando rol funcional'
            return { success: false, error: 'Error eliminando rol funcional' }
        } finally {
            loading.value = false
        }
    }

    /* Metodos auxiliares */
    const getDepartamentoById = (id) => {
        return departamentos.value.find(depto => depto.id === id)
    }

    const getMunicipiosByDepartamento = (departamentoId) => {
        return municipios.value.filter(municipio => municipio.departamento_id === departamentoId)
    }

    const getCiudadesByMunicipio = (municipioId) => {
        return ciudades.value.filter(ciudad => ciudad.municipio_id === municipioId)
    }

    const getUbicacionesByFiltros = (paisId, departamentoId, municipioId) => {
        return ubicacionesGeograficas.value.filter(ubicacion => {
            return (!paisId || ubicacion.pais_id === paisId) &&
                (!departamentoId || ubicacion.departamento_id === departamentoId) &&
                (!municipioId || ubicacion.municipio_id === municipioId)
        })
    }

    const getCargosByEstructura = (estructuraId) => {
        return cargos.value.filter(cargo => cargo.estructura_militar_id === estructuraId)
    }

    const resetStore = () => {
        departamentos.value = []
        municipios.value = []
        ciudades.value = []
        ubicacionesGeograficas.value = []
        estructuraMilitar.value = []
        cargos.value = []
        rolesFuncionales.value = []
        clearErrors()
        hideModal()
    }

    return {
        // Estado
        loading,
        loadingDepartamentos,
        loadingMunicipios,
        loadingCiudades,
        loadingUbicacionesGeograficas,
        loadingEstructuraMilitar,
        loadingCargos,
        loadingRolesFuncionales,
        error,
        errors,

        // Datos de organizacion
        departamentos,
        municipios,
        ciudades,
        ubicacionesGeograficas,
        estructuraMilitar,
        cargos,
        rolesFuncionales,

        // Estados UI
        modalVisible,
        modalMode,
        currentItem,
        currentTable,
        filtroPais,
        filtroDepartamento,
        filtroMunicipio,
        searchTerm,

        // Computed
        departamentosActivos,
        municipiosActivos,
        ciudadesActivas,
        ubicacionesGeograficasActivas,
        estructuraMilitarActiva,
        cargosActivos,
        rolesFuncionalesActivos,
        totalRegistros,
        isLoading,

        // Actions generales
        clearErrors,
        showModal,
        hideModal,

        // Actions departamentos
        loadDepartamentos,
        createDepartamento,
        updateDepartamento,
        deleteDepartamento,

        // Actions municipios
        loadMunicipios,
        createMunicipio,
        updateMunicipio,
        deleteMunicipio,

        // Actions ciudades
        loadCiudades,
        createCiudad,
        updateCiudad,
        deleteCiudad,

        // Actions ubicaciones geograficas
        loadUbicacionesGeograficas,
        createUbicacionGeografica,
        updateUbicacionGeografica,
        deleteUbicacionGeografica,

        // Actions estructura militar
        loadEstructuraMilitar,
        createEstructuraMilitar,
        updateEstructuraMilitar,
        deleteEstructuraMilitar,

        // Actions cargos
        loadCargos,
        createCargo,
        updateCargo,
        deleteCargo,

        // Actions roles funcionales
        loadRolesFuncionales,
        createRolFuncional,
        updateRolFuncional,
        deleteRolFuncional,

        // Utilidades
        getDepartamentoById,
        getMunicipiosByDepartamento,
        getCiudadesByMunicipio,
        getUbicacionesByFiltros,
        getCargosByEstructura,
        resetStore
    }
})