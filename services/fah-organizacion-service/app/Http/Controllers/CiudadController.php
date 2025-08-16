<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CiudadController extends Controller
{
    /**
     * Cargar información completa (país, departamento, municipio)
     */
    private function cargarInformacionCompleta($ciudad)
    {
        if ($ciudad->municipio && $ciudad->municipio->departamento) {
            // Cargar información del país vía API
            if ($ciudad->municipio->departamento->pais_id) {
                try {
                    $response = Http::timeout(5)->get("http://172.18.0.1:8008/api/catalogos/paises/{$ciudad->municipio->departamento->pais_id}");
                    if ($response->successful()) {
                        $ciudad->municipio->departamento->pais = $response->json()['data'] ?? null;
                    }
                } catch (\Exception $e) {
                    Log::warning("Error al obtener país para ciudad {$ciudad->id}: " . $e->getMessage());
                }
            }
        }

        return $ciudad;
    }

    /**
     * Buscar ciudades por texto (para autocompletado)
     * GET /api/organizacion/ciudades/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $municipio_id = $request->get('municipio_id', null);
            $tipo_localidad = $request->get('tipo_localidad', null);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingrese al menos 2 caracteres para buscar',
                    'data' => []
                ], 200);
            }

            $ciudades = Ciudad::query()
                ->with(['municipio.departamento'])
                ->activos()
                ->where(function ($q) use ($query) {
                    $q->where('nombre_ciudad', 'ILIKE', "%{$query}%")
                        ->orWhere('codigo_ciudad', 'ILIKE', "%{$query}%");
                });

            // Filtros adicionales
            if ($municipio_id) {
                $ciudades->porMunicipio($municipio_id);
            }

            if ($tipo_localidad) {
                $ciudades->porTipo($tipo_localidad);
            }

            $ciudades = $ciudades->orderBy('nombre_ciudad', 'asc')
                ->limit(20)
                ->get(['id', 'municipio_id', 'codigo_ciudad', 'nombre_ciudad', 'tipo_localidad']);

            // Cargar información completa para cada ciudad
            foreach ($ciudades as $ciudad) {
                $ciudad = $this->cargarInformacionCompleta($ciudad);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ciudades encontradas',
                'data' => $ciudades,
                'total' => $ciudades->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar ciudades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las ciudades CON información completa
     * GET /api/organizacion/ciudades
     */
    public function index(Request $request)
    {
        try {
            $query = Ciudad::with(['municipio.departamento'])
                ->activos();

            // Filtrar por municipio si se especifica
            if ($request->has('municipio_id')) {
                $query->porMunicipio($request->municipio_id);
            }

            // Filtrar por tipo de localidad
            if ($request->has('tipo_localidad')) {
                $query->porTipo($request->tipo_localidad);
            }

            $ciudades = $query->get();

            // ✅ CARGAR INFORMACIÓN COMPLETA PARA CADA CIUDAD
            foreach ($ciudades as $ciudad) {
                $ciudad = $this->cargarInformacionCompleta($ciudad);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ciudades obtenidas correctamente',
                'data' => $ciudades
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ciudades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva ciudad
     * POST /api/organizacion/ciudades
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'municipio_id' => 'required|integer|exists:municipios,id',
                'codigo_ciudad' => 'required|string|max:10',
                'nombre_ciudad' => 'required|string|max:100',
                'tipo_localidad' => 'nullable|string|max:20|in:Ciudad,Aldea,Caserio,Colonia'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $ciudad = Ciudad::create([
                'municipio_id' => $request->municipio_id,
                'codigo_ciudad' => $request->codigo_ciudad,
                'nombre_ciudad' => $request->nombre_ciudad,
                'tipo_localidad' => $request->tipo_localidad ?? 'Ciudad',
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $ciudad->load(['municipio.departamento']);
            $ciudad = $this->cargarInformacionCompleta($ciudad);

            return response()->json([
                'success' => true,
                'message' => 'Ciudad creada correctamente',
                'data' => $ciudad
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear ciudad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ciudad específica CON información completa
     * GET /api/organizacion/ciudades/{id}
     */
    public function show($id)
    {
        try {
            $ciudad = Ciudad::with(['municipio.departamento', 'ubicacionesGeograficas'])->find($id);

            if (!$ciudad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ciudad no encontrada'
                ], 404);
            }

            $ciudad = $this->cargarInformacionCompleta($ciudad);

            return response()->json([
                'success' => true,
                'message' => 'Ciudad obtenida correctamente',
                'data' => $ciudad
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ciudad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar ciudad
     * PUT /api/organizacion/ciudades/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $ciudad = Ciudad::find($id);

            if (!$ciudad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ciudad no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'municipio_id' => 'required|integer|exists:municipios,id',
                'codigo_ciudad' => 'required|string|max:10',
                'nombre_ciudad' => 'required|string|max:100',
                'tipo_localidad' => 'nullable|string|max:20|in:Ciudad,Aldea,Caserio,Colonia',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $ciudad->update([
                'municipio_id' => $request->municipio_id,
                'codigo_ciudad' => $request->codigo_ciudad,
                'nombre_ciudad' => $request->nombre_ciudad,
                'tipo_localidad' => $request->tipo_localidad ?? 'Ciudad',
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $ciudad->version + 1
            ]);

            $ciudad->load(['municipio.departamento']);
            $ciudad = $this->cargarInformacionCompleta($ciudad);

            return response()->json([
                'success' => true,
                'message' => 'Ciudad actualizada correctamente',
                'data' => $ciudad
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar ciudad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar ciudad (soft delete)
     * DELETE /api/organizacion/ciudades/{id}
     */
    public function destroy($id)
    {
        try {
            $ciudad = Ciudad::find($id);

            if (!$ciudad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ciudad no encontrada'
                ], 404);
            }

            // Verificar si tiene ubicaciones geográficas asociadas
            if ($ciudad->ubicacionesGeograficas()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la ciudad porque tiene ubicaciones geográficas asociadas'
                ], 409);
            }

            $ciudad->update([
                'deleted_by' => 1,
            ]);

            $ciudad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ciudad eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar ciudad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ciudades por municipio CON información completa
     * GET /api/organizacion/ciudades/por-municipio/{municipio_id}
     */
    public function porMunicipio($municipioId)
    {
        try {
            $municipio = Municipio::with('departamento')->find($municipioId);

            if (!$municipio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Municipio no encontrado'
                ], 404);
            }

            $ciudades = Ciudad::with(['municipio.departamento'])
                ->porMunicipio($municipioId)
                ->activos()
                ->get();

            // Cargar información completa
            foreach ($ciudades as $ciudad) {
                $ciudad = $this->cargarInformacionCompleta($ciudad);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ciudades por municipio obtenidas correctamente',
                'data' => [
                    'municipio' => $municipio,
                    'ciudades' => $ciudades
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ciudades por municipio',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
