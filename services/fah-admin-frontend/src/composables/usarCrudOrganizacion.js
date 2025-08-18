// services\fah-admin-frontend\src\composables\usarCrudOrganizacion.js

// Composable CRUD dinamico - Organizacion FAH
// Operaciones CRUD especificas para modulos de organizacion

import { ref, computed } from 'vue'
import { useToastFAH } from '@/composables/useToastFAH'
import { useOrganizacionStore } from '@/stores/organizacionStore'
import { obtenerEsquema, obtenerNotificacion } from '@/config/esquemaOrganizacion'

/**
 * Composable para operaciones CRUD de organizacion
 * @param {string} esquema - Nombre del esquema de organizacion
 */
export const usarCrudOrganizacion = (esquema) => {
    // Composables y dependencias
    const toast = useToastFAH()
    const organizacionStore = useOrganizacionStore()

    // Estado reactivo
    const cargando = ref(false)
    const error = ref(null)

    // Obtener registros segun el esquema
    const registros = computed(() => {
        switch (esquema) {
            case 'departamentos':
                return organizacionStore.departamentos || []
            case 'municipios':
                return organizacionStore.municipios || []
            case 'ciudades':
                return organizacionStore.ciudades || []
            case 'ubicaciones_geograficas':
                return organizacionStore.ubicacionesGeograficas || []
            case 'estructura_militar':
                return organizacionStore.estructuraMilitar || []
            case 'cargos':
                return organizacionStore.cargos || []
            case 'roles_funcionales':
                return organizacionStore.rolesFuncionales || []
            default:
                return []
        }
    })

    // Configuracion del esquema
    const configuracionEsquema = computed(() => {
        return obtenerEsquema(esquema)
    })

    // Registros activos unicamente
    const registrosActivos = computed(() => {
        return registros.value.filter(registro =>
            registro.is_active !== false
        )
    })

    // Estadisticas de registros
    const estadisticas = computed(() => ({
        total: registros.value.length,
        activos: registrosActivos.value.length,
        inactivos: registros.value.length - registrosActivos.value.length
    }))

    // Cargar datos
    const cargarDatos = async () => {
        cargando.value = true
        error.value = null

        try {
            switch (esquema) {
                case 'departamentos':
                    await organizacionStore.loadDepartamentos()
                    break
                case 'municipios':
                    await organizacionStore.loadMunicipios()
                    break
                case 'ciudades':
                    await organizacionStore.loadCiudades()
                    break
                case 'ubicaciones_geograficas':
                    await organizacionStore.loadUbicacionesGeograficas()
                    break
                case 'estructura_militar':
                    await organizacionStore.loadEstructuraMilitar()
                    break
                case 'cargos':
                    await organizacionStore.loadCargos()
                    break
                case 'roles_funcionales':
                    await organizacionStore.loadRolesFuncionales()
                    break
                default:
                    console.warn(`Esquema no reconocido: ${esquema}`)
            }

        } catch (err) {
            error.value = err.message || 'Error cargando datos'
            toast.error("Error de Conexion", "No se pudo conectar con el servidor")
        } finally {
            cargando.value = false
        }
    }

    // Crear registro
    const crearRegistro = async (datos) => {
        cargando.value = true
        error.value = null

        try {
            let resultado

            switch (esquema) {
                case 'departamentos':
                    resultado = await organizacionStore.createDepartamento(datos)
                    break
                case 'municipios':
                    resultado = await organizacionStore.createMunicipio(datos)
                    break
                case 'ciudades':
                    resultado = await organizacionStore.createCiudad(datos)
                    break
                case 'ubicaciones_geograficas':
                    resultado = await organizacionStore.createUbicacionGeografica(datos)
                    break
                case 'estructura_militar':
                    resultado = await organizacionStore.createEstructuraMilitar(datos)
                    break
                case 'cargos':
                    resultado = await organizacionStore.createCargo(datos)
                    break
                case 'roles_funcionales':
                    resultado = await organizacionStore.createRolFuncional(datos)
                    break
                default:
                    throw new Error(`Creacion no implementada para esquema: ${esquema}`)
            }

            if (resultado.success) {
                const nombreRegistro = obtenerNombreRegistro(datos)
                toast.success("Registro Creado", `${nombreRegistro} creado exitosamente`)
                return resultado
            } else {
                throw new Error(resultado.error || 'Error desconocido')
            }

        } catch (err) {
            error.value = err.message
            toast.error("Error al Crear", `No se pudo crear el registro`)
            throw err
        } finally {
            cargando.value = false
        }
    }

    // Actualizar registro
    const actualizarRegistro = async (id, datos) => {
        cargando.value = true
        error.value = null

        try {
            let resultado

            switch (esquema) {
                case 'departamentos':
                    resultado = await organizacionStore.updateDepartamento(id, datos)
                    break
                case 'municipios':
                    resultado = await organizacionStore.updateMunicipio(id, datos)
                    break
                case 'ciudades':
                    resultado = await organizacionStore.updateCiudad(id, datos)
                    break
                case 'ubicaciones_geograficas':
                    resultado = await organizacionStore.updateUbicacionGeografica(id, datos)
                    break
                case 'estructura_militar':
                    resultado = await organizacionStore.updateEstructuraMilitar(id, datos)
                    break
                case 'cargos':
                    resultado = await organizacionStore.updateCargo(id, datos)
                    break
                case 'roles_funcionales':
                    resultado = await organizacionStore.updateRolFuncional(id, datos)
                    break
                default:
                    throw new Error(`Actualizacion no implementada para esquema: ${esquema}`)
            }

            if (resultado.success) {
                const nombreRegistro = obtenerNombreRegistro(datos)
                toast.success("Registro Actualizado", `${nombreRegistro} actualizado exitosamente`)
                return resultado
            } else {
                throw new Error(resultado.error || 'Error desconocido')
            }

        } catch (err) {
            error.value = err.message
            toast.error("Error al Actualizar", `No se pudo actualizar el registro`)
            throw err
        } finally {
            cargando.value = false
        }
    }

    // Eliminar registro
    const eliminarRegistro = async (id) => {
        cargando.value = true
        error.value = null

        try {
            let resultado

            switch (esquema) {
                case 'departamentos':
                    resultado = await organizacionStore.deleteDepartamento(id)
                    break
                case 'municipios':
                    resultado = await organizacionStore.deleteMunicipio(id)
                    break
                case 'ciudades':
                    resultado = await organizacionStore.deleteCiudad(id)
                    break
                case 'ubicaciones_geograficas':
                    resultado = await organizacionStore.deleteUbicacionGeografica(id)
                    break
                case 'estructura_militar':
                    resultado = await organizacionStore.deleteEstructuraMilitar(id)
                    break
                case 'cargos':
                    resultado = await organizacionStore.deleteCargo(id)
                    break
                case 'roles_funcionales':
                    resultado = await organizacionStore.deleteRolFuncional(id)
                    break
                default:
                    throw new Error(`Eliminacion no implementada para esquema: ${esquema}`)
            }

            if (resultado.success) {
                toast.success("Registro Eliminado", "Registro eliminado exitosamente")
                return resultado
            } else {
                throw new Error(resultado.error || 'Error desconocido')
            }

        } catch (err) {
            error.value = err.message
            toast.error("Error al Eliminar", `No se pudo eliminar el registro`)
            throw err
        } finally {
            cargando.value = false
        }
    }

    // Funciones auxiliares

    // Buscar registro por ID
    const buscarPorId = (id) => {
        return registros.value.find(registro =>
            registro.id === id || registro.id === parseInt(id)
        )
    }

    // Buscar registros por campo
    const buscarPorCampo = (campo, valor) => {
        return registros.value.filter(registro =>
            registro[campo] === valor
        )
    }

    // Filtrar registros
    const filtrarRegistros = (filtros) => {
        return registros.value.filter(registro => {
            return Object.entries(filtros).every(([campo, valor]) => {
                if (valor === null || valor === undefined || valor === '') {
                    return true
                }

                const valorRegistro = registro[campo]

                if (typeof valor === 'string') {
                    return valorRegistro?.toString().toLowerCase().includes(valor.toLowerCase())
                }

                return valorRegistro === valor
            })
        })
    }

    // Ordenar registros
    const ordenarRegistros = (campo, direccion = 'asc') => {
        return [...registros.value].sort((a, b) => {
            const valorA = a[campo]
            const valorB = b[campo]

            if (valorA < valorB) return direccion === 'asc' ? -1 : 1
            if (valorA > valorB) return direccion === 'asc' ? 1 : -1
            return 0
        })
    }

    // Obtener nombre representativo del registro
    const obtenerNombreRegistro = (registro) => {
        const camposPrioridad = [
            'nombre_departamento',
            'nombre_municipio',
            'nombre_ciudad',
            'nombre_ubicacion',
            'nombre_unidad',
            'nombre_cargo',
            'nombre_rol',
            'codigo_departamento',
            'codigo_municipio',
            'codigo_ciudad',
            'codigo_ubicacion',
            'codigo_unidad',
            'codigo_cargo',
            'codigo_rol'
        ]

        for (const campo of camposPrioridad) {
            if (registro[campo]) {
                return registro[campo]
            }
        }

        return 'Registro'
    }

    // Obtener titulo del esquema
    const obtenerTituloEsquema = () => {
        const config = configuracionEsquema.value
        return config ? config.titulo : esquema
    }

    // Validar si un registro puede ser eliminado
    const puedeEliminar = (registro) => {
        switch (esquema) {
            case 'departamentos':
                return !organizacionStore.municipios.some(municipio =>
                    municipio.departamento_id === registro.id
                )
            case 'municipios':
                return !organizacionStore.ciudades.some(ciudad =>
                    ciudad.municipio_id === registro.id
                )
            case 'ciudades':
                return !organizacionStore.ubicacionesGeograficas.some(ubicacion =>
                    ubicacion.ciudad_id === registro.id
                )
            case 'estructura_militar':
                return !organizacionStore.cargos.some(cargo =>
                    cargo.estructura_militar_id === registro.id
                )
            default:
                return true
        }
    }

    // Obtener dependencias de un registro
    const obtenerDependencias = (registro) => {
        const dependencias = []

        switch (esquema) {
            case 'departamentos':
                const municipiosAsociados = organizacionStore.municipios.filter(municipio =>
                    municipio.departamento_id === registro.id
                )
                if (municipiosAsociados.length > 0) {
                    dependencias.push({
                        tipo: 'municipios',
                        cantidad: municipiosAsociados.length,
                        mensaje: `${municipiosAsociados.length} municipio(s)`
                    })
                }
                break
            case 'municipios':
                const ciudadesAsociadas = organizacionStore.ciudades.filter(ciudad =>
                    ciudad.municipio_id === registro.id
                )
                if (ciudadesAsociadas.length > 0) {
                    dependencias.push({
                        tipo: 'ciudades',
                        cantidad: ciudadesAsociadas.length,
                        mensaje: `${ciudadesAsociadas.length} ciudad(es)`
                    })
                }
                break
            case 'ciudades':
                const ubicacionesAsociadas = organizacionStore.ubicacionesGeograficas.filter(ubicacion =>
                    ubicacion.ciudad_id === registro.id
                )
                if (ubicacionesAsociadas.length > 0) {
                    dependencias.push({
                        tipo: 'ubicaciones_geograficas',
                        cantidad: ubicacionesAsociadas.length,
                        mensaje: `${ubicacionesAsociadas.length} ubicacion(es) geografica(s)`
                    })
                }
                break
            case 'estructura_militar':
                const cargosAsociados = organizacionStore.cargos.filter(cargo =>
                    cargo.estructura_militar_id === registro.id
                )
                if (cargosAsociados.length > 0) {
                    dependencias.push({
                        tipo: 'cargos',
                        cantidad: cargosAsociados.length,
                        mensaje: `${cargosAsociados.length} cargo(s)`
                    })
                }
                break
        }

        return dependencias
    }

    // Limpiar errores
    const limpiarErrores = () => {
        error.value = null
    }

    // Refrescar datos
    const refrescar = async () => {
        await cargarDatos()
    }

    return {
        // Estado reactivo
        cargando,
        error,

        // Computed
        registros,
        configuracionEsquema,
        registrosActivos,
        estadisticas,

        // Operaciones CRUD
        cargarDatos,
        crearRegistro,
        actualizarRegistro,
        eliminarRegistro,

        // Busqueda y filtrado
        buscarPorId,
        buscarPorCampo,
        filtrarRegistros,
        ordenarRegistros,

        // Validaciones y utilidades
        puedeEliminar,
        obtenerDependencias,
        obtenerNombreRegistro,
        obtenerTituloEsquema,
        limpiarErrores,
        refrescar
    }
}

export default usarCrudOrganizacion