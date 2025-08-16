<?php

namespace App\Http\Controllers;

use App\Models\VersionArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VersionArchivoController extends Controller
{
    /**
     * Listar todas las versiones de archivo
     * GET /api/archivos/versiones-archivo
     */
    public function index(Request $request)
    {
        try {
            $query = VersionArchivo::with(['archivoDigital', 'motivoCambio'])
                ->activos()
                ->orderBy('numero_version', 'desc');

            // Filtrar por archivo si se especifica
            if ($request->has('archivo_id')) {
                $query->porArchivo($request->archivo_id);
            }

            $versiones = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Versiones de archivo obtenidas correctamente',
                'data' => $versiones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener versiones de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva versión de archivo
     * POST /api/archivos/versiones-archivo
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'archivo_digital_id' => 'required|integer|exists:digital_assets.archivos_digitales,id',
                'numero_version' => 'required|integer|min:1',
                'ruta_storage_version' => 'required|string|max:500',
                'tamano_bytes' => 'required|integer|min:1',
                'checksum_md5' => 'required|string|size:32',
                'modificado_por_id' => 'required|integer',
                'comentarios_version' => 'nullable|string',
                'motivo_cambio_id' => 'required|integer|exists:digital_assets.motivos_cambio,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $version = VersionArchivo::create([
                'archivo_digital_id' => $request->archivo_digital_id,
                'numero_version' => $request->numero_version,
                'ruta_storage_version' => $request->ruta_storage_version,
                'tamano_bytes' => $request->tamano_bytes,
                'checksum_md5' => $request->checksum_md5,
                'modificado_por_id' => $request->modificado_por_id,
                'fecha_modificacion' => now(),
                'comentarios_version' => $request->comentarios_version,
                'motivo_cambio_id' => $request->motivo_cambio_id,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $version->load(['archivoDigital', 'motivoCambio']);

            return response()->json([
                'success' => true,
                'message' => 'Versión de archivo creada correctamente',
                'data' => $version
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear versión de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener versión específica
     * GET /api/archivos/versiones-archivo/{id}
     */
    public function show($id)
    {
        try {
            $version = VersionArchivo::with(['archivoDigital', 'motivoCambio'])->find($id);

            if (!$version) {
                return response()->json([
                    'success' => false,
                    'message' => 'Versión de archivo no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Versión de archivo obtenida correctamente',
                'data' => $version
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener versión de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar versión de archivo
     * PUT /api/archivos/versiones-archivo/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $version = VersionArchivo::find($id);

            if (!$version) {
                return response()->json([
                    'success' => false,
                    'message' => 'Versión de archivo no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'comentarios_version' => 'nullable|string',
                'motivo_cambio_id' => 'required|integer|exists:digital_assets.motivos_cambio,id',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $version->update([
                'comentarios_version' => $request->comentarios_version,
                'motivo_cambio_id' => $request->motivo_cambio_id,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $version->version + 1
            ]);

            $version->load(['archivoDigital', 'motivoCambio']);

            return response()->json([
                'success' => true,
                'message' => 'Versión de archivo actualizada correctamente',
                'data' => $version
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar versión de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar versión de archivo (soft delete)
     * DELETE /api/archivos/versiones-archivo/{id}
     */
    public function destroy($id)
    {
        try {
            $version = VersionArchivo::find($id);

            if (!$version) {
                return response()->json([
                    'success' => false,
                    'message' => 'Versión de archivo no encontrada'
                ], 404);
            }

            $version->update([
                'deleted_by' => 1,
            ]);

            $version->delete();

            return response()->json([
                'success' => true,
                'message' => 'Versión de archivo eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar versión de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
