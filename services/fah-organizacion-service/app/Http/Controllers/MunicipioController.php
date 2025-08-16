<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MunicipioController extends Controller
{
    /**
     * Obtener información del país y departamento desde otros servicios
     */
    private function cargarInformacionCompleta($municipio)
    {
        // Cargar información del país vía API
        if ($municipio->departamento && $municipio->departamento->pais_id) {
            try {
                $response = Http::timeout(5)->get("http://172.18.0.1:8008/api/catalogos/paises/{$municipio->departamento->pais_id}");
                if ($response->successful()) {
                    $municipio->departamento->pais = $response->json()['data'] ?? null;
                }
            } catch (\Exception $e) {
                Log::warning("Error al obtener país para municipio {$municipio->id}: " . $e->getMessage());
            }
        }

        return $municipio;
    }

    /**
     * Buscar municipios por texto (para autocompletado)
     * GET /api/organizacion/municipios/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $departamento_id = $request->get('departamento_id', null);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingrese al menos 2 caracteres para buscar',
                    'data' => []
                ], 200);
            }

            $municipios = Municipio::query()
                ->with('departamento')
                ->activos()
                ->where(function ($q) use ($query) {
                    $q->where('nombre_municipio', 'ILIKE', "%{$query}%")
                        ->orWhere('codigo_municipio', 'ILIKE', "%{$query}%");
                });

            // Filtrar por departamento si se especifica
            if ($departamento_id) {
                $municipios->porDepartamento($departamento_id);
            }

            $municipios = $municipios->orderBy('nombre_municipio', 'asc')
                ->limit(20)
                ->get(['id', 'departamento_id', 'codigo_municipio', 'nombre_municipio']);

            // Cargar información completa para cada municipio
            foreach ($municipios as $municipio) {
                $municipio = $this->cargarInformacionCompleta($municipio);
            }

            return response()->json([
                'success' => true,
                'message' => 'Municipios encontrados',
                'data' => $municipios,
                'total' => $municipios->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar municipios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos los municipios CON información completa
     * GET /api/organizacion/municipios
     */
    public function index(Request $request)
    {
        try {
            $query = Municipio::with('departamento')
                ->activos();

            // Filtrar por departamento si se especifica
            if ($request->has('departamento_id')) {
                $query->porDepartamento($request->departamento_id);
            }

            $municipios = $query->get();

            // ✅ CARGAR INFORMACIÓN COMPLETA PARA CADA MUNICIPIO
            foreach ($municipios as $municipio) {
                $municipio = $this->cargarInformacionCompleta($municipio);
            }

            return response()->json([
                'success' => true,
                'message' => 'Municipios obtenidos correctamente',
                'data' => $municipios
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener municipios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo municipio
     * POST /api/organizacion/municipios
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'departamento_id' => 'required|integer|exists:departamentos,id',
                'codigo_municipio' => 'required|string|max:10',
                'nombre_municipio' => 'required|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $municipio = Municipio::create([
                'departamento_id' => $request->departamento_id,
                'codigo_municipio' => $request->codigo_municipio,
                'nombre_municipio' => $request->nombre_municipio,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $municipio->load('departamento');
            $municipio = $this->cargarInformacionCompleta($municipio);

            return response()->json([
                'success' => true,
                'message' => 'Municipio creado correctamente',
                'data' => $municipio
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear municipio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener municipio específico CON información completa
     * GET /api/organizacion/municipios/{id}
     */
    public function show($id)
    {
        try {
            $municipio = Municipio::with(['departamento', 'ciudades'])->find($id);

            if (!$municipio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Municipio no encontrado'
                ], 404);
            }

            $municipio = $this->cargarInformacionCompleta($municipio);

            return response()->json([
                'success' => true,
                'message' => 'Municipio obtenido correctamente',
                'data' => $municipio
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener municipio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar municipio
     * PUT /api/organizacion/municipios/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $municipio = Municipio::find($id);

            if (!$municipio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Municipio no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'departamento_id' => 'required|integer|exists:departamentos,id',
                'codigo_municipio' => 'required|string|max:10',
                'nombre_municipio' => 'required|string|max:100',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $municipio->update([
                'departamento_id' => $request->departamento_id,
                'codigo_municipio' => $request->codigo_municipio,
                'nombre_municipio' => $request->nombre_municipio,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $municipio->version + 1
            ]);

            $municipio->load('departamento');
            $municipio = $this->cargarInformacionCompleta($municipio);

            return response()->json([
                'success' => true,
                'message' => 'Municipio actualizado correctamente',
                'data' => $municipio
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar municipio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar municipio (soft delete)
     * DELETE /api/organizacion/municipios/{id}
     */
    public function destroy($id)
    {
        try {
            $municipio = Municipio::find($id);

            if (!$municipio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Municipio no encontrado'
                ], 404);
            }

            // Verificar si tiene ciudades asociadas
            if ($municipio->ciudades()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el municipio porque tiene ciudades asociadas'
                ], 409);
            }

            $municipio->update([
                'deleted_by' => 1,
            ]);

            $municipio->delete();

            return response()->json([
                'success' => true,
                'message' => 'Municipio eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar municipio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener municipios por departamento CON información completa
     * GET /api/organizacion/municipios/por-departamento/{departamento_id}
     */
    public function porDepartamento($departamentoId)
    {
        try {
            $departamento = Departamento::find($departamentoId);

            if (!$departamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento no encontrado'
                ], 404);
            }

            $municipios = Municipio::with('departamento')
                ->porDepartamento($departamentoId)
                ->activos()
                ->get();

            // Cargar información completa
            foreach ($municipios as $municipio) {
                $municipio = $this->cargarInformacionCompleta($municipio);
            }

            return response()->json([
                'success' => true,
                'message' => 'Municipios por departamento obtenidos correctamente',
                'data' => [
                    'departamento' => $departamento,
                    'municipios' => $municipios
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener municipios por departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
