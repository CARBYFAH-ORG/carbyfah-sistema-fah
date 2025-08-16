<?php

namespace App\Http\Controllers;

use App\Models\TipoArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoArchivoController extends Controller
{
    /**
     * Listar todos los tipos de archivo
     * GET /api/archivos/tipos-archivo
     */
    public function index(Request $request)
    {
        try {
            $query = TipoArchivo::with('categoriaArchivo')
                ->activos()
                ->orderBy('extension', 'asc');

            // Filtrar por categoría si se especifica
            if ($request->has('categoria_id')) {
                $query->porCategoria($request->categoria_id);
            }

            $tipos = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de archivo obtenidos correctamente',
                'data' => $tipos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo tipo de archivo
     * POST /api/archivos/tipos-archivo
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categoria_archivo_id' => 'required|integer|exists:categorias_archivo,id',
                'extension' => 'required|string|max:10',
                'mime_type' => 'required|string|max:100',
                'tamano_maximo_mb' => 'required|integer|min:1',
                'descripcion' => 'nullable|string',
                'requiere_antivirus' => 'boolean',
                'compresion_automatica' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipo = TipoArchivo::create([
                'categoria_archivo_id' => $request->categoria_archivo_id,
                'extension' => $request->extension,
                'mime_type' => $request->mime_type,
                'tamano_maximo_mb' => $request->tamano_maximo_mb,
                'descripcion' => $request->descripcion,
                'requiere_antivirus' => $request->requiere_antivirus ?? true,
                'compresion_automatica' => $request->compresion_automatica ?? false,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $tipo->load('categoriaArchivo');

            return response()->json([
                'success' => true,
                'message' => 'Tipo de archivo creado correctamente',
                'data' => $tipo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipo específico
     * GET /api/archivos/tipos-archivo/{id}
     */
    public function show($id)
    {
        try {
            $tipo = TipoArchivo::with('categoriaArchivo')->find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de archivo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de archivo obtenido correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar tipo de archivo
     * PUT /api/archivos/tipos-archivo/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $tipo = TipoArchivo::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de archivo no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'categoria_archivo_id' => 'required|integer|exists:categorias_archivo,id',
                'extension' => 'required|string|max:10',
                'mime_type' => 'required|string|max:100',
                'tamano_maximo_mb' => 'required|integer|min:1',
                'descripcion' => 'nullable|string',
                'requiere_antivirus' => 'boolean',
                'compresion_automatica' => 'boolean',
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
                'categoria_archivo_id' => $request->categoria_archivo_id,
                'extension' => $request->extension,
                'mime_type' => $request->mime_type,
                'tamano_maximo_mb' => $request->tamano_maximo_mb,
                'descripcion' => $request->descripcion,
                'requiere_antivirus' => $request->requiere_antivirus ?? true,
                'compresion_automatica' => $request->compresion_automatica ?? false,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $tipo->version + 1
            ]);

            $tipo->load('categoriaArchivo');

            return response()->json([
                'success' => true,
                'message' => 'Tipo de archivo actualizado correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar tipo de archivo (soft delete)
     * DELETE /api/archivos/tipos-archivo/{id}
     */
    public function destroy($id)
    {
        try {
            $tipo = TipoArchivo::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de archivo no encontrado'
                ], 404);
            }

            // Verificar si tiene archivos asociados
            if ($tipo->archivosDigitales()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el tipo porque tiene archivos asociados'
                ], 409);
            }

            $tipo->update([
                'deleted_by' => 1,
            ]);

            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de archivo eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
