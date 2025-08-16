<?php

namespace App\Http\Controllers;

use App\Models\NivelPrioridad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NivelPrioridadController extends Controller
{
    /**
     * Listar todos los niveles de prioridad
     * GET /api/catalogos/niveles-prioridad
     */
    public function index()
    {
        try {
            $niveles = NivelPrioridad::activos()->orderBy('nivel_numerico', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Niveles de prioridad obtenidos correctamente',
                'data' => $niveles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener niveles de prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo nivel de prioridad
     * POST /api/catalogos/niveles-prioridad
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:20|unique:niveles_prioridad,codigo',
                'nombre' => 'required|string|max:100',
                'nivel_numerico' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $nivel = NivelPrioridad::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'nivel_numerico' => $request->nivel_numerico,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nivel de prioridad creado correctamente',
                'data' => $nivel
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nivel de prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener nivel de prioridad específico
     * GET /api/catalogos/niveles-prioridad/{id}
     */
    public function show($id)
    {
        try {
            $nivel = NivelPrioridad::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de prioridad no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Nivel de prioridad obtenido correctamente',
                'data' => $nivel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener nivel de prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar nivel de prioridad
     * PUT /api/catalogos/niveles-prioridad/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $nivel = NivelPrioridad::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de prioridad no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:20|unique:niveles_prioridad,codigo,' . $id,
                'nombre' => 'required|string|max:100',
                'nivel_numerico' => 'required|integer',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $nivel->update([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'nivel_numerico' => $request->nivel_numerico,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $nivel->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nivel de prioridad actualizado correctamente',
                'data' => $nivel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar nivel de prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar nivel de prioridad (soft delete)
     * DELETE /api/catalogos/niveles-prioridad/{id}
     */
    public function destroy($id)
    {
        try {
            $nivel = NivelPrioridad::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de prioridad no encontrado'
                ], 404);
            }

            $nivel->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $nivel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nivel de prioridad eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar nivel de prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
