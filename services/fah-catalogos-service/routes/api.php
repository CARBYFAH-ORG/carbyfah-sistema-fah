<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogosController;
use App\Http\Controllers\TipoGeneroController;
use App\Http\Controllers\CategoriaPersonalController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\TipoEstadoGeneralController;
use App\Http\Controllers\NivelPrioridadController;
use App\Http\Controllers\NivelSeguridadController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\TipoEstructuraMilitarController;
use App\Http\Controllers\TipoJerarquiaController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\TipoEventoController;

/*
|--------------------------------------------------------------------------
| API Routes - FAH Catalogos Service
|--------------------------------------------------------------------------
|
| Rutas API para el microservicio de catálogos de la Fuerza Aérea Hondureña
| Datos maestros para todo el sistema CARBYFAH
| TODOS LOS 11 CONTROLLERS INCLUIDOS
|
*/

// Rutas públicas (sin autenticación por ahora)
Route::prefix('catalogos')->group(function () {

    // Health check del servicio
    Route::get('/health', [CatalogosController::class, 'health']);

    // Catálogos generales
    Route::get('/basicos', [CatalogosController::class, 'catalogosBasicos']);
    Route::get('/estadisticas', [CatalogosController::class, 'estadisticas']);

    // === TIPOS DE GÉNERO ===
    Route::prefix('tipos-genero')->group(function () {
        Route::get('/', [TipoGeneroController::class, 'index']);           // GET /catalogos/tipos-genero
        Route::post('/', [TipoGeneroController::class, 'store']);          // POST /catalogos/tipos-genero
        Route::get('/{id}', [TipoGeneroController::class, 'show']);        // GET /catalogos/tipos-genero/{id}
        Route::put('/{id}', [TipoGeneroController::class, 'update']);      // PUT /catalogos/tipos-genero/{id}
        Route::delete('/{id}', [TipoGeneroController::class, 'destroy']);  // DELETE /catalogos/tipos-genero/{id}
    });

    // === CATEGORÍAS DE PERSONAL ===
    Route::prefix('categorias-personal')->group(function () {
        Route::get('/', [CategoriaPersonalController::class, 'index']);           // GET /catalogos/categorias-personal
        Route::post('/', [CategoriaPersonalController::class, 'store']);          // POST /catalogos/categorias-personal
        Route::get('/{id}', [CategoriaPersonalController::class, 'show']);        // GET /catalogos/categorias-personal/{id}
        Route::put('/{id}', [CategoriaPersonalController::class, 'update']);      // PUT /catalogos/categorias-personal/{id}
        Route::delete('/{id}', [CategoriaPersonalController::class, 'destroy']);  // DELETE /catalogos/categorias-personal/{id}
    });

    // === GRADOS MILITARES ===
    Route::prefix('grados')->group(function () {
        Route::get('/', [GradoController::class, 'index']);                    // GET /catalogos/grados
        Route::post('/', [GradoController::class, 'store']);                   // POST /catalogos/grados
        Route::get('/{id}', [GradoController::class, 'show']);                 // GET /catalogos/grados/{id}
        Route::put('/{id}', [GradoController::class, 'update']);               // PUT /catalogos/grados/{id}
        Route::delete('/{id}', [GradoController::class, 'destroy']);           // DELETE /catalogos/grados/{id}

        // Ruta especial: grados por categoría
        Route::get('/por-categoria/{categoria_id}', [GradoController::class, 'porCategoria']); // GET /catalogos/grados/por-categoria/{categoria_id}
    });

    // === TIPOS DE ESTADO GENERAL ===
    Route::prefix('tipos-estado-general')->group(function () {
        Route::get('/', [TipoEstadoGeneralController::class, 'index']);           // GET /catalogos/tipos-estado-general
        Route::post('/', [TipoEstadoGeneralController::class, 'store']);          // POST /catalogos/tipos-estado-general
        Route::get('/{id}', [TipoEstadoGeneralController::class, 'show']);        // GET /catalogos/tipos-estado-general/{id}
        Route::put('/{id}', [TipoEstadoGeneralController::class, 'update']);      // PUT /catalogos/tipos-estado-general/{id}
        Route::delete('/{id}', [TipoEstadoGeneralController::class, 'destroy']);  // DELETE /catalogos/tipos-estado-general/{id}
    });

    // === NIVELES DE PRIORIDAD ===
    Route::prefix('niveles-prioridad')->group(function () {
        Route::get('/', [NivelPrioridadController::class, 'index']);           // GET /catalogos/niveles-prioridad
        Route::post('/', [NivelPrioridadController::class, 'store']);          // POST /catalogos/niveles-prioridad
        Route::get('/{id}', [NivelPrioridadController::class, 'show']);        // GET /catalogos/niveles-prioridad/{id}
        Route::put('/{id}', [NivelPrioridadController::class, 'update']);      // PUT /catalogos/niveles-prioridad/{id}
        Route::delete('/{id}', [NivelPrioridadController::class, 'destroy']);  // DELETE /catalogos/niveles-prioridad/{id}
    });

    // === NIVELES DE SEGURIDAD ===
    Route::prefix('niveles-seguridad')->group(function () {
        Route::get('/', [NivelSeguridadController::class, 'index']);           // GET /catalogos/niveles-seguridad
        Route::post('/', [NivelSeguridadController::class, 'store']);          // POST /catalogos/niveles-seguridad
        Route::get('/{id}', [NivelSeguridadController::class, 'show']);        // GET /catalogos/niveles-seguridad/{id}
        Route::put('/{id}', [NivelSeguridadController::class, 'update']);      // PUT /catalogos/niveles-seguridad/{id}
        Route::delete('/{id}', [NivelSeguridadController::class, 'destroy']);  // DELETE /catalogos/niveles-seguridad/{id}
    });

    // === PAÍSES ===
    Route::prefix('paises')->group(function () {
        // ✅ NUEVA RUTA DE BÚSQUEDA (debe ir ANTES de {id})
        Route::get('/buscar', [PaisController::class, 'buscar']);           // GET /catalogos/paises/buscar?q=texto

        Route::get('/', [PaisController::class, 'index']);           // GET /catalogos/paises
        Route::post('/', [PaisController::class, 'store']);          // POST /catalogos/paises
        Route::get('/{id}', [PaisController::class, 'show']);        // GET /catalogos/paises/{id}
        Route::put('/{id}', [PaisController::class, 'update']);      // PUT /catalogos/paises/{id}
        Route::delete('/{id}', [PaisController::class, 'destroy']);  // DELETE /catalogos/paises/{id}
    });

    // === TIPOS DE ESTRUCTURA MILITAR ===
    Route::prefix('tipos-estructura-militar')->group(function () {
        Route::get('/', [TipoEstructuraMilitarController::class, 'index']);           // GET /catalogos/tipos-estructura-militar
        Route::post('/', [TipoEstructuraMilitarController::class, 'store']);          // POST /catalogos/tipos-estructura-militar
        Route::get('/{id}', [TipoEstructuraMilitarController::class, 'show']);        // GET /catalogos/tipos-estructura-militar/{id}
        Route::put('/{id}', [TipoEstructuraMilitarController::class, 'update']);      // PUT /catalogos/tipos-estructura-militar/{id}
        Route::delete('/{id}', [TipoEstructuraMilitarController::class, 'destroy']);  // DELETE /catalogos/tipos-estructura-militar/{id}

        // Ruta especial: tipos por nivel organizacional
        Route::get('/por-nivel/{nivel}', [TipoEstructuraMilitarController::class, 'porNivel']); // GET /catalogos/tipos-estructura-militar/por-nivel/{nivel}
    });

    // === TIPOS DE JERARQUÍA ===
    Route::prefix('tipos-jerarquia')->group(function () {
        Route::get('/', [TipoJerarquiaController::class, 'index']);           // GET /catalogos/tipos-jerarquia
        Route::post('/', [TipoJerarquiaController::class, 'store']);          // POST /catalogos/tipos-jerarquia
        Route::get('/{id}', [TipoJerarquiaController::class, 'show']);        // GET /catalogos/tipos-jerarquia/{id}
        Route::put('/{id}', [TipoJerarquiaController::class, 'update']);      // PUT /catalogos/tipos-jerarquia/{id}
        Route::delete('/{id}', [TipoJerarquiaController::class, 'destroy']);  // DELETE /catalogos/tipos-jerarquia/{id}

        // Ruta especial: tipos por nivel de autoridad
        Route::get('/por-nivel/{nivel}', [TipoJerarquiaController::class, 'porNivel']); // GET /catalogos/tipos-jerarquia/por-nivel/{nivel}
    });

    // === ESPECIALIDADES ===
    Route::prefix('especialidades')->group(function () {
        Route::get('/', [EspecialidadController::class, 'index']);           // GET /catalogos/especialidades
        Route::post('/', [EspecialidadController::class, 'store']);          // POST /catalogos/especialidades
        Route::get('/{id}', [EspecialidadController::class, 'show']);        // GET /catalogos/especialidades/{id}
        Route::put('/{id}', [EspecialidadController::class, 'update']);      // PUT /catalogos/especialidades/{id}
        Route::delete('/{id}', [EspecialidadController::class, 'destroy']);  // DELETE /catalogos/especialidades/{id}
    });

    // === TIPOS DE EVENTO ===
    Route::prefix('tipos-evento')->group(function () {
        Route::get('/', [TipoEventoController::class, 'index']);           // GET /catalogos/tipos-evento
        Route::post('/', [TipoEventoController::class, 'store']);          // POST /catalogos/tipos-evento
        Route::get('/{id}', [TipoEventoController::class, 'show']);        // GET /catalogos/tipos-evento/{id}
        Route::put('/{id}', [TipoEventoController::class, 'update']);      // PUT /catalogos/tipos-evento/{id}
        Route::delete('/{id}', [TipoEventoController::class, 'destroy']);  // DELETE /catalogos/tipos-evento/{id}
    });
});

