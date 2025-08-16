import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

// Import tailwindcss
import './assets/css/tailwind.css'

// PrimeVue Core
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'

// Componentes básicos
import Button from 'primevue/button'
import Card from 'primevue/card'
import Toast from 'primevue/toast'
import ProgressSpinner from 'primevue/progressspinner'
import Toolbar from 'primevue/toolbar'
import Dropdown from 'primevue/dropdown'
import Tooltip from 'primevue/tooltip'

// SIN IMPORTS DE CSS PRIMEVUE - Como admin-frontend

const app = createApp(App)

// Configurar Pinia y router
app.use(createPinia())
app.use(router)

// Configurar PrimeVue
app.use(PrimeVue)
app.use(ToastService)

// Registrar componentes
app.component('Button', Button)
app.component('Card', Card)
app.component('Toast', Toast)
app.component('ProgressSpinner', ProgressSpinner)
app.component('Toolbar', Toolbar)
app.component('Dropdown', Dropdown)

// Directiva tooltip
app.directive('tooltip', Tooltip)

app.mount('#app')