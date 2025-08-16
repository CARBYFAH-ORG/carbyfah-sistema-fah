<?php

namespace App\Http\Controllers;

use App\Models\UsuarioSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login de usuario militar
     * POST /api/login
     */
    public function login(Request $request)
    {
        try {
            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de acceso incompletos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Buscar usuario por username
            // $usuario = UsuarioSistema::where('username', $request->username)->first();
            $usuario = UsuarioSistema::where('username', $request->username)
                ->select(['id', 'perfil_militar_id', 'username', 'password_hash', 'email_institucional', 'is_active', 'cuenta_bloqueada', 'fecha_bloqueo', 'intentos_fallidos', 'ultimo_acceso'])
                ->first();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Verificar si el usuario puede acceder
            if (!$usuario->puedeAcceder()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuenta inactiva o bloqueada',
                    'cuenta_bloqueada' => $usuario->cuenta_bloqueada,
                    'fecha_bloqueo' => $usuario->fecha_bloqueo
                ], 403);
            }

            // Verificar contraseña
            if (!Hash::check($request->password, $usuario->password_hash)) {
                // Incrementar intentos fallidos
                $usuario->incrementarIntentosFallidos();

                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta',
                    'intentos_restantes' => max(0, 3 - $usuario->intentos_fallidos)
                ], 401);
            }

            // Login exitoso - resetear intentos fallidos
            $usuario->resetearIntentosFallidos();

            // Actualizar último acceso
            $usuario->ultimo_acceso = now();
            $usuario->save();

            // Crear token de acceso
            $token = $usuario->createToken('fah-auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'usuario' => [
                        'id' => $usuario->id,
                        'username' => $usuario->username,
                        'email_institucional' => $usuario->email_institucional,
                        'perfil_militar_id' => $usuario->perfil_militar_id,
                        'ultimo_acceso' => $usuario->ultimo_acceso,
                        'is_active' => $usuario->is_active
                    ],
                    'token' => $token,
                    'tipo_token' => 'Bearer'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout de usuario
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        try {
            // Revocar token actual
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout exitoso'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información del usuario actual
     * GET /api/me
     */
    public function me(Request $request)
    {
        try {
            $usuario = $request->user();

            return response()->json([
                'success' => true,
                'message' => 'Información del usuario',
                'data' => [
                    'usuario' => [
                        'id' => $usuario->id,
                        'username' => $usuario->username,
                        'email_institucional' => $usuario->email_institucional,
                        'perfil_militar_id' => $usuario->perfil_militar_id,
                        'ultimo_acceso' => $usuario->ultimo_acceso,
                        'is_active' => $usuario->is_active
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar estado del servicio de autenticación
     * GET /api/auth/health
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'message' => 'Servicio de autenticación FAH operativo',
            'timestamp' => now(),
            'version' => '1.0.0'
        ], 200);
    }
}
