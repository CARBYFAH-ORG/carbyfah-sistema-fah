<?php

namespace App\Http\Controllers;

use App\Models\CategoriaArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaArchivoController extends Controller
{
    /**
     * Listar todas las categorías de archivo
     * GET /api/archivos/categorias-archivo
     */
    public function index()
    {
        try {
            $categorias = CategoriaArchivo::with(['tiposArchivo' => function ($query) {
                $query->activos()->orderBy('extension', 'asc');
            }])
                ->activos()
                ->orderBy('orden_listado', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Categorías de archivo obtenidas correctamente',
                'data' => $categorias
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva categoría de archivo
     * POST /api/archivos/categorias-archivo
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_categoria' => 'required|string|max:20|unique:categorias_archivo,codigo_categoria',
                'nombre_categoria' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'extension_principal' => 'nullable|string|max:10',
                'icono_categoria' => 'nullable|string|max:50',
                'color_categoria' => 'nullable|string|max:7',
                'orden_listado' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $categoria = CategoriaArchivo::create([
                'codigo_categoria' => $request->codigo_categoria,
                'nombre_categoria' => $request->nombre_categoria,
                'descripcion' => $request->descripcion,
                'extension_principal' => $request->extension_principal,
                'icono_categoria' => $request->icono_categoria,
                'color_categoria' => $request->color_categoria,
                'orden_listado' => $request->orden_listado,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría de archivo creada correctamente',
                'data' => $categoria
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear categoría de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener categoría específica
     * GET /api/archivos/categorias-archivo/{id}
     */
    public function show($id)
    {
        try {
            $categoria = CategoriaArchivo::with('tiposArchivo')->find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de archivo no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoría de archivo obtenida correctamente',
                'data' => $categoria
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categoría de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar categoría de archivo
     * PUT /api/archivos/categorias-archivo/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $categoria = CategoriaArchivo::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de archivo no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_categoria' => 'required|string|max:20|unique:categorias_archivo,codigo_categoria,' . $id,
                'nombre_categoria' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'extension_principal' => 'nullable|string|max:10',
                'icono_categoria' => 'nullable|string|max:50',
                'color_categoria' => 'nullable|string|max:7',
                'orden_listado' => 'nullable|integer|min:1',
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
                'extension_principal' => $request->extension_principal,
                'icono_categoria' => $request->icono_categoria,
                'color_categoria' => $request->color_categoria,
                'orden_listado' => $request->orden_listado,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $categoria->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría de archivo actualizada correctamente',
                'data' => $categoria
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar categoría de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar categoría de archivo (soft delete)
     * DELETE /api/archivos/categorias-archivo/{id}
     */
    public function destroy($id)
    {
        try {
            $categoria = CategoriaArchivo::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de archivo no encontrada'
                ], 404);
            }

            // Verificar si tiene tipos de archivo asociados
            if ($categoria->tiposArchivo()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene tipos de archivo asociados'
                ], 409);
            }

            $categoria->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoría de archivo eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar categoría de archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
