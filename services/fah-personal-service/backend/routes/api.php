<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers existentes
use App\Http\Controllers\Api\OrganigramaController;

// Controllers nuevos del schema personal
use App\Http\Controllers\DatosPersonalesController;
use App\Http\Controllers\PerfilesMilitaresController;
use App\Http\Controllers\AsignacionesActualesController;
use App\Http\Controllers\UsuariosSistemaController;
use App\Http\Controllers\HistorialesCargosController;
use App\Http\Controllers\AsignacionRolesController;

/*
|--------------------------------------------------------------------------
| API Routes - FAH Personal Service
|--------------------------------------------------------------------------
|
| Rutas para el microservicio de Personal de la Fuerza Aérea Hondureña
| Incluye: Organigramas (existente) + Personal completo (nuevo)
| Arquitectura: RESTful API con recursos anidados y acciones específicas
| Versionado: Preparado para /api/v1
| Autenticación: Bearer Token con Sanctum
|
*/

// =====================================================
// RUTA DE USUARIO AUTENTICADO (SANCTUM)
// =====================================================

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// =====================================================
// RUTAS DE SALUD Y INFORMACIÓN DEL SERVICIO
// =====================================================

Route::get('/health', function () {
    return response()->json([
        'service' => 'fah-personal-service',
        'version' => '1.0.0',
        'status' => 'healthy',
        'timestamp' => now(),
        'environment' => app()->environment(),
        'databases' => [
            'connection' => 'active',
            'schemas' => ['personal', 'organizacion', 'catalogos']
        ],
        'features' => [
            'organigramas' => 'enabled',
            'personal_management' => 'enabled',
            'user_management' => 'enabled'
        ]
    ]);
});

Route::get('/info', function () {
    return response()->json([
        'service' => 'FAH Personal Service',
        'description' => 'Microservicio completo para gestión de personal militar y organigramas',
        'version' => '1.0.0',
        'schemas' => ['personal', 'organizacion', 'catalogos'],
        'modules' => [
            'organigramas' => 'Gestión de estructura organizacional FAH',
            'personal' => 'Gestión completa de personal militar',
            'usuarios' => 'Sistema de usuarios y autenticación',
            'reportes' => 'Reportes y estadísticas integradas'
        ],
        'endpoints' => [
            'organigramas' => '/api/organigramas',
            'datos_personales' => '/api/personal/datos-personales',
            'perfiles_militares' => '/api/personal/perfiles-militares',
            'asignaciones_actuales' => '/api/personal/asignaciones-actuales',
            'usuarios_sistema' => '/api/personal/usuarios-sistema',
            'historiales_cargos' => '/api/personal/historiales-cargos',
            'asignacion_roles' => '/api/personal/asignacion-roles'
        ]
    ]);
});

// =====================================================
// ORGANIGRAMAS FAH (EXISTENTE - MANTENIDO)
// =====================================================

Route::prefix('organigramas')->group(function () {
    Route::get('/estructura-fah', [OrganigramaController::class, 'obtenerEstructuraFAH']);
    Route::get('/unidad/{unidad_id}', [OrganigramaController::class, 'obtenerOrganigramaUnidad']);
    Route::get('/exportar', [OrganigramaController::class, 'exportarOrganigrama']);
});

// =====================================================
// PERSONAL FAH (NUEVO - SCHEMA PERSONAL COMPLETO)
// =====================================================

