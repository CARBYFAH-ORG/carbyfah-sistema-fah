<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes - FAH Auth Service
|--------------------------------------------------------------------------
|
| Rutas API para el microservicio de autenticación de la Fuerza Aérea Hondureña
|
*/

// Rutas públicas (sin autenticación)
Route::prefix('auth')->group(function () {
    // Health check del servicio
    Route::get('/health', [AuthController::class, 'health']);

    // Login de usuario
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    // Información del usuario actual
    Route::get('/me', [AuthController::class, 'me']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Ruta de verificación general de la API
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'FAH Auth Service API funcionando correctamente',
        'timestamp' => now(),
        'service' => 'fah-auth-service',
        'version' => '1.0.0'
    ]);
});
