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

Route::prefix('catalogos')->group(function () {

    // Health check del servicio
    Route::get('/health', [CatalogosController::class, 'health']);

    // Catálogos generales
    Route::get('/basicos', [CatalogosController::class, 'catalogosBasicos']);
    Route::get('/estadisticas', [CatalogosController::class, 'estadisticas']);

    // Tipos de género
    Route::prefix('tipos-genero')->group(function () {
        Route::get('/', [TipoGeneroController::class, 'index']);
        Route::post('/', [TipoGeneroController::class, 'store']);
        Route::get('/{id}', [TipoGeneroController::class, 'show']);
        Route::put('/{id}', [TipoGeneroController::class, 'update']);
        Route::delete('/{id}', [TipoGeneroController::class, 'destroy']);
    });

    // Categorías de personal
    Route::prefix('categorias-personal')->group(function () {
        Route::get('/', [CategoriaPersonalController::class, 'index']);
        Route::post('/', [CategoriaPersonalController::class, 'store']);
        Route::get('/{id}', [CategoriaPersonalController::class, 'show']);
        Route::put('/{id}', [CategoriaPersonalController::class, 'update']);
        Route::delete('/{id}', [CategoriaPersonalController::class, 'destroy']);
    });

    // Grados militares
    Route::prefix('grados')->group(function () {
        Route::get('/', [GradoController::class, 'index']);
        Route::post('/', [GradoController::class, 'store']);
        Route::get('/{id}', [GradoController::class, 'show']);
        Route::put('/{id}', [GradoController::class, 'update']);
        Route::delete('/{id}', [GradoController::class, 'destroy']);
        Route::get('/por-categoria/{categoria_id}', [GradoController::class, 'porCategoria']);
    });

    // Tipos de estado general
    Route::prefix('tipos-estado-general')->group(function () {
        Route::get('/', [TipoEstadoGeneralController::class, 'index']);
        Route::post('/', [TipoEstadoGeneralController::class, 'store']);
        Route::get('/{id}', [TipoEstadoGeneralController::class, 'show']);
        Route::put('/{id}', [TipoEstadoGeneralController::class, 'update']);
        Route::delete('/{id}', [TipoEstadoGeneralController::class, 'destroy']);
    });

    // Niveles de prioridad
    Route::prefix('niveles-prioridad')->group(function () {
        Route::get('/', [NivelPrioridadController::class, 'index']);
        Route::post('/', [NivelPrioridadController::class, 'store']);
        Route::get('/{id}', [NivelPrioridadController::class, 'show']);
        Route::put('/{id}', [NivelPrioridadController::class, 'update']);
        Route::delete('/{id}', [NivelPrioridadController::class, 'destroy']);
    });

    // Niveles de seguridad
    Route::prefix('niveles-seguridad')->group(function () {
        Route::get('/', [NivelSeguridadController::class, 'index']);
        Route::post('/', [NivelSeguridadController::class, 'store']);
        Route::get('/{id}', [NivelSeguridadController::class, 'show']);
        Route::put('/{id}', [NivelSeguridadController::class, 'update']);
        Route::delete('/{id}', [NivelSeguridadController::class, 'destroy']);
    });

    // Países
    Route::prefix('paises')->group(function () {
        Route::get('/buscar', [PaisController::class, 'buscar']);
        Route::get('/', [PaisController::class, 'index']);
        Route::post('/', [PaisController::class, 'store']);
        Route::get('/{id}', [PaisController::class, 'show']);
        Route::put('/{id}', [PaisController::class, 'update']);
        Route::delete('/{id}', [PaisController::class, 'destroy']);
    });

    // Tipos de estructura militar
    Route::prefix('tipos-estructura-militar')->group(function () {
        Route::get('/', [TipoEstructuraMilitarController::class, 'index']);
        Route::post('/', [TipoEstructuraMilitarController::class, 'store']);
        Route::get('/{id}', [TipoEstructuraMilitarController::class, 'show']);
        Route::put('/{id}', [TipoEstructuraMilitarController::class, 'update']);
        Route::delete('/{id}', [TipoEstructuraMilitarController::class, 'destroy']);
        Route::get('/por-nivel/{nivel}', [TipoEstructuraMilitarController::class, 'porNivel']);
    });

    // Tipos de jerarquía
    Route::prefix('tipos-jerarquia')->group(function () {
        Route::get('/', [TipoJerarquiaController::class, 'index']);
        Route::post('/', [TipoJerarquiaController::class, 'store']);
        Route::get('/{id}', [TipoJerarquiaController::class, 'show']);
        Route::put('/{id}', [TipoJerarquiaController::class, 'update']);
        Route::delete('/{id}', [TipoJerarquiaController::class, 'destroy']);
        Route::get('/por-nivel/{nivel}', [TipoJerarquiaController::class, 'porNivel']);
    });

    // Especialidades
    Route::prefix('especialidades')->group(function () {
        Route::get('/', [EspecialidadController::class, 'index']);
        Route::post('/', [EspecialidadController::class, 'store']);
        Route::get('/{id}', [EspecialidadController::class, 'show']);
        Route::put('/{id}', [EspecialidadController::class, 'update']);
        Route::delete('/{id}', [EspecialidadController::class, 'destroy']);
    });

    // Tipos de evento
    Route::prefix('tipos-evento')->group(function () {
        Route::get('/', [TipoEventoController::class, 'index']);
        Route::post('/', [TipoEventoController::class, 'store']);
        Route::get('/{id}', [TipoEventoController::class, 'show']);
        Route::put('/{id}', [TipoEventoController::class, 'update']);
        Route::delete('/{id}', [TipoEventoController::class, 'destroy']);
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