Route::prefix('personal')->group(function () {

    // =====================================================
    // 1. DATOS PERSONALES
    // =====================================================
    Route::prefix('datos-personales')->group(function () {
        // CRUD básico
        Route::get('/', [DatosPersonalesController::class, 'index']);
        Route::post('/', [DatosPersonalesController::class, 'store']);
        Route::get('/{id}', [DatosPersonalesController::class, 'show']);
        Route::put('/{id}', [DatosPersonalesController::class, 'update']);
        Route::delete('/{id}', [DatosPersonalesController::class, 'destroy']);

        // Rutas específicas
        Route::get('/por-identidad/{numeroIdentidad}', [DatosPersonalesController::class, 'porIdentidad']);
        Route::get('/estadisticas/generales', [DatosPersonalesController::class, 'estadisticas']);
    });

    // =====================================================
    // 2. PERFILES MILITARES
    // =====================================================
    Route::prefix('perfiles-militares')->group(function () {
        // CRUD básico
        Route::get('/', [PerfilesMilitaresController::class, 'index']);
        Route::post('/', [PerfilesMilitaresController::class, 'store']);
        Route::get('/{id}', [PerfilesMilitaresController::class, 'show']);
        Route::put('/{id}', [PerfilesMilitaresController::class, 'update']);
        Route::delete('/{id}', [PerfilesMilitaresController::class, 'destroy']);

        // Rutas específicas
        Route::get('/por-serie/{serieMilitar}', [PerfilesMilitaresController::class, 'porSerie']);
        Route::get('/disponibles/asignacion', [PerfilesMilitaresController::class, 'disponibles']);
        Route::get('/estadisticas/generales', [PerfilesMilitaresController::class, 'estadisticas']);

        // Acciones específicas
        Route::post('/{id}/retirar', [PerfilesMilitaresController::class, 'retirar']);
    });

    // =====================================================
    // 3. ASIGNACIONES ACTUALES
    // =====================================================
    Route::prefix('asignaciones-actuales')->group(function () {
        // CRUD básico
        Route::get('/', [AsignacionesActualesController::class, 'index']);
        Route::post('/', [AsignacionesActualesController::class, 'store']);
        Route::get('/{id}', [AsignacionesActualesController::class, 'show']);
        Route::put('/{id}', [AsignacionesActualesController::class, 'update']);
        Route::delete('/{id}', [AsignacionesActualesController::class, 'destroy']);

        // Rutas específicas
        Route::get('/por-unidad/{unidadId}', [AsignacionesActualesController::class, 'porUnidad']);
        Route::get('/por-vencer/alertas', [AsignacionesActualesController::class, 'porVencer']);
        Route::get('/estadisticas/generales', [AsignacionesActualesController::class, 'estadisticas']);

        // Acciones específicas
        Route::post('/{id}/finalizar', [AsignacionesActualesController::class, 'finalizar']);
        Route::post('/{id}/extender', [AsignacionesActualesController::class, 'extender']);
    });

    // =====================================================
    // 4. USUARIOS SISTEMA
    // =====================================================
    Route::prefix('usuarios-sistema')->group(function () {
        // CRUD básico
        Route::get('/', [UsuariosSistemaController::class, 'index']);
        Route::post('/', [UsuariosSistemaController::class, 'store']);
        Route::get('/{id}', [UsuariosSistemaController::class, 'show']);
        Route::put('/{id}', [UsuariosSistemaController::class, 'update']);
        Route::delete('/{id}', [UsuariosSistemaController::class, 'destroy']);

        // Rutas específicas
        Route::get('/estadisticas/generales', [UsuariosSistemaController::class, 'estadisticas']);

        // Acciones de seguridad
        Route::post('/{id}/cambiar-password', [UsuariosSistemaController::class, 'cambiarPassword']);
        Route::post('/{id}/restablecer-password', [UsuariosSistemaController::class, 'restablecerPassword']);
        Route::post('/{id}/bloquear', [UsuariosSistemaController::class, 'bloquear']);
        Route::post('/{id}/desbloquear', [UsuariosSistemaController::class, 'desbloquear']);
        Route::post('/{id}/token-recuperacion', [UsuariosSistemaController::class, 'generarTokenRecuperacion']);
    });

    // =====================================================
    // 5. HISTORIALES CARGOS
    // =====================================================
    Route::prefix('historiales-cargos')->group(function () {
        // CRUD básico
        Route::get('/', [HistorialesCargosController::class, 'index']);
        Route::post('/', [HistorialesCargosController::class, 'store']);
        Route::get('/{id}', [HistorialesCargosController::class, 'show']);
        Route::put('/{id}', [HistorialesCargosController::class, 'update']);
        Route::delete('/{id}', [HistorialesCargosController::class, 'destroy']);

        // Rutas específicas
        Route::get('/por-militar/{perfilMilitarId}', [HistorialesCargosController::class, 'porMilitar']);
        Route::get('/vigentes/listado', [HistorialesCargosController::class, 'vigentes']);
        Route::get('/promociones/reporte', [HistorialesCargosController::class, 'promociones']);
        Route::get('/estadisticas/generales', [HistorialesCargosController::class, 'estadisticas']);

        // Acciones específicas
        Route::post('/{id}/finalizar', [HistorialesCargosController::class, 'finalizar']);
        Route::post('/{id}/extender', [HistorialesCargosController::class, 'extender']);
    });

    // =====================================================
    // 6. ASIGNACIÓN ROLES
    // =====================================================
    Route::prefix('asignacion-roles')->group(function () {
        // CRUD básico
        Route::get('/', [AsignacionRolesController::class, 'index']);
        Route::post('/', [AsignacionRolesController::class, 'store']);
        Route::get('/{id}', [AsignacionRolesController::class, 'show']);
        Route::put('/{id}', [AsignacionRolesController::class, 'update']);
        Route::delete('/{id}', [AsignacionRolesController::class, 'destroy']);

        // Rutas específicas
        Route::get('/por-militar/{perfilMilitarId}', [AsignacionRolesController::class, 'porMilitar']);
        Route::get('/alertas-vencimiento/listado', [AsignacionRolesController::class, 'alertasVencimiento']);
        Route::get('/reporte/asignaciones', [AsignacionRolesController::class, 'reporte']);
        Route::get('/estadisticas/generales', [AsignacionRolesController::class, 'estadisticas']);

        // Acciones específicas
        Route::post('/{id}/revocar', [AsignacionRolesController::class, 'revocar']);
        Route::post('/{id}/extender', [AsignacionRolesController::class, 'extender']);
        Route::post('/{id}/hacer-permanente', [AsignacionRolesController::class, 'hacerPermanente']);
        Route::post('/{id}/renovar', [AsignacionRolesController::class, 'renovar']);
    });
});

