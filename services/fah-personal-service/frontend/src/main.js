import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

// Estilos en orden correcto
import './styles/base/variables.css'
import './styles/base/global.css'
import './styles/themes/personal-theme.css'
import './assets/css/tailwind.css'

// PrimeVue core
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'

// Componentes básicos
import Button from 'primevue/button'
import Card from 'primevue/card'
import Toast from 'primevue/toast'
import ProgressSpinner from 'primevue/progressspinner'
import Toolbar from 'primevue/toolbar'
import Dropdown from 'primevue/dropdown'
import Tooltip from 'primevue/tooltip'

// Componentes para tablas y formularios
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Badge from 'primevue/badge'
import Chip from 'primevue/chip'
import Tag from 'primevue/tag'
import ProgressBar from 'primevue/progressbar'
import Image from 'primevue/image'
import Menu from 'primevue/menu'
import Breadcrumb from 'primevue/breadcrumb'

// Componentes para formularios dinámicos
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import Calendar from 'primevue/calendar'
import Message from 'primevue/message'
import ConfirmDialog from 'primevue/confirmdialog'

// Componentes para autocompletado
import AutoComplete from 'primevue/autocomplete'
import Select from 'primevue/select'

// Componentes adicionales
import InputSwitch from 'primevue/inputswitch'

const app = createApp(App)

// Configurar servicios
app.use(createPinia())
app.use(router)
app.use(PrimeVue)
app.use(ToastService)
app.use(ConfirmationService)

// Registrar componentes básicos
app.component('Button', Button)
app.component('Card', Card)
app.component('Toast', Toast)
app.component('ProgressSpinner', ProgressSpinner)
app.component('Toolbar', Toolbar)
app.component('Dropdown', Dropdown)

// Registrar componentes para tablas y formularios
app.component('DataTable', DataTable)
app.component('Column', Column)
app.component('Dialog', Dialog)
app.component('Badge', Badge)
app.component('Chip', Chip)
app.component('Tag', Tag)
app.component('ProgressBar', ProgressBar)
app.component('Image', Image)
app.component('Menu', Menu)
app.component('Breadcrumb', Breadcrumb)

// Registrar componentes para formularios dinámicos
app.component('InputText', InputText)
app.component('Password', Password)
app.component('InputNumber', InputNumber)
app.component('Textarea', Textarea)
app.component('Checkbox', Checkbox)
app.component('Calendar', Calendar)
app.component('Message', Message)
app.component('ConfirmDialog', ConfirmDialog)

// Registrar componentes para autocompletado
app.component('AutoComplete', AutoComplete)
app.component('Select', Select)

// Registrar componentes adicionales
app.component('InputSwitch', InputSwitch)

// Directivas
app.directive('tooltip', Tooltip)

app.mount('#app')