<?php

namespace App\Http\Controllers;

use App\Models\PerfilMilitar;
use App\Models\DatosPersonales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerfilesMilitaresController extends Controller
{
    /**
     * Listar todos los perfiles militares
     * GET /api/personal/perfiles-militares
     */
    public function index(Request $request)
    {
        try {
            $query = PerfilMilitar::with([
                'datosPersonales',
                'categoriaPersonal',
                'especialidad',
                'gradoActual',
                'asignacionActual.estructuraMilitar',
                'asignacionActual.cargo'
            ])->activos();

            // Filtros opcionales
            if ($request->has('estado_servicio')) {
                if ($request->estado_servicio === 'ACTIVO') {
                    $query->enServicio();
                } elseif ($request->estado_servicio === 'RETIRADO') {
                    $query->retirados();
                }
            }

            if ($request->has('categoria_id')) {
                $query->porCategoria($request->categoria_id);
            }

            if ($request->has('especialidad_id')) {
                $query->porEspecialidad($request->especialidad_id);
            }

            if ($request->has('grado_id')) {
                $query->porGrado($request->grado_id);
            }

            if ($request->has('serie_militar')) {
                $query->porSerie($request->serie_militar);
            }

            if ($request->has('situacion')) {
                $query->where('situacion', $request->situacion);
            }

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

    /**
     * Crear nuevo perfil militar
     * POST /api/personal/perfiles-militares
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'datos_personales_id' => 'required|integer|exists:personal.datos_personales,id|unique:personal.perfiles_militares,datos_personales_id',
                'serie_militar' => 'nullable|string|max:20|unique:personal.perfiles_militares,serie_militar',
                'fecha_ingreso_fah' => 'required|date|before_or_equal:today',
                'fecha_retiro_fah' => 'nullable|date|after:fecha_ingreso_fah',
                'motivo_retiro' => 'nullable|string|max:200',
                'categoria_personal_id' => 'nullable|integer|exists:catalogos.categorias_personal,id',
                'especialidad_id' => 'nullable|integer|exists:catalogos.especialidades,id',
                'grado_actual_id' => 'nullable|integer|exists:catalogos.grados,id',
                'estado_servicio' => 'nullable|string|in:ACTIVO,RETIRADO,SUSPENDIDO',
                'situacion' => 'nullable|string|max:50',
                'tiempo_servicio_anos' => 'nullable|integer|min:0',
                'tiempo_servicio_meses' => 'nullable|integer|min:0|max:11'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar que los datos personales existan
            $datosPersonales = DatosPersonales::find($request->datos_personales_id);
            if (!$datosPersonales) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos personales no encontrados'
                ], 404);
            }

            $perfilMilitar = PerfilMilitar::create(array_merge(
                $request->all(),
                [
                    'created_by' => 1, // TODO: Obtener del usuario autenticado
                    'updated_by' => 1
                ]
            ));

            // Calcular tiempo de servicio automáticamente
            $perfilMilitar->calcularTiempoServicio()->save();

            $perfilMilitar->load([
                'datosPersonales',
                'categoriaPersonal',
                'especialidad',
                'gradoActual'
            ]);

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

    /**
     * Obtener perfil militar específico
     * GET /api/personal/perfiles-militares/{id}
     */
    public function show($id)
    {
        try {
            $perfilMilitar = PerfilMilitar::with([
                'datosPersonales',
                'categoriaPersonal',
                'especialidad',
                'gradoActual',
                'asignacionActual.estructuraMilitar',
                'asignacionActual.cargo',
                'usuarioSistema',
                'historialesCargos' => function ($query) {
                    $query->ordenadoCronologicamente()->limit(5);
                },
                'asignacionRoles' => function ($query) {
                    $query->activos()->vigentes();
                }
            ])->find($id);

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

    /**
     * Actualizar perfil militar
     * PUT /api/personal/perfiles-militares/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $perfilMilitar = PerfilMilitar::find($id);

            if (!$perfilMilitar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil militar no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'datos_personales_id' => 'required|integer|exists:personal.datos_personales,id|unique:personal.perfiles_militares,datos_personales_id,' . $id,
                'serie_militar' => 'nullable|string|max:20|unique:personal.perfiles_militares,serie_militar,' . $id,
                'fecha_ingreso_fah' => 'required|date|before_or_equal:today',
                'fecha_retiro_fah' => 'nullable|date|after:fecha_ingreso_fah',
                'motivo_retiro' => 'nullable|string|max:200',
                'categoria_personal_id' => 'nullable|integer|exists:catalogos.categorias_personal,id',
                'especialidad_id' => 'nullable|integer|exists:catalogos.especialidades,id',
                'grado_actual_id' => 'nullable|integer|exists:catalogos.grados,id',
                'estado_servicio' => 'nullable|string|in:ACTIVO,RETIRADO,SUSPENDIDO',
                'situacion' => 'nullable|string|max:50',
                'tiempo_servicio_anos' => 'nullable|integer|min:0',
                'tiempo_servicio_meses' => 'nullable|integer|min:0|max:11',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $perfilMilitar->update(array_merge(
                $request->all(),
                [
                    'updated_by' => 1, // TODO: Obtener del usuario autenticado
                    'version' => $perfilMilitar->version + 1
                ]
            ));

            // Recalcular tiempo de servicio si cambió la fecha de ingreso
            if ($request->has('fecha_ingreso_fah')) {
                $perfilMilitar->calcularTiempoServicio()->save();
            }

            $perfilMilitar->load([
                'datosPersonales',
                'categoriaPersonal',
                'especialidad',
                'gradoActual',
                'asignacionActual'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil militar actualizado correctamente',
                'data' => $perfilMilitar
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar perfil militar (soft delete)
     * DELETE /api/personal/perfiles-militares/{id}
     */
    public function destroy($id)
    {
        try {
            $perfilMilitar = PerfilMilitar::find($id);

            if (!$perfilMilitar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil militar no encontrado'
                ], 404);
            }

            // Verificar dependencias
            if ($perfilMilitar->asignacionActual || $perfilMilitar->usuarioSistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque tiene asignaciones o usuario del sistema asociado'
                ], 409);
            }

            $perfilMilitar->update([
                'deleted_by' => 1, // TODO: Obtener del usuario autenticado
            ]);

            $perfilMilitar->delete();

            return response()->json([
                'success' => true,
                'message' => 'Perfil militar eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar perfil militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar por serie militar
     * GET /api/personal/perfiles-militares/por-serie/{serieMilitar}
     */
    public function porSerie($serieMilitar)
    {
        try {
            $perfilMilitar = PerfilMilitar::porSerie($serieMilitar)
                ->with([
                    'datosPersonales',
                    'categoriaPersonal',
                    'especialidad',
                    'gradoActual',
                    'asignacionActual.estructuraMilitar',
                    'asignacionActual.cargo'
                ])
                ->first();

            if (!$perfilMilitar) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró militar con esa serie'
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
                'message' => 'Error al buscar perfil militar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disponibles para asignación
     * GET /api/personal/perfiles-militares/disponibles
     */
    public function disponibles(Request $request)
    {
        try {
            $query = PerfilMilitar::enServicio()
                ->disponibles()
                ->with([
                    'datosPersonales',
                    'categoriaPersonal',
                    'especialidad',
                    'gradoActual'
                ]);

            // Filtros adicionales
            if ($request->has('categoria_id')) {
                $query->porCategoria($request->categoria_id);
            }

            if ($request->has('especialidad_id')) {
                $query->porEspecialidad($request->especialidad_id);
            }

            if ($request->has('tiempo_servicio_min')) {
                $query->conTiempoServicio($request->tiempo_servicio_min);
            }

            $disponibles = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Personal disponible obtenido correctamente',
                'data' => $disponibles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener personal disponible',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas del personal militar
     * GET /api/personal/perfiles-militares/estadisticas
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_efectivos' => PerfilMilitar::activos()->count(),
                'por_estado_servicio' => [
                    'activos' => PerfilMilitar::enServicio()->count(),
                    'retirados' => PerfilMilitar::retirados()->count(),
                    'suspendidos' => PerfilMilitar::where('estado_servicio', 'SUSPENDIDO')->count()
                ],
                'por_situacion' => PerfilMilitar::activos()
                    ->selectRaw('situacion, COUNT(*) as total')
                    ->groupBy('situacion')
                    ->get(),
                'por_categoria' => PerfilMilitar::activos()
                    ->join('catalogos.categorias_personal', 'personal.perfiles_militares.categoria_personal_id', '=', 'catalogos.categorias_personal.id')
                    ->selectRaw('catalogos.categorias_personal.nombre_categoria as categoria, COUNT(*) as total')
                    ->groupBy('catalogos.categorias_personal.nombre_categoria')
                    ->get(),
                'por_especialidad' => PerfilMilitar::activos()
                    ->join('catalogos.especialidades', 'personal.perfiles_militares.especialidad_id', '=', 'catalogos.especialidades.id')
                    ->selectRaw('catalogos.especialidades.nombre_especialidad as especialidad, COUNT(*) as total')
                    ->groupBy('catalogos.especialidades.nombre_especialidad')
                    ->get(),
                'tiempo_servicio_promedio' => PerfilMilitar::activos()->avg('tiempo_servicio_anos'),
                'disponibles_asignacion' => PerfilMilitar::enServicio()->disponibles()->count()
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

    /**
     * Retirar del servicio
     * POST /api/personal/perfiles-militares/{id}/retirar
     */
    public function retirar(Request $request, $id)
    {
        try {
            $perfilMilitar = PerfilMilitar::find($id);

            if (!$perfilMilitar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil militar no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'fecha_retiro_fah' => 'required|date|after:fecha_ingreso_fah',
                'motivo_retiro' => 'required|string|max:200'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $perfilMilitar->update([
                'fecha_retiro_fah' => $request->fecha_retiro_fah,
                'motivo_retiro' => $request->motivo_retiro,
                'estado_servicio' => 'RETIRADO',
                'situacion' => 'RETIRADO',
                'updated_by' => 1,
                'version' => $perfilMilitar->version + 1
            ]);

            // Recalcular tiempo de servicio
            $perfilMilitar->calcularTiempoServicio()->save();

            return response()->json([
                'success' => true,
                'message' => 'Militar retirado del servicio correctamente',
                'data' => $perfilMilitar
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al retirar del servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
