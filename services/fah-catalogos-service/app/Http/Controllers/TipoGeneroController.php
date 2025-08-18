<?php

namespace App\Http\Controllers;

use App\Models\TipoGenero;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoGeneroController extends Controller
{
    // Listar todos los tipos de genero
    public function index()
    {
        try {
            $tiposGenero = TipoGenero::activos()->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de género obtenidos correctamente',
                'data' => $tiposGenero
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de género',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear nuevo tipo de genero
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:10|unique:tipos_genero,codigo',
                'nombre' => 'required|string|max:50',
                'abreviatura' => 'nullable|string|max:5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipoGenero = TipoGenero::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'abreviatura' => $request->abreviatura,
                'created_by' => 1,
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de género creado correctamente',
                'data' => $tipoGenero
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de género',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener tipo de genero específico
    public function show($id)
    {
        try {
            $tipoGenero = TipoGenero::find($id);

            if (!$tipoGenero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de género no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de género obtenido correctamente',
                'data' => $tipoGenero
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de género',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar tipo de genero
    public function update(Request $request, $id)
    {
        try {
            $tipoGenero = TipoGenero::find($id);

            if (!$tipoGenero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de género no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:10|unique:tipos_genero,codigo,' . $id,
                'nombre' => 'required|string|max:50',
                'abreviatura' => 'nullable|string|max:5',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipoGenero->update([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'abreviatura' => $request->abreviatura,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $tipoGenero->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de género actualizado correctamente',
                'data' => $tipoGenero
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de género',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar tipo de genero (soft delete)
    public function destroy($id)
    {
        try {
            $tipoGenero = TipoGenero::find($id);

            if (!$tipoGenero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de género no encontrado'
                ], 404);
            }

            $tipoGenero->update([
                'deleted_by' => 1,
            ]);

            $tipoGenero->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de género eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de género',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
