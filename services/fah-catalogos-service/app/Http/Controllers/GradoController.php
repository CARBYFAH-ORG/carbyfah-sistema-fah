<?php

// services\fah-catalogos-service\app\Http\Controllers\GradoController.php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\CategoriaPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GradoController extends Controller
{
    // Listar todos los grados
    public function index(Request $request)
    {
        try {
            $query = Grado::with('categoriaPersonal')
                ->activos()
                ->ordenadoPorJerarquia();

            // Filtrar por categoria si se especifica
            if ($request->has('categoria_id')) {
                $query->porCategoria($request->categoria_id);
            }

            $grados = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Grados obtenidos correctamente',
                'data' => $grados
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear nuevo grado
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categoria_personal_id' => 'required|integer|exists:categorias_personal,id',
                'codigo_grado' => 'required|string|max:20|unique:grados,codigo_grado',
                'nombre_grado' => 'required|string|max:100',
                'abreviatura' => 'nullable|string|max:20',
                'orden_jerarquico' => 'required|integer|min:1',
                'insignia_url' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $grado = Grado::create([
                'categoria_personal_id' => $request->categoria_personal_id,
                'codigo_grado' => $request->codigo_grado,
                'nombre_grado' => $request->nombre_grado,
                'abreviatura' => $request->abreviatura,
                'orden_jerarquico' => $request->orden_jerarquico,
                'insignia_url' => $request->insignia_url,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            // Cargar la relacion para la respuesta
            $grado->load('categoriaPersonal');

            return response()->json([
                'success' => true,
                'message' => 'Grado creado correctamente',
                'data' => $grado
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear grado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener grado especifico
    public function show($id)
    {
        try {
            $grado = Grado::with('categoriaPersonal')->find($id);

            if (!$grado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grado no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Grado obtenido correctamente',
                'data' => $grado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar grado
    public function update(Request $request, $id)
    {
        try {
            $grado = Grado::find($id);

            if (!$grado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grado no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'categoria_personal_id' => 'required|integer|exists:categorias_personal,id',
                'codigo_grado' => 'required|string|max:20|unique:grados,codigo_grado,' . $id,
                'nombre_grado' => 'required|string|max:100',
                'abreviatura' => 'nullable|string|max:20',
                'orden_jerarquico' => 'required|integer|min:1',
                'insignia_url' => 'nullable|string|max:500',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $grado->update([
                'categoria_personal_id' => $request->categoria_personal_id,
                'codigo_grado' => $request->codigo_grado,
                'nombre_grado' => $request->nombre_grado,
                'abreviatura' => $request->abreviatura,
                'orden_jerarquico' => $request->orden_jerarquico,
                'insignia_url' => $request->insignia_url,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $grado->version + 1
            ]);

            $grado->load('categoriaPersonal');

            return response()->json([
                'success' => true,
                'message' => 'Grado actualizado correctamente',
                'data' => $grado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar grado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar grado (soft delete)
    public function destroy($id)
    {
        try {
            $grado = Grado::find($id);

            if (!$grado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grado no encontrado'
                ], 404);
            }

            $grado->update([
                'deleted_by' => 1,
            ]);

            $grado->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grado eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar grado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener grados por categoria
    public function porCategoria($categoriaId)
    {
        try {
            $categoria = CategoriaPersonal::find($categoriaId);

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria no encontrada'
                ], 404);
            }

            $grados = Grado::porCategoria($categoriaId)
                ->activos()
                ->ordenadoPorJerarquia()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Grados por categoria obtenidos correctamente',
                'data' => [
                    'categoria' => $categoria,
                    'grados' => $grados
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grados por categoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
