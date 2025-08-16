<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\EstructuraMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CargoController extends Controller
{
    /**
     * Buscar cargos por texto (para autocompletado)
     * GET /api/organizacion/cargos/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $estructura_militar_id = $request->get('estructura_militar_id', null);
            $nivel_jerarquico = $request->get('nivel_jerarquico', null);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingrese al menos 2 caracteres para buscar',
                    'data' => []
                ], 200);
            }

            $cargos = Cargo::query()
                ->with('estructuraMilitar')
                ->activos()
                ->where(function ($q) use ($query) {
                    $q->where('nombre_cargo', 'ILIKE', "%{$query}%")
                        ->orWhere('codigo_cargo', 'ILIKE', "%{$query}%");
                });

            // Filtros adicionales
            if ($estructura_militar_id) {
                $cargos->porEstructura($estructura_militar_id);
            }

            if ($nivel_jerarquico) {
                $cargos->where('nivel_jerarquico', $nivel_jerarquico);
            }

            $cargos = $cargos->ordenadoPorJerarquia()
                ->limit(20)
                ->get(['id', 'estructura_militar_id', 'codigo_cargo', 'nombre_cargo', 'nivel_jerarquico']);

            return response()->json([
                'success' => true,
                'message' => 'Cargos encontrados',
                'data' => $cargos,
                'total' => $cargos->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar cargos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos los cargos
     * GET /api/organizacion/cargos
     */
    public function index(Request $request)
    {
        try {
            $query = Cargo::with('estructuraMilitar')
                ->activos()
                ->ordenadoPorJerarquia();

            if ($request->has('estructura_militar_id')) {
                $query->porEstructura($request->estructura_militar_id);
            }

            if ($request->has('nivel_jerarquico')) {
                $query->where('nivel_jerarquico', $request->nivel_jerarquico);
            }

            $cargos = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Cargos obtenidos correctamente',
                'data' => $cargos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cargos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo cargo
     * POST /api/organizacion/cargos
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'estructura_militar_id' => 'required|integer|exists:estructura_militar,id',
                'codigo_cargo' => 'required|string|max:50|unique:cargos,codigo_cargo',
                'nombre_cargo' => 'required|string|max:200',
                'nivel_jerarquico' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $cargo = Cargo::create([
                'estructura_militar_id' => $request->estructura_militar_id,
                'codigo_cargo' => $request->codigo_cargo,
                'nombre_cargo' => $request->nombre_cargo,
                'nivel_jerarquico' => $request->nivel_jerarquico,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $cargo->load('estructuraMilitar');

            return response()->json([
                'success' => true,
                'message' => 'Cargo creado correctamente',
                'data' => $cargo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cargo específico
     * GET /api/organizacion/cargos/{id}
     */
    public function show($id)
    {
        try {
            $cargo = Cargo::with('estructuraMilitar')->find($id);

            if (!$cargo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cargo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cargo obtenido correctamente',
                'data' => $cargo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cargo
     * PUT /api/organizacion/cargos/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $cargo = Cargo::find($id);

            if (!$cargo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cargo no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'estructura_militar_id' => 'required|integer|exists:estructura_militar,id',
                'codigo_cargo' => 'required|string|max:50|unique:cargos,codigo_cargo,' . $id,
                'nombre_cargo' => 'required|string|max:200',
                'nivel_jerarquico' => 'required|integer|min:1',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $cargo->update([
                'estructura_militar_id' => $request->estructura_militar_id,
                'codigo_cargo' => $request->codigo_cargo,
                'nombre_cargo' => $request->nombre_cargo,
                'nivel_jerarquico' => $request->nivel_jerarquico,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $cargo->version + 1
            ]);

            $cargo->load('estructuraMilitar');

            return response()->json([
                'success' => true,
                'message' => 'Cargo actualizado correctamente',
                'data' => $cargo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cargo (soft delete)
     * DELETE /api/organizacion/cargos/{id}
     */
    public function destroy($id)
    {
        try {
            $cargo = Cargo::find($id);

            if (!$cargo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cargo no encontrado'
                ], 404);
            }

            $cargo->update([
                'deleted_by' => 1,
            ]);

            $cargo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cargo eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cargos por estructura militar
     * GET /api/organizacion/cargos/por-estructura/{estructura_id}
     */
    public function porEstructura($estructuraId)
    {
        try {
            $estructura = EstructuraMilitar::find($estructuraId);

            if (!$estructura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estructura militar no encontrada'
                ], 404);
            }

            $cargos = Cargo::with('estructuraMilitar')
                ->porEstructura($estructuraId)
                ->activos()
                ->ordenadoPorJerarquia()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Cargos por estructura obtenidos correctamente',
                'data' => [
                    'estructura' => $estructura,
                    'cargos' => $cargos
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cargos por estructura',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
