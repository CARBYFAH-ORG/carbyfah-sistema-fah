// C:\FAH\services\fah-personal-service\frontend\src\main.js

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

// Import tailwindcss
import './assets/css/tailwind.css'
import './assets/css/toast-styles.css'

// PrimeVue Core
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'

// Componentes básicos
import Button from 'primevue/button'
import Card from 'primevue/card'
import Toast from 'primevue/toast'
import ProgressSpinner from 'primevue/progressspinner'
import ProgressBar from 'primevue/progressbar'
import Toolbar from 'primevue/toolbar'
import Dropdown from 'primevue/dropdown'
import Tooltip from 'primevue/tooltip'

// Componentes para formularios
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Calendar from 'primevue/calendar'
import Checkbox from 'primevue/checkbox'

// Componentes para tablas y datos
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import ConfirmDialog from 'primevue/confirmdialog'

// Componentes para navegación
import Breadcrumb from 'primevue/breadcrumb'
import Menu from 'primevue/menu'

// Stores
import { useNivelesStore } from './stores/nivelesStore'

const app = createApp(App)

// Configurar Pinia
const pinia = createPinia()
app.use(pinia)

// Configurar router
app.use(router)

// Configurar PrimeVue
app.use(PrimeVue, {
    ripple: true,
    inputStyle: 'outlined'
})
app.use(ToastService)
app.use(ConfirmationService)

// Registrar componentes básicos
app.component('Button', Button)
app.component('Card', Card)
app.component('Toast', Toast)
app.component('ProgressSpinner', ProgressSpinner)
app.component('ProgressBar', ProgressBar)
app.component('Toolbar', Toolbar)
app.component('Dropdown', Dropdown)

// Registrar componentes de formularios
app.component('InputText', InputText)
app.component('Textarea', Textarea)
app.component('Calendar', Calendar)
app.component('Checkbox', Checkbox)

// Registrar componentes de tablas
app.component('DataTable', DataTable)
app.component('Column', Column)
app.component('Dialog', Dialog)
app.component('ConfirmDialog', ConfirmDialog)

// Registrar componentes de navegación
app.component('Breadcrumb', Breadcrumb)
app.component('Menu', Menu)

// Directiva tooltip
app.directive('tooltip', Tooltip)

// Inicializar sistema de niveles
const initializeApp = async () => {
    try {
        console.log('🚀 Inicializando FA-1 Personal Service...')

        // Cargar configuración de usuario y niveles
        const nivelesStore = useNivelesStore()
        await nivelesStore.cargarUsuarioActual()

        console.log('✅ Sistema de niveles inicializado:', {
            usuario: nivelesStore.usuarioActual?.nombre,
            nivel: nivelesStore.nivelAcceso,
            alcance: nivelesStore.descripcionAlcance
        })

        // Montar la aplicación
        app.mount('#app')

        console.log('✅ FA-1 Personal Service iniciado exitosamente')

    } catch (error) {
        console.error('❌ Error inicializando aplicación:', error)

        // En caso de error, montar con configuración básica
        app.mount('#app')

        // Mostrar error al usuario
        setTimeout(() => {
            const toast = app.config.globalProperties.$toast
            if (toast) {
                toast.add({
                    severity: 'warn',
                    summary: 'Advertencia de Sistema',
                    detail: 'Sistema iniciado con configuración limitada. Recargue la página.',
                    life: 5000
                })
            }
        }, 1000)
    }
}

// Configurar manejo global de errores
app.config.errorHandler = (error, instance, info) => {
    console.error('Error global capturado:', error)
    console.error('Información del componente:', info)

    // En producción, aquí se enviaría el error a un servicio de monitoreo
}

// Configurar propiedades globales útiles
app.config.globalProperties.$log = (mensaje, datos = {}) => {
    const nivelesStore = useNivelesStore()
    console.log(`[${nivelesStore.nivelAcceso || 'SYSTEM'}] ${mensaje}`, datos)
}

// Inicializar aplicación
initializeApp()