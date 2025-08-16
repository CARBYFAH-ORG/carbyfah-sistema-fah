<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepartamentoController extends Controller
{
    /**
     * Obtener información del país desde el servicio de catálogos
     */
    private function obtenerPais($paisId)
    {
        try {
            // ✅ USAR IP DEL HOST DOCKER (gateway)
            $response = Http::timeout(10)->get("http://172.18.0.1:8008/api/catalogos/paises/{$paisId}");

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning("Error al obtener país {$paisId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Validar que el país existe en el servicio de catálogos
     */
    private function validarPaisExiste($paisId)
    {
        try {
            // ✅ USAR IP DEL HOST DOCKER (gateway)
            $response = Http::timeout(10)->get("http://172.18.0.1:8008/api/catalogos/paises/{$paisId}");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("Error al validar país {$paisId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todos los departamentos CON información del país
     * GET /api/organizacion/departamentos
     */
    public function index(Request $request)
    {
        try {
            $query = Departamento::query()
                ->activos();

            // Filtrar por país si se especifica
            if ($request->has('pais_id')) {
                $query->porPais($request->pais_id);
            }

            $departamentos = $query->get();

            // ✅ CARGAR INFORMACIÓN DE PAÍSES VÍA API
            foreach ($departamentos as $departamento) {
                $paisInfo = $this->obtenerPais($departamento->pais_id);
                $departamento->pais = $paisInfo;
            }

            return response()->json([
                'success' => true,
                'message' => 'Departamentos obtenidos correctamente',
                'data' => $departamentos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener departamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo departamento CON validación cross-service
     * POST /api/organizacion/departamentos
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pais_id' => 'required|integer',
                'codigo_departamento' => 'required|string|max:10',
                'nombre_departamento' => 'required|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // ✅ VALIDACIÓN CROSS-SERVICE
            if (!$this->validarPaisExiste($request->pais_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El país especificado no existe',
                    'errors' => ['pais_id' => ['El país seleccionado no es válido']]
                ], 400);
            }

            $departamento = Departamento::create([
                'pais_id' => $request->pais_id,
                'codigo_departamento' => $request->codigo_departamento,
                'nombre_departamento' => $request->nombre_departamento,
                'created_by' => 1, // TODO: Obtener del usuario autenticado
                'updated_by' => 1
            ]);

            // ✅ CARGAR INFORMACIÓN DEL PAÍS PARA LA RESPUESTA
            $paisInfo = $this->obtenerPais($departamento->pais_id);
            $departamento->pais = $paisInfo;

            return response()->json([
                'success' => true,
                'message' => 'Departamento creado correctamente',
                'data' => $departamento
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener departamento específico CON información del país
     * GET /api/organizacion/departamentos/{id}
     */
    public function show($id)
    {
        try {
            $departamento = Departamento::with('municipios')->find($id);

            if (!$departamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento no encontrado'
                ], 404);
            }

            // ✅ CARGAR INFORMACIÓN DEL PAÍS
            $paisInfo = $this->obtenerPais($departamento->pais_id);
            $departamento->pais = $paisInfo;

            return response()->json([
                'success' => true,
                'message' => 'Departamento obtenido correctamente',
                'data' => $departamento
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar departamento CON validación cross-service
     * PUT /api/organizacion/departamentos/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $departamento = Departamento::find($id);

            if (!$departamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'pais_id' => 'required|integer',
                'codigo_departamento' => 'required|string|max:10',
                'nombre_departamento' => 'required|string|max:100',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // ✅ VALIDACIÓN CROSS-SERVICE
            if (!$this->validarPaisExiste($request->pais_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El país especificado no existe',
                    'errors' => ['pais_id' => ['El país seleccionado no es válido']]
                ], 400);
            }

            $departamento->update([
                'pais_id' => $request->pais_id,
                'codigo_departamento' => $request->codigo_departamento,
                'nombre_departamento' => $request->nombre_departamento,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1, // TODO: Obtener del usuario autenticado
                'version' => $departamento->version + 1
            ]);

            // ✅ CARGAR INFORMACIÓN DEL PAÍS PARA LA RESPUESTA
            $paisInfo = $this->obtenerPais($departamento->pais_id);
            $departamento->pais = $paisInfo;

            return response()->json([
                'success' => true,
                'message' => 'Departamento actualizado correctamente',
                'data' => $departamento
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar departamento (soft delete)
     * DELETE /api/organizacion/departamentos/{id}
     */
    public function destroy($id)
    {
        try {
            $departamento = Departamento::find($id);

            if (!$departamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento no encontrado'
                ], 404);
            }

            // Verificar si tiene municipios asociados
            if ($departamento->municipios()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el departamento porque tiene municipios asociados'
                ], 409);
            }

            $departamento->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $departamento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Departamento eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener departamentos por país CON información del país
     * GET /api/organizacion/departamentos/por-pais/{pais_id}
     */
    public function porPais($paisId)
    {
        try {
            // ✅ VALIDAR QUE EL PAÍS EXISTE
            $paisInfo = $this->obtenerPais($paisId);
            if (!$paisInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'País no encontrado'
                ], 404);
            }

            $departamentos = Departamento::porPais($paisId)
                ->activos()
                ->get();

            // ✅ AGREGAR INFORMACIÓN DEL PAÍS A CADA DEPARTAMENTO
            foreach ($departamentos as $departamento) {
                $departamento->pais = $paisInfo;
            }

            return response()->json([
                'success' => true,
                'message' => 'Departamentos por país obtenidos correctamente',
                'data' => [
                    'pais' => $paisInfo,
                    'departamentos' => $departamentos
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener departamentos por país',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar departamentos por texto (para autocompletado)
     * GET /api/organizacion/departamentos/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $pais_id = $request->get('pais_id', null);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingrese al menos 2 caracteres para buscar',
                    'data' => []
                ], 200);
            }

            $departamentos = Departamento::query()
                ->activos()
                ->where(function ($q) use ($query) {
                    $q->where('nombre_departamento', 'ILIKE', "%{$query}%")
                        ->orWhere('codigo_departamento', 'ILIKE', "%{$query}%");
                });

            // Filtrar por país si se especifica
            if ($pais_id) {
                $departamentos->porPais($pais_id);
            }

            $departamentos = $departamentos->orderBy('nombre_departamento', 'asc')
                ->limit(20)
                ->get(['id', 'pais_id', 'codigo_departamento', 'nombre_departamento']);

            // ✅ CARGAR INFORMACIÓN DEL PAÍS PARA CADA DEPARTAMENTO
            foreach ($departamentos as $departamento) {
                $paisInfo = $this->obtenerPais($departamento->pais_id);
                $departamento->pais = $paisInfo;
            }

            return response()->json([
                'success' => true,
                'message' => 'Departamentos encontrados',
                'data' => $departamentos,
                'total' => $departamentos->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar departamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
