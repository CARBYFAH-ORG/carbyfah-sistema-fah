<?php

namespace App\Http\Controllers;

use App\Models\RegistroAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistroAccesoController extends Controller
{
    /**
     * Listar todos los registros de acceso
     * GET /api/archivos/registros-acceso
     */
    public function index(Request $request)
    {
        try {
            $query = RegistroAcceso::with(['archivoDigital'])
                ->activos()
                ->orderBy('fecha_acceso', 'desc');

            // Filtrar por archivo si se especifica
            if ($request->has('archivo_id')) {
                $query->porArchivo($request->archivo_id);
            }

            // Filtrar por usuario si se especifica
            if ($request->has('usuario_id')) {
                $query->porUsuario($request->usuario_id);
            }

            // Filtrar por tipo de acceso
            if ($request->has('tipo_acceso')) {
                $query->porTipoAcceso($request->tipo_acceso);
            }

            // Filtrar solo exitosos o fallidos
            if ($request->has('exitoso')) {
                $query->where('exitoso', $request->boolean('exitoso'));
            }

            $registros = $query->paginate(50);

            return response()->json([
                'success' => true,
                'message' => 'Registros de acceso obtenidos correctamente',
                'data' => $registros
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener registros de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo registro de acceso
     * POST /api/archivos/registros-acceso
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'archivo_digital_id' => 'required|integer|exists:digital_assets.archivos_digitales,id',
                'usuario_id' => 'required|integer',
                'tipo_acceso' => 'required|string|max:20|in:DOWNLOAD,VIEW,MODIFICATION,DELETE,SHARE',
                'direccion_ip' => 'nullable|ip',
                'user_agent' => 'nullable|string',
                'exitoso' => 'boolean',
                'motivo_fallo' => 'nullable|string|max:200',
                'tamano_descargado' => 'nullable|integer|min:0',
                'tiempo_acceso_segundos' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $registro = RegistroAcceso::create([
                'archivo_digital_id' => $request->archivo_digital_id,
                'usuario_id' => $request->usuario_id,
                'tipo_acceso' => $request->tipo_acceso,
                'fecha_acceso' => now(),
                'direccion_ip' => $request->direccion_ip ?? $request->ip(),
                'user_agent' => $request->user_agent ?? $request->header('User-Agent'),
                'exitoso' => $request->exitoso ?? true,
                'motivo_fallo' => $request->motivo_fallo,
                'tamano_descargado' => $request->tamano_descargado,
                'tiempo_acceso_segundos' => $request->tiempo_acceso_segundos,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $registro->load('archivoDigital');

            return response()->json([
                'success' => true,
                'message' => 'Registro de acceso creado correctamente',
                'data' => $registro
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear registro de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener registro específico
     * GET /api/archivos/registros-acceso/{id}
     */
    public function show($id)
    {
        try {
            $registro = RegistroAcceso::with('archivoDigital')->find($id);

            if (!$registro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro de acceso no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Registro de acceso obtenido correctamente',
                'data' => $registro
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener registro de acceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Los registros de acceso NO se actualizan - son inmutables para auditoría
     * Esta función está deshabilitada intencionalmente
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Los registros de acceso no pueden ser modificados por seguridad'
        ], 403);
    }

    /**
     * Los registros de acceso NO se eliminan - son para auditoría permanente
     * Esta función está deshabilitada intencionalmente
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Los registros de acceso no pueden ser eliminados por seguridad'
        ], 403);
    }
}
