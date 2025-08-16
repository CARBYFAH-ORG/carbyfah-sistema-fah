<?php

namespace App\Http\Controllers;

use App\Models\RepositorioStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RepositorioStorageController extends Controller
{
    /**
     * Listar todos los repositorios de storage
     * GET /api/repositorios-storage
     */
    public function index()
    {
        try {
            $repositorios = RepositorioStorage::with(['tipoStorage', 'estadoConexion', 'archivos' => function ($query) {
                $query->activos()->orderBy('created_at', 'desc');
            }])
                ->activos()
                ->orderBy('nombre_repositorio', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Repositorios de storage obtenidos correctamente',
                'data' => $repositorios
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener repositorios de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo repositorio de storage
     * POST /api/repositorios-storage
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_repositorio' => 'required|string|max:100|unique:repositorios_storage,nombre_repositorio',
                'tipo_storage_id' => 'required|integer|exists:tipos_storage,id',
                'estado_conexion_id' => 'required|integer|exists:estados_conexion,id',
                'configuracion_json' => 'nullable|array',
                'capacidad_gb' => 'nullable|integer|min:1',
                'usado_gb' => 'nullable|numeric|min:0',
                'url_base' => 'nullable|string|max:500',
                'bucket_default' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $repositorio = RepositorioStorage::create([
                'nombre_repositorio' => $request->nombre_repositorio,
                'tipo_storage_id' => $request->tipo_storage_id,
                'estado_conexion_id' => $request->estado_conexion_id,
                'configuracion_json' => $request->configuracion_json,
                'capacidad_gb' => $request->capacidad_gb,
                'usado_gb' => $request->usado_gb ?? 0,
                'url_base' => $request->url_base,
                'bucket_default' => $request->bucket_default,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $repositorio->load(['tipoStorage', 'estadoConexion']);

            return response()->json([
                'success' => true,
                'message' => 'Repositorio de storage creado correctamente',
                'data' => $repositorio
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear repositorio de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener repositorio específico
     * GET /api/repositorios-storage/{id}
     */
    public function show($id)
    {
        try {
            $repositorio = RepositorioStorage::with(['tipoStorage', 'estadoConexion', 'archivos'])->find($id);

            if (!$repositorio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Repositorio de storage no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Repositorio de storage obtenido correctamente',
                'data' => $repositorio
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener repositorio de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar repositorio de storage
     * PUT /api/repositorios-storage/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $repositorio = RepositorioStorage::find($id);

            if (!$repositorio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Repositorio de storage no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre_repositorio' => 'required|string|max:100|unique:repositorios_storage,nombre_repositorio,' . $id,
                'tipo_storage_id' => 'required|integer|exists:tipos_storage,id',
                'estado_conexion_id' => 'required|integer|exists:estados_conexion,id',
                'configuracion_json' => 'nullable|array',
                'capacidad_gb' => 'nullable|integer|min:1',
                'usado_gb' => 'nullable|numeric|min:0',
                'url_base' => 'nullable|string|max:500',
                'bucket_default' => 'nullable|string|max:100',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $repositorio->update([
                'nombre_repositorio' => $request->nombre_repositorio,
                'tipo_storage_id' => $request->tipo_storage_id,
                'estado_conexion_id' => $request->estado_conexion_id,
                'configuracion_json' => $request->configuracion_json,
                'capacidad_gb' => $request->capacidad_gb,
                'usado_gb' => $request->usado_gb ?? 0,
                'url_base' => $request->url_base,
                'bucket_default' => $request->bucket_default,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $repositorio->version + 1
            ]);

            $repositorio->load(['tipoStorage', 'estadoConexion']);

            return response()->json([
                'success' => true,
                'message' => 'Repositorio de storage actualizado correctamente',
                'data' => $repositorio
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar repositorio de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar repositorio de storage (soft delete)
     * DELETE /api/repositorios-storage/{id}
     */
    public function destroy($id)
    {
        try {
            $repositorio = RepositorioStorage::find($id);

            if (!$repositorio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Repositorio de storage no encontrado'
                ], 404);
            }

            // Verificar si tiene archivos asociados
            if ($repositorio->archivos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el repositorio porque tiene archivos asociados'
                ], 409);
            }

            $repositorio->update([
                'deleted_by' => 1,
            ]);

            $repositorio->delete();

            return response()->json([
                'success' => true,
                'message' => 'Repositorio de storage eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar repositorio de storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
