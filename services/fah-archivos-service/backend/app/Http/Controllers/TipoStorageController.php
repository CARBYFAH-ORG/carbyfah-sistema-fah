<?php

namespace App\Http\Controllers;

use App\Models\TipoStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoStorageController extends Controller
{
    /**
     * Listar todos los tipos de storage
     * GET /api/archivos/tipos-storage
     */
    public function index()
    {
        try {
            $tipos = TipoStorage::with(['repositorios' => function ($query) {
                $query->activos()->orderBy('nombre_repositorio', 'asc');
            }])
                ->activos()
                ->orderBy('costo_relativo', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de storage obtenidos correctamente',
                'data' => $tipos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo tipo de storage
     * POST /api/archivos/tipos-storage
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_tipo' => 'required|string|max:20|unique:tipos_storage,codigo_tipo',
                'nombre_tipo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'requiere_configuracion' => 'boolean',
                'soporta_versionado' => 'boolean',
                'soporta_encriptacion' => 'boolean',
                'costo_relativo' => 'integer|min:1|max:5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipo = TipoStorage::create([
                'codigo_tipo' => $request->codigo_tipo,
                'nombre_tipo' => $request->nombre_tipo,
                'descripcion' => $request->descripcion,
                'requiere_configuracion' => $request->requiere_configuracion ?? true,
                'soporta_versionado' => $request->soporta_versionado ?? false,
                'soporta_encriptacion' => $request->soporta_encriptacion ?? false,
                'costo_relativo' => $request->costo_relativo ?? 1,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de storage creado correctamente',
                'data' => $tipo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipo específico
     * GET /api/archivos/tipos-storage/{id}
     */
    public function show($id)
    {
        try {
            $tipo = TipoStorage::with('repositorios')->find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de storage no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de storage obtenido correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar tipo de storage
     * PUT /api/archivos/tipos-storage/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $tipo = TipoStorage::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de storage no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_tipo' => 'required|string|max:20|unique:tipos_storage,codigo_tipo,' . $id,
                'nombre_tipo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'requiere_configuracion' => 'boolean',
                'soporta_versionado' => 'boolean',
                'soporta_encriptacion' => 'boolean',
                'costo_relativo' => 'integer|min:1|max:5',
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
                'descripcion' => $request->descripcion,
                'requiere_configuracion' => $request->requiere_configuracion ?? true,
                'soporta_versionado' => $request->soporta_versionado ?? false,
                'soporta_encriptacion' => $request->soporta_encriptacion ?? false,
                'costo_relativo' => $request->costo_relativo ?? 1,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $tipo->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de storage actualizado correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar tipo de storage (soft delete)
     * DELETE /api/archivos/tipos-storage/{id}
     */
    public function destroy($id)
    {
        try {
            $tipo = TipoStorage::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de storage no encontrado'
                ], 404);
            }

            // Verificar si tiene repositorios asociados
            if ($tipo->repositorios()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el tipo porque tiene repositorios asociados'
                ], 409);
            }

            $tipo->update([
                'deleted_by' => 1,
            ]);

            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de storage eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
