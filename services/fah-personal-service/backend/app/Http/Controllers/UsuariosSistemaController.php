<?php

namespace App\Http\Controllers;

use App\Models\UsuarioSistema;
use App\Models\PerfilMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UsuariosSistemaController extends Controller
{
    /**
     * Listar todos los usuarios del sistema
     * GET /api/personal/usuarios-sistema
     */
    public function index(Request $request)
    {
        try {
            $query = UsuarioSistema::with([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'perfilMilitar.asignacionActual.estructuraMilitar'
            ])->activos();

            // Filtros opcionales
            if ($request->has('estado_cuenta')) {
                switch ($request->estado_cuenta) {
                    case 'BLOQUEADA':
                        $query->bloqueados();
                        break;
                    case 'ACTIVA':
                        $query->desbloqueados();
                        break;
                    case 'REQUIERE_CAMBIO':
                        $query->requierenCambioPassword();
                        break;
                }
            }

            if ($request->has('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('username', 'ILIKE', "%{$buscar}%")
                        ->orWhere('email_institucional', 'ILIKE', "%{$buscar}%")
                        ->orWhereHas('perfilMilitar.datosPersonales', function ($q2) use ($buscar) {
                            $q2->where('primer_nombre', 'ILIKE', "%{$buscar}%")
                                ->orWhere('primer_apellido', 'ILIKE', "%{$buscar}%");
                        });
                });
            }

            if ($request->has('sin_acceso_dias')) {
                $query->sinAccesoReciente($request->sin_acceso_dias);
            }

            $usuarios = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Usuarios del sistema obtenidos correctamente',
                'data' => $usuarios
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios del sistema',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo usuario del sistema
     * POST /api/personal/usuarios-sistema
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'perfil_militar_id' => 'required|integer|exists:personal.perfiles_militares,id|unique:personal.usuarios_sistema,perfil_militar_id',
                'username' => 'required|string|max:100|unique:personal.usuarios_sistema,username',
                'email_institucional' => 'required|email|max:200|unique:personal.usuarios_sistema,email_institucional',
                'password' => 'required|string|min:8',
                'requiere_cambio_password' => 'boolean',
                'configuraciones_usuario' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar que el perfil militar existe y está activo
            $perfilMilitar = PerfilMilitar::find($request->perfil_militar_id);
            if (!$perfilMilitar || !$perfilMilitar->esta_activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El perfil militar no existe o no está activo'
                ], 400);
            }

            $usuarioSistema = UsuarioSistema::create([
                'perfil_militar_id' => $request->perfil_militar_id,
                'username' => $request->username,
                'email_institucional' => $request->email_institucional,
                'password_hash' => $request->password, // Usa el mutator que hace hash automáticamente
                'requiere_cambio_password' => $request->get('requiere_cambio_password', true),
                'configuraciones_usuario' => $request->configuraciones_usuario ?? [],
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1
            ]);

            $usuarioSistema->load([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario del sistema creado correctamente',
                'data' => $usuarioSistema
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario del sistema',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuario específico
     * GET /api/personal/usuarios-sistema/{id}
     */
    public function show($id)
    {
        try {
            $usuarioSistema = UsuarioSistema::with([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'perfilMilitar.categoriaPersonal',
                'perfilMilitar.especialidad',
                'perfilMilitar.asignacionActual.estructuraMilitar',
                'perfilMilitar.asignacionActual.cargo'
            ])->find($id);

            if (!$usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario del sistema no encontrado'
                ], 404);
            }

            // Agregar información adicional
            $data = $usuarioSistema->toArray();
            $data['errores_integridad'] = $usuarioSistema->validarIntegridadCuenta();
            $data['tiempo_sin_acceso'] = $usuarioSistema->tiempoSinAcceso();

            return response()->json([
                'success' => true,
                'message' => 'Usuario del sistema obtenido correctamente',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario del sistema',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar usuario del sistema
     * PUT /api/personal/usuarios-sistema/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $usuarioSistema = UsuarioSistema::find($id);

            if (!$usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario del sistema no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'perfil_militar_id' => 'required|integer|exists:personal.perfiles_militares,id|unique:personal.usuarios_sistema,perfil_militar_id,' . $id,
                'username' => 'required|string|max:100|unique:personal.usuarios_sistema,username,' . $id,
                'email_institucional' => 'required|email|max:200|unique:personal.usuarios_sistema,email_institucional,' . $id,
                'configuraciones_usuario' => 'nullable|array',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // No permitir cambio de password aquí - usar endpoint específico
            $datosActualizacion = $request->except(['password', 'password_hash']);
            $datosActualizacion['updated_by'] = 1; // TODO: Obtener del usuario autenticado
            $datosActualizacion['version'] = $usuarioSistema->version + 1;

            $usuarioSistema->update($datosActualizacion);

            $usuarioSistema->load([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario del sistema actualizado correctamente',
                'data' => $usuarioSistema
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario del sistema',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar usuario del sistema (soft delete)
     * DELETE /api/personal/usuarios-sistema/{id}
     */
    public function destroy($id)
    {
        try {
            $usuarioSistema = UsuarioSistema::find($id);

            if (!$usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario del sistema no encontrado'
                ], 404);
            }

            $usuarioSistema->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $usuarioSistema->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario del sistema eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario del sistema',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar contraseña
     * POST /api/personal/usuarios-sistema/{id}/cambiar-password
     */
    public function cambiarPassword(Request $request, $id)
    {
        try {
            $usuarioSistema = UsuarioSistema::find($id);

            if (!$usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario del sistema no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'password_actual' => 'required|string',
                'password_nuevo' => 'required|string|min:8|confirmed',
                'requiere_cambio' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar password actual
            if (!$usuarioSistema->verificarPassword($request->password_actual)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 400);
            }

            $usuarioSistema->cambiarPassword(
                $request->password_nuevo,
                $request->get('requiere_cambio', false)
            );

            return response()->json([
                'success' => true,
                'message' => 'Contraseña cambiada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restablecer contraseña
     * POST /api/personal/usuarios-sistema/{id}/restablecer-password
     */
    public function restablecerPassword(Request $request, $id)
    {
        try {
            $usuarioSistema = UsuarioSistema::find($id);

            if (!$usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario del sistema no encontrado'
                ], 404);
            }

            // Generar password temporal
            $passwordTemporal = Str::random(12);

            $usuarioSistema->cambiarPassword($passwordTemporal, true);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña restablecida correctamente',
                'data' => [
                    'password_temporal' => $passwordTemporal,
                    'debe_cambiar' => true
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restablecer contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bloquear cuenta
     * POST /api/personal/usuarios-sistema/{id}/bloquear
     */
    public function bloquear(Request $request, $id)
    {
        try {
            $usuarioSistema = UsuarioSistema::find($id);

            if (!$usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario del sistema no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'motivo' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $usuarioSistema->bloquearCuenta($request->motivo);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta bloqueada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al bloquear cuenta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desbloquear cuenta
     * POST /api/personal/usuarios-sistema/{id}/desbloquear
     */
    public function desbloquear($id)
    {
        try {
            $usuarioSistema = UsuarioSistema::find($id);

            if (!$usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario del sistema no encontrado'
                ], 404);
            }

            $usuarioSistema->desbloquearCuenta();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta desbloqueada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desbloquear cuenta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar token de recuperación
     * POST /api/personal/usuarios-sistema/{id}/token-recuperacion
     */
    public function generarTokenRecuperacion($id)
    {
        try {
            $usuarioSistema = UsuarioSistema::find($id);

            if (!$usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario del sistema no encontrado'
                ], 404);
            }

            $usuarioSistema->generarTokenRecuperacion();

            return response()->json([
                'success' => true,
                'message' => 'Token de recuperación generado correctamente',
                'data' => [
                    'token' => $usuarioSistema->token_recuperacion,
                    'expira_en' => $usuarioSistema->fecha_expiracion_token
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar token de recuperación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de usuarios
     * GET /api/personal/usuarios-sistema/estadisticas
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_usuarios' => UsuarioSistema::activos()->count(),
                'por_estado' => [
                    'activos' => UsuarioSistema::activos()->desbloqueados()->count(),
                    'bloqueados' => UsuarioSistema::activos()->bloqueados()->count(),
                    'requieren_cambio_password' => UsuarioSistema::activos()->requierenCambioPassword()->count()
                ],
                'acceso_reciente' => [
                    'ultimo_mes' => UsuarioSistema::activos()->conAccesoReciente(30)->count(),
                    'sin_acceso_90_dias' => UsuarioSistema::activos()->sinAccesoReciente(90)->count(),
                    'nunca_accedido' => UsuarioSistema::activos()->whereNull('ultimo_acceso')->count()
                ],
                'con_token_valido' => UsuarioSistema::activos()->conTokenValido()->count(),
                'passwords_temporales' => UsuarioSistema::activos()
                    ->requierenCambioPassword()
                    ->where('fecha_cambio_password', '>', now()->subDay())
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => $estadisticas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
