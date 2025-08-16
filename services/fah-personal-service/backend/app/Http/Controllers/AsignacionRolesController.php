<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRol;
use App\Models\PerfilMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AsignacionRolesController extends Controller
{
    /**
     * Listar todas las asignaciones de roles
     * GET /api/personal/asignacion-roles
     */
    public function index(Request $request)
    {
        try {
            $query = AsignacionRol::with([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'rolFuncional'
            ])->activos();

            // Filtros opcionales
            if ($request->has('perfil_militar_id')) {
                $query->porPersonal($request->perfil_militar_id);
            }

            if ($request->has('rol_funcional_id')) {
                $query->porRol($request->rol_funcional_id);
            }

            if ($request->has('estado')) {
                switch ($request->estado) {
                    case 'vigente':
                        $query->vigentes();
                        break;
                    case 'expirado':
                        $query->expirados();
                        break;
                    case 'por_vencer':
                        $query->porVencer($request->get('dias_alerta', 30));
                        break;
                }
            }

            if ($request->has('nivel_autoridad_min')) {
                $query->conNivelAutoridad($request->nivel_autoridad_min);
            }

            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->asignadosEntre($request->fecha_inicio, $request->fecha_fin);
            }

            $asignaciones = $query->ordenadoPorAutoridad()
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Asignaciones de roles obtenidas correctamente',
                'data' => $asignaciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones de roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva asignación de rol
     * POST /api/personal/asignacion-roles
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'perfil_militar_id' => 'required|integer|exists:personal.perfiles_militares,id',
                'rol_funcional_id' => 'required|integer|exists:organizacion.roles_funcionales,id',
                'fecha_asignacion' => 'required|date|before_or_equal:today',
                'fecha_expiracion' => 'nullable|date|after:fecha_asignacion'
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

            // Verificar conflictos de roles
            $nuevaAsignacion = new AsignacionRol($request->all());
            if ($nuevaAsignacion->tieneConflictoRoles($request->fecha_asignacion, $request->fecha_expiracion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El militar ya tiene este rol asignado en el período especificado'
                ], 409);
            }

            $asignacionRol = AsignacionRol::create(array_merge(
                $request->all(),
                [
                    'created_by' => 1, // TODO: Obtener del usuario autenticado
                    'updated_by' => 1
                ]
            ));

            $asignacionRol->load([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'rolFuncional'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asignación de rol creada correctamente',
                'data' => $asignacionRol
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear asignación de rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener asignación de rol específica
     * GET /api/personal/asignacion-roles/{id}
     */
    public function show($id)
    {
        try {
            $asignacionRol = AsignacionRol::with([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'perfilMilitar.categoriaPersonal',
                'perfilMilitar.especialidad',
                'rolFuncional'
            ])->find($id);

            if (!$asignacionRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación de rol no encontrada'
                ], 404);
            }

            // Agregar información adicional
            $data = $asignacionRol->toArray();
            $data['historial_roles'] = $asignacionRol->obtenerHistorialRoles()
                ->take(5)
                ->map(function ($hist) {
                    return $hist->asignacion_completa;
                });

            return response()->json([
                'success' => true,
                'message' => 'Asignación de rol obtenida correctamente',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignación de rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar asignación de rol
     * PUT /api/personal/asignacion-roles/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $asignacionRol = AsignacionRol::find($id);

            if (!$asignacionRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación de rol no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'perfil_militar_id' => 'required|integer|exists:personal.perfiles_militares,id',
                'rol_funcional_id' => 'required|integer|exists:organizacion.roles_funcionales,id',
                'fecha_asignacion' => 'required|date|before_or_equal:today',
                'fecha_expiracion' => 'nullable|date|after:fecha_asignacion',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar conflictos de roles si cambiaron las fechas o el rol
            if ($request->has('fecha_asignacion') || $request->has('fecha_expiracion') || $request->has('rol_funcional_id')) {
                if ($asignacionRol->tieneConflictoRoles($request->fecha_asignacion, $request->fecha_expiracion)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Existe conflicto con otras asignaciones del mismo rol'
                    ], 409);
                }
            }

            $asignacionRol->update(array_merge(
                $request->all(),
                [
                    'updated_by' => 1, // TODO: Obtener del usuario autenticado
                    'version' => $asignacionRol->version + 1
                ]
            ));

            $asignacionRol->load([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'rolFuncional'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asignación de rol actualizada correctamente',
                'data' => $asignacionRol
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar asignación de rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar asignación de rol (soft delete)
     * DELETE /api/personal/asignacion-roles/{id}
     */
    public function destroy($id)
    {
        try {
            $asignacionRol = AsignacionRol::find($id);

            if (!$asignacionRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación de rol no encontrada'
                ], 404);
            }

            $asignacionRol->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $asignacionRol->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asignación de rol eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar asignación de rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revocar rol
     * POST /api/personal/asignacion-roles/{id}/revocar
     */
    public function revocar(Request $request, $id)
    {
        try {
            $asignacionRol = AsignacionRol::find($id);

            if (!$asignacionRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación de rol no encontrada'
                ], 404);
            }

            if (!$asignacionRol->es_vigente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol ya ha sido revocado o expirado'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'fecha_revocacion' => 'nullable|date|after_or_equal:' . $asignacionRol->fecha_asignacion
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fecha de revocación inválida',
                    'errors' => $validator->errors()
                ], 400);
            }

            $fechaRevocacion = $request->fecha_revocacion ?? now()->toDateString();
            $asignacionRol->revocar($fechaRevocacion);

            return response()->json([
                'success' => true,
                'message' => 'Rol revocado correctamente',
                'data' => $asignacionRol->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al revocar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extender asignación de rol
     * POST /api/personal/asignacion-roles/{id}/extender
     */
    public function extender(Request $request, $id)
    {
        try {
            $asignacionRol = AsignacionRol::find($id);

            if (!$asignacionRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación de rol no encontrada'
                ], 404);
            }

            if (!$asignacionRol->es_vigente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden extender roles vigentes'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'nueva_fecha_expiracion' => 'required|date|after:' . $asignacionRol->fecha_asignacion
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nueva fecha de expiración inválida',
                    'errors' => $validator->errors()
                ], 400);
            }

            if (!$asignacionRol->extender($request->nueva_fecha_expiracion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo extender el rol'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rol extendido correctamente',
                'data' => $asignacionRol->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al extender rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hacer rol permanente
     * POST /api/personal/asignacion-roles/{id}/hacer-permanente
     */
    public function hacerPermanente($id)
    {
        try {
            $asignacionRol = AsignacionRol::find($id);

            if (!$asignacionRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación de rol no encontrada'
                ], 404);
            }

            if ($asignacionRol->es_permanente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol ya es permanente'
                ], 400);
            }

            $asignacionRol->hacerPermanente();

            return response()->json([
                'success' => true,
                'message' => 'Rol convertido a permanente correctamente',
                'data' => $asignacionRol->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al hacer rol permanente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renovar rol
     * POST /api/personal/asignacion-roles/{id}/renovar
     */
    public function renovar(Request $request, $id)
    {
        try {
            $asignacionRol = AsignacionRol::find($id);

            if (!$asignacionRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación de rol no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'meses_adicionales' => 'nullable|integer|min:1|max:60'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Período de renovación inválido',
                    'errors' => $validator->errors()
                ], 400);
            }

            $mesesAdicionales = $request->get('meses_adicionales', 12);
            $asignacionRol->renovar($mesesAdicionales);

            return response()->json([
                'success' => true,
                'message' => "Rol renovado por {$mesesAdicionales} meses adicionales",
                'data' => $asignacionRol->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Roles vigentes por militar
     * GET /api/personal/asignacion-roles/por-militar/{perfilMilitarId}
     */
    public function porMilitar($perfilMilitarId)
    {
        try {
            $perfilMilitar = PerfilMilitar::find($perfilMilitarId);

            if (!$perfilMilitar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil militar no encontrado'
                ], 404);
            }

            $rolesVigentes = AsignacionRol::rolesVigentesPersonal($perfilMilitarId);

            return response()->json([
                'success' => true,
                'message' => 'Roles vigentes obtenidos correctamente',
                'data' => [
                    'militar' => $perfilMilitar->grado_completo,
                    'roles' => $rolesVigentes->map(function ($asignacion) {
                        return $asignacion->asignacion_completa;
                    })
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles por militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alertas de vencimiento
     * GET /api/personal/asignacion-roles/alertas-vencimiento
     */
    public function alertasVencimiento(Request $request)
    {
        try {
            $diasAlerta = $request->get('dias_alerta', 30);
            $alertas = AsignacionRol::alertasVencimiento($diasAlerta);

            return response()->json([
                'success' => true,
                'message' => 'Alertas de vencimiento obtenidas correctamente',
                'data' => $alertas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener alertas de vencimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de asignaciones
     * GET /api/personal/asignacion-roles/reporte
     */
    public function reporte(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after:fecha_inicio'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Período de reporte inválido',
                    'errors' => $validator->errors()
                ], 400);
            }

            $reporte = AsignacionRol::generarReporteAsignaciones(
                $request->fecha_inicio,
                $request->fecha_fin
            );

            return response()->json([
                'success' => true,
                'message' => 'Reporte de asignaciones generado correctamente',
                'data' => $reporte
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de asignaciones de roles
     * GET /api/personal/asignacion-roles/estadisticas
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_asignaciones' => AsignacionRol::activos()->count(),
                'vigentes' => AsignacionRol::activos()->vigentes()->count(),
                'expiradas' => AsignacionRol::activos()->expirados()->count(),
                'permanentes' => AsignacionRol::activos()->whereNull('fecha_expiracion')->count(),
                'temporales' => AsignacionRol::activos()->whereNotNull('fecha_expiracion')->count(),
                'por_vencer_30_dias' => AsignacionRol::activos()->porVencer(30)->count(),
                'por_nivel_autoridad' => AsignacionRol::activos()
                    ->vigentes()
                    ->join('organizacion.roles_funcionales', 'personal.asignacion_roles.rol_funcional_id', '=', 'organizacion.roles_funcionales.id')
                    ->selectRaw('organizacion.roles_funcionales.nivel_autoridad, COUNT(*) as total')
                    ->groupBy('organizacion.roles_funcionales.nivel_autoridad')
                    ->orderBy('organizacion.roles_funcionales.nivel_autoridad', 'desc')
                    ->get(),
                'roles_mas_asignados' => AsignacionRol::activos()
                    ->vigentes()
                    ->join('organizacion.roles_funcionales', 'personal.asignacion_roles.rol_funcional_id', '=', 'organizacion.roles_funcionales.id')
                    ->selectRaw('organizacion.roles_funcionales.nombre_rol as rol, COUNT(*) as total')
                    ->groupBy('organizacion.roles_funcionales.nombre_rol')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
                'asignaciones_ultimo_mes' => AsignacionRol::activos()
                    ->asignadosEntre(now()->subMonth(), now())
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
