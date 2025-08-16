import { createRouter, createWebHistory } from 'vue-router'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import CatalogosView from '../views/CatalogosView.vue'
import EstructuraOrganizacionalView from '../views/EstructuraOrganizacionalView.vue'

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
                component: DashboardView
            },
            {
                path: 'catalogos',
                name: 'catalogos',
                component: CatalogosView,
                meta: {
                    title: 'Administrar Catálogos - Sistema FAH',
                    permissions: ['catalogos.view', 'eme.access']
                }
            },
            {
                path: 'estructura-organizacional',
                name: 'estructura-organizacional',
                component: EstructuraOrganizacionalView,
                meta: {
                    title: 'Estructura Organizacional - Sistema FAH',
                    permissions: ['organizacion.view', 'admin.access']
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
    const token = localStorage.getItem('fah_token')
    if (to.meta.requiresAuth && !token) {
        next('/login')
    } else if (to.path === '/login' && token) {
        next('/dashboard')
    } else {
        next()
    }
})

export default router