<?php

namespace App\Http\Controllers;

use App\Models\TipoGenero;
use App\Models\CategoriaPersonal;
use App\Models\Grado;
use App\Models\TipoEstadoGeneral;
use App\Models\NivelPrioridad;
use App\Models\NivelSeguridad;
use App\Models\TipoEvento;
use App\Models\TipoEstructuraMilitar;
use App\Models\TipoJerarquia;
use App\Models\Especialidad;
use App\Models\Pais;
use Illuminate\Http\Request;

class CatalogosController extends Controller
{
    /**
     * Health check del servicio de catálogos
     * GET /api/catalogos/health
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'message' => 'Servicio de catálogos FAH operativo',
            'timestamp' => now(),
            'version' => '1.0.0',
            'service' => 'fah-catalogos-service'
        ], 200);
    }

    /**
     * Obtener todos los catálogos básicos - VERSIÓN EXPANDIDA
     * GET /api/catalogos/basicos
     */
    public function catalogosBasicos()
    {
        try {
            $catalogos = [
                // === GRUPO 1: BÁSICOS ===
                'tipos_genero' => TipoGenero::activos()->get(),
                'categorias_personal' => CategoriaPersonal::activos()
                    ->orderBy('orden_jerarquico', 'asc')
                    ->get(),
                'grados_por_categoria' => $this->getGradosPorCategoria(),

                // === GRUPO 2: ESTADOS Y PRIORIDADES ===
                'tipos_estado_general' => TipoEstadoGeneral::activos()->get(),
                'niveles_prioridad' => NivelPrioridad::activos()
                    ->orderBy('nivel_numerico', 'asc')
                    ->get(),
                'niveles_seguridad' => NivelSeguridad::activos()
                    ->orderBy('nivel_numerico', 'asc')
                    ->get(),
                'tipos_evento' => TipoEvento::activos()->get(),

                // === GRUPO 3: ESTRUCTURA MILITAR ===
                'tipos_estructura_militar' => TipoEstructuraMilitar::activos()
                    ->orderBy('nivel_organizacional', 'asc')
                    ->get(),
                'tipos_jerarquia' => TipoJerarquia::activos()
                    ->orderBy('nivel_autoridad', 'asc')
                    ->get(),
                'especialidades' => Especialidad::activos()->get(),

                // === GRUPO 4: GEOGRÁFICOS ===
                'paises' => Pais::activos()
                    ->orderBy('nombre', 'asc')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Catálogos básicos obtenidos correctamente',
                'data' => $catalogos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener catálogos básicos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener grados agrupados por categoría
     */
    private function getGradosPorCategoria()
    {
        $categorias = CategoriaPersonal::activos()
            ->with(['grados' => function ($query) {
                $query->activos()->orderBy('orden_jerarquico', 'asc');
            }])
            ->orderBy('orden_jerarquico', 'asc')
            ->get();

        $gradosPorCategoria = [];
        foreach ($categorias as $categoria) {
            $gradosPorCategoria[$categoria->codigo_categoria] = [
                'categoria' => $categoria->nombre_categoria,
                'grados' => $categoria->grados->map(function ($grado) {
                    return [
                        'id' => $grado->id,
                        'codigo' => $grado->codigo_grado,
                        'nombre' => $grado->nombre_grado,
                        'abreviatura' => $grado->abreviatura,
                        'orden' => $grado->orden_jerarquico
                    ];
                })
            ];
        }

        return $gradosPorCategoria;
    }

    /**
     * Estadísticas de catálogos - VERSIÓN EXPANDIDA
     * GET /api/catalogos/estadisticas
     */
    public function estadisticas()
    {
        try {
            $stats = [
                // Grupo 1: Básicos
                'tipos_genero' => [
                    'total' => TipoGenero::count(),
                    'activos' => TipoGenero::activos()->count()
                ],
                'categorias_personal' => [
                    'total' => CategoriaPersonal::count(),
                    'activos' => CategoriaPersonal::activos()->count()
                ],
                'grados' => [
                    'total' => Grado::count(),
                    'activos' => Grado::activos()->count(),
                    'por_categoria' => Grado::selectRaw('categoria_personal_id, count(*) as total')
                        ->groupBy('categoria_personal_id')
                        ->with('categoriaPersonal:id,nombre_categoria')
                        ->get()
                ],

                // Grupo 2: Estados y Prioridades
                'tipos_estado_general' => [
                    'total' => TipoEstadoGeneral::count(),
                    'activos' => TipoEstadoGeneral::activos()->count()
                ],
                'niveles_prioridad' => [
                    'total' => NivelPrioridad::count(),
                    'activos' => NivelPrioridad::activos()->count()
                ],
                'niveles_seguridad' => [
                    'total' => NivelSeguridad::count(),
                    'activos' => NivelSeguridad::activos()->count()
                ],
                'tipos_evento' => [
                    'total' => TipoEvento::count(),
                    'activos' => TipoEvento::activos()->count()
                ],

                // Grupo 3: Estructura Militar
                'tipos_estructura_militar' => [
                    'total' => TipoEstructuraMilitar::count(),
                    'activos' => TipoEstructuraMilitar::activos()->count()
                ],
                'tipos_jerarquia' => [
                    'total' => TipoJerarquia::count(),
                    'activos' => TipoJerarquia::activos()->count()
                ],
                'especialidades' => [
                    'total' => Especialidad::count(),
                    'activos' => Especialidad::activos()->count()
                ],

                // Grupo 4: Geográficos
                'paises' => [
                    'total' => Pais::count(),
                    'activos' => Pais::activos()->count()
                ]
            ];

            // Resumen general
            $stats['resumen'] = [
                'total_catalogos' => 11,
                'total_registros' => array_sum(array_column($stats, 'total')),
                'total_activos' => array_sum(array_column($stats, 'activos'))
            ];

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas de catálogos',
                'data' => $stats
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
