<?php

namespace App\Http\Controllers;

use App\Models\DatosPersonales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DatosPersonalesController extends Controller
{
    /**
     * Listar todos los datos personales
     * GET /api/personal/datos-personales
     */
    public function index(Request $request)
    {
        try {
            $query = DatosPersonales::with([
                'tipoGenero',
                'paisNacimiento',
                'departamentoNacimiento',
                'municipioNacimiento',
                'ciudadNacimiento',
                'paisResidencia',
                'departamentoResidencia',
                'municipioResidencia',
                'ciudadResidencia',
                'perfilMilitar'
            ])->activos();

            // Filtros opcionales
            if ($request->has('buscar')) {
                $query->porNombre($request->buscar);
            }

            if ($request->has('identidad')) {
                $query->porIdentidad($request->identidad);
            }

            if ($request->has('genero_id')) {
                $query->porGenero($request->genero_id);
            }

            $datosPersonales = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Datos personales obtenidos correctamente',
                'data' => $datosPersonales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos personales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevos datos personales
     * POST /api/personal/datos-personales
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'numero_identidad' => 'required|string|max:20|unique:personal.datos_personales,numero_identidad',
                'primer_nombre' => 'required|string|max:100',
                'segundo_nombre' => 'nullable|string|max:100',
                'tercer_nombre' => 'nullable|string|max:100',
                'primer_apellido' => 'required|string|max:100',
                'segundo_apellido' => 'nullable|string|max:100',
                'tercer_apellido' => 'nullable|string|max:100',
                'fecha_nacimiento' => 'required|date|before:today',

                // Lugar de nacimiento
                'pais_nacimiento_id' => 'nullable|integer|exists:catalogos.paises,id',
                'departamento_nacimiento_id' => 'nullable|integer|exists:organizacion.departamentos,id',
                'municipio_nacimiento_id' => 'nullable|integer|exists:organizacion.municipios,id',
                'ciudad_nacimiento_id' => 'nullable|integer|exists:organizacion.ciudades,id',
                'lugar_nacimiento_especifico' => 'nullable|string|max:200',

                'nacionalidad' => 'nullable|string|max:100',

                // Residencia actual
                'pais_residencia_id' => 'nullable|integer|exists:catalogos.paises,id',
                'departamento_residencia_id' => 'nullable|integer|exists:organizacion.departamentos,id',
                'municipio_residencia_id' => 'nullable|integer|exists:organizacion.municipios,id',
                'ciudad_residencia_id' => 'nullable|integer|exists:organizacion.ciudades,id',
                'direccion_residencia_especifica' => 'nullable|string|max:500',

                // Datos personales
                'tipo_genero_id' => 'nullable|integer|exists:catalogos.tipos_genero,id',
                'estado_civil' => 'nullable|string|max:50',
                'telefono_personal' => 'nullable|string|max:20',
                'telefono_emergencia' => 'nullable|string|max:20',
                'email_personal' => 'nullable|email|max:200',

                // Contacto emergencia
                'contacto_emergencia_nombre' => 'nullable|string|max:200',
                'contacto_emergencia_telefono' => 'nullable|string|max:20',
                'contacto_emergencia_relacion' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $datosPersonales = DatosPersonales::create(array_merge(
                $request->all(),
                [
                    'created_by' => 1, // TODO: Obtener del usuario autenticado
                    'updated_by' => 1
                ]
            ));

            $datosPersonales->load([
                'tipoGenero',
                'paisNacimiento',
                'departamentoNacimiento',
                'municipioNacimiento',
                'ciudadNacimiento',
                'paisResidencia',
                'departamentoResidencia',
                'municipioResidencia',
                'ciudadResidencia'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Datos personales creados correctamente',
                'data' => $datosPersonales
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear datos personales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos personales específicos
     * GET /api/personal/datos-personales/{id}
     */
    public function show($id)
    {
        try {
            $datosPersonales = DatosPersonales::with([
                'tipoGenero',
                'paisNacimiento',
                'departamentoNacimiento',
                'municipioNacimiento',
                'ciudadNacimiento',
                'paisResidencia',
                'departamentoResidencia',
                'municipioResidencia',
                'ciudadResidencia',
                'perfilMilitar'
            ])->find($id);

            if (!$datosPersonales) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos personales no encontrados'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Datos personales obtenidos correctamente',
                'data' => $datosPersonales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos personales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar datos personales
     * PUT /api/personal/datos-personales/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $datosPersonales = DatosPersonales::find($id);

            if (!$datosPersonales) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos personales no encontrados'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'numero_identidad' => 'required|string|max:20|unique:personal.datos_personales,numero_identidad,' . $id,
                'primer_nombre' => 'required|string|max:100',
                'segundo_nombre' => 'nullable|string|max:100',
                'tercer_nombre' => 'nullable|string|max:100',
                'primer_apellido' => 'required|string|max:100',
                'segundo_apellido' => 'nullable|string|max:100',
                'tercer_apellido' => 'nullable|string|max:100',
                'fecha_nacimiento' => 'required|date|before:today',

                // Lugar de nacimiento
                'pais_nacimiento_id' => 'nullable|integer|exists:catalogos.paises,id',
                'departamento_nacimiento_id' => 'nullable|integer|exists:organizacion.departamentos,id',
                'municipio_nacimiento_id' => 'nullable|integer|exists:organizacion.municipios,id',
                'ciudad_nacimiento_id' => 'nullable|integer|exists:organizacion.ciudades,id',
                'lugar_nacimiento_especifico' => 'nullable|string|max:200',

                'nacionalidad' => 'nullable|string|max:100',

                // Residencia actual
                'pais_residencia_id' => 'nullable|integer|exists:catalogos.paises,id',
                'departamento_residencia_id' => 'nullable|integer|exists:organizacion.departamentos,id',
                'municipio_residencia_id' => 'nullable|integer|exists:organizacion.municipios,id',
                'ciudad_residencia_id' => 'nullable|integer|exists:organizacion.ciudades,id',
                'direccion_residencia_especifica' => 'nullable|string|max:500',

                // Datos personales
                'tipo_genero_id' => 'nullable|integer|exists:catalogos.tipos_genero,id',
                'estado_civil' => 'nullable|string|max:50',
                'telefono_personal' => 'nullable|string|max:20',
                'telefono_emergencia' => 'nullable|string|max:20',
                'email_personal' => 'nullable|email|max:200',

                // Contacto emergencia
                'contacto_emergencia_nombre' => 'nullable|string|max:200',
                'contacto_emergencia_telefono' => 'nullable|string|max:20',
                'contacto_emergencia_relacion' => 'nullable|string|max:100',

                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $datosPersonales->update(array_merge(
                $request->all(),
                [
                    'updated_by' => 1, // TODO: Obtener del usuario autenticado
                    'version' => $datosPersonales->version + 1
                ]
            ));

            $datosPersonales->load([
                'tipoGenero',
                'paisNacimiento',
                'departamentoNacimiento',
                'municipioNacimiento',
                'ciudadNacimiento',
                'paisResidencia',
                'departamentoResidencia',
                'municipioResidencia',
                'ciudadResidencia'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Datos personales actualizados correctamente',
                'data' => $datosPersonales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar datos personales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar datos personales (soft delete)
     * DELETE /api/personal/datos-personales/{id}
     */
    public function destroy($id)
    {
        try {
            $datosPersonales = DatosPersonales::find($id);

            if (!$datosPersonales) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos personales no encontrados'
                ], 404);
            }

            // Verificar si tiene perfil militar asociado
            if ($datosPersonales->perfilMilitar) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque tiene un perfil militar asociado'
                ], 409);
            }

            $datosPersonales->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $datosPersonales->delete();

            return response()->json([
                'success' => true,
                'message' => 'Datos personales eliminados correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar datos personales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar por número de identidad
     * GET /api/personal/datos-personales/por-identidad/{numeroIdentidad}
     */
    public function porIdentidad($numeroIdentidad)
    {
        try {
            $datosPersonales = DatosPersonales::porIdentidad($numeroIdentidad)
                ->with([
                    'tipoGenero',
                    'paisNacimiento',
                    'departamentoNacimiento',
                    'municipioNacimiento',
                    'ciudadNacimiento',
                    'perfilMilitar'
                ])
                ->first();

            if (!$datosPersonales) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró persona con ese número de identidad'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Datos personales obtenidos correctamente',
                'data' => $datosPersonales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar datos personales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas generales
     * GET /api/personal/datos-personales/estadisticas
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_registros' => DatosPersonales::activos()->count(),
                'por_genero' => DatosPersonales::activos()
                    ->join('catalogos.tipos_genero', 'personal.datos_personales.tipo_genero_id', '=', 'catalogos.tipos_genero.id')
                    ->selectRaw('catalogos.tipos_genero.nombre as genero, COUNT(*) as total')
                    ->groupBy('catalogos.tipos_genero.nombre')
                    ->get(),
                'rangos_edad' => [
                    '18-25' => DatosPersonales::activos()->whereRaw('EXTRACT(YEAR FROM AGE(fecha_nacimiento)) BETWEEN 18 AND 25')->count(),
                    '26-35' => DatosPersonales::activos()->whereRaw('EXTRACT(YEAR FROM AGE(fecha_nacimiento)) BETWEEN 26 AND 35')->count(),
                    '36-45' => DatosPersonales::activos()->whereRaw('EXTRACT(YEAR FROM AGE(fecha_nacimiento)) BETWEEN 36 AND 45')->count(),
                    '46-55' => DatosPersonales::activos()->whereRaw('EXTRACT(YEAR FROM AGE(fecha_nacimiento)) BETWEEN 46 AND 55')->count(),
                    '56+' => DatosPersonales::activos()->whereRaw('EXTRACT(YEAR FROM AGE(fecha_nacimiento)) > 55')->count(),
                ],
                'con_perfil_militar' => DatosPersonales::activos()->has('perfilMilitar')->count(),
                'sin_perfil_militar' => DatosPersonales::activos()->doesntHave('perfilMilitar')->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => $estadisticas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
