<?php

namespace App\Http\Controllers;

use App\Models\MotivoCambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MotivoCambioController extends Controller
{
    /**
     * Listar todos los motivos de cambio
     * GET /api/motivos-cambio
     */
    public function index()
    {
        try {
            $motivos = MotivoCambio::activos()
                ->orderBy('orden_criticidad', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Motivos de cambio obtenidos correctamente',
                'data' => $motivos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener motivos de cambio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo motivo de cambio
     * POST /api/motivos-cambio
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_motivo' => 'required|string|max:30|unique:motivos_cambio,codigo_motivo',
                'nombre_motivo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'requiere_justificacion' => 'boolean',
                'genera_notificacion' => 'boolean',
                'orden_criticidad' => 'integer|min:1|max:5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $motivo = MotivoCambio::create([
                'codigo_motivo' => $request->codigo_motivo,
                'nombre_motivo' => $request->nombre_motivo,
                'descripcion' => $request->descripcion,
                'requiere_justificacion' => $request->requiere_justificacion ?? false,
                'genera_notificacion' => $request->genera_notificacion ?? false,
                'orden_criticidad' => $request->orden_criticidad ?? 1,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Motivo de cambio creado correctamente',
                'data' => $motivo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear motivo de cambio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener motivo específico
     * GET /api/motivos-cambio/{id}
     */
    public function show($id)
    {
        try {
            $motivo = MotivoCambio::find($id);

            if (!$motivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Motivo de cambio no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Motivo de cambio obtenido correctamente',
                'data' => $motivo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener motivo de cambio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar motivo de cambio
     * PUT /api/motivos-cambio/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $motivo = MotivoCambio::find($id);

            if (!$motivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Motivo de cambio no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_motivo' => 'required|string|max:30|unique:motivos_cambio,codigo_motivo,' . $id,
                'nombre_motivo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'requiere_justificacion' => 'boolean',
                'genera_notificacion' => 'boolean',
                'orden_criticidad' => 'integer|min:1|max:5',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $motivo->update([
                'codigo_motivo' => $request->codigo_motivo,
                'nombre_motivo' => $request->nombre_motivo,
                'descripcion' => $request->descripcion,
                'requiere_justificacion' => $request->requiere_justificacion ?? false,
                'genera_notificacion' => $request->genera_notificacion ?? false,
                'orden_criticidad' => $request->orden_criticidad ?? 1,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $motivo->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Motivo de cambio actualizado correctamente',
                'data' => $motivo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar motivo de cambio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar motivo de cambio (soft delete)
     * DELETE /api/motivos-cambio/{id}
     */
    public function destroy($id)
    {
        try {
            $motivo = MotivoCambio::find($id);

            if (!$motivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Motivo de cambio no encontrado'
                ], 404);
            }

            $motivo->update([
                'deleted_by' => 1,
            ]);

            $motivo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Motivo de cambio eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar motivo de cambio',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
