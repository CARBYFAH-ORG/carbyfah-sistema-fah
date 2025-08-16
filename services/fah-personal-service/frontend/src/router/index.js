// C:\FAH\services\fah-personal-service\frontend\src\router\index.js

import { createRouter, createWebHistory } from 'vue-router'

// Layout principal
import DashboardLayout from '../components/layout/DashboardLayout.vue'

// Views principales
import DashboardPersonalView from '../views/DashboardPersonalView.vue'

// View Organigrama (existente)
import OrganigramaView from '../views/OrganigramaView.vue'

const routes = [
    // Layout principal con rutas protegidas
    {
        path: '/',
        component: DashboardLayout,
        meta: { requiresAuth: true },
        children: [
            // Redirect al dashboard
            {
                path: '',
                redirect: '/dashboard'
            },

            // ================================
            // DASHBOARD PRINCIPAL ADAPTATIVO
            // ================================
            {
                path: 'dashboard',
                name: 'dashboard-personal',
                component: DashboardPersonalView,
                meta: {
                    title: 'Dashboard Personal - FAH',
                    breadcrumb: 'Dashboard Personal'
                }
            },

            // ================================
            // ORGANIGRAMA (EXISTENTE)
            // ================================
            {
                path: 'organigrama',
                name: 'organigrama',
                component: OrganigramaView,
                meta: {
                    title: 'Organigrama FAH - Personal',
                    breadcrumb: 'Organigrama'
                }
            },

            // ================================
            // RUTAS BÁSICAS TEMPORALES
            // ================================
            {
                path: 'fa1-fuerza',
                name: 'fa1-fuerza',
                component: () => import('../views/FA1FuerzaView.vue'),
                meta: {
                    title: 'FA-1 Fuerza Aérea - Personal FAH',
                    breadcrumb: 'FA-1 Fuerza'
                }
            },

            {
                path: 's1-unidad',
                name: 's1-unidad',
                component: () => import('../views/S1UnidadView.vue'),
                meta: {
                    title: 'S-1 Unidad - Personal Base',
                    breadcrumb: 'S-1 Unidad'
                }
            },

            {
                path: 'seccion',
                name: 'seccion',
                component: () => import('../views/SeccionView.vue'),
                meta: {
                    title: 'Mi Sección - Personal',
                    breadcrumb: 'Mi Sección'
                }
            },

            // ================================
            // PLACEHOLDERS PARA DESARROLLO
            // ================================
            {
                path: 'carbychat',
                name: 'carbychat',
                component: () => import('../views/PlaceholderView.vue'),
                meta: {
                    title: 'CARBYCHAT - En Desarrollo',
                    breadcrumb: 'CARBYCHAT',
                    placeholder: 'Sistema de comunicaciones en desarrollo'
                }
            },

            {
                path: 'solicitudes',
                name: 'solicitudes',
                component: () => import('../views/PlaceholderView.vue'),
                meta: {
                    title: 'Solicitudes - En Desarrollo',
                    breadcrumb: 'Solicitudes',
                    placeholder: 'Sistema de solicitudes en desarrollo'
                }
            },

            {
                path: 'admin',
                name: 'admin',
                component: () => import('../views/PlaceholderView.vue'),
                meta: {
                    title: 'Administración - En Desarrollo',
                    breadcrumb: 'Admin',
                    placeholder: 'Panel de administración en desarrollo'
                }
            }
        ]
    },

    // ================================
    // RUTAS DE ERROR
    // ================================
    {
        path: '/acceso-denegado',
        name: 'acceso-denegado',
        component: () => import('../views/AccesoDenegadoView.vue'),
        meta: {
            title: 'Acceso Denegado',
            requiresAuth: false
        }
    },

    // Catch-all para rutas no encontradas
    {
        path: '/:pathMatch(.*)*',
        redirect: '/dashboard'
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        } else {
            return { top: 0 }
        }
    }
})

// Guard de autenticación TEMPORAL simplificado
router.beforeEach((to, from, next) => {
    console.log(`🚀 Navegando a: ${to.path}`)

    // Por ahora, permitir todas las rutas
    next()
})

// Guard para títulos de página
router.afterEach((to) => {
    if (to.meta.title) {
        document.title = `${to.meta.title} | FAH Personal Service`
    } else {
        document.title = 'FAH Personal Service'
    }

    console.log(`✅ Página cargada: ${to.path}`)
})

export default router