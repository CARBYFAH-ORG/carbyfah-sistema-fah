<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EspecialidadController extends Controller
{
    /**
     * Listar todas las especialidades
     * GET /api/catalogos/especialidades
     */
    public function index()
    {
        try {
            $especialidades = Especialidad::activos()
                ->orderBy('nombre_especialidad', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Especialidades obtenidas correctamente',
                'data' => $especialidades
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener especialidades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva especialidad
     * POST /api/catalogos/especialidades
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_especialidad' => 'required|string|max:20|unique:especialidades,codigo_especialidad',
                'nombre_especialidad' => 'required|string|max:100',
                'insignia_url' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $especialidad = Especialidad::create([
                'codigo_especialidad' => $request->codigo_especialidad,
                'nombre_especialidad' => $request->nombre_especialidad,
                'insignia_url' => $request->insignia_url,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Especialidad creada correctamente',
                'data' => $especialidad
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear especialidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener especialidad específica
     * GET /api/catalogos/especialidades/{id}
     */
    public function show($id)
    {
        try {
            $especialidad = Especialidad::find($id);

            if (!$especialidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Especialidad no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Especialidad obtenida correctamente',
                'data' => $especialidad
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener especialidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar especialidad
     * PUT /api/catalogos/especialidades/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $especialidad = Especialidad::find($id);

            if (!$especialidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Especialidad no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_especialidad' => 'required|string|max:20|unique:especialidades,codigo_especialidad,' . $id,
                'nombre_especialidad' => 'required|string|max:100',
                'insignia_url' => 'nullable|string|max:500',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $especialidad->update([
                'codigo_especialidad' => $request->codigo_especialidad,
                'nombre_especialidad' => $request->nombre_especialidad,
                'insignia_url' => $request->insignia_url,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $especialidad->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Especialidad actualizada correctamente',
                'data' => $especialidad
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar especialidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar especialidad (soft delete)
     * DELETE /api/catalogos/especialidades/{id}
     */
    public function destroy($id)
    {
        try {
            $especialidad = Especialidad::find($id);

            if (!$especialidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Especialidad no encontrada'
                ], 404);
            }

            $especialidad->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $especialidad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Especialidad eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar especialidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
