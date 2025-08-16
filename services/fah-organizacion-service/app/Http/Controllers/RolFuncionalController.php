<?php

namespace App\Http\Controllers;

use App\Models\RolFuncional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolFuncionalController extends Controller
{
    /**
     * Buscar roles funcionales por texto (para autocompletado)
     * GET /api/organizacion/roles-funcionales/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $nivel_autoridad = $request->get('nivel_autoridad', null);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingrese al menos 2 caracteres para buscar',
                    'data' => []
                ], 200);
            }

            $roles = RolFuncional::query()
                ->activos()
                ->where(function ($q) use ($query) {
                    $q->where('nombre_rol', 'ILIKE', "%{$query}%")
                        ->orWhere('codigo_rol', 'ILIKE', "%{$query}%");
                });

            // Filtrar por nivel de autoridad si se especifica
            if ($nivel_autoridad) {
                $roles->porNivelAutoridad($nivel_autoridad);
            }

            $roles = $roles->ordenadoPorAutoridad()
                ->limit(20)
                ->get(['id', 'codigo_rol', 'nombre_rol', 'nivel_autoridad']);

            return response()->json([
                'success' => true,
                'message' => 'Roles funcionales encontrados',
                'data' => $roles,
                'total' => $roles->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar roles funcionales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos los roles funcionales
     * GET /api/organizacion/roles-funcionales
     */
    public function index(Request $request)
    {
        try {
            $query = RolFuncional::activos()
                ->ordenadoPorAutoridad();

            if ($request->has('nivel_autoridad')) {
                $query->porNivelAutoridad($request->nivel_autoridad);
            }

            $roles = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles funcionales obtenidos correctamente',
                'data' => $roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles funcionales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo rol funcional
     * POST /api/organizacion/roles-funcionales
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_rol' => 'required|string|max:50|unique:roles_funcionales,codigo_rol',
                'nombre_rol' => 'required|string|max:200',
                'nivel_autoridad' => 'required|integer|min:1|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $rol = RolFuncional::create([
                'codigo_rol' => $request->codigo_rol,
                'nombre_rol' => $request->nombre_rol,
                'nivel_autoridad' => $request->nivel_autoridad,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rol funcional creado correctamente',
                'data' => $rol
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear rol funcional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener rol funcional específico
     * GET /api/organizacion/roles-funcionales/{id}
     */
    public function show($id)
    {
        try {
            $rol = RolFuncional::find($id);

            if (!$rol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol funcional no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rol funcional obtenido correctamente',
                'data' => $rol
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener rol funcional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar rol funcional
     * PUT /api/organizacion/roles-funcionales/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $rol = RolFuncional::find($id);

            if (!$rol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol funcional no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_rol' => 'required|string|max:50|unique:roles_funcionales,codigo_rol,' . $id,
                'nombre_rol' => 'required|string|max:200',
                'nivel_autoridad' => 'required|integer|min:1|max:10',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $rol->update([
                'codigo_rol' => $request->codigo_rol,
                'nombre_rol' => $request->nombre_rol,
                'nivel_autoridad' => $request->nivel_autoridad,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $rol->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rol funcional actualizado correctamente',
                'data' => $rol
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar rol funcional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar rol funcional (soft delete)
     * DELETE /api/organizacion/roles-funcionales/{id}
     */
    public function destroy($id)
    {
        try {
            $rol = RolFuncional::find($id);

            if (!$rol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol funcional no encontrado'
                ], 404);
            }

            $rol->update([
                'deleted_by' => 1,
            ]);

            $rol->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol funcional eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar rol funcional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener roles por nivel de autoridad
     * GET /api/organizacion/roles-funcionales/por-nivel/{nivel}
     */
    public function porNivel($nivel)
    {
        try {
            $roles = RolFuncional::porNivelAutoridad($nivel)
                ->activos()
                ->ordenadoPorAutoridad()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles por nivel de autoridad obtenidos correctamente',
                'data' => [
                    'nivel_autoridad' => $nivel,
                    'roles' => $roles
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles por nivel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
