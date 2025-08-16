<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// controladores existentes
use App\Http\Controllers\Api\OrganigramaController;

// controladores nuevos del schema personal
use App\Http\Controllers\DatosPersonalesController;
use App\Http\Controllers\PerfilesMilitaresController;
use App\Http\Controllers\AsignacionesActualesController;
use App\Http\Controllers\UsuariosSistemaController;
use App\Http\Controllers\HistorialesCargosController;
use App\Http\Controllers\AsignacionRolesController;

// rutas para el microservicio de personal de la fuerza aerea hondurena
// incluye organigramas existente mas personal completo nuevo
// arquitectura restful api con recursos anidados y acciones especificas
// versionado preparado para api v1
// autenticacion bearer token con sanctum

// ruta de usuario autenticado sanctum
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// rutas de salud e informacion del servicio
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

// organigramas fah existente mantenido
Route::prefix('organigramas')->group(function () {
    Route::get('/estructura-fah', [OrganigramaController::class, 'obtenerEstructuraFAH']);
    Route::get('/unidad/{unidad_id}', [OrganigramaController::class, 'obtenerOrganigramaUnidad']);
    Route::get('/exportar', [OrganigramaController::class, 'exportarOrganigrama']);
});

// personal fah nuevo schema personal completo
Route::prefix('personal')->group(function () {

    // datos personales
    Route::prefix('datos-personales')->group(function () {
        // crud basico
        Route::get('/', [DatosPersonalesController::class, 'index']);
        Route::post('/', [DatosPersonalesController::class, 'store']);
        Route::get('/{id}', [DatosPersonalesController::class, 'show']);
        Route::put('/{id}', [DatosPersonalesController::class, 'update']);
        Route::delete('/{id}', [DatosPersonalesController::class, 'destroy']);

        // rutas especificas
        Route::get('/por-identidad/{numeroIdentidad}', [DatosPersonalesController::class, 'porIdentidad']);
        Route::get('/estadisticas/generales', [DatosPersonalesController::class, 'estadisticas']);
    });

    // perfiles militares
    Route::prefix('perfiles-militares')->group(function () {
        // crud basico
        Route::get('/', [PerfilesMilitaresController::class, 'index']);
        Route::post('/', [PerfilesMilitaresController::class, 'store']);
        Route::get('/{id}', [PerfilesMilitaresController::class, 'show']);
        Route::put('/{id}', [PerfilesMilitaresController::class, 'update']);
        Route::delete('/{id}', [PerfilesMilitaresController::class, 'destroy']);

        // rutas especificas
        Route::get('/por-serie/{serieMilitar}', [PerfilesMilitaresController::class, 'porSerie']);
        Route::get('/disponibles/asignacion', [PerfilesMilitaresController::class, 'disponibles']);
        Route::get('/estadisticas/generales', [PerfilesMilitaresController::class, 'estadisticas']);

        // acciones especificas
        Route::post('/{id}/retirar', [PerfilesMilitaresController::class, 'retirar']);
    });

    // asignaciones actuales
    Route::prefix('asignaciones-actuales')->group(function () {
        // crud basico
        Route::get('/', [AsignacionesActualesController::class, 'index']);
        Route::post('/', [AsignacionesActualesController::class, 'store']);
        Route::get('/{id}', [AsignacionesActualesController::class, 'show']);
        Route::put('/{id}', [AsignacionesActualesController::class, 'update']);
        Route::delete('/{id}', [AsignacionesActualesController::class, 'destroy']);

        // rutas especificas
        Route::get('/por-unidad/{unidadId}', [AsignacionesActualesController::class, 'porUnidad']);
        Route::get('/por-vencer/alertas', [AsignacionesActualesController::class, 'porVencer']);
        Route::get('/estadisticas/generales', [AsignacionesActualesController::class, 'estadisticas']);

        // acciones especificas
        Route::post('/{id}/finalizar', [AsignacionesActualesController::class, 'finalizar']);
        Route::post('/{id}/extender', [AsignacionesActualesController::class, 'extender']);
    });

    // usuarios sistema
    Route::prefix('usuarios-sistema')->group(function () {
        // crud basico
        Route::get('/', [UsuariosSistemaController::class, 'index']);
        Route::post('/', [UsuariosSistemaController::class, 'store']);
        Route::get('/{id}', [UsuariosSistemaController::class, 'show']);
        Route::put('/{id}', [UsuariosSistemaController::class, 'update']);
        Route::delete('/{id}', [UsuariosSistemaController::class, 'destroy']);

        // rutas especificas
        Route::get('/estadisticas/generales', [UsuariosSistemaController::class, 'estadisticas']);

        // acciones de seguridad
        Route::post('/{id}/cambiar-password', [UsuariosSistemaController::class, 'cambiarPassword']);
        Route::post('/{id}/restablecer-password', [UsuariosSistemaController::class, 'restablecerPassword']);
        Route::post('/{id}/bloquear', [UsuariosSistemaController::class, 'bloquear']);
        Route::post('/{id}/desbloquear', [UsuariosSistemaController::class, 'desbloquear']);
        Route::post('/{id}/token-recuperacion', [UsuariosSistemaController::class, 'generarTokenRecuperacion']);
    });

    // historiales cargos
    Route::prefix('historiales-cargos')->group(function () {
        // crud basico
        Route::get('/', [HistorialesCargosController::class, 'index']);
        Route::post('/', [HistorialesCargosController::class, 'store']);
        Route::get('/{id}', [HistorialesCargosController::class, 'show']);
        Route::put('/{id}', [HistorialesCargosController::class, 'update']);
        Route::delete('/{id}', [HistorialesCargosController::class, 'destroy']);

        // rutas especificas
        Route::get('/por-militar/{perfilMilitarId}', [HistorialesCargosController::class, 'porMilitar']);
        Route::get('/vigentes/listado', [HistorialesCargosController::class, 'vigentes']);
        Route::get('/promociones/reporte', [HistorialesCargosController::class, 'promociones']);
        Route::get('/estadisticas/generales', [HistorialesCargosController::class, 'estadisticas']);

        // acciones especificas
        Route::post('/{id}/finalizar', [HistorialesCargosController::class, 'finalizar']);
        Route::post('/{id}/extender', [HistorialesCargosController::class, 'extender']);
    });

    // asignacion roles
    Route::prefix('asignacion-roles')->group(function () {
        // crud basico
        Route::get('/', [AsignacionRolesController::class, 'index']);
        Route::post('/', [AsignacionRolesController::class, 'store']);
        Route::get('/{id}', [AsignacionRolesController::class, 'show']);
        Route::put('/{id}', [AsignacionRolesController::class, 'update']);
        Route::delete('/{id}', [AsignacionRolesController::class, 'destroy']);

        // rutas especificas
        Route::get('/por-militar/{perfilMilitarId}', [AsignacionRolesController::class, 'porMilitar']);
        Route::get('/alertas-vencimiento/listado', [AsignacionRolesController::class, 'alertasVencimiento']);
        Route::get('/reporte/asignaciones', [AsignacionRolesController::class, 'reporte']);
        Route::get('/estadisticas/generales', [AsignacionRolesController::class, 'estadisticas']);

        // acciones especificas
        Route::post('/{id}/revocar', [AsignacionRolesController::class, 'revocar']);
        Route::post('/{id}/extender', [AsignacionRolesController::class, 'extender']);
        Route::post('/{id}/hacer-permanente', [AsignacionRolesController::class, 'hacerPermanente']);
        Route::post('/{id}/renovar', [AsignacionRolesController::class, 'renovar']);
    });
});

