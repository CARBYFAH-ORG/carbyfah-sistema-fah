// C:\FAH\services\fah-personal-service\frontend\src\router\index.js

import { createRouter, createWebHistory } from 'vue-router'
import { useNivelesStore } from '@/stores/nivelesStore'
import { NIVELES_ACCESO } from '@/config/nivelesJerarquicos'

// Layout principal
import DashboardLayout from '../components/layout/DashboardLayout.vue'

// Views principales
import DashboardPersonalView from '../views/DashboardPersonalView.vue'

// Views FA-1 (Nivel Fuerza)
import FA1FuerzaView from '../views/FA1FuerzaView.vue'

// Views S-1 (Nivel Unidad/Base)
import S1UnidadView from '../views/S1UnidadView.vue'

// Views Sección (Nivel Operativo)
import SeccionView from '../views/SeccionView.vue'

// View Organigrama (existente)
import OrganigramaView from '../views/OrganigramaView.vue'

const routes = [
    // Layout principal con rutas protegidas
    {
        path: '/',
        component: DashboardLayout,
        meta: { requiresAuth: true },
        children: [
            // Redirect al dashboard apropiado según nivel
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
                    breadcrumb: 'Dashboard Personal',
                    nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1, NIVELES_ACCESO.SECCION, NIVELES_ACCESO.OPERADOR]
                }
            },

            // ================================
            // FA-1: NIVEL FUERZA AÉREA (ESTRATÉGICO)
            // ================================
            {
                path: 'fa1-fuerza',
                name: 'fa1-fuerza',
                component: FA1FuerzaView,
                meta: {
                    title: 'FA-1 Fuerza Aérea - Personal FAH',
                    breadcrumb: 'FA-1 Fuerza',
                    nivelRequerido: NIVELES_ACCESO.FA1
                },
                children: [
                    // Dashboard FA-1
                    {
                        path: '',
                        redirect: 'dashboard'
                    },
                    {
                        path: 'dashboard',
                        name: 'fa1-dashboard',
                        component: () => import('../components/modulos/fa1-fuerza/DashboardFA1.vue'),
                        meta: {
                            breadcrumb: 'Dashboard FA-1',
                            title: 'Dashboard Estratégico FA-1'
                        }
                    },

                    // Personal de toda la Fuerza
                    {
                        path: 'personal',
                        name: 'fa1-personal',
                        component: () => import('../components/modulos/fa1-fuerza/PersonalFAH.vue'),
                        meta: {
                            breadcrumb: 'Personal FAH',
                            title: 'Personal de la Fuerza Aérea'
                        },
                        children: [
                            {
                                path: '',
                                name: 'fa1-personal-listado',
                                component: () => import('../components/modulos/fa1-fuerza/PersonalListado.vue')
                            },
                            {
                                path: 'nuevo',
                                name: 'fa1-personal-nuevo',
                                component: () => import('../components/modulos/fa1-fuerza/PersonalFormulario.vue'),
                                meta: {
                                    breadcrumb: 'Nuevo Personal',
                                    accion: 'crear'
                                }
                            },
                            {
                                path: ':id',
                                name: 'fa1-personal-detalle',
                                component: () => import('../components/modulos/fa1-fuerza/PersonalDetalle.vue'),
                                meta: { breadcrumb: 'Detalle Personal' }
                            },
                            {
                                path: ':id/editar',
                                name: 'fa1-personal-editar',
                                component: () => import('../components/modulos/fa1-fuerza/PersonalFormulario.vue'),
                                meta: {
                                    breadcrumb: 'Editar Personal',
                                    accion: 'editar'
                                }
                            }
                        ]
                    },

                    // Estadísticas de la Fuerza
                    {
                        path: 'estadisticas',
                        name: 'fa1-estadisticas',
                        component: () => import('../components/modulos/fa1-fuerza/EstadisticasFAH.vue'),
                        meta: {
                            breadcrumb: 'Estadísticas FAH',
                            title: 'Estadísticas Estratégicas'
                        }
                    },

                    // Reportes de la Fuerza
                    {
                        path: 'reportes',
                        name: 'fa1-reportes',
                        component: () => import('../components/modulos/fa1-fuerza/ReportesFAH.vue'),
                        meta: {
                            breadcrumb: 'Reportes FAH',
                            title: 'Reportes Estratégicos'
                        }
                    },

                    // Administración de Personal FA-1
                    {
                        path: 'administracion',
                        name: 'fa1-administracion',
                        component: () => import('../components/modulos/fa1-fuerza/AdministracionPersonal.vue'),
                        meta: {
                            breadcrumb: 'Administración Personal',
                            title: 'Administración de Personal FA-1'
                        },
                        children: [
                            {
                                path: 'datos-personales',
                                name: 'fa1-datos-personales',
                                component: () => import('../components/modulos/fa1-fuerza/DatosPersonales.vue'),
                                meta: { breadcrumb: 'Datos Personales' }
                            },
                            {
                                path: 'perfiles-militares',
                                name: 'fa1-perfiles-militares',
                                component: () => import('../components/modulos/fa1-fuerza/PerfilesMilitares.vue'),
                                meta: { breadcrumb: 'Perfiles Militares' }
                            },
                            {
                                path: 'asignaciones',
                                name: 'fa1-asignaciones',
                                component: () => import('../components/modulos/fa1-fuerza/AsignacionesActuales.vue'),
                                meta: { breadcrumb: 'Asignaciones Actuales' }
                            },
                            {
                                path: 'historiales',
                                name: 'fa1-historiales',
                                component: () => import('../components/modulos/fa1-fuerza/HistorialesCargos.vue'),
                                meta: { breadcrumb: 'Historiales de Cargos' }
                            }
                        ]
                    },

                    // Mantenimiento del Efectivo
                    {
                        path: 'mantenimiento-efectivo',
                        name: 'fa1-mantenimiento-efectivo',
                        component: () => import('../components/modulos/fa1-fuerza/MantenimientoEfectivo.vue'),
                        meta: {
                            breadcrumb: 'Mantenimiento Efectivo',
                            title: 'Control de Efectivos FA-1'
                        }
                    },

                    // Disciplina Ley y Orden
                    {
                        path: 'disciplina',
                        name: 'fa1-disciplina',
                        component: () => import('../components/modulos/fa1-fuerza/DisciplinaLeyOrden.vue'),
                        meta: {
                            breadcrumb: 'Disciplina y Orden',
                            title: 'Disciplina Ley y Orden FA-1'
                        }
                    },

                    // Servicios de Personal
                    {
                        path: 'servicios',
                        name: 'fa1-servicios',
                        component: () => import('../components/modulos/fa1-fuerza/ServiciosPersonal.vue'),
                        meta: {
                            breadcrumb: 'Servicios Personal',
                            title: 'Servicios de Personal FA-1'
                        }
                    },

                    // Bienestar de Personal
                    {
                        path: 'bienestar',
                        name: 'fa1-bienestar',
                        component: () => import('../components/modulos/fa1-fuerza/BienestarPersonal.vue'),
                        meta: {
                            breadcrumb: 'Bienestar Personal',
                            title: 'Bienestar de Personal FA-1'
                        }
                    }
                ]
            },

            // ================================
            // S-1: NIVEL UNIDAD/BASE (TÁCTICO)
            // ================================
            {
                path: 's1-unidad',
                name: 's1-unidad',
                component: S1UnidadView,
                meta: {
                    title: 'S-1 Unidad - Personal Base',
                    breadcrumb: 'S-1 Unidad',
                    nivelRequerido: [NIVELES_ACCESO.S1, NIVELES_ACCESO.SECCION]
                },
                children: [
                    // Dashboard S-1
                    {
                        path: '',
                        redirect: 'dashboard'
                    },
                    {
                        path: 'dashboard',
                        name: 's1-dashboard',
                        component: () => import('../components/modulos/s1-unidad/DashboardS1.vue'),
                        meta: {
                            breadcrumb: 'Dashboard S-1',
                            title: 'Dashboard Táctico S-1'
                        }
                    },

                    // Personal de la Base
                    {
                        path: 'personal',
                        name: 's1-personal',
                        component: () => import('../components/modulos/s1-unidad/PersonalBase.vue'),
                        meta: {
                            breadcrumb: 'Personal Base',
                            title: 'Personal de la Unidad'
                        },
                        children: [
                            {
                                path: '',
                                name: 's1-personal-listado',
                                component: () => import('../components/modulos/s1-unidad/PersonalListadoBase.vue')
                            },
                            {
                                path: ':id',
                                name: 's1-personal-detalle',
                                component: () => import('../components/modulos/s1-unidad/PersonalDetalleBase.vue'),
                                meta: { breadcrumb: 'Detalle Personal' }
                            }
                        ]
                    },

                    // Estadísticas de la Base
                    {
                        path: 'estadisticas',
                        name: 's1-estadisticas',
                        component: () => import('../components/modulos/s1-unidad/EstadisticasBase.vue'),
                        meta: {
                            breadcrumb: 'Estadísticas Base',
                            title: 'Estadísticas de la Unidad'
                        }
                    },

                    // Reportes de la Base
                    {
                        path: 'reportes',
                        name: 's1-reportes',
                        component: () => import('../components/modulos/s1-unidad/ReportesBase.vue'),
                        meta: {
                            breadcrumb: 'Reportes Base',
                            title: 'Reportes de la Unidad'
                        }
                    },

                    // Secciones de la Base
                    {
                        path: 'secciones',
                        name: 's1-secciones',
                        component: () => import('../components/modulos/s1-unidad/SeccionesBase.vue'),
                        meta: {
                            breadcrumb: 'Secciones',
                            title: 'Secciones de la Base'
                        }
                    },

                    // Servicios de la Base
                    {
                        path: 'servicios',
                        name: 's1-servicios-base',
                        component: () => import('../components/modulos/s1-unidad/ServiciosBase.vue'),
                        meta: {
                            breadcrumb: 'Servicios Base',
                            title: 'Servicios de la Unidad'
                        }
                    }
                ]
            },

            // ================================
            // SECCIÓN: NIVEL OPERATIVO
            // ================================
            {
                path: 'seccion',
                name: 'seccion-dashboard',
                component: SeccionView,
                meta: {
                    title: 'Mi Sección - Personal',
                    breadcrumb: 'Mi Sección',
                    nivelRequerido: NIVELES_ACCESO.SECCION
                },
                children: [
                    {
                        path: '',
                        redirect: 'dashboard'
                    },
                    {
                        path: 'dashboard',
                        name: 'seccion-dashboard-main',
                        component: () => import('../components/modulos/seccion/DashboardSeccion.vue'),
                        meta: {
                            breadcrumb: 'Dashboard Sección',
                            title: 'Dashboard Operativo'
                        }
                    },
                    {
                        path: 'personal',
                        name: 'seccion-personal',
                        component: () => import('../components/modulos/seccion/PersonalSeccion.vue'),
                        meta: {
                            breadcrumb: 'Personal Sección',
                            title: 'Personal de Mi Sección'
                        }
                    },
                    {
                        path: 'operaciones',
                        name: 'seccion-operaciones',
                        component: () => import('../components/modulos/seccion/OperacionesSeccion.vue'),
                        meta: {
                            breadcrumb: 'Operaciones',
                            title: 'Operaciones de la Sección'
                        }
                    },
                    {
                        path: 'reportes',
                        name: 'seccion-reportes',
                        component: () => import('../components/modulos/seccion/ReportesSeccion.vue'),
                        meta: {
                            breadcrumb: 'Reportes Sección',
                            title: 'Reportes Operativos'
                        }
                    }
                ]
            },

            // ================================
            // JEFE FA-1: FUNCIONES EJECUTIVAS
            // ================================
            {
                path: 'jefe-fa1',
                name: 'jefe-fa1',
                component: () => import('../views/JefeFA1View.vue'),
                meta: {
                    title: 'Jefe FA-1 - Vista Ejecutiva',
                    breadcrumb: 'Jefe FA-1',
                    nivelRequerido: NIVELES_ACCESO.FA1
                },
                children: [
                    {
                        path: '',
                        redirect: 'situacion'
                    },
                    {
                        path: 'situacion',
                        name: 'jefe-situacion-general',
                        component: () => import('../components/modulos/jefe-fa1/SituacionGeneral.vue'),
                        meta: {
                            breadcrumb: 'Situación General',
                            title: 'Situación General del Personal'
                        }
                    },
                    {
                        path: 'decisiones',
                        name: 'jefe-decisiones-pendientes',
                        component: () => import('../components/modulos/jefe-fa1/DecisionesPendientes.vue'),
                        meta: {
                            breadcrumb: 'Decisiones Pendientes',
                            title: 'Decisiones que Requieren Aprobación'
                        }
                    },
                    {
                        path: 'war-room',
                        name: 'jefe-war-room',
                        component: () => import('../components/modulos/jefe-fa1/WarRoom.vue'),
                        meta: {
                            breadcrumb: 'War Room',
                            title: 'Centro de Control y Crisis'
                        }
                    },
                    {
                        path: 'reportes-ejecutivos',
                        name: 'jefe-reportes-ejecutivos',
                        component: () => import('../components/modulos/jefe-fa1/ReportesEjecutivos.vue'),
                        meta: {
                            breadcrumb: 'Reportes Ejecutivos',
                            title: 'Reportes para la Comandancia'
                        }
                    }
                ]
            },

            // ================================
            // CARBYCHAT: SISTEMA DE MENSAJERÍA
            // ================================
            {
                path: 'carbychat',
                name: 'carbychat',
                component: () => import('../views/CarbychatView.vue'),
                meta: {
                    title: 'CARBYCHAT - Sistema de Comunicaciones',
                    breadcrumb: 'CARBYCHAT',
                    nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1, NIVELES_ACCESO.SECCION, NIVELES_ACCESO.OPERADOR]
                },
                children: [
                    {
                        path: '',
                        redirect: 'mensajes'
                    },
                    {
                        path: 'mensajes',
                        name: 'carbychat-mensajes',
                        component: () => import('../components/modulos/carbychat/MensajeriaOficial.vue'),
                        meta: { breadcrumb: 'Mensajes' }
                    },
                    {
                        path: 'grupos',
                        name: 'carbychat-grupos',
                        component: () => import('../components/modulos/carbychat/GruposOrganizacionales.vue'),
                        meta: { breadcrumb: 'Grupos' }
                    },
                    {
                        path: 'reportes-automaticos',
                        name: 'carbychat-reportes',
                        component: () => import('../components/modulos/carbychat/ReportesAutomaticos.vue'),
                        meta: { breadcrumb: 'Reportes Automáticos' }
                    },
                    {
                        path: 'configuracion',
                        name: 'carbychat-config',
                        component: () => import('../components/modulos/carbychat/ConfiguracionChat.vue'),
                        meta: {
                            breadcrumb: 'Configuración',
                            nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1]
                        }
                    }
                ]
            },

            // ================================
            // SOLICITUDES: WORKFLOW ENGINE
            // ================================
            {
                path: 'solicitudes',
                name: 'solicitudes',
                component: () => import('../views/SolicitudesView.vue'),
                meta: {
                    title: 'Sistema de Solicitudes',
                    breadcrumb: 'Solicitudes',
                    nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1, NIVELES_ACCESO.SECCION, NIVELES_ACCESO.OPERADOR]
                },
                children: [
                    {
                        path: '',
                        redirect: 'mis-solicitudes'
                    },
                    {
                        path: 'nueva',
                        name: 'solicitudes-nueva',
                        component: () => import('../components/modulos/solicitudes/NuevaSolicitud.vue'),
                        meta: { breadcrumb: 'Nueva Solicitud' }
                    },
                    {
                        path: 'mis-solicitudes',
                        name: 'solicitudes-mis',
                        component: () => import('../components/modulos/solicitudes/MisSolicitudes.vue'),
                        meta: { breadcrumb: 'Mis Solicitudes' }
                    },
                    {
                        path: 'bandeja-aprobaciones',
                        name: 'solicitudes-aprobaciones',
                        component: () => import('../components/modulos/solicitudes/BandejaAprobaciones.vue'),
                        meta: {
                            breadcrumb: 'Bandeja de Aprobaciones',
                            nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1, NIVELES_ACCESO.SECCION]
                        }
                    },
                    {
                        path: 'estadisticas',
                        name: 'solicitudes-estadisticas',
                        component: () => import('../components/modulos/solicitudes/EstadisticasSolicitudes.vue'),
                        meta: {
                            breadcrumb: 'Estadísticas',
                            nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1]
                        }
                    },
                    {
                        path: ':id',
                        name: 'solicitudes-detalle',
                        component: () => import('../components/modulos/solicitudes/DetalleSolicitud.vue'),
                        meta: { breadcrumb: 'Detalle Solicitud' }
                    }
                ]
            },

            // ================================
            // ORGANIGRAMA (EXISTENTE MEJORADO)
            // ================================
            {
                path: 'organigrama',
                name: 'organigrama',
                component: OrganigramaView,
                meta: {
                    title: 'Organigrama FAH - Personal',
                    breadcrumb: 'Organigrama',
                    nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1, NIVELES_ACCESO.SECCION]
                }
            },

            // ================================
            // ADMIN: CONFIGURACIÓN DEL SISTEMA
            // ================================
            {
                path: 'admin',
                name: 'admin',
                component: () => import('../views/AdminView.vue'),
                meta: {
                    title: 'Administración del Sistema',
                    breadcrumb: 'Administración',
                    nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1]
                },
                children: [
                    {
                        path: '',
                        redirect: 'usuarios'
                    },
                    {
                        path: 'usuarios',
                        name: 'admin-usuarios',
                        component: () => import('../components/modulos/admin/RolesUsuarios.vue'),
                        meta: {
                            breadcrumb: 'Roles y Usuarios',
                            title: 'Gestión de Usuarios del Sistema'
                        }
                    },
                    {
                        path: 'permisos',
                        name: 'admin-permisos',
                        component: () => import('../components/modulos/admin/PermisosRoles.vue'),
                        meta: {
                            breadcrumb: 'Permisos y Roles',
                            title: 'Configuración de Permisos'
                        }
                    },
                    {
                        path: 'diagrama-fa1',
                        name: 'admin-diagrama',
                        component: () => import('../components/modulos/admin/DiagramaFA1.vue'),
                        meta: {
                            breadcrumb: 'Diagrama FA-1',
                            title: 'Organigrama Interactivo FA-1'
                        }
                    },
                    {
                        path: 'configuracion',
                        name: 'admin-configuracion',
                        component: () => import('../components/modulos/admin/ConfiguracionSistema.vue'),
                        meta: {
                            breadcrumb: 'Configuración Sistema',
                            title: 'Configuración General'
                        }
                    }
                ]
            },

            // ================================
            // AYUDA Y SOPORTE
            // ================================
            {
                path: 'ayuda',
                name: 'ayuda',
                component: () => import('../views/AyudaView.vue'),
                meta: {
                    title: 'Centro de Ayuda',
                    breadcrumb: 'Ayuda',
                    nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1, NIVELES_ACCESO.SECCION, NIVELES_ACCESO.OPERADOR]
                }
            },

            // ================================
            // ACERCA DE
            // ================================
            {
                path: 'acerca-de',
                name: 'acerca-de',
                component: () => import('../views/AcercaDeView.vue'),
                meta: {
                    title: 'Acerca del Sistema',
                    breadcrumb: 'Acerca de',
                    nivelRequerido: [NIVELES_ACCESO.FA1, NIVELES_ACCESO.S1, NIVELES_ACCESO.SECCION, NIVELES_ACCESO.OPERADOR]
                }
            }
        ]
    },

    // ================================
    // RUTAS DE ERROR Y FALLBACK
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
        name: 'not-found',
        component: () => import('../views/NotFoundView.vue'),
        meta: {
            title: 'Página No Encontrada',
            requiresAuth: false
        }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        // Scroll al top en navegación nueva
        if (savedPosition) {
            return savedPosition
        } else {
            return { top: 0 }
        }
    }
})

// Guard de autenticación y niveles
router.beforeEach(async (to, from, next) => {
    try {
        // Verificar token compartido desde fah-admin-frontend
        const token = localStorage.getItem('fah_token')

        if (!token && to.meta.requiresAuth !== false) {
            console.log('Token no encontrado, redirigiendo al admin-frontend')
            window.location.href = 'http://localhost:5173/login'
            return
        }

        // Cargar datos de usuario si no están cargados
        const nivelesStore = useNivelesStore()
        if (!nivelesStore.usuarioActual && token) {
            console.log('Cargando datos de usuario...')
            try {
                await nivelesStore.cargarUsuarioActual()
            } catch (error) {
                console.error('Error cargando usuario:', error)
                localStorage.removeItem('fah_token')
                window.location.href = 'http://localhost:5173/login'
                return
            }
        }

        // Verificar permisos por ruta
        if (to.meta.nivelRequerido && nivelesStore.usuarioActual) {
            const nivelesPermitidos = Array.isArray(to.meta.nivelRequerido)
                ? to.meta.nivelRequerido
                : [to.meta.nivelRequerido]

            if (!nivelesPermitidos.includes(nivelesStore.nivelAcceso)) {
                console.warn(`Acceso denegado a ${to.path} para nivel ${nivelesStore.nivelAcceso}`)
                next('/acceso-denegado')
                return
            }
        }

        // Redirigir a dashboard apropiado según nivel si va a root
        if ((to.path === '/' || to.path === '/dashboard') && nivelesStore.usuarioActual) {
            const dashboardPorNivel = {
                [NIVELES_ACCESO.FA1]: '/dashboard',
                [NIVELES_ACCESO.S1]: '/s1-unidad/dashboard',
                [NIVELES_ACCESO.SECCION]: '/seccion/dashboard',
                [NIVELES_ACCESO.OPERADOR]: '/dashboard'
            }

            const dashboardDestino = dashboardPorNivel[nivelesStore.nivelAcceso] || '/dashboard'

            if (to.path !== dashboardDestino) {
                next(dashboardDestino)
                return
            }
        }

        next()

    } catch (error) {
        console.error('Error en router guard:', error)
        next('/acceso-denegado')
    }
})

// Guard para títulos de página
router.afterEach((to) => {
    // Establecer título de página
    if (to.meta.title) {
        document.title = `${to.meta.title} | FAH Personal Service`
    } else {
        document.title = 'FAH Personal Service'
    }

    // Log de navegación para auditoría
    const nivelesStore = useNivelesStore()
    if (nivelesStore.usuarioActual) {
        console.log(`[${nivelesStore.nivelAcceso}] Navegación a: ${to.path}`, {
            usuario: nivelesStore.usuarioActual.nombre,
            destino: to.name,
            titulo: to.meta.title
        })
    }
})

export default router