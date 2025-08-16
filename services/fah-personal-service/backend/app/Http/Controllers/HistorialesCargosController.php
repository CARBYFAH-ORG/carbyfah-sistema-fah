<?php

namespace App\Http\Controllers;

use App\Models\HistorialCargo;
use App\Models\PerfilMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HistorialesCargosController extends Controller
{
    /**
     * Listar todos los historiales de cargos
     * GET /api/personal/historiales-cargos
     */
    public function index(Request $request)
    {
        try {
            // solo incluir relaciones internas del servicio personal
            $query = HistorialCargo::with([
                'perfilMilitar.datosPersonales'
            ])->activos();

            // filtros opcionales
            if ($request->has('perfil_militar_id')) {
                $query->porPersonal($request->perfil_militar_id);
            }

            if ($request->has('cargo_id')) {
                $query->porCargo($request->cargo_id);
            }

            if ($request->has('unidad_id')) {
                $query->porUnidad($request->unidad_id);
            }

            if ($request->has('estado')) {
                if ($request->estado === 'vigente') {
                    $query->vigentes();
                } elseif ($request->estado === 'finalizado') {
                    $query->finalizados();
                }
            }

            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->enPeriodo($request->fecha_inicio, $request->fecha_fin);
            }

            if ($request->has('duracion_minima_meses')) {
                $query->conDuracionMinima($request->duracion_minima_meses);
            }

            $historiales = $query->ordenadoCronologicamente()
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Historiales de cargos obtenidos correctamente',
                'data' => $historiales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historiales de cargos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo historial de cargo
     * POST /api/personal/historiales-cargos
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'perfil_militar_id' => 'required|integer|exists:perfiles_militares,id',
                // sin exists microservicios externos
                'cargo_id' => 'required|integer',
                'estructura_militar_id' => 'required|integer',
                'fecha_inicio' => 'required|date|before_or_equal:today',
                'fecha_fin' => 'nullable|date|after:fecha_inicio'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // verificar que el perfil militar existe y esta activo
            $perfilMilitar = PerfilMilitar::find($request->perfil_militar_id);
            if (!$perfilMilitar || !$perfilMilitar->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El perfil militar no existe o no está activo'
                ], 400);
            }

            $historialCargo = HistorialCargo::create(array_merge(
                $request->all(),
                [
                    'created_by' => 1,
                    'updated_by' => 1
                ]
            ));

            // solo cargar relaciones internas
            $historialCargo->load([
                'perfilMilitar.datosPersonales'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Historial de cargo creado correctamente',
                'data' => $historialCargo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear historial de cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de cargo específico
     * GET /api/personal/historiales-cargos/{id}
     */
    public function show($id)
    {
        try {
            $historialCargo = HistorialCargo::with([
                'perfilMilitar.datosPersonales'
            ])->find($id);

            if (!$historialCargo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Historial de cargo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Historial de cargo obtenido correctamente',
                'data' => $historialCargo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial de cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar historial de cargo
     * PUT /api/personal/historiales-cargos/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $historialCargo = HistorialCargo::find($id);

            if (!$historialCargo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Historial de cargo no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'perfil_militar_id' => 'required|integer|exists:perfiles_militares,id',
                // sin exists microservicios externos
                'cargo_id' => 'required|integer',
                'estructura_militar_id' => 'required|integer',
                'fecha_inicio' => 'required|date|before_or_equal:today',
                'fecha_fin' => 'nullable|date|after:fecha_inicio',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $historialCargo->update(array_merge(
                $request->all(),
                [
                    'updated_by' => 1,
                    'version' => $historialCargo->version + 1
                ]
            ));

            // solo cargar relaciones internas
            $historialCargo->load([
                'perfilMilitar.datosPersonales'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Historial de cargo actualizado correctamente',
                'data' => $historialCargo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar historial de cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar historial de cargo soft delete
     * DELETE /api/personal/historiales-cargos/{id}
     */
    public function destroy($id)
    {
        try {
            $historialCargo = HistorialCargo::find($id);

            if (!$historialCargo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Historial de cargo no encontrado'
                ], 404);
            }

            $historialCargo->update([
                'deleted_by' => 1,
            ]);

            $historialCargo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Historial de cargo eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar historial de cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Historiales por militar
     * GET /api/personal/historiales-cargos/por-militar/{perfilMilitarId}
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

            $historiales = HistorialCargo::porPersonal($perfilMilitarId)
                ->with(['perfilMilitar.datosPersonales'])
                ->ordenadoCronologicamente()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Historiales por militar obtenidos correctamente',
                'data' => [
                    'militar' => $perfilMilitar->nombre_completo_militar,
                    'historiales' => $historiales
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historiales por militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadisticas de historiales de cargos
     * GET /api/personal/historiales-cargos/estadisticas/generales
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_registros' => HistorialCargo::activos()->count(),
                'por_estado' => [
                    'vigentes' => HistorialCargo::activos()->vigentes()->count(),
                    'finalizados' => HistorialCargo::activos()->finalizados()->count()
                ],
                'duracion_promedio_meses' => HistorialCargo::activos()
                    ->finalizados()
                    ->selectRaw('AVG(EXTRACT(MONTH FROM AGE(fecha_fin, fecha_inicio))) as promedio')
                    ->first()
                    ->promedio ?? 0,
                'creados_este_mes' => HistorialCargo::activos()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
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