// rutas de integracion entre modulos
Route::prefix('integracion')->group(function () {

    // integracion organigramas mas personal
    Route::prefix('organigrama-personal')->group(function () {
        Route::get('/unidad/{unidadId}/personal', [AsignacionesActualesController::class, 'porUnidad'])
            ->name('integracion.unidad.personal');
        Route::get('/cargo/{cargoId}/ocupantes', [HistorialesCargosController::class, 'porCargo'])
            ->name('integracion.cargo.ocupantes');
        Route::get('/estructura-con-personal', function () {
            // combinar estructura organizacional con personal asignado
            return response()->json([
                'success' => true,
                'message' => 'Integración organigramas + personal',
                'data' => 'Endpoint para implementar'
            ]);
        })->name('integracion.estructura.personal');
    });

    // para el servicio de autenticacion
    Route::prefix('auth')->group(function () {
        Route::get('/usuario/{username}', [UsuariosSistemaController::class, 'show'])
            ->name('auth.usuario.buscar');
        Route::post('/usuario/{id}/acceso', function ($id) {
            // registrar acceso del usuario
            $usuario = \App\Models\UsuarioSistema::find($id);
            if ($usuario) {
                $usuario->registrarAcceso();
                return response()->json(['success' => true, 'message' => 'Acceso registrado']);
            }
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        })->name('auth.usuario.acceso');
    });

    // para reportes consolidados
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

// rutas de middleware para implementar
/*
// rutas protegidas con autenticacion
Route::middleware(['auth:sanctum'])->group(function () {
   // todas las rutas de gestion van aqui cuando se implemente auth
});

// rutas solo para administradores
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
   // usuarios sistema estadisticas sensibles etc
});

// rutas para personal de rrhh
Route::middleware(['auth:sanctum', 'role:rrhh'])->group(function () {
   // gestion de personal historiales etc
});

// rutas para comandantes
Route::middleware(['auth:sanctum', 'role:comandante'])->group(function () {
   // organigramas asignaciones de su unidad etc
});
*/

// rutas de fallback y manejo de errores
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
