<?php
// services\fah-catalogos-service\app\Http\Controllers\TipoEventoController.php

namespace App\Http\Controllers;

use App\Models\TipoEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoEventoController extends Controller
{
    // Listar todos los tipos de evento
    public function index()
    {
        try {
            $tipos = TipoEvento::activos()->orderBy('nombre_evento', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de evento obtenidos correctamente',
                'data' => $tipos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear nuevo tipo de evento
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_evento' => 'required|string|max:20|unique:tipos_evento,codigo_evento',
                'nombre_evento' => 'required|string|max:100',
                'requiere_aprobacion' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $tipo = TipoEvento::create([
                'codigo_evento' => $request->codigo_evento,
                'nombre_evento' => $request->nombre_evento,
                'requiere_aprobacion' => $request->requiere_aprobacion ?? false,
                'created_by' => 1,
                'updated_by' => 1,
                'version' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de evento creado correctamente',
                'data' => $tipo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener tipo de evento específico
    public function show($id)
    {
        try {
            $tipo = TipoEvento::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de evento no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de evento obtenido correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar tipo de evento
    public function update(Request $request, $id)
    {
        try {
            $tipo = TipoEvento::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de evento no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_evento' => 'required|string|max:20|unique:tipos_evento,codigo_evento,' . $id,
                'nombre_evento' => 'required|string|max:100',
                'requiere_aprobacion' => 'boolean',
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
                'codigo_evento' => $request->codigo_evento,
                'nombre_evento' => $request->nombre_evento,
                'requiere_aprobacion' => $request->requiere_aprobacion ?? false,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $tipo->version + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de evento actualizado correctamente',
                'data' => $tipo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar tipo de evento (soft delete)
    public function destroy($id)
    {
        try {
            $tipo = TipoEvento::find($id);

            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de evento no encontrado'
                ], 404);
            }

            $tipo->update([
                'deleted_by' => 1,
            ]);

            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de evento eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
