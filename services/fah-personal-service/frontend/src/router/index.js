import { createRouter, createWebHistory } from 'vue-router'
import OrganigramaView from '../views/OrganigramaView.vue'

const routes = [
    {
        path: '/',
        redirect: '/organigrama'
    },
    {
        path: '/organigrama',
        name: 'Organigrama',
        component: OrganigramaView
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router