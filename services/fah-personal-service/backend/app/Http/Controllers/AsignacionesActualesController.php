<?php

namespace App\Http\Controllers;

use App\Models\AsignacionActual;
use App\Models\PerfilMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AsignacionesActualesController extends Controller
{
    /**
     * Listar todas las asignaciones actuales
     * GET /api/personal/asignaciones-actuales
     */
    public function index(Request $request)
    {
        try {
            $query = AsignacionActual::with([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'estructuraMilitar',
                'cargo'
            ])->activos();

            // Filtros opcionales
            if ($request->has('unidad_id')) {
                $query->porUnidad($request->unidad_id);
            }

            if ($request->has('cargo_id')) {
                $query->porCargo($request->cargo_id);
            }

            if ($request->has('perfil_militar_id')) {
                $query->porPersonal($request->perfil_militar_id);
            }

            if ($request->has('vigentes')) {
                if ($request->vigentes === 'true') {
                    $query->vigentes();
                } elseif ($request->vigentes === 'false') {
                    $query->vencidas();
                }
            }

            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->iniciadasEntre($request->fecha_inicio, $request->fecha_fin);
            }

            $asignaciones = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Asignaciones actuales obtenidas correctamente',
                'data' => $asignaciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones actuales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva asignación actual
     * POST /api/personal/asignaciones-actuales
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'perfil_militar_id' => 'required|integer|exists:personal.perfiles_militares,id',
                'estructura_militar_id' => 'nullable|integer|exists:organizacion.estructura_militar,id',
                'cargo_id' => 'nullable|integer|exists:organizacion.cargos,id',
                'fecha_inicio_asignacion' => 'required|date|before_or_equal:today',
                'fecha_fin_asignacion' => 'nullable|date|after:fecha_inicio_asignacion'
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

            // Verificar si ya tiene una asignación vigente
            $asignacionExistente = AsignacionActual::porPersonal($request->perfil_militar_id)
                ->activos()
                ->vigentes()
                ->first();

            if ($asignacionExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El militar ya tiene una asignación vigente. Debe finalizar la actual antes de crear una nueva.',
                    'asignacion_existente' => $asignacionExistente->asignacion_completa
                ], 409);
            }

            // Verificar conflictos de fechas
            $nuevaAsignacion = new AsignacionActual($request->all());
            if ($nuevaAsignacion->tieneConflictosFecha($request->fecha_inicio_asignacion, $request->fecha_fin_asignacion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Existe conflicto de fechas con otras asignaciones del mismo militar'
                ], 409);
            }

            $asignacionActual = AsignacionActual::create(array_merge(
                $request->all(),
                [
                    'created_by' => 1, // TODO: Obtener del usuario autenticado
                    'updated_by' => 1
                ]
            ));

            $asignacionActual->load([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'estructuraMilitar',
                'cargo'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asignación actual creada correctamente',
                'data' => $asignacionActual
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear asignación actual',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener asignación actual específica
     * GET /api/personal/asignaciones-actuales/{id}
     */
    public function show($id)
    {
        try {
            $asignacionActual = AsignacionActual::with([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'perfilMilitar.categoriaPersonal',
                'perfilMilitar.especialidad',
                'estructuraMilitar',
                'cargo'
            ])->find($id);

            if (!$asignacionActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación actual no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Asignación actual obtenida correctamente',
                'data' => $asignacionActual
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignación actual',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar asignación actual
     * PUT /api/personal/asignaciones-actuales/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $asignacionActual = AsignacionActual::find($id);

            if (!$asignacionActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación actual no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'perfil_militar_id' => 'required|integer|exists:personal.perfiles_militares,id',
                'estructura_militar_id' => 'nullable|integer|exists:organizacion.estructura_militar,id',
                'cargo_id' => 'nullable|integer|exists:organizacion.cargos,id',
                'fecha_inicio_asignacion' => 'required|date|before_or_equal:today',
                'fecha_fin_asignacion' => 'nullable|date|after:fecha_inicio_asignacion',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar conflictos de fechas si cambiaron
            if ($request->has('fecha_inicio_asignacion') || $request->has('fecha_fin_asignacion')) {
                if ($asignacionActual->tieneConflictosFecha($request->fecha_inicio_asignacion, $request->fecha_fin_asignacion)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Existe conflicto de fechas con otras asignaciones del mismo militar'
                    ], 409);
                }
            }

            $asignacionActual->update(array_merge(
                $request->all(),
                [
                    'updated_by' => 1, // TODO: Obtener del usuario autenticado
                    'version' => $asignacionActual->version + 1
                ]
            ));

            $asignacionActual->load([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'estructuraMilitar',
                'cargo'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asignación actual actualizada correctamente',
                'data' => $asignacionActual
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar asignación actual',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar asignación actual (soft delete)
     * DELETE /api/personal/asignaciones-actuales/{id}
     */
    public function destroy($id)
    {
        try {
            $asignacionActual = AsignacionActual::find($id);

            if (!$asignacionActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación actual no encontrada'
                ], 404);
            }

            $asignacionActual->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $asignacionActual->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asignación actual eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar asignación actual',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar asignación actual
     * POST /api/personal/asignaciones-actuales/{id}/finalizar
     */
    public function finalizar(Request $request, $id)
    {
        try {
            $asignacionActual = AsignacionActual::find($id);

            if (!$asignacionActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación actual no encontrada'
                ], 404);
            }

            if (!$asignacionActual->es_vigente) {
                return response()->json([
                    'success' => false,
                    'message' => 'La asignación ya ha sido finalizada'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'fecha_fin' => 'nullable|date|after_or_equal:' . $asignacionActual->fecha_inicio_asignacion
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fecha de finalización inválida',
                    'errors' => $validator->errors()
                ], 400);
            }

            $fechaFin = $request->fecha_fin ?? now()->toDateString();
            $asignacionActual->finalizar($fechaFin);

            return response()->json([
                'success' => true,
                'message' => 'Asignación finalizada correctamente',
                'data' => $asignacionActual->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar asignación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extender asignación actual
     * POST /api/personal/asignaciones-actuales/{id}/extender
     */
    public function extender(Request $request, $id)
    {
        try {
            $asignacionActual = AsignacionActual::find($id);

            if (!$asignacionActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación actual no encontrada'
                ], 404);
            }

            if (!$asignacionActual->puede_ser_extendida) {
                return response()->json([
                    'success' => false,
                    'message' => 'La asignación no puede ser extendida'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'nueva_fecha_fin' => 'required|date|after:' . $asignacionActual->fecha_inicio_asignacion
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nueva fecha de finalización inválida',
                    'errors' => $validator->errors()
                ], 400);
            }

            $asignacionActual->extender($request->nueva_fecha_fin);

            return response()->json([
                'success' => true,
                'message' => 'Asignación extendida correctamente',
                'data' => $asignacionActual->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al extender asignación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignaciones por unidad
     * GET /api/personal/asignaciones-actuales/por-unidad/{unidadId}
     */
    public function porUnidad($unidadId)
    {
        try {
            $asignaciones = AsignacionActual::porUnidad($unidadId)
                ->activos()
                ->vigentes()
                ->with([
                    'perfilMilitar.datosPersonales',
                    'perfilMilitar.gradoActual',
                    'cargo'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Asignaciones por unidad obtenidas correctamente',
                'data' => $asignaciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones por unidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignaciones próximas a vencer
     * GET /api/personal/asignaciones-actuales/por-vencer
     */
    public function porVencer(Request $request)
    {
        try {
            $diasAlerta = $request->get('dias_alerta', 30);

            $asignaciones = AsignacionActual::activos()
                ->vigentes()
                ->whereNotNull('fecha_fin_asignacion')
                ->where('fecha_fin_asignacion', '<=', now()->addDays($diasAlerta))
                ->with([
                    'perfilMilitar.datosPersonales',
                    'perfilMilitar.gradoActual',
                    'estructuraMilitar',
                    'cargo'
                ])
                ->get()
                ->map(function ($asignacion) {
                    $data = $asignacion->asignacion_completa;
                    $data['dias_restantes'] = now()->diffInDays($asignacion->fecha_fin_asignacion, false);
                    $data['tipo_alerta'] = $data['dias_restantes'] <= 7 ? 'CRITICA' : 'ADVERTENCIA';
                    return $data;
                });

            return response()->json([
                'success' => true,
                'message' => 'Asignaciones próximas a vencer obtenidas correctamente',
                'data' => $asignaciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones próximas a vencer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de asignaciones
     * GET /api/personal/asignaciones-actuales/estadisticas
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_asignaciones' => AsignacionActual::activos()->count(),
                'vigentes' => AsignacionActual::activos()->vigentes()->count(),
                'vencidas' => AsignacionActual::activos()->vencidas()->count(),
                'temporales' => AsignacionActual::activos()->whereNotNull('fecha_fin_asignacion')->count(),
                'permanentes' => AsignacionActual::activos()->whereNull('fecha_fin_asignacion')->count(),
                'por_vencer_30_dias' => AsignacionActual::activos()
                    ->vigentes()
                    ->whereNotNull('fecha_fin_asignacion')
                    ->where('fecha_fin_asignacion', '<=', now()->addDays(30))
                    ->count(),
                'por_unidad' => AsignacionActual::activos()
                    ->vigentes()
                    ->join('organizacion.estructura_militar', 'personal.asignaciones_actuales.estructura_militar_id', '=', 'organizacion.estructura_militar.id')
                    ->selectRaw('organizacion.estructura_militar.nombre_unidad as unidad, COUNT(*) as total')
                    ->groupBy('organizacion.estructura_militar.nombre_unidad')
                    ->get(),
                'duracion_promedio_dias' => AsignacionActual::activos()
                    ->whereNotNull('fecha_fin_asignacion')
                    ->selectRaw('AVG(DATE_PART(\'day\', fecha_fin_asignacion - fecha_inicio_asignacion)) as promedio')
                    ->first()
                    ->promedio ?? 0
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
