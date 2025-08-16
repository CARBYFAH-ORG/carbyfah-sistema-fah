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
            $query = HistorialCargo::with([
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'cargo',
                'estructuraMilitar'
            ])->activos();

            // Filtros opcionales
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
                'perfil_militar_id' => 'required|integer|exists:personal.perfiles_militares,id',
                'cargo_id' => 'required|integer|exists:organizacion.cargos,id',
                'estructura_militar_id' => 'required|integer|exists:organizacion.estructura_militar,id',
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

            $historialCargo = HistorialCargo::create(array_merge(
                $request->all(),
                [
                    'created_by' => 1, // TODO: Obtener del usuario autenticado
                    'updated_by' => 1
                ]
            ));

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
                'perfilMilitar.datosPersonales',
                'perfilMilitar.gradoActual',
                'cargo',
                'estructuraMilitar'
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
                'perfil_militar_id' => 'required|integer|exists:personal.perfiles_militares,id',
                'cargo_id' => 'required|integer|exists:organizacion.cargos,id',
                'estructura_militar_id' => 'required|integer|exists:organizacion.estructura_militar,id',
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
     * Eliminar historial de cargo (soft delete)
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
     * Estadísticas de historiales de cargos
     * GET /api/personal/historiales-cargos/estadisticas/generales
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_registros' => 0, // Placeholder
                'cargos_vigentes' => 0,
                'cargos_finalizados' => 0
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
