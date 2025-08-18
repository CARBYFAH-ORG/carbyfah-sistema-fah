<?php
// services\fah-catalogos-service\app\Http\Controllers\TipoEstadoGeneralController.php

namespace App\Http\Controllers;

use App\Models\TipoEstadoGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoEstadoGeneralController extends Controller
{
    // Listar todos los tipos de estado general
    public function index()
    {
        try {
            $tiposEstado = TipoEstadoGeneral::activos()->orderBy('nombre', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de estado general obtenidos correctamente',
                'data' => $tiposEstado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de estado general',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear nuevo tipo de estado general
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:20|unique:tipos_estado_general,codigo',
                'nombre' => 'required|string|max:100',
                'permite_operaciones' => 'boolean',
                'es_estado_final' => 'boolean',
                'requiere_justificacion' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipoEstado = TipoEstadoGeneral::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'permite_operaciones' => $request->permite_operaciones ?? true,
                'es_estado_final' => $request->es_estado_final ?? false,
                'requiere_justificacion' => $request->requiere_justificacion ?? false,
                'created_by' => 1,
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de estado general creado correctamente',
                'data' => $tipoEstado
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de estado general',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener tipo de estado general específico
    public function show($id)
    {
        try {
            $tipoEstado = TipoEstadoGeneral::find($id);

            if (!$tipoEstado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de estado general no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de estado general obtenido correctamente',
                'data' => $tipoEstado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de estado general',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar tipo de estado general
    public function update(Request $request, $id)
    {
        try {
            $tipoEstado = TipoEstadoGeneral::find($id);

            if (!$tipoEstado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de estado general no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:20|unique:tipos_estado_general,codigo,' . $id,
                'nombre' => 'required|string|max:100',
                'permite_operaciones' => 'boolean',
                'es_estado_final' => 'boolean',
                'requiere_justificacion' => 'boolean',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipoEstado->update([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'permite_operaciones' => $request->permite_operaciones ?? true,
                'es_estado_final' => $request->es_estado_final ?? false,
                'requiere_justificacion' => $request->requiere_justificacion ?? false,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $tipoEstado->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de estado general actualizado correctamente',
                'data' => $tipoEstado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de estado general',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar tipo de estado general (soft delete)
    public function destroy($id)
    {
        try {
            $tipoEstado = TipoEstadoGeneral::find($id);

            if (!$tipoEstado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de estado general no encontrado'
                ], 404);
            }

            $tipoEstado->update([
                'deleted_by' => 1,
            ]);

            $tipoEstado->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de estado general eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de estado general',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