// Ruta de verificación general de la API
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'FAH Catalogos Service API funcionando correctamente',
        'timestamp' => now(),
        'service' => 'fah-catalogos-service',
        'version' => '1.0.0',
        'total_endpoints' => 11,
        'endpoints' => [
            'health' => '/api/catalogos/health',
            'basicos' => '/api/catalogos/basicos',
            'tipos_genero' => '/api/catalogos/tipos-genero',
            'categorias_personal' => '/api/catalogos/categorias-personal',
            'grados' => '/api/catalogos/grados',
            'tipos_estado_general' => '/api/catalogos/tipos-estado-general',
            'niveles_prioridad' => '/api/catalogos/niveles-prioridad',
            'niveles_seguridad' => '/api/catalogos/niveles-seguridad',
            'paises' => '/api/catalogos/paises',
            'tipos_estructura_militar' => '/api/catalogos/tipos-estructura-militar',
            'tipos_jerarquia' => '/api/catalogos/tipos-jerarquia',
            'especialidades' => '/api/catalogos/especialidades',
            'tipos_evento' => '/api/catalogos/tipos-evento'
        ]
    ], 200);
});

// Rutas futuras protegidas (cuando implementemos autenticación)
/*
Route::middleware('auth:sanctum')->group(function () {
    // Rutas que requieren autenticación
    Route::prefix('catalogos')->group(function () {
        // Solo super admins pueden crear/editar/eliminar
        Route::middleware('can:manage-catalogos')->group(function () {
            // CRUD routes ya definidas arriba
        });
    });
});
*/
