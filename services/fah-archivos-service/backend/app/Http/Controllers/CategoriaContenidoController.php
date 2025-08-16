<?php

namespace App\Http\Controllers;

use App\Models\CategoriaContenido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaContenidoController extends Controller
{
    /**
     * Listar todas las categorías de contenido
     * GET /api/archivos/categorias-contenido
     */
    public function index()
    {
        try {
            $categorias = CategoriaContenido::activos()
                ->orderBy('codigo_categoria', 'asc')
                ->get();
            // SIN with() porque es tabla independiente

            return response()->json([
                'success' => true,
                'message' => 'Categorías de contenido obtenidas correctamente',
                'data' => $categorias
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías de contenido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva categoría de contenido
     * POST /api/archivos/categorias-contenido
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_categoria' => 'required|string|max:50|unique:categorias_contenido,codigo_categoria',
                'nombre_categoria' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'requiere_clasificacion' => 'boolean',
                'permite_descarga_publica' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $categoria = CategoriaContenido::create([
                'codigo_categoria' => $request->codigo_categoria,
                'nombre_categoria' => $request->nombre_categoria,
                'descripcion' => $request->descripcion,
                'requiere_clasificacion' => $request->requiere_clasificacion ?? false,
                'permite_descarga_publica' => $request->permite_descarga_publica ?? false,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría de contenido creada correctamente',
                'data' => $categoria
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear categoría de contenido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener categoría específica
     * GET /api/archivos/categorias-contenido/{id}
     */
    public function show($id)
    {
        try {
            $categoria = CategoriaContenido::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de contenido no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoría de contenido obtenida correctamente',
                'data' => $categoria
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categoría de contenido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar categoría de contenido
     * PUT /api/archivos/categorias-contenido/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $categoria = CategoriaContenido::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de contenido no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_categoria' => 'required|string|max:50|unique:categorias_contenido,codigo_categoria,' . $id,
                'nombre_categoria' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'requiere_clasificacion' => 'boolean',
                'permite_descarga_publica' => 'boolean',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $categoria->update([
                'codigo_categoria' => $request->codigo_categoria,
                'nombre_categoria' => $request->nombre_categoria,
                'descripcion' => $request->descripcion,
                'requiere_clasificacion' => $request->requiere_clasificacion ?? false,
                'permite_descarga_publica' => $request->permite_descarga_publica ?? false,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $categoria->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría de contenido actualizada correctamente',
                'data' => $categoria
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar categoría de contenido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar categoría de contenido (soft delete)
     * DELETE /api/archivos/categorias-contenido/{id}
     */
    public function destroy($id)
    {
        try {
            $categoria = CategoriaContenido::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de contenido no encontrada'
                ], 404);
            }

            // Verificar si tiene archivos asociados
            if ($categoria->archivos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene archivos asociados'
                ], 409);
            }

            $categoria->update([
                'deleted_by' => 1,
            ]);

            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoría de contenido eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar categoría de contenido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
