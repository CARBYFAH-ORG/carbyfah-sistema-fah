<?php

namespace App\Http\Controllers;

use App\Models\NivelAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NivelAccesoController extends Controller
{
    /**
     * Listar todos los niveles de acceso
     * GET /api/archivos/niveles-acceso
     */
    public function index()
    {
        try {
            $niveles = NivelAcceso::with(['archivos' => function ($query) {
                $query->activos()->orderBy('created_at', 'desc');
            }])
                ->activos()
                ->orderBy('orden_seguridad', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Niveles de acceso obtenidos correctamente',
                'data' => $niveles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener niveles de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo nivel de acceso
     * POST /api/archivos/niveles-acceso
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_nivel' => 'required|string|max:20|unique:niveles_acceso,codigo_nivel',
                'nombre_nivel' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'orden_seguridad' => 'required|integer|min:1|max:5',
                'requiere_autenticacion' => 'boolean',
                'requiere_autorizacion' => 'boolean',
                'log_accesos' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $nivel = NivelAcceso::create([
                'codigo_nivel' => $request->codigo_nivel,
                'nombre_nivel' => $request->nombre_nivel,
                'descripcion' => $request->descripcion,
                'orden_seguridad' => $request->orden_seguridad,
                'requiere_autenticacion' => $request->requiere_autenticacion ?? false,
                'requiere_autorizacion' => $request->requiere_autorizacion ?? false,
                'log_accesos' => $request->log_accesos ?? false,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nivel de acceso creado correctamente',
                'data' => $nivel
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nivel de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener nivel específico
     * GET /api/archivos/niveles-acceso/{id}
     */
    public function show($id)
    {
        try {
            $nivel = NivelAcceso::with('archivos')->find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de acceso no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Nivel de acceso obtenido correctamente',
                'data' => $nivel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener nivel de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar nivel de acceso
     * PUT /api/archivos/niveles-acceso/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $nivel = NivelAcceso::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de acceso no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_nivel' => 'required|string|max:20|unique:niveles_acceso,codigo_nivel,' . $id,
                'nombre_nivel' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'orden_seguridad' => 'required|integer|min:1|max:5',
                'requiere_autenticacion' => 'boolean',
                'requiere_autorizacion' => 'boolean',
                'log_accesos' => 'boolean',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $nivel->update([
                'codigo_nivel' => $request->codigo_nivel,
                'nombre_nivel' => $request->nombre_nivel,
                'descripcion' => $request->descripcion,
                'orden_seguridad' => $request->orden_seguridad,
                'requiere_autenticacion' => $request->requiere_autenticacion ?? false,
                'requiere_autorizacion' => $request->requiere_autorizacion ?? false,
                'log_accesos' => $request->log_accesos ?? false,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $nivel->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nivel de acceso actualizado correctamente',
                'data' => $nivel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar nivel de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar nivel de acceso (soft delete)
     * DELETE /api/archivos/niveles-acceso/{id}
     */
    public function destroy($id)
    {
        try {
            $nivel = NivelAcceso::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel de acceso no encontrado'
                ], 404);
            }

            // Verificar si tiene archivos asociados
            if ($nivel->archivos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el nivel porque tiene archivos asociados'
                ], 409);
            }

            $nivel->update([
                'deleted_by' => 1,
            ]);

            $nivel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nivel de acceso eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar nivel de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
