<?php

namespace App\Http\Controllers;

use App\Models\PerfilMilitar;
use App\Models\DatosPersonales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerfilesMilitaresController extends Controller
{
    // listar todos los perfiles militares
    public function index(Request $request)
    {
        try {
            // solo relacion con datos personales para evitar errores
            $query = PerfilMilitar::with(['datosPersonales'])->activos();

            $perfiles = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Perfiles militares obtenidos correctamente',
                'data' => $perfiles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfiles militares',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // crear nuevo perfil militar
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'datos_personales_id' => 'required|integer|exists:datos_personales,id',
                'serie_militar' => 'nullable|string|max:20|unique:perfiles_militares,serie_militar',
                'fecha_ingreso_fah' => 'nullable|date',
                'categoria_personal_id' => 'nullable|integer',
                'especialidad_id' => 'nullable|integer',
                'grado_actual_id' => 'nullable|integer',
                'estado_servicio' => 'nullable|string|in:ACTIVO,RETIRADO,SUSPENDIDO'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $perfilMilitar = PerfilMilitar::create(array_merge(
                $request->all(),
                [
                    'created_by' => 1,
                    'updated_by' => 1,
                    'estado_servicio' => $request->estado_servicio ?? 'ACTIVO',
                    'situacion' => 'DISPONIBLE'
                ]
            ));

            // cargar solo datos personales
            $perfilMilitar->load(['datosPersonales']);

            return response()->json([
                'success' => true,
                'message' => 'Perfil militar creado correctamente',
                'data' => $perfilMilitar
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear perfil militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // obtener perfil militar especifico
    public function show($id)
    {
        try {
            $perfilMilitar = PerfilMilitar::with(['datosPersonales'])->find($id);

            if (!$perfilMilitar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil militar no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Perfil militar obtenido correctamente',
                'data' => $perfilMilitar
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfil militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
