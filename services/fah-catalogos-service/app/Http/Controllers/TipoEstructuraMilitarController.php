<?php

namespace App\Http\Controllers;

use App\Models\TipoEstructuraMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoEstructuraMilitarController extends Controller
{
    /**
     * Listar todos los tipos de estructura militar
     * GET /api/catalogos/tipos-estructura-militar
     */
    public function index()
    {
        try {
            $tipos = TipoEstructuraMilitar::activos()->orderBy('nivel_organizacional', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de estructura militar obtenidos correctamente',
                'data' => $tipos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo tipo de estructura militar
     * POST /api/catalogos/tipos-estructura-militar
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_tipo' => 'required|string|max:20|unique:tipos_estructura_militar,codigo_tipo',
                'nombre_tipo' => 'required|string|max:100',
                'nivel_organizacional' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipo = TipoEstructuraMilitar::create([
                'codigo_tipo' => $request->codigo_tipo,
                'nombre_tipo' => $request->nombre_tipo,
                'nivel_organizacional' => $request->nivel_organizacional,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de estructura militar creado correctamente',
                'data' => $tipo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipo de estructura militar específico
     * GET /api/catalogos/tipos-estructura-militar/{id}
     */
    public function show($id)
    {
        try {
            $tipo = TipoEstructuraMilitar::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de estructura militar no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de estructura militar obtenido correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar tipo de estructura militar
     * PUT /api/catalogos/tipos-estructura-militar/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $tipo = TipoEstructuraMilitar::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de estructura militar no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_tipo' => 'required|string|max:20|unique:tipos_estructura_militar,codigo_tipo,' . $id,
                'nombre_tipo' => 'required|string|max:100',
                'nivel_organizacional' => 'required|integer',
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
                'codigo_tipo' => $request->codigo_tipo,
                'nombre_tipo' => $request->nombre_tipo,
                'nivel_organizacional' => $request->nivel_organizacional,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $tipo->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de estructura militar actualizado correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar tipo de estructura militar (soft delete)
     * DELETE /api/catalogos/tipos-estructura-militar/{id}
     */
    public function destroy($id)
    {
        try {
            $tipo = TipoEstructuraMilitar::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de estructura militar no encontrado'
                ], 404);
            }

            $tipo->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de estructura militar eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipos por nivel organizacional
     * GET /api/catalogos/tipos-estructura-militar/por-nivel/{nivel}
     */
    public function porNivel($nivel)
    {
        try {
            $tipos = TipoEstructuraMilitar::activos()
                ->where('nivel_organizacional', $nivel)
                ->orderBy('nombre_tipo', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de estructura militar por nivel obtenidos correctamente',
                'data' => [
                    'nivel_organizacional' => $nivel,
                    'tipos' => $tipos
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos por nivel organizacional',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
