<?php

namespace App\Http\Controllers;

use App\Models\PermisoAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermisoAccesoController extends Controller
{
    /**
     * Listar todos los permisos de acceso
     * GET /api/archivos/permisos-acceso
     */
    public function index(Request $request)
    {
        try {
            $query = PermisoAcceso::with(['archivoDigital', 'tipoPermiso'])
                ->activos()
                ->orderBy('fecha_concedido', 'desc');

            // Filtrar por archivo si se especifica
            if ($request->has('archivo_id')) {
                $query->porArchivo($request->archivo_id);
            }

            // Filtrar por persona si se especifica
            if ($request->has('persona_id')) {
                $query->porPersona($request->persona_id);
            }

            $permisos = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Permisos de acceso obtenidos correctamente',
                'data' => $permisos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo permiso de acceso
     * POST /api/archivos/permisos-acceso
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'archivo_digital_id' => 'required|integer|exists:digital_assets.archivos_digitales,id',
                'persona_id' => 'nullable|integer',
                'rol_id' => 'nullable|integer',
                'tipo_permiso_id' => 'required|integer|exists:digital_assets.tipos_permiso,id',
                'concedido_por_id' => 'required|integer',
                'fecha_expiracion' => 'nullable|date|after:today',
                'motivo_permiso' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Validar que solo uno de persona_id o rol_id esté presente
            if (($request->persona_id && $request->rol_id) || (!$request->persona_id && !$request->rol_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar persona_id O rol_id, no ambos'
                ], 400);
            }

            $permiso = PermisoAcceso::create([
                'archivo_digital_id' => $request->archivo_digital_id,
                'persona_id' => $request->persona_id,
                'rol_id' => $request->rol_id,
                'tipo_permiso_id' => $request->tipo_permiso_id,
                'fecha_concedido' => now(),
                'concedido_por_id' => $request->concedido_por_id,
                'fecha_expiracion' => $request->fecha_expiracion,
                'motivo_permiso' => $request->motivo_permiso,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $permiso->load(['archivoDigital', 'tipoPermiso']);

            return response()->json([
                'success' => true,
                'message' => 'Permiso de acceso creado correctamente',
                'data' => $permiso
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear permiso de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener permiso específico
     * GET /api/archivos/permisos-acceso/{id}
     */
    public function show($id)
    {
        try {
            $permiso = PermisoAcceso::with(['archivoDigital', 'tipoPermiso'])->find($id);

            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso de acceso no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Permiso de acceso obtenido correctamente',
                'data' => $permiso
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permiso de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar permiso de acceso
     * PUT /api/archivos/permisos-acceso/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $permiso = PermisoAcceso::find($id);

            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso de acceso no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'fecha_expiracion' => 'nullable|date|after:today',
                'motivo_permiso' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $permiso->update([
                'fecha_expiracion' => $request->fecha_expiracion,
                'motivo_permiso' => $request->motivo_permiso,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $permiso->version + 1
            ]);

            $permiso->load(['archivoDigital', 'tipoPermiso']);

            return response()->json([
                'success' => true,
                'message' => 'Permiso de acceso actualizado correctamente',
                'data' => $permiso
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar permiso de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar permiso de acceso (soft delete)
     * DELETE /api/archivos/permisos-acceso/{id}
     */
    public function destroy($id)
    {
        try {
            $permiso = PermisoAcceso::find($id);

            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso de acceso no encontrado'
                ], 404);
            }

            $permiso->update([
                'deleted_by' => 1,
            ]);

            $permiso->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permiso de acceso eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar permiso de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
