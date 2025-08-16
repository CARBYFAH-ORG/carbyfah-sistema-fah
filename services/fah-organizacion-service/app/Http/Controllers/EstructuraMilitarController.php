<?php

namespace App\Http\Controllers;

use App\Models\EstructuraMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EstructuraMilitarController extends Controller
{
    /**
     * Validar que el tipo estructura existe en el servicio de catálogos
     */
    private function validarTipoEstructuraExiste($tipoId)
    {
        try {
            $response = Http::timeout(5)->get("http://172.18.0.1:8008/api/catalogos/tipos-estructura-militar/{$tipoId}");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("Error al validar tipo estructura {$tipoId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cargar información del tipo estructura vía API
     */
    private function cargarInformacionCompleta($estructura)
    {
        if ($estructura->tipo_estructura_id) {
            try {
                $response = Http::timeout(5)->get("http://172.18.0.1:8008/api/catalogos/tipos-estructura-militar/{$estructura->tipo_estructura_id}");
                if ($response->successful()) {
                    $estructura->tipo_estructura = $response->json()['data'] ?? null;
                }
            } catch (\Exception $e) {
                Log::warning("Error al obtener tipo estructura para unidad {$estructura->id}: " . $e->getMessage());
            }
        }

        return $estructura;
    }

    /**
     * Buscar estructuras militares por texto (para autocompletado)
     * GET /api/organizacion/estructura-militar/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $nivel_jerarquico = $request->get('nivel_jerarquico', null);
            $tipo_estructura_id = $request->get('tipo_estructura_id', null);
            $solo_raiz = $request->get('solo_raiz', false);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingrese al menos 2 caracteres para buscar',
                    'data' => []
                ], 200);
            }

            $estructuras = EstructuraMilitar::query()
                ->with(['ubicacionGeografica', 'unidadPadre'])
                ->activos()
                ->where(function ($q) use ($query) {
                    $q->where('nombre_unidad', 'ILIKE', "%{$query}%")
                        ->orWhere('codigo_unidad', 'ILIKE', "%{$query}%")
                        ->orWhere('mision', 'ILIKE', "%{$query}%");
                });

            // Filtros adicionales
            if ($nivel_jerarquico) {
                $estructuras->porNivel($nivel_jerarquico);
            }

            if ($tipo_estructura_id) {
                $estructuras->porTipoEstructura($tipo_estructura_id);
            }

            if ($solo_raiz) {
                $estructuras->unidadesRaiz();
            }

            $estructuras = $estructuras->ordenadoPorJerarquia()
                ->limit(20)
                ->get([
                    'id',
                    'codigo_unidad',
                    'nombre_unidad',
                    'tipo_estructura_id',
                    'nivel_jerarquico',
                    'unidad_padre_id',
                    'ubicacion_geografica_id'
                ]);

            // Cargar información completa para cada estructura
            foreach ($estructuras as $estructura) {
                $estructura = $this->cargarInformacionCompleta($estructura);
            }

            return response()->json([
                'success' => true,
                'message' => 'Estructuras militares encontradas',
                'data' => $estructuras,
                'total' => $estructuras->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar estructuras militares',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las estructuras militares CON información completa
     * GET /api/organizacion/estructura-militar
     */
    public function index(Request $request)
    {
        try {
            $query = EstructuraMilitar::with([
                'ubicacionGeografica',
                'unidadPadre'
            ])
                ->activos()
                ->ordenadoPorJerarquia();

            if ($request->has('nivel_jerarquico')) {
                $query->porNivel($request->nivel_jerarquico);
            }

            if ($request->has('tipo_estructura_id')) {
                $query->porTipoEstructura($request->tipo_estructura_id);
            }

            if ($request->has('solo_raiz') && $request->solo_raiz) {
                $query->unidadesRaiz();
            }

            $estructuras = $query->get();

            // ✅ CARGAR INFORMACIÓN COMPLETA PARA CADA ESTRUCTURA
            foreach ($estructuras as $estructura) {
                $estructura = $this->cargarInformacionCompleta($estructura);
            }

            return response()->json([
                'success' => true,
                'message' => 'Estructuras militares obtenidas correctamente',
                'data' => $estructuras
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estructuras militares',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva estructura militar CON validación cross-service
     * POST /api/organizacion/estructura-militar
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo_unidad' => 'required|string|max:50|unique:estructura_militar,codigo_unidad',
                'nombre_unidad' => 'required|string|max:200',
                'tipo_estructura_id' => 'required|integer',
                'ubicacion_geografica_id' => 'nullable|integer|exists:ubicaciones_geograficas,id',
                'unidad_padre_id' => 'nullable|integer|exists:estructura_militar,id',
                'nivel_jerarquico' => 'required|integer|min:1',
                'orden_horizontal' => 'nullable|integer',
                'capacidad_personal' => 'nullable|integer|min:1',
                'fecha_activacion' => 'nullable|date',
                'fecha_desactivacion' => 'nullable|date|after:fecha_activacion',
                'logo_url' => 'nullable|string|max:500',
                'mision' => 'nullable|string',
                'vision' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // ✅ VALIDACIÓN CROSS-SERVICE DEL TIPO ESTRUCTURA
            if (!$this->validarTipoEstructuraExiste($request->tipo_estructura_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El tipo de estructura especificado no existe',
                    'errors' => ['tipo_estructura_id' => ['El tipo de estructura seleccionado no es válido']]
                ], 400);
            }

            $estructura = EstructuraMilitar::create([
                'codigo_unidad' => $request->codigo_unidad,
                'nombre_unidad' => $request->nombre_unidad,
                'tipo_estructura_id' => $request->tipo_estructura_id,
                'ubicacion_geografica_id' => $request->ubicacion_geografica_id,
                'unidad_padre_id' => $request->unidad_padre_id,
                'nivel_jerarquico' => $request->nivel_jerarquico,
                'orden_horizontal' => $request->orden_horizontal,
                'capacidad_personal' => $request->capacidad_personal,
                'fecha_activacion' => $request->fecha_activacion,
                'fecha_desactivacion' => $request->fecha_desactivacion,
                'logo_url' => $request->logo_url,
                'mision' => $request->mision,
                'vision' => $request->vision,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $estructura->load(['ubicacionGeografica', 'unidadPadre']);
            $estructura = $this->cargarInformacionCompleta($estructura);

            return response()->json([
                'success' => true,
                'message' => 'Estructura militar creada correctamente',
                'data' => $estructura
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estructura militar específica CON información completa
     * GET /api/organizacion/estructura-militar/{id}
     */
    public function show($id)
    {
        try {
            $estructura = EstructuraMilitar::with([
                'ubicacionGeografica',
                'unidadPadre',
                'unidadesHijas',
                'cargos'
            ])->find($id);

            if (!$estructura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estructura militar no encontrada'
                ], 404);
            }

            $estructura = $this->cargarInformacionCompleta($estructura);

            return response()->json([
                'success' => true,
                'message' => 'Estructura militar obtenida correctamente',
                'data' => $estructura
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estructura militar CON validación cross-service
     * PUT /api/organizacion/estructura-militar/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $estructura = EstructuraMilitar::find($id);

            if (!$estructura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estructura militar no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_unidad' => 'required|string|max:50|unique:estructura_militar,codigo_unidad,' . $id,
                'nombre_unidad' => 'required|string|max:200',
                'tipo_estructura_id' => 'required|integer',
                'ubicacion_geografica_id' => 'nullable|integer|exists:ubicaciones_geograficas,id',
                'unidad_padre_id' => 'nullable|integer|exists:estructura_militar,id|not_in:' . $id,
                'nivel_jerarquico' => 'required|integer|min:1',
                'orden_horizontal' => 'nullable|integer',
                'capacidad_personal' => 'nullable|integer|min:1',
                'fecha_activacion' => 'nullable|date',
                'fecha_desactivacion' => 'nullable|date|after:fecha_activacion',
                'logo_url' => 'nullable|string|max:500',
                'mision' => 'nullable|string',
                'vision' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // ✅ VALIDACIÓN CROSS-SERVICE DEL TIPO ESTRUCTURA
            if (!$this->validarTipoEstructuraExiste($request->tipo_estructura_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El tipo de estructura especificado no existe',
                    'errors' => ['tipo_estructura_id' => ['El tipo de estructura seleccionado no es válido']]
                ], 400);
            }

            $estructura->update([
                'codigo_unidad' => $request->codigo_unidad,
                'nombre_unidad' => $request->nombre_unidad,
                'tipo_estructura_id' => $request->tipo_estructura_id,
                'ubicacion_geografica_id' => $request->ubicacion_geografica_id,
                'unidad_padre_id' => $request->unidad_padre_id,
                'nivel_jerarquico' => $request->nivel_jerarquico,
                'orden_horizontal' => $request->orden_horizontal,
                'capacidad_personal' => $request->capacidad_personal,
                'fecha_activacion' => $request->fecha_activacion,
                'fecha_desactivacion' => $request->fecha_desactivacion,
                'logo_url' => $request->logo_url,
                'mision' => $request->mision,
                'vision' => $request->vision,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $estructura->version + 1
            ]);

            $estructura->load(['ubicacionGeografica', 'unidadPadre']);
            $estructura = $this->cargarInformacionCompleta($estructura);

            return response()->json([
                'success' => true,
                'message' => 'Estructura militar actualizada correctamente',
                'data' => $estructura
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar estructura militar (soft delete)
     * DELETE /api/organizacion/estructura-militar/{id}
     */
    public function destroy($id)
    {
        try {
            $estructura = EstructuraMilitar::find($id);

            if (!$estructura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estructura militar no encontrada'
                ], 404);
            }

            if ($estructura->unidadesHijas()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la estructura porque tiene unidades subordinadas'
                ], 409);
            }

            if ($estructura->cargos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la estructura porque tiene cargos asociados'
                ], 409);
            }

            $estructura->update([
                'deleted_by' => 1,
            ]);

            $estructura->delete();

            return response()->json([
                'success' => true,
                'message' => 'Estructura militar eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar estructura militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener jerarquía completa de una unidad CON información completa
     * GET /api/organizacion/estructura-militar/{id}/jerarquia
     */
    public function jerarquia($id)
    {
        try {
            $estructura = EstructuraMilitar::with(['unidadesHijas.unidadesHijas'])->find($id);

            if (!$estructura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estructura militar no encontrada'
                ], 404);
            }

            // Cargar información para la estructura principal y sus hijas
            $estructura = $this->cargarInformacionCompleta($estructura);

            // Cargar información para unidades hijas recursivamente
            foreach ($estructura->unidadesHijas as $hija) {
                $this->cargarInformacionCompleta($hija);
                foreach ($hija->unidadesHijas as $nieta) {
                    $this->cargarInformacionCompleta($nieta);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Jerarquía obtenida correctamente',
                'data' => $estructura
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener jerarquía',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
