<?php

namespace App\Http\Controllers;

use App\Models\CategoriaPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaPersonalController extends Controller
{
    /**
     * Listar todas las categorías de personal
     * GET /api/catalogos/categorias-personal
     */
    public function index()
    {
        try {
            $categorias = CategoriaPersonal::with(['grados' => function ($query) {
                $query->activos()->ordenadoPorJerarquia();
            }])
                ->activos()
                ->ordenadoPorJerarquia()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Categorías de personal obtenidas correctamente',
                'data' => $categorias
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva categoría de personal
     * POST /api/catalogos/categorias-personal
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_categoria' => 'required|string|max:20|unique:categorias_personal,codigo_categoria',
                'nombre_categoria' => 'required|string|max:100',
                'orden_jerarquico' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $categoria = CategoriaPersonal::create([
                'codigo_categoria' => $request->codigo_categoria,
                'nombre_categoria' => $request->nombre_categoria,
                'orden_jerarquico' => $request->orden_jerarquico,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría de personal creada correctamente',
                'data' => $categoria
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear categoría de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener categoría específica
     * GET /api/catalogos/categorias-personal/{id}
     */
    public function show($id)
    {
        try {
            $categoria = CategoriaPersonal::with('grados')->find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de personal no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoría de personal obtenida correctamente',
                'data' => $categoria
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categoría de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar categoría de personal
     * PUT /api/catalogos/categorias-personal/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $categoria = CategoriaPersonal::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de personal no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_categoria' => 'required|string|max:20|unique:categorias_personal,codigo_categoria,' . $id,
                'nombre_categoria' => 'required|string|max:100',
                'orden_jerarquico' => 'required|integer|min:1',
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
                'orden_jerarquico' => $request->orden_jerarquico,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $categoria->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría de personal actualizada correctamente',
                'data' => $categoria
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar categoría de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar categoría de personal (soft delete)
     * DELETE /api/catalogos/categorias-personal/{id}
     */
    public function destroy($id)
    {
        try {
            $categoria = CategoriaPersonal::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de personal no encontrada'
                ], 404);
            }

            // Verificar si tiene grados asociados
            if ($categoria->grados()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene grados asociados'
                ], 409);
            }

            $categoria->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoría de personal eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar categoría de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