// =====================================================
// RUTAS DE INTEGRACIÓN ENTRE MÓDULOS
// =====================================================

Route::prefix('integracion')->group(function () {

    // Integración Organigramas + Personal
    Route::prefix('organigrama-personal')->group(function () {
        Route::get('/unidad/{unidadId}/personal', [AsignacionesActualesController::class, 'porUnidad'])
            ->name('integracion.unidad.personal');
        Route::get('/cargo/{cargoId}/ocupantes', [HistorialesCargosController::class, 'porCargo'])
            ->name('integracion.cargo.ocupantes');
        Route::get('/estructura-con-personal', function () {
            // Combinar estructura organizacional con personal asignado
            return response()->json([
                'success' => true,
                'message' => 'Integración organigramas + personal',
                'data' => 'Endpoint para implementar'
            ]);
        })->name('integracion.estructura.personal');
    });

    // Para el servicio de autenticación
    Route::prefix('auth')->group(function () {
        Route::get('/usuario/{username}', [UsuariosSistemaController::class, 'show'])
            ->name('auth.usuario.buscar');
        Route::post('/usuario/{id}/acceso', function ($id) {
            // Registrar acceso del usuario
            $usuario = \App\Models\UsuarioSistema::find($id);
            if ($usuario) {
                $usuario->registrarAcceso();
                return response()->json(['success' => true, 'message' => 'Acceso registrado']);
            }
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        })->name('auth.usuario.acceso');
    });

    // Para reportes consolidados
    Route::prefix('reportes')->group(function () {
        Route::get('/personal-completo/{perfilMilitarId}', function ($perfilMilitarId) {
            $perfil = \App\Models\PerfilMilitar::with([
                'datosPersonales',
                'gradoActual',
                'categoriaPersonal',
                'especialidad',
                'asignacionActual.estructuraMilitar',
                'asignacionActual.cargo',
                'usuarioSistema',
                'historialesCargos' => function ($query) {
                    $query->ordenadoCronologicamente()->limit(10);
                },
                'asignacionRoles' => function ($query) {
                    $query->activos()->vigentes();
                }
            ])->find($perfilMilitarId);

            if (!$perfil) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil militar no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reporte completo de personal generado',
                'data' => $perfil
            ]);
        })->name('reportes.personal.completo');

        Route::get('/dashboard-personal', function () {
            $dashboard = [
                'totales' => [
                    'efectivos_activos' => \App\Models\PerfilMilitar::enServicio()->count(),
                    'usuarios_sistema' => \App\Models\UsuarioSistema::activos()->count(),
                    'asignaciones_vigentes' => \App\Models\AsignacionActual::vigentes()->count(),
                    'roles_vigentes' => \App\Models\AsignacionRol::vigentes()->count()
                ],
                'alertas' => [
                    'asignaciones_por_vencer' => \App\Models\AsignacionActual::activos()
                        ->vigentes()
                        ->whereNotNull('fecha_fin_asignacion')
                        ->where('fecha_fin_asignacion', '<=', now()->addDays(30))
                        ->count(),
                    'roles_por_vencer' => \App\Models\AsignacionRol::activos()->porVencer(30)->count(),
                    'usuarios_sin_acceso' => \App\Models\UsuarioSistema::activos()->sinAccesoReciente(90)->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dashboard de personal generado',
                'data' => $dashboard
            ]);
        })->name('reportes.dashboard.personal');
    });
});

