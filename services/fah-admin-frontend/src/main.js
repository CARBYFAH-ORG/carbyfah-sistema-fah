// services\fah-admin-frontend\src\main.js

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

// Estilos en orden correcto
// 1. Variables base
import './styles/base/variables.css'

// 2. Tailwind (utilidades)
import './assets/css/tailwind.css'

// 3. Toast styles especificos
import './assets/css/toast-styles.css'

// PrimeVue core
import PrimeVue from 'primevue/config'
import ConfirmationService from 'primevue/confirmationservice'

// Componentes basicos existentes
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Card from 'primevue/card'
import Menu from 'primevue/menu'
import Breadcrumb from 'primevue/breadcrumb'

// Componentes para catalogos existentes
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Toolbar from 'primevue/toolbar'
import Badge from 'primevue/badge'
import Chip from 'primevue/chip'
import Tag from 'primevue/tag'
import ProgressBar from 'primevue/progressbar'
import Tooltip from 'primevue/tooltip'
import Dropdown from 'primevue/dropdown'
import Image from 'primevue/image'

// Nuevos componentes para sistema dinamico
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import Calendar from 'primevue/calendar'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import ConfirmDialog from 'primevue/confirmdialog'

// Componentes para autocompletado
import AutoComplete from 'primevue/autocomplete'
import Select from 'primevue/select'

// Componentes adicionales que podrias necesitar
import InputSwitch from 'primevue/inputswitch'

const app = createApp(App)

// Configurar servicios
// Configurar Pinia (store)
app.use(createPinia())

// Configurar router
app.use(router)

// Configurar PrimeVue
app.use(PrimeVue)
app.use(ConfirmationService)

// Registrar componentes basicos existentes
app.component('Button', Button)
app.component('InputText', InputText)
app.component('Password', Password)
app.component('Card', Card)
app.component('Menu', Menu)
app.component('Breadcrumb', Breadcrumb)

// Registrar componentes para catalogos existentes
app.component('DataTable', DataTable)
app.component('Column', Column)
app.component('Dialog', Dialog)
app.component('Toolbar', Toolbar)
app.component('Badge', Badge)
app.component('Chip', Chip)
app.component('Tag', Tag)
app.component('ProgressBar', ProgressBar)
app.component('Dropdown', Dropdown)
app.component('Image', Image)

// Registrar nuevos componentes para sistema dinamico
app.component('InputNumber', InputNumber)
app.component('Textarea', Textarea)
app.component('Checkbox', Checkbox)
app.component('Calendar', Calendar)
app.component('Message', Message)
app.component('ProgressSpinner', ProgressSpinner)
app.component('ConfirmDialog', ConfirmDialog)

// Registrar componentes para autocompletado
app.component('AutoComplete', AutoComplete)
app.component('Select', Select)

// Registrar componentes adicionales
app.component('InputSwitch', InputSwitch)

// Directivas
app.directive('tooltip', Tooltip)

app.mount('#app')