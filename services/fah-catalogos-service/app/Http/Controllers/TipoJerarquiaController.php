<?php

namespace App\Http\Controllers;

use App\Models\TipoJerarquia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoJerarquiaController extends Controller
{
    /**
     * Listar todos los tipos de jerarquía
     * GET /api/catalogos/tipos-jerarquia
     */
    public function index()
    {
        try {
            $tipos = TipoJerarquia::activos()->orderBy('nivel_autoridad', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de jerarquía obtenidos correctamente',
                'data' => $tipos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de jerarquía',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo tipo de jerarquía
     * POST /api/catalogos/tipos-jerarquia
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_tipo' => 'required|string|max:20|unique:tipos_jerarquia,codigo_tipo',
                'nombre_tipo' => 'required|string|max:100',
                'nivel_autoridad' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipo = TipoJerarquia::create([
                'codigo_tipo' => $request->codigo_tipo,
                'nombre_tipo' => $request->nombre_tipo,
                'nivel_autoridad' => $request->nivel_autoridad,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de jerarquía creado correctamente',
                'data' => $tipo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de jerarquía',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipo de jerarquía específico
     * GET /api/catalogos/tipos-jerarquia/{id}
     */
    public function show($id)
    {
        try {
            $tipo = TipoJerarquia::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de jerarquía no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de jerarquía obtenido correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de jerarquía',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar tipo de jerarquía
     * PUT /api/catalogos/tipos-jerarquia/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $tipo = TipoJerarquia::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de jerarquía no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_tipo' => 'required|string|max:20|unique:tipos_jerarquia,codigo_tipo,' . $id,
                'nombre_tipo' => 'required|string|max:100',
                'nivel_autoridad' => 'required|integer',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipo->update([
                'codigo_tipo' => $request->codigo_tipo,
                'nombre_tipo' => $request->nombre_tipo,
                'nivel_autoridad' => $request->nivel_autoridad,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $tipo->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de jerarquía actualizado correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de jerarquía',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar tipo de jerarquía (soft delete)
     * DELETE /api/catalogos/tipos-jerarquia/{id}
     */
    public function destroy($id)
    {
        try {
            $tipo = TipoJerarquia::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de jerarquía no encontrado'
                ], 404);
            }

            $tipo->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de jerarquía eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de jerarquía',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipos por nivel de autoridad
     * GET /api/catalogos/tipos-jerarquia/por-nivel/{nivel}
     */
    public function porNivel($nivel)
    {
        try {
            $tipos = TipoJerarquia::activos()
                ->where('nivel_autoridad', $nivel)
                ->orderBy('nombre_tipo', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de jerarquía por nivel obtenidos correctamente',
                'data' => [
                    'nivel_autoridad' => $nivel,
                    'tipos' => $tipos
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos por nivel de autoridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
