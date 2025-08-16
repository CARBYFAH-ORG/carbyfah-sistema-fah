import axios from 'axios'

const API_BASE_URL = '/api'

export default {
    async obtenerEstructuraFAH() {
        try {
            const response = await axios.get(`${API_BASE_URL}/organigramas/estructura-fah`)
            return response.data
        } catch (error) {
            console.error('Error al obtener estructura FAH:', error)
            throw error
        }
    },

    async obtenerOrganigramaUnidad(unidadId) {
        try {
            const response = await axios.get(`${API_BASE_URL}/organigramas/unidad/${unidadId}`)
            return response.data
        } catch (error) {
            console.error('Error al obtener organigrama de unidad:', error)
            throw error
        }
    },

    async exportarOrganigrama(tipo = 'fah', unidadId = null) {
        try {
            const params = { tipo }
            if (unidadId) params.unidad_id = unidadId

            const response = await axios.get(`${API_BASE_URL}/organigramas/exportar`, { params })
            return response.data
        } catch (error) {
            console.error('Error al exportar organigrama:', error)
            throw error
        }
    }
}