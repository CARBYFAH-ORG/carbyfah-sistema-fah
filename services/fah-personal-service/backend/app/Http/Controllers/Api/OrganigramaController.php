<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganigramaController extends Controller
{
    /**
     * Obtener estructura completa de la FAH para organigrama
     */
    public function obtenerEstructuraFAH()
    {
        try {
            $estructura = DB::select("
                SELECT
                    em.id,
                    em.codigo_unidad,
                    em.nombre_unidad,
                    em.unidad_padre_id,
                    em.nivel_jerarquico,
                    em.orden_horizontal,
                    em.capacidad_personal,
                    COUNT(DISTINCT aa.id) as total_personal_asignado,
                    json_agg(
                        CASE WHEN aa.id IS NOT NULL THEN
                            json_build_object(
                                'persona_id', pm.id,
                                'nombre_completo',
                                CONCAT(dp.primer_nombre, ' ', dp.primer_apellido),
                                'grado', g.nombre_grado,
                                'grado_abreviatura', g.abreviatura,
                                'cargo', c.nombre_cargo,
                                'serie_militar', pm.serie_militar
                            )
                        ELSE NULL END
                    ) FILTER (WHERE aa.id IS NOT NULL) as personal_asignado
                FROM organizacion.estructura_militar em
                LEFT JOIN personal.asignaciones_actuales aa ON aa.estructura_militar_id = em.id AND aa.is_active = true
                LEFT JOIN personal.perfiles_militares pm ON pm.id = aa.perfil_militar_id AND pm.is_active = true
                LEFT JOIN personal.datos_personales dp ON dp.id = pm.datos_personales_id AND dp.is_active = true
                LEFT JOIN catalogos.grados g ON g.id = pm.grado_actual_id AND g.is_active = true
                LEFT JOIN organizacion.cargos c ON c.id = aa.cargo_id AND c.is_active = true
                WHERE em.is_active = true
                GROUP BY em.id, em.codigo_unidad, em.nombre_unidad, em.unidad_padre_id,
                         em.nivel_jerarquico, em.orden_horizontal, em.capacidad_personal
                ORDER BY em.nivel_jerarquico, em.orden_horizontal
            ");

            return response()->json([
                'success' => true,
                'data' => $estructura,
                'total' => count($estructura)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estructura organizacional FAH',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener organigrama de una unidad específica
     */
    public function obtenerOrganigramaUnidad($unidad_id)
    {
        try {
            $unidad = DB::select("
                SELECT
                    em.id,
                    em.codigo_unidad,
                    em.nombre_unidad,
                    em.nivel_jerarquico,
                    em.capacidad_personal,
                    json_agg(
                        CASE WHEN aa.id IS NOT NULL THEN
                            json_build_object(
                                'persona_id', pm.id,
                                'nombre_completo',
                                CONCAT(dp.primer_nombre, ' ', dp.primer_apellido),
                                'grado', g.nombre_grado,
                                'grado_abreviatura', g.abreviatura,
                                'cargo', c.nombre_cargo,
                                'serie_militar', pm.serie_militar,
                                'es_comando', c.es_comando
                            )
                        ELSE NULL END
                    ) FILTER (WHERE aa.id IS NOT NULL) as personal_asignado
                FROM organizacion.estructura_militar em
                LEFT JOIN personal.asignaciones_actuales aa ON aa.estructura_militar_id = em.id AND aa.is_active = true
                LEFT JOIN personal.perfiles_militares pm ON pm.id = aa.perfil_militar_id AND pm.is_active = true
                LEFT JOIN personal.datos_personales dp ON dp.id = pm.datos_personales_id AND dp.is_active = true
                LEFT JOIN catalogos.grados g ON g.id = pm.grado_actual_id AND g.is_active = true
                LEFT JOIN organizacion.cargos c ON c.id = aa.cargo_id AND c.is_active = true
                WHERE em.id = ? AND em.is_active = true
                GROUP BY em.id, em.codigo_unidad, em.nombre_unidad, em.nivel_jerarquico, em.capacidad_personal
            ", [$unidad_id]);

            if (empty($unidad)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unidad no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $unidad[0]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener organigrama de la unidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para exportar organigrama
     */
    public function exportarOrganigrama(Request $request)
    {
        try {
            $tipo = $request->get('tipo', 'fah'); // fah, unidad
            $unidad_id = $request->get('unidad_id');

            if ($tipo === 'unidad' && $unidad_id) {
                return $this->obtenerOrganigramaUnidad($unidad_id);
            }

            return $this->obtenerEstructuraFAH();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar organigrama',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
