<?php

namespace App\Http\Controllers;

use App\Models\EstadoConexion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EstadoConexionController extends Controller
{
    /**
     * Listar todos los estados de conexión
     * GET /api/archivos/estados-conexion
     */
    public function index()
    {
        try {
            $estados = EstadoConexion::with(['repositorios' => function ($query) {
                $query->activos()->orderBy('nombre_repositorio', 'asc');
            }])
                ->activos()
                ->orderBy('codigo_estado', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Estados de conexión obtenidos correctamente',
                'data' => $estados
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estados de conexión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo estado de conexión
     * POST /api/archivos/estados-conexion
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_estado' => 'required|string|max:20|unique:estados_conexion,codigo_estado',
                'nombre_estado' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'permite_operaciones' => 'boolean',
                'requiere_intervencion' => 'boolean',
                'color_estado' => 'nullable|string|max:7',
                'icono_estado' => 'nullable|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $estado = EstadoConexion::create([
                'codigo_estado' => $request->codigo_estado,
                'nombre_estado' => $request->nombre_estado,
                'descripcion' => $request->descripcion,
                'permite_operaciones' => $request->permite_operaciones ?? true,
                'requiere_intervencion' => $request->requiere_intervencion ?? false,
                'color_estado' => $request->color_estado,
                'icono_estado' => $request->icono_estado,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado de conexión creado correctamente',
                'data' => $estado
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear estado de conexión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estado específico
     * GET /api/archivos/estados-conexion/{id}
     */
    public function show($id)
    {
        try {
            $estado = EstadoConexion::with('repositorios')->find($id);

            if (!$estado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado de conexión no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado de conexión obtenido correctamente',
                'data' => $estado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado de conexión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado de conexión
     * PUT /api/archivos/estados-conexion/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $estado = EstadoConexion::find($id);

            if (!$estado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado de conexión no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_estado' => 'required|string|max:20|unique:estados_conexion,codigo_estado,' . $id,
                'nombre_estado' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'permite_operaciones' => 'boolean',
                'requiere_intervencion' => 'boolean',
                'color_estado' => 'nullable|string|max:7',
                'icono_estado' => 'nullable|string|max:50',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $estado->update([
                'codigo_estado' => $request->codigo_estado,
                'nombre_estado' => $request->nombre_estado,
                'descripcion' => $request->descripcion,
                'permite_operaciones' => $request->permite_operaciones ?? true,
                'requiere_intervencion' => $request->requiere_intervencion ?? false,
                'color_estado' => $request->color_estado,
                'icono_estado' => $request->icono_estado,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $estado->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado de conexión actualizado correctamente',
                'data' => $estado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado de conexión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar estado de conexión (soft delete)
     * DELETE /api/archivos/estados-conexion/{id}
     */
    public function destroy($id)
    {
        try {
            $estado = EstadoConexion::find($id);

            if (!$estado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado de conexión no encontrado'
                ], 404);
            }

            // Verificar si tiene repositorios asociados
            if ($estado->repositorios()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el estado porque tiene repositorios asociados'
                ], 409);
            }

            $estado->update([
                'deleted_by' => 1,
            ]);

            $estado->delete();

            return response()->json([
                'success' => true,
                'message' => 'Estado de conexión eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar estado de conexión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