// =====================================================
// RUTAS DE MIDDLEWARE (PARA IMPLEMENTAR)
// =====================================================

/*
// Rutas protegidas con autenticación
Route::middleware(['auth:sanctum'])->group(function () {
    // Todas las rutas de gestión van aquí cuando se implemente auth
});

// Rutas solo para administradores
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Usuarios sistema, estadísticas sensibles, etc.
});

// Rutas para personal de RRHH
Route::middleware(['auth:sanctum', 'role:rrhh'])->group(function () {
    // Gestión de personal, historiales, etc.
});

// Rutas para comandantes
Route::middleware(['auth:sanctum', 'role:comandante'])->group(function () {
    // Organigramas, asignaciones de su unidad, etc.
});
*/

// =====================================================
// RUTAS DE FALLBACK Y MANEJO DE ERRORES
// =====================================================

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint no encontrado en FAH Personal Service',
        'service' => 'fah-personal-service',
        'version' => '1.0.0',
        'available_modules' => [
            'organigramas' => 'GET /api/organigramas/*',
            'personal' => 'GET /api/personal/*',
            'integracion' => 'GET /api/integracion/*',
            'info' => 'GET /api/info',
            'health' => 'GET /api/health'
        ],
        'main_endpoints' => [
            'GET /api/organigramas/estructura-fah' => 'Estructura organizacional FAH',
            'GET /api/personal/datos-personales' => 'Gestión de datos personales',
            'GET /api/personal/perfiles-militares' => 'Gestión de perfiles militares',
            'GET /api/personal/asignaciones-actuales' => 'Gestión de asignaciones',
            'GET /api/personal/usuarios-sistema' => 'Gestión de usuarios',
            'GET /api/personal/historiales-cargos' => 'Gestión de historiales',
            'GET /api/personal/asignacion-roles' => 'Gestión de roles',
            'GET /api/integracion/reportes/dashboard-personal' => 'Dashboard integrado'
        ]
    ], 404);
});

// =====================================================
// DOCUMENTACIÓN COMPLETA DE LA API
// =====================================================

