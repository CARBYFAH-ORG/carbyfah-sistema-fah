<?php

namespace App\Http\Controllers;

use App\Models\NivelSeguridad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NivelSeguridadController extends Controller
{
    /**
     * Listar todos los niveles de seguridad
     * GET /api/catalogos/niveles-seguridad
     */
    public function index()
    {
        try {
            $niveles = NivelSeguridad::activos()->orderBy('nivel_numerico', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Niveles de seguridad obtenidos correctamente',
                'data' => $niveles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener niveles de seguridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo nivel de seguridad
     * POST /api/catalogos/niveles-seguridad
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:20|unique:niveles_seguridad,codigo',
                'nombre' => 'required|string|max:100',
                'nivel_numerico' => 'required|integer',
                'requiere_autorizacion' => 'boolean',
                'tiempo_retencion_anos' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $nivel = NivelSeguridad::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'nivel_numerico' => $request->nivel_numerico,
                'requiere_autorizacion' => $request->requiere_autorizacion ?? false,
                'tiempo_retencion_anos' => $request->tiempo_retencion_anos,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nivel de seguridad creado correctamente',
                'data' => $nivel
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nivel de seguridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener nivel de seguridad específico
     * GET /api/catalogos/niveles-seguridad/{id}
     */
    public function show($id)
    {
        try {
            $nivel = NivelSeguridad::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de seguridad no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Nivel de seguridad obtenido correctamente',
                'data' => $nivel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener nivel de seguridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar nivel de seguridad
     * PUT /api/catalogos/niveles-seguridad/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $nivel = NivelSeguridad::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de seguridad no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:20|unique:niveles_seguridad,codigo,' . $id,
                'nombre' => 'required|string|max:100',
                'nivel_numerico' => 'required|integer',
                'requiere_autorizacion' => 'boolean',
                'tiempo_retencion_anos' => 'nullable|integer',
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
                'requiere_autorizacion' => $request->requiere_autorizacion ?? false,
                'tiempo_retencion_anos' => $request->tiempo_retencion_anos,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $nivel->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nivel de seguridad actualizado correctamente',
                'data' => $nivel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar nivel de seguridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar nivel de seguridad (soft delete)
     * DELETE /api/catalogos/niveles-seguridad/{id}
     */
    public function destroy($id)
    {
        try {
            $nivel = NivelSeguridad::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de seguridad no encontrado'
                ], 404);
            }

            $nivel->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $nivel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nivel de seguridad eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar nivel de seguridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
