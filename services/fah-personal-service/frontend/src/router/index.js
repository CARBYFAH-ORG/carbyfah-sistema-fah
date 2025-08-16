import { createRouter, createWebHistory } from 'vue-router'
import DashboardLayout from '../components/layout/DashboardLayout.vue'
import LoginView from '../views/LoginView.vue'
import DashboardPersonalView from '../views/DashboardPersonalView.vue'
import GestionPersonalView from '../views/GestionPersonalView.vue'
import OrganigramasView from '../views/OrganigramasView.vue'
import ReportesPersonalView from '../views/ReportesPersonalView.vue'

const routes = [
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { requiresAuth: false }
    },
    {
        path: '/',
        component: DashboardLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                redirect: '/dashboard'
            },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: DashboardPersonalView,
                meta: {
                    title: 'Dashboard Personal FA-1 - Sistema FAH',
                    breadcrumb: 'Dashboard'
                }
            },
            {
                path: 'gestion-personal',
                name: 'gestion-personal',
                component: GestionPersonalView,
                meta: {
                    title: 'Gestión de Personal - FA-1 Sistema FAH',
                    permissions: ['personal.view', 'fa1.access'],
                    breadcrumb: 'Gestión Personal'
                }
            },
            {
                path: 'organigramas',
                name: 'organigramas',
                component: OrganigramasView,
                meta: {
                    title: 'Organigramas - FA-1 Sistema FAH',
                    permissions: ['organigramas.view', 'fa1.access'],
                    breadcrumb: 'Organigramas'
                }
            },
            {
                path: 'reportes',
                name: 'reportes',
                component: ReportesPersonalView,
                meta: {
                    title: 'Reportes Personal - FA-1 Sistema FAH',
                    permissions: ['reportes.view', 'fa1.access'],
                    breadcrumb: 'Reportes'
                }
            }
        ]
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Guard de autenticación 
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('fah_personal_token')
    if (to.meta.requiresAuth && !token) {
        next('/login')
    } else if (to.path === '/login' && token) {
        next('/dashboard')
    } else {
        next()
    }
})

export default router