<?php

// services\fah-catalogos-service\app\Http\Controllers\CategoriaPersonalController.php

namespace App\Http\Controllers;

use App\Models\CategoriaPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaPersonalController extends Controller
{
    // Listar todas las categorias de personal
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
                'message' => 'Categorias de personal obtenidas correctamente',
                'data' => $categorias
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorias de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear nueva categoria de personal
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
                    'message' => 'Datos de entrada invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $categoria = CategoriaPersonal::create([
                'codigo_categoria' => $request->codigo_categoria,
                'nombre_categoria' => $request->nombre_categoria,
                'orden_jerarquico' => $request->orden_jerarquico,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoria de personal creada correctamente',
                'data' => $categoria
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear categoria de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener categoria especifica
    public function show($id)
    {
        try {
            $categoria = CategoriaPersonal::with('grados')->find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de personal no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoria de personal obtenida correctamente',
                'data' => $categoria
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categoria de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar categoria de personal
    public function update(Request $request, $id)
    {
        try {
            $categoria = CategoriaPersonal::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de personal no encontrada'
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
                    'message' => 'Datos de entrada invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $categoria->update([
                'codigo_categoria' => $request->codigo_categoria,
                'nombre_categoria' => $request->nombre_categoria,
                'orden_jerarquico' => $request->orden_jerarquico,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $categoria->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoria de personal actualizada correctamente',
                'data' => $categoria
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar categoria de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar categoria de personal (soft delete)
    public function destroy($id)
    {
        try {
            $categoria = CategoriaPersonal::find($id);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de personal no encontrada'
                ], 404);
            }

            // Verificar si tiene grados asociados
            if ($categoria->grados()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoria porque tiene grados asociados'
                ], 409);
            }

            $categoria->update([
                'deleted_by' => 1,
            ]);

            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoria de personal eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar categoria de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
