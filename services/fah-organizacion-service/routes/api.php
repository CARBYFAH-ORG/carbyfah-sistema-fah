<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizacionController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\UbicacionGeograficaController;
use App\Http\Controllers\RolFuncionalController;
use App\Http\Controllers\EstructuraMilitarController;
use App\Http\Controllers\CargoController;

/*
|--------------------------------------------------------------------------
| API Routes - FAH Organizacion Service
|--------------------------------------------------------------------------
|
| Rutas API para el microservicio de organización de la Fuerza Aérea Hondureña
| Estructura geográfica y militar para todo el sistema CARBYFAH
| TODOS LOS 7 CONTROLLERS INCLUIDOS + RUTAS DE BÚSQUEDA
|
*/

// Rutas públicas (sin autenticación por ahora)
Route::prefix('organizacion')->group(function () {

    // Health check del servicio
    Route::get('/health', [OrganizacionController::class, 'health']);

    // Estadísticas generales
    Route::get('/estadisticas', [OrganizacionController::class, 'estadisticas']);

    // === DEPARTAMENTOS ===
    Route::prefix('departamentos')->group(function () {
        // ✅ RUTA DE BÚSQUEDA (debe ir ANTES de {id})
        Route::get('/buscar', [DepartamentoController::class, 'buscar']);     // GET /organizacion/departamentos/buscar?q=texto
        Route::get('/por-pais/{pais_id}', [DepartamentoController::class, 'porPais']); // GET /organizacion/departamentos/por-pais/{pais_id}

        Route::get('/', [DepartamentoController::class, 'index']);           // GET /organizacion/departamentos
        Route::post('/', [DepartamentoController::class, 'store']);          // POST /organizacion/departamentos
        Route::get('/{id}', [DepartamentoController::class, 'show']);        // GET /organizacion/departamentos/{id}
        Route::put('/{id}', [DepartamentoController::class, 'update']);      // PUT /organizacion/departamentos/{id}
        Route::delete('/{id}', [DepartamentoController::class, 'destroy']);  // DELETE /organizacion/departamentos/{id}
    });

    // === MUNICIPIOS ===
    Route::prefix('municipios')->group(function () {
        // ✅ RUTA DE BÚSQUEDA (debe ir ANTES de {id})
        Route::get('/buscar', [MunicipioController::class, 'buscar']);        // GET /organizacion/municipios/buscar?q=texto
        Route::get('/por-departamento/{departamento_id}', [MunicipioController::class, 'porDepartamento']); // GET /organizacion/municipios/por-departamento/{departamento_id}

        Route::get('/', [MunicipioController::class, 'index']);           // GET /organizacion/municipios
        Route::post('/', [MunicipioController::class, 'store']);          // POST /organizacion/municipios
        Route::get('/{id}', [MunicipioController::class, 'show']);        // GET /organizacion/municipios/{id}
        Route::put('/{id}', [MunicipioController::class, 'update']);      // PUT /organizacion/municipios/{id}
        Route::delete('/{id}', [MunicipioController::class, 'destroy']);  // DELETE /organizacion/municipios/{id}
    });

    // === CIUDADES ===
    Route::prefix('ciudades')->group(function () {
        // ✅ RUTA DE BÚSQUEDA (debe ir ANTES de {id})
        Route::get('/buscar', [CiudadController::class, 'buscar']);           // GET /organizacion/ciudades/buscar?q=texto
        Route::get('/por-municipio/{municipio_id}', [CiudadController::class, 'porMunicipio']); // GET /organizacion/ciudades/por-municipio/{municipio_id}

        Route::get('/', [CiudadController::class, 'index']);           // GET /organizacion/ciudades
        Route::post('/', [CiudadController::class, 'store']);          // POST /organizacion/ciudades
        Route::get('/{id}', [CiudadController::class, 'show']);        // GET /organizacion/ciudades/{id}
        Route::put('/{id}', [CiudadController::class, 'update']);      // PUT /organizacion/ciudades/{id}
        Route::delete('/{id}', [CiudadController::class, 'destroy']);  // DELETE /organizacion/ciudades/{id}
    });

    // === UBICACIONES GEOGRÁFICAS ===
    Route::prefix('ubicaciones-geograficas')->group(function () {
        // ✅ RUTA DE BÚSQUEDA (debe ir ANTES de {id})
        Route::get('/buscar', [UbicacionGeograficaController::class, 'buscar']); // GET /organizacion/ubicaciones-geograficas/buscar?q=texto

        Route::get('/', [UbicacionGeograficaController::class, 'index']);           // GET /organizacion/ubicaciones-geograficas
        Route::post('/', [UbicacionGeograficaController::class, 'store']);          // POST /organizacion/ubicaciones-geograficas
        Route::get('/{id}', [UbicacionGeograficaController::class, 'show']);        // GET /organizacion/ubicaciones-geograficas/{id}
        Route::put('/{id}', [UbicacionGeograficaController::class, 'update']);      // PUT /organizacion/ubicaciones-geograficas/{id}
        Route::delete('/{id}', [UbicacionGeograficaController::class, 'destroy']);  // DELETE /organizacion/ubicaciones-geograficas/{id}
    });

    // === ROLES FUNCIONALES ===
    Route::prefix('roles-funcionales')->group(function () {
        // ✅ RUTA DE BÚSQUEDA (debe ir ANTES de {id})
        Route::get('/buscar', [RolFuncionalController::class, 'buscar']);     // GET /organizacion/roles-funcionales/buscar?q=texto
        Route::get('/por-nivel/{nivel}', [RolFuncionalController::class, 'porNivel']); // GET /organizacion/roles-funcionales/por-nivel/{nivel}

        Route::get('/', [RolFuncionalController::class, 'index']);           // GET /organizacion/roles-funcionales
        Route::post('/', [RolFuncionalController::class, 'store']);          // POST /organizacion/roles-funcionales
        Route::get('/{id}', [RolFuncionalController::class, 'show']);        // GET /organizacion/roles-funcionales/{id}
        Route::put('/{id}', [RolFuncionalController::class, 'update']);      // PUT /organizacion/roles-funcionales/{id}
        Route::delete('/{id}', [RolFuncionalController::class, 'destroy']);  // DELETE /organizacion/roles-funcionales/{id}
    });

    // === ESTRUCTURA MILITAR ===
    Route::prefix('estructura-militar')->group(function () {
        // ✅ RUTA DE BÚSQUEDA (debe ir ANTES de {id})
        Route::get('/buscar', [EstructuraMilitarController::class, 'buscar']); // GET /organizacion/estructura-militar/buscar?q=texto

        Route::get('/', [EstructuraMilitarController::class, 'index']);           // GET /organizacion/estructura-militar
        Route::post('/', [EstructuraMilitarController::class, 'store']);          // POST /organizacion/estructura-militar
        Route::get('/{id}', [EstructuraMilitarController::class, 'show']);        // GET /organizacion/estructura-militar/{id}
        Route::put('/{id}', [EstructuraMilitarController::class, 'update']);      // PUT /organizacion/estructura-militar/{id}
        Route::delete('/{id}', [EstructuraMilitarController::class, 'destroy']);  // DELETE /organizacion/estructura-militar/{id}

        // Ruta especial: jerarquía completa de unidad
        Route::get('/{id}/jerarquia', [EstructuraMilitarController::class, 'jerarquia']); // GET /organizacion/estructura-militar/{id}/jerarquia
    });

    // === CARGOS ===
    Route::prefix('cargos')->group(function () {
        // ✅ RUTA DE BÚSQUEDA (debe ir ANTES de {id})
        Route::get('/buscar', [CargoController::class, 'buscar']);            // GET /organizacion/cargos/buscar?q=texto
        Route::get('/por-estructura/{estructura_id}', [CargoController::class, 'porEstructura']); // GET /organizacion/cargos/por-estructura/{estructura_id}

        Route::get('/', [CargoController::class, 'index']);           // GET /organizacion/cargos
        Route::post('/', [CargoController::class, 'store']);          // POST /organizacion/cargos
        Route::get('/{id}', [CargoController::class, 'show']);        // GET /organizacion/cargos/{id}
        Route::put('/{id}', [CargoController::class, 'update']);      // PUT /organizacion/cargos/{id}
        Route::delete('/{id}', [CargoController::class, 'destroy']);  // DELETE /organizacion/cargos/{id}
    });
});

// Ruta de verificación general de la API
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'FAH Organizacion Service API funcionando correctamente',
        'timestamp' => now(),
        'service' => 'fah-organizacion-service',
        'version' => '1.0.0',
        'puerto' => '8010',
        'total_endpoints' => 7,
        'endpoints' => [
            'health' => '/api/organizacion/health',
            'estadisticas' => '/api/organizacion/estadisticas',
            'departamentos' => '/api/organizacion/departamentos',
            'municipios' => '/api/organizacion/municipios',
            'ciudades' => '/api/organizacion/ciudades',
            'ubicaciones_geograficas' => '/api/organizacion/ubicaciones-geograficas',
            'roles_funcionales' => '/api/organizacion/roles-funcionales',
            'estructura_militar' => '/api/organizacion/estructura-militar',
            'cargos' => '/api/organizacion/cargos'
        ],
        'rutas_busqueda' => [
            'departamentos_buscar' => '/api/organizacion/departamentos/buscar?q=texto',
            'municipios_buscar' => '/api/organizacion/municipios/buscar?q=texto&departamento_id=1',
            'ciudades_buscar' => '/api/organizacion/ciudades/buscar?q=texto&municipio_id=1&tipo_localidad=Ciudad',
            'ubicaciones_buscar' => '/api/organizacion/ubicaciones-geograficas/buscar?q=texto&pais_id=1',
            'roles_buscar' => '/api/organizacion/roles-funcionales/buscar?q=texto&nivel_autoridad=5',
            'estructura_buscar' => '/api/organizacion/estructura-militar/buscar?q=texto&nivel_jerarquico=1',
            'cargos_buscar' => '/api/organizacion/cargos/buscar?q=texto&estructura_militar_id=1'
        ],
        'rutas_especiales' => [
            'departamentos_por_pais' => '/api/organizacion/departamentos/por-pais/{pais_id}',
            'municipios_por_departamento' => '/api/organizacion/municipios/por-departamento/{departamento_id}',
            'ciudades_por_municipio' => '/api/organizacion/ciudades/por-municipio/{municipio_id}',
            'roles_por_nivel' => '/api/organizacion/roles-funcionales/por-nivel/{nivel}',
            'jerarquia_unidad' => '/api/organizacion/estructura-militar/{id}/jerarquia',
            'cargos_por_estructura' => '/api/organizacion/cargos/por-estructura/{estructura_id}'
        ]
    ], 200);
});

// Rutas futuras protegidas (cuando implementemos autenticación)
/*
Route::middleware('auth:sanctum')->group(function () {
    // Rutas que requieren autenticación
    Route::prefix('organizacion')->group(function () {
        // Solo super admins pueden crear/editar/eliminar
        Route::middleware('can:manage-organizacion')->group(function () {
            // CRUD routes ya definidas arriba
        });
    });
});
*/
