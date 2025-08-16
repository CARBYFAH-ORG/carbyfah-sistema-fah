import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

// ====================================
// 📍 ESTILOS EN ORDEN CORRECTO
// ====================================
// 1. PRIMERO: Variables base (NUEVA UBICACIÓN)
import './styles/base/variables.css'

// 2. SEGUNDO: Tailwind (utilidades)
import './assets/css/tailwind.css'

// 3. TERCERO: Toast styles específicos
import './assets/css/toast-styles.css'

// ====================================
// 🎨 PRIMEVUE CORE
// ====================================
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'

// ====================================
// 📦 COMPONENTES BÁSICOS EXISTENTES
// ====================================
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Card from 'primevue/card'
import Toast from 'primevue/toast'
import Menu from 'primevue/menu'
import Breadcrumb from 'primevue/breadcrumb'

// ====================================
// 📊 COMPONENTES PARA CATÁLOGOS EXISTENTES
// ====================================
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

// ====================================
// 🚀 NUEVOS COMPONENTES PARA SISTEMA DINÁMICO
// ====================================
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import Calendar from 'primevue/calendar'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import ConfirmDialog from 'primevue/confirmdialog'

// ====================================
// 🔍 COMPONENTES PARA AUTOCOMPLETADO
// ====================================
import AutoComplete from 'primevue/autocomplete'
import Select from 'primevue/select'

// ====================================
// 🔧 COMPONENTES ADICIONALES QUE PODRÍAS NECESITAR
// ====================================
import InputSwitch from 'primevue/inputswitch'

const app = createApp(App)

// ====================================
// ⚙️ CONFIGURAR SERVICIOS
// ====================================
// Configurar Pinia (store)
app.use(createPinia())

// Configurar router
app.use(router)

// Configurar PrimeVue
app.use(PrimeVue)
app.use(ToastService)
app.use(ConfirmationService)

// ====================================
// 📦 REGISTRAR COMPONENTES BÁSICOS EXISTENTES
// ====================================
app.component('Button', Button)
app.component('InputText', InputText)
app.component('Password', Password)
app.component('Card', Card)
app.component('Toast', Toast)
app.component('Menu', Menu)
app.component('Breadcrumb', Breadcrumb)

// ====================================
// 📊 REGISTRAR COMPONENTES PARA CATÁLOGOS EXISTENTES
// ====================================
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

// ====================================
// 🚀 REGISTRAR NUEVOS COMPONENTES PARA SISTEMA DINÁMICO
// ====================================
app.component('InputNumber', InputNumber)
app.component('Textarea', Textarea)
app.component('Checkbox', Checkbox)
app.component('Calendar', Calendar)
app.component('Message', Message)
app.component('ProgressSpinner', ProgressSpinner)
app.component('ConfirmDialog', ConfirmDialog)

// ====================================
// 🔍 REGISTRAR COMPONENTES PARA AUTOCOMPLETADO
// ====================================
app.component('AutoComplete', AutoComplete)
app.component('Select', Select)

// ====================================
// 🔧 REGISTRAR COMPONENTES ADICIONALES
// ====================================
app.component('InputSwitch', InputSwitch)

// ====================================
// 📍 DIRECTIVAS
// ====================================
app.directive('tooltip', Tooltip)

app.mount('#app')