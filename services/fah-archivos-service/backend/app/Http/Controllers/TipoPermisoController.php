<?php

namespace App\Http\Controllers;

use App\Models\TipoPermiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoPermisoController extends Controller
{
    /**
     * Listar todos los tipos de permiso
     * GET /api/tipos-permiso
     */
    public function index()
    {
        try {
            $tipos = TipoPermiso::activos()
                ->orderBy('nivel_criticidad', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de permiso obtenidos correctamente',
                'data' => $tipos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo tipo de permiso
     * POST /api/tipos-permiso
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_permiso' => 'required|string|max:20|unique:tipos_permiso,codigo_permiso',
                'nombre_permiso' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'nivel_criticidad' => 'integer|min:1|max:5',
                'requiere_autorizacion' => 'boolean',
                'afecta_auditoria' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipo = TipoPermiso::create([
                'codigo_permiso' => $request->codigo_permiso,
                'nombre_permiso' => $request->nombre_permiso,
                'descripcion' => $request->descripcion,
                'nivel_criticidad' => $request->nivel_criticidad ?? 1,
                'requiere_autorizacion' => $request->requiere_autorizacion ?? false,
                'afecta_auditoria' => $request->afecta_auditoria ?? true,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de permiso creado correctamente',
                'data' => $tipo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipo específico
     * GET /api/tipos-permiso/{id}
     */
    public function show($id)
    {
        try {
            $tipo = TipoPermiso::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de permiso no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de permiso obtenido correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar tipo de permiso
     * PUT /api/tipos-permiso/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $tipo = TipoPermiso::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de permiso no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_permiso' => 'required|string|max:20|unique:tipos_permiso,codigo_permiso,' . $id,
                'nombre_permiso' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'nivel_criticidad' => 'integer|min:1|max:5',
                'requiere_autorizacion' => 'boolean',
                'afecta_auditoria' => 'boolean',
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
                'codigo_permiso' => $request->codigo_permiso,
                'nombre_permiso' => $request->nombre_permiso,
                'descripcion' => $request->descripcion,
                'nivel_criticidad' => $request->nivel_criticidad ?? 1,
                'requiere_autorizacion' => $request->requiere_autorizacion ?? false,
                'afecta_auditoria' => $request->afecta_auditoria ?? true,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $tipo->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de permiso actualizado correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar tipo de permiso (soft delete)
     * DELETE /api/tipos-permiso/{id}
     */
    public function destroy($id)
    {
        try {
            $tipo = TipoPermiso::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de permiso no encontrado'
                ], 404);
            }

            $tipo->update([
                'deleted_by' => 1,
            ]);

            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de permiso eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