/*
ESTRUCTURA COMPLETA DE ENDPOINTS - FAH PERSONAL SERVICE:

🏛️ ORGANIGRAMAS FAH (/api/organigramas) - EXISTENTE
├── GET /estructura-fah              → Estructura completa FAH
├── GET /unidad/{unidad_id}         → Organigrama de unidad específica
└── GET /exportar                   → Exportar organigrama

📋 DATOS PERSONALES (/api/personal/datos-personales) - NUEVO
├── GET    /                                 → Listar todos
├── POST   /                                 → Crear nuevo
├── GET    /{id}                            → Obtener específico
├── PUT    /{id}                            → Actualizar
├── DELETE /{id}                            → Eliminar
├── GET    /por-identidad/{numeroIdentidad} → Buscar por cédula
└── GET    /estadisticas/generales          → Estadísticas

👤 PERFILES MILITARES (/api/personal/perfiles-militares) - NUEVO
├── GET    /                          → Listar todos
├── POST   /                          → Crear nuevo
├── GET    /{id}                      → Obtener específico
├── PUT    /{id}                      → Actualizar
├── DELETE /{id}                      → Eliminar
├── GET    /por-serie/{serieMilitar}  → Buscar por serie
├── GET    /disponibles/asignacion    → Personal disponible
├── GET    /estadisticas/generales    → Estadísticas
└── POST   /{id}/retirar             → Retirar del servicio

🏢 ASIGNACIONES ACTUALES (/api/personal/asignaciones-actuales) - NUEVO
├── GET    /                        → Listar todas
├── POST   /                        → Crear nueva
├── GET    /{id}                    → Obtener específica
├── PUT    /{id}                    → Actualizar
├── DELETE /{id}                    → Eliminar
├── GET    /por-unidad/{unidadId}   → Por unidad
├── GET    /por-vencer/alertas      → Alertas vencimiento
├── GET    /estadisticas/generales  → Estadísticas
├── POST   /{id}/finalizar         → Finalizar asignación
└── POST   /{id}/extender          → Extender asignación

👥 USUARIOS SISTEMA (/api/personal/usuarios-sistema) - NUEVO
├── GET    /                           → Listar todos
├── POST   /                           → Crear nuevo
├── GET    /{id}                       → Obtener específico
├── PUT    /{id}                       → Actualizar
├── DELETE /{id}                       → Eliminar
├── GET    /estadisticas/generales     → Estadísticas
├── POST   /{id}/cambiar-password      → Cambiar contraseña
├── POST   /{id}/restablecer-password  → Restablecer contraseña
├── POST   /{id}/bloquear             → Bloquear cuenta
├── POST   /{id}/desbloquear          → Desbloquear cuenta
└── POST   /{id}/token-recuperacion   → Generar token

📊 HISTORIALES CARGOS (/api/personal/historiales-cargos) - NUEVO
├── GET    /                                → Listar todos
├── POST   /                                → Crear nuevo
├── GET    /{id}                            → Obtener específico
├── PUT    /{id}                            → Actualizar
├── DELETE /{id}                            → Eliminar
├── GET    /por-militar/{perfilMilitarId}   → Por militar
├── GET    /vigentes/listado                → Cargos vigentes
├── GET    /promociones/reporte             → Reporte promociones
├── GET    /estadisticas/generales          → Estadísticas
├── POST   /{id}/finalizar                 → Finalizar cargo
└── POST   /{id}/extender                  → Extender cargo

🔐 ASIGNACIÓN ROLES (/api/personal/asignacion-roles) - NUEVO
├── GET    /                                → Listar todas
├── POST   /                                → Crear nueva
├── GET    /{id}                            → Obtener específica
├── PUT    /{id}                            → Actualizar
├── DELETE /{id}                            → Eliminar
├── GET    /por-militar/{perfilMilitarId}   → Por militar
├── GET    /alertas-vencimiento/listado     → Alertas vencimiento
├── GET    /reporte/asignaciones            → Reporte asignaciones
├── GET    /estadisticas/generales          → Estadísticas
├── POST   /{id}/revocar                   → Revocar rol
├── POST   /{id}/extender                  → Extender rol
├── POST   /{id}/hacer-permanente          → Hacer permanente
└── POST   /{id}/renovar                   → Renovar rol

🔗 INTEGRACIÓN (/api/integracion) - NUEVO
├── GET    /organigrama-personal/unidad/{id}/personal     → Personal por unidad
├── GET    /organigrama-personal/cargo/{id}/ocupantes     → Ocupantes de cargo
├── GET    /organigrama-personal/estructura-con-personal  → Estructura + personal
├── GET    /auth/usuario/{username}                       → Para servicio auth
├── POST   /auth/usuario/{id}/acceso                      → Registrar acceso
├── GET    /reportes/personal-completo/{id}               → Reporte consolidado
└── GET    /reportes/dashboard-personal                   → Dashboard integrado

🛠️ INFRAESTRUCTURA
├── GET /api/health     → Estado del servicio
├── GET /api/info       → Información del servicio
├── GET /api/user       → Usuario autenticado (Sanctum)
└── *                   → Fallback con documentación

TOTAL ENDPOINTS: 68 rutas operativas
- 3 Organigramas (existente)
- 51 Personal management (nuevo)
- 8 Integración (nuevo)
- 6 Infraestructura

CARACTERÍSTICAS:
✅ Mantiene compatibilidad con sistema existente
✅ Agrega gestión completa de personal militar
✅ Integración entre organigramas y personal
✅ Dashboard y reportes consolidados
✅ Sistema de autenticación con Sanctum
✅ Preparado para roles y permisos
✅ API RESTful enterprise-grade
*/
