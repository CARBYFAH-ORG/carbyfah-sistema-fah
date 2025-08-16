<?php

namespace App\Http\Controllers;

use App\Models\UbicacionGeografica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UbicacionGeograficaController extends Controller
{
    /**
     * Validar que el país existe en el servicio de catálogos
     */
    private function validarPaisExiste($paisId)
    {
        try {
            $response = Http::timeout(5)->get("http://172.18.0.1:8008/api/catalogos/paises/{$paisId}");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("Error al validar país {$paisId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cargar información completa de toda la cadena geográfica
     */
    private function cargarInformacionCompleta($ubicacion)
    {
        // Cargar información del país vía API
        if ($ubicacion->pais_id) {
            try {
                $response = Http::timeout(5)->get("http://172.18.0.1:8008/api/catalogos/paises/{$ubicacion->pais_id}");
                if ($response->successful()) {
                    $ubicacion->pais = $response->json()['data'] ?? null;
                }
            } catch (\Exception $e) {
                Log::warning("Error al obtener país para ubicación {$ubicacion->id}: " . $e->getMessage());
            }
        }

        return $ubicacion;
    }

    /**
     * Buscar ubicaciones geográficas por texto (para autocompletado)
     * GET /api/organizacion/ubicaciones-geograficas/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $pais_id = $request->get('pais_id', null);
            $departamento_id = $request->get('departamento_id', null);
            $municipio_id = $request->get('municipio_id', null);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingrese al menos 2 caracteres para buscar',
                    'data' => []
                ], 200);
            }

            $ubicaciones = UbicacionGeografica::query()
                ->with(['departamento', 'municipio', 'ciudad'])
                ->activos()
                ->where(function ($q) use ($query) {
                    $q->where('nombre_ubicacion', 'ILIKE', "%{$query}%")
                        ->orWhere('codigo_ubicacion', 'ILIKE', "%{$query}%")
                        ->orWhere('direccion_referencia', 'ILIKE', "%{$query}%");
                });

            // Filtros geográficos
            if ($pais_id) {
                $ubicaciones->porPais($pais_id);
            }

            if ($departamento_id) {
                $ubicaciones->porDepartamento($departamento_id);
            }

            if ($municipio_id) {
                $ubicaciones->porMunicipio($municipio_id);
            }

            $ubicaciones = $ubicaciones->orderBy('nombre_ubicacion', 'asc')
                ->limit(20)
                ->get([
                    'id',
                    'pais_id',
                    'departamento_id',
                    'municipio_id',
                    'ciudad_id',
                    'codigo_ubicacion',
                    'nombre_ubicacion',
                    'latitud',
                    'longitud',
                    'direccion_referencia'
                ]);

            // Cargar información completa para cada ubicación
            foreach ($ubicaciones as $ubicacion) {
                $ubicacion = $this->cargarInformacionCompleta($ubicacion);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ubicaciones geográficas encontradas',
                'data' => $ubicaciones,
                'total' => $ubicaciones->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar ubicaciones geográficas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las ubicaciones geográficas CON información completa
     * GET /api/organizacion/ubicaciones-geograficas
     */
    public function index(Request $request)
    {
        try {
            $query = UbicacionGeografica::with(['departamento', 'municipio', 'ciudad'])
                ->activos();

            // Filtros opcionales
            if ($request->has('pais_id')) {
                $query->porPais($request->pais_id);
            }

            if ($request->has('departamento_id')) {
                $query->porDepartamento($request->departamento_id);
            }

            if ($request->has('municipio_id')) {
                $query->porMunicipio($request->municipio_id);
            }

            $ubicaciones = $query->get();

            // ✅ CARGAR INFORMACIÓN COMPLETA PARA CADA UBICACIÓN
            foreach ($ubicaciones as $ubicacion) {
                $ubicacion = $this->cargarInformacionCompleta($ubicacion);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ubicaciones geográficas obtenidas correctamente',
                'data' => $ubicaciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicaciones geográficas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva ubicación geográfica CON validación cross-service
     * POST /api/organizacion/ubicaciones-geograficas
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_ubicacion' => 'required|string|max:30|unique:ubicaciones_geograficas,codigo_ubicacion',
                'nombre_ubicacion' => 'required|string|max:200',
                'pais_id' => 'required|integer',
                'departamento_id' => 'required|integer|exists:departamentos,id',
                'municipio_id' => 'required|integer|exists:municipios,id',
                'ciudad_id' => 'nullable|integer|exists:ciudades,id',
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
                'direccion_referencia' => 'nullable|string|max:500',
                'altitud_metros' => 'nullable|integer',
                'telefono_principal' => 'nullable|string|max:20',
                'telefono_emergencia' => 'nullable|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // ✅ VALIDACIÓN CROSS-SERVICE DEL PAÍS
            if (!$this->validarPaisExiste($request->pais_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El país especificado no existe',
                    'errors' => ['pais_id' => ['El país seleccionado no es válido']]
                ], 400);
            }

            $ubicacion = UbicacionGeografica::create([
                'codigo_ubicacion' => $request->codigo_ubicacion,
                'nombre_ubicacion' => $request->nombre_ubicacion,
                'pais_id' => $request->pais_id,
                'departamento_id' => $request->departamento_id,
                'municipio_id' => $request->municipio_id,
                'ciudad_id' => $request->ciudad_id,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'direccion_referencia' => $request->direccion_referencia,
                'altitud_metros' => $request->altitud_metros,
                'telefono_principal' => $request->telefono_principal,
                'telefono_emergencia' => $request->telefono_emergencia,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $ubicacion->load(['departamento', 'municipio', 'ciudad']);
            $ubicacion = $this->cargarInformacionCompleta($ubicacion);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación geográfica creada correctamente',
                'data' => $ubicacion
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear ubicación geográfica',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ubicación geográfica específica CON información completa
     * GET /api/organizacion/ubicaciones-geograficas/{id}
     */
    public function show($id)
    {
        try {
            $ubicacion = UbicacionGeografica::with([
                'departamento',
                'municipio',
                'ciudad',
                'estructurasMilitares'
            ])->find($id);

            if (!$ubicacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ubicación geográfica no encontrada'
                ], 404);
            }

            $ubicacion = $this->cargarInformacionCompleta($ubicacion);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación geográfica obtenida correctamente',
                'data' => $ubicacion
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicación geográfica',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar ubicación geográfica CON validación cross-service
     * PUT /api/organizacion/ubicaciones-geograficas/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $ubicacion = UbicacionGeografica::find($id);

            if (!$ubicacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ubicación geográfica no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_ubicacion' => 'required|string|max:30|unique:ubicaciones_geograficas,codigo_ubicacion,' . $id,
                'nombre_ubicacion' => 'required|string|max:200',
                'pais_id' => 'required|integer',
                'departamento_id' => 'required|integer|exists:departamentos,id',
                'municipio_id' => 'required|integer|exists:municipios,id',
                'ciudad_id' => 'nullable|integer|exists:ciudades,id',
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
                'direccion_referencia' => 'nullable|string|max:500',
                'altitud_metros' => 'nullable|integer',
                'telefono_principal' => 'nullable|string|max:20',
                'telefono_emergencia' => 'nullable|string|max:20',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // ✅ VALIDACIÓN CROSS-SERVICE DEL PAÍS
            if (!$this->validarPaisExiste($request->pais_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El país especificado no existe',
                    'errors' => ['pais_id' => ['El país seleccionado no es válido']]
                ], 400);
            }

            $ubicacion->update([
                'codigo_ubicacion' => $request->codigo_ubicacion,
                'nombre_ubicacion' => $request->nombre_ubicacion,
                'pais_id' => $request->pais_id,
                'departamento_id' => $request->departamento_id,
                'municipio_id' => $request->municipio_id,
                'ciudad_id' => $request->ciudad_id,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'direccion_referencia' => $request->direccion_referencia,
                'altitud_metros' => $request->altitud_metros,
                'telefono_principal' => $request->telefono_principal,
                'telefono_emergencia' => $request->telefono_emergencia,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $ubicacion->version + 1
            ]);

            $ubicacion->load(['departamento', 'municipio', 'ciudad']);
            $ubicacion = $this->cargarInformacionCompleta($ubicacion);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación geográfica actualizada correctamente',
                'data' => $ubicacion
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar ubicación geográfica',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar ubicación geográfica (soft delete)
     * DELETE /api/organizacion/ubicaciones-geograficas/{id}
     */
    public function destroy($id)
    {
        try {
            $ubicacion = UbicacionGeografica::find($id);

            if (!$ubicacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ubicación geográfica no encontrada'
                ], 404);
            }

            // Verificar si tiene estructuras militares asociadas
            if ($ubicacion->estructurasMilitares()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la ubicación porque tiene estructuras militares asociadas'
                ], 409);
            }

            $ubicacion->update([
                'deleted_by' => 1,
            ]);

            $ubicacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación geográfica eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar ubicación geográfica',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
