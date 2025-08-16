<?php

namespace App\Http\Controllers;

use App\Models\MetadatoArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MetadatoArchivoController extends Controller
{
    /**
     * Listar todos los metadatos de archivo
     * GET /api/archivos/metadatos-archivo
     */
    public function index(Request $request)
    {
        try {
            $query = MetadatoArchivo::with('archivoDigital')
                ->activos()
                ->orderBy('orden_display', 'asc');

            // Filtrar por archivo si se especifica
            if ($request->has('archivo_id')) {
                $query->porArchivo($request->archivo_id);
            }

            // Filtrar solo indexables
            if ($request->has('indexable')) {
                $query->where('es_indexable', $request->boolean('indexable'));
            }

            $metadatos = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Metadatos de archivo obtenidos correctamente',
                'data' => $metadatos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener metadatos de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo metadato de archivo
     * POST /api/archivos/metadatos-archivo
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'archivo_digital_id' => 'required|integer|exists:digital_assets.archivos_digitales,id',
                'clave_metadato' => 'required|string|max:100',
                'valor_metadato' => 'nullable|string',
                'tipo_dato' => 'string|in:TEXT,NUMBER,DATE,BOOLEAN',
                'es_indexable' => 'boolean',
                'es_modificable' => 'boolean',
                'orden_display' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar que no exista el mismo metadato para el archivo
            $existeMetadato = MetadatoArchivo::where('archivo_digital_id', $request->archivo_digital_id)
                ->where('clave_metadato', $request->clave_metadato)
                ->exists();

            if ($existeMetadato) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un metadato con esa clave para este archivo'
                ], 409);
            }

            $metadato = MetadatoArchivo::create([
                'archivo_digital_id' => $request->archivo_digital_id,
                'clave_metadato' => $request->clave_metadato,
                'valor_metadato' => $request->valor_metadato,
                'tipo_dato' => $request->tipo_dato ?? 'TEXT',
                'es_indexable' => $request->es_indexable ?? true,
                'es_modificable' => $request->es_modificable ?? true,
                'orden_display' => $request->orden_display,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $metadato->load('archivoDigital');

            return response()->json([
                'success' => true,
                'message' => 'Metadato de archivo creado correctamente',
                'data' => $metadato
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear metadato de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener metadato específico
     * GET /api/archivos/metadatos-archivo/{id}
     */
    public function show($id)
    {
        try {
            $metadato = MetadatoArchivo::with('archivoDigital')->find($id);

            if (!$metadato) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metadato de archivo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Metadato de archivo obtenido correctamente',
                'data' => $metadato
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener metadato de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar metadato de archivo
     * PUT /api/archivos/metadatos-archivo/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $metadato = MetadatoArchivo::find($id);

            if (!$metadato) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metadato de archivo no encontrado'
                ], 404);
            }

            // Verificar si es modificable
            if (!$metadato->es_modificable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este metadato está marcado como no modificable'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'valor_metadato' => 'nullable|string',
                'tipo_dato' => 'string|in:TEXT,NUMBER,DATE,BOOLEAN',
                'es_indexable' => 'boolean',
                'es_modificable' => 'boolean',
                'orden_display' => 'nullable|integer|min:1',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $metadato->update([
                'valor_metadato' => $request->valor_metadato,
                'tipo_dato' => $request->tipo_dato ?? 'TEXT',
                'es_indexable' => $request->es_indexable ?? true,
                'es_modificable' => $request->es_modificable ?? true,
                'orden_display' => $request->orden_display,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $metadato->version + 1
            ]);

            $metadato->load('archivoDigital');

            return response()->json([
                'success' => true,
                'message' => 'Metadato de archivo actualizado correctamente',
                'data' => $metadato
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar metadato de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar metadato de archivo (soft delete)
     * DELETE /api/archivos/metadatos-archivo/{id}
     */
    public function destroy($id)
    {
        try {
            $metadato = MetadatoArchivo::find($id);

            if (!$metadato) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metadato de archivo no encontrado'
                ], 404);
            }

            // Verificar si es modificable
            if (!$metadato->es_modificable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este metadato está marcado como no modificable'
                ], 403);
            }

            $metadato->update([
                'deleted_by' => 1,
            ]);

            $metadato->delete();

            return response()->json([
                'success' => true,
                'message' => 'Metadato de archivo eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar metadato de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
