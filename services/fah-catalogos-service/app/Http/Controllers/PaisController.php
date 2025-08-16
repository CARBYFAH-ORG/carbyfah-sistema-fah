<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaisController extends Controller
{
    /**
     * Listar todos los países
     * GET /api/catalogos/paises
     */
    public function index()
    {
        try {
            $paises = Pais::activos()->orderBy('nombre', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Países obtenidos correctamente',
                'data' => $paises
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener países',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo país
     * POST /api/catalogos/paises
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'nombre_oficial' => 'nullable|string|max:200',
                'codigo_iso3' => 'required|string|size:3|unique:paises,codigo_iso3',
                'codigo_telefono' => 'nullable|string|max:10',
                'moneda_oficial' => 'nullable|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $pais = Pais::create([
                'nombre' => $request->nombre,
                'nombre_oficial' => $request->nombre_oficial,
                'codigo_iso3' => strtoupper($request->codigo_iso3),
                'codigo_telefono' => $request->codigo_telefono,
                'moneda_oficial' => $request->moneda_oficial,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'País creado correctamente',
                'data' => $pais
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear país',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener país específico
     * GET /api/catalogos/paises/{id}
     */
    public function show($id)
    {
        try {
            $pais = Pais::find($id);

            if (!$pais) {
                return response()->json([
                    'success' => false,
                    'message' => 'País no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'País obtenido correctamente',
                'data' => $pais
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener país',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar país
     * PUT /api/catalogos/paises/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $pais = Pais::find($id);

            if (!$pais) {
                return response()->json([
                    'success' => false,
                    'message' => 'País no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'nombre_oficial' => 'nullable|string|max:200',
                'codigo_iso3' => 'required|string|size:3|unique:paises,codigo_iso3,' . $id,
                'codigo_telefono' => 'nullable|string|max:10',
                'moneda_oficial' => 'nullable|string|max:50',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $pais->update([
                'nombre' => $request->nombre,
                'nombre_oficial' => $request->nombre_oficial,
                'codigo_iso3' => strtoupper($request->codigo_iso3),
                'codigo_telefono' => $request->codigo_telefono,
                'moneda_oficial' => $request->moneda_oficial,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $pais->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'País actualizado correctamente',
                'data' => $pais
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar país',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar país (soft delete)
     * DELETE /api/catalogos/paises/{id}
     */
    public function destroy($id)
    {
        try {
            $pais = Pais::find($id);

            if (!$pais) {
                return response()->json([
                    'success' => false,
                    'message' => 'País no encontrado'
                ], 404);
            }

            $pais->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $pais->delete();

            return response()->json([
                'success' => true,
                'message' => 'País eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar país',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar países por texto (para autocompletado)
     * GET /api/catalogos/paises/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingrese al menos 2 caracteres para buscar',
                    'data' => []
                ], 200);
            }

            $paises = Pais::query()
                ->activos()
                ->where(function ($q) use ($query) {
                    $q->where('nombre', 'ILIKE', "%{$query}%")
                        ->orWhere('nombre_oficial', 'ILIKE', "%{$query}%")
                        ->orWhere('codigo_iso3', 'ILIKE', "%{$query}%");
                })
                ->orderBy('nombre', 'asc')
                ->limit(20)
                ->get(['id', 'nombre', 'nombre_oficial', 'codigo_iso3']);

            return response()->json([
                'success' => true,
                'message' => 'Países encontrados',
                'data' => $paises,
                'total' => $paises->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar países',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
