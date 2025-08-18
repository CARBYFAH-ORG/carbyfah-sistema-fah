// services\fah-admin-frontend\src\composables\useToastFAH.js

import iziToast from 'izitoast'
import 'izitoast/dist/css/iziToast.min.css'

// Configuracion base con paleta FAH
iziToast.settings({
    timeout: 4000,
    resetOnHover: true,
    icon: '',
    transitionIn: 'slideInRight',
    transitionOut: 'slideOutRight',
    position: 'topRight',
    close: true,
    onOpening: function () {
        console.log('Notificacion FAH abierta');
    },
    onClosing: function () {
        console.log('Notificacion FAH cerrada');
    }
})

export function useToastFAH() {

    // Exito - Verde FAH
    const success = (title, message, options = {}) => {
        iziToast.success({
            title: title || 'Exito',
            message: message || 'Operacion completada correctamente',
            backgroundColor: '#28a745',
            titleColor: '#ffffff',
            messageColor: '#ffffff',
            iconColor: '#ffffff',
            progressBarColor: 'rgba(255,255,255,0.3)',
            icon: 'pi pi-check-circle',
            class: 'fah-toast-success',
            ...options
        })
    }

    // Informacion - Azul Naval FAH
    const info = (title, message, options = {}) => {
        iziToast.info({
            title: title || 'Informacion',
            message: message || 'Nueva informacion disponible',
            backgroundColor: '#1e3a5f',
            titleColor: '#ffffff',
            messageColor: '#ffffff',
            iconColor: '#d4af37',
            progressBarColor: 'rgba(212, 175, 55, 0.3)',
            icon: 'pi pi-info-circle',
            class: 'fah-toast-info',
            ...options
        })
    }

    // Error - Azul Naval FAH (segun tu solicitud)
    const error = (title, message, options = {}) => {
        iziToast.error({
            title: title || 'Error',
            message: message || 'Ha ocurrido un error en el sistema',
            backgroundColor: '#1e3a5f',
            titleColor: '#ffffff',
            messageColor: '#ffffff',
            iconColor: '#ffffff',
            progressBarColor: 'rgba(255,255,255,0.3)',
            icon: 'pi pi-exclamation-triangle',
            class: 'fah-toast-error',
            timeout: 6000,
            ...options
        })
    }

    // Advertencia - Azul Naval FAH
    const warning = (title, message, options = {}) => {
        iziToast.warning({
            title: title || 'Advertencia',
            message: message || 'Atencion requerida',
            backgroundColor: '#1e3a5f',
            titleColor: '#ffffff',
            messageColor: '#ffffff',
            iconColor: '#d4af37',
            progressBarColor: 'rgba(212, 175, 55, 0.3)',
            icon: 'pi pi-exclamation-circle',
            class: 'fah-toast-warning',
            ...options
        })
    }

    // Especial - Con borde dorado para operaciones importantes
    const important = (title, message, options = {}) => {
        iziToast.success({
            title: title || 'Operacion Importante',
            message: message,
            backgroundColor: '#1e3a5f',
            titleColor: '#d4af37',
            messageColor: '#ffffff',
            iconColor: '#d4af37',
            progressBarColor: 'rgba(212, 175, 55, 0.5)',
            icon: 'pi pi-star-fill',
            class: 'fah-toast-important',
            timeout: 6000,
            ...options
        })
    }

    // Critico - Con efecto especial
    const critical = (title, message, options = {}) => {
        iziToast.error({
            title: title || 'CRITICO',
            message: message,
            backgroundColor: '#c1666b',
            titleColor: '#ffffff',
            messageColor: '#ffffff',
            iconColor: '#ffffff',
            progressBarColor: 'rgba(255,255,255,0.3)',
            icon: 'pi pi-times-circle',
            class: 'fah-toast-critical',
            timeout: 8000,
            close: false,
            ...options
        })
    }

    const pending = (title, message, options = {}) => {
        iziToast.info({
            title: title || 'Funcion no disponible',
            message: message,
            backgroundColor: '#0ea5e9',
            titleColor: '#ffffff',
            messageColor: '#ffffff',
            iconColor: '#ffffff',
            progressBarColor: 'rgba(255,255,255,0.3)',
            icon: 'pi pi-clock',
            class: 'fah-toast-pending',
            ...options
        })
    }

    // Utilidades
    const clear = () => {
        iziToast.destroy()
    }

    const show = (type, title, message, options = {}) => {
        switch (type) {
            case 'success':
                success(title, message, options)
                break
            case 'error':
                error(title, message, options)
                break
            case 'info':
                info(title, message, options)
                break
            case 'warning':
                warning(title, message, options)
                break
            case 'important':
                important(title, message, options)
                break
            case 'critical':
                critical(title, message, options)
                break
            case 'pending':
                pending(title, message, options)
                break
            default:
                info(title, message, options)
        }
    }

    return {
        success,
        error,
        info,
        warning,
        important,
        critical,
        clear,
        pending,
        show
    }
}