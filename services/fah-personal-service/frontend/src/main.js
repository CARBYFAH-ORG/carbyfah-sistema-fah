import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

// Estilos en orden correcto
import './styles/base/variables.css'
import './assets/css/tailwind.css'
import './assets/css/toast-styles.css'

// PrimeVue Core
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'

// Componentes básicos
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Card from 'primevue/card'
import Toast from 'primevue/toast'
import Menu from 'primevue/menu'
import Breadcrumb from 'primevue/breadcrumb'

// Componentes para tablas y datos
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

// Componentes para formularios
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

// Componentes adicionales
import InputSwitch from 'primevue/inputswitch'
import FileUpload from 'primevue/fileupload'
import Avatar from 'primevue/avatar'
import Sidebar from 'primevue/sidebar'

const app = createApp(App)

// Configurar servicios
app.use(createPinia())
app.use(router)
app.use(PrimeVue)
app.use(ToastService)
app.use(ConfirmationService)

// Registrar componentes básicos
app.component('Button', Button)
app.component('InputText', InputText)
app.component('Password', Password)
app.component('Card', Card)
app.component('Toast', Toast)
app.component('Menu', Menu)
app.component('Breadcrumb', Breadcrumb)

// Registrar componentes para tablas
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

// Registrar componentes de formularios
app.component('InputNumber', InputNumber)
app.component('Textarea', Textarea)
app.component('Checkbox', Checkbox)
app.component('Calendar', Calendar)
app.component('Message', Message)
app.component('ProgressSpinner', ProgressSpinner)
app.component('ConfirmDialog', ConfirmDialog)

// Registrar componentes de autocompletado
app.component('AutoComplete', AutoComplete)
app.component('Select', Select)

// Registrar componentes adicionales
app.component('InputSwitch', InputSwitch)
app.component('FileUpload', FileUpload)
app.component('Avatar', Avatar)
app.component('Sidebar', Sidebar)

// Directivas
app.directive('tooltip', Tooltip)

app.mount('#app')