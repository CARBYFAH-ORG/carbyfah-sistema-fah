<?php

use App\Http\Controllers\ArchivosController;
use App\Http\Controllers\CategoriaArchivoController;
use App\Http\Controllers\TipoStorageController;
use App\Http\Controllers\EstadoConexionController;
use App\Http\Controllers\TipoArchivoController;
use App\Http\Controllers\CategoriaContenidoController;
use App\Http\Controllers\RepositorioStorageController;
use App\Http\Controllers\NivelAccesoController;
use App\Http\Controllers\TipoPermisoController;
use App\Http\Controllers\MotivoCambioController;
use App\Http\Controllers\VersionArchivoController;
use App\Http\Controllers\PermisoAccesoController;
use App\Http\Controllers\RegistroAccesoController;
use App\Http\Controllers\MetadatoArchivoController;
use App\Http\Controllers\ArchivoDigitalController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'service' => 'fah-archivos-service']);
});

// ============================================
// RUTAS ARCHIVOS (Upload/Download específico)
// ============================================
Route::prefix('archivos')->group(function () {
    Route::get('/', [ArchivosController::class, 'index']);
    Route::post('/upload', [ArchivosController::class, 'upload']);

    // RUTAS ESPECÍFICAS PRIMERO (ANTES DE {id})
    Route::get('/test-minio', [ArchivosController::class, 'testMinio']);
    Route::get('/test-minio-simple', [ArchivosController::class, 'testMinioSimple']);
    Route::get('/debug-config', [ArchivosController::class, 'debugConfig']);
    Route::get('/storage-stats', [ArchivosController::class, 'storageStats']);

    // RUTAS CON PARÁMETROS AL FINAL
    Route::get('/{id}', [ArchivosController::class, 'show']);
    Route::get('/{id}/download', [ArchivosController::class, 'download']);
    Route::delete('/{id}', [ArchivosController::class, 'destroy']);
});

// ============================================
// RUTAS CRUD PARA TODOS LOS CATÁLOGOS
// ============================================

// Categorías de Archivo
Route::apiResource('categorias-archivo', CategoriaArchivoController::class);

// Tipos de Storage
Route::apiResource('tipos-storage', TipoStorageController::class);

// Estados de Conexión
Route::apiResource('estados-conexion', EstadoConexionController::class);

// Tipos de Archivo
Route::apiResource('tipos-archivo', TipoArchivoController::class);

// Categorías de Contenido
Route::apiResource('categorias-contenido', CategoriaContenidoController::class);

// Repositorios de Storage
Route::apiResource('repositorios-storage', RepositorioStorageController::class);

// Niveles de Acceso
Route::apiResource('niveles-acceso', NivelAccesoController::class);

// Tipos de Permiso
Route::apiResource('tipos-permiso', TipoPermisoController::class);

// Motivos de Cambio
Route::apiResource('motivos-cambio', MotivoCambioController::class);

// Versiones de Archivo
Route::apiResource('versiones-archivo', VersionArchivoController::class);

// Permisos de Acceso
Route::apiResource('permisos-acceso', PermisoAccesoController::class);

// Registro de Accesos (solo lectura por seguridad)
Route::get('registros-acceso', [RegistroAccesoController::class, 'index']);
Route::get('registros-acceso/{id}', [RegistroAccesoController::class, 'show']);
Route::post('registros-acceso', [RegistroAccesoController::class, 'store']);

// Metadatos de Archivo
Route::apiResource('metadatos-archivo', MetadatoArchivoController::class);

// Archivos Digitales (CRUD completo)
Route::apiResource('archivos-digitales', ArchivoDigitalController::class);
