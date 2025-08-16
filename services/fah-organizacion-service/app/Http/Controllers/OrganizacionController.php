<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Ciudad;
use App\Models\UbicacionGeografica;
use App\Models\RolFuncional;
use App\Models\EstructuraMilitar;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizacionController extends Controller
{
    /**
     * Health check del microservicio
     * GET /api/organizacion/health
     */
    public function health()
    {
        try {
            DB::connection('pgsql')->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'FAH Organizacion Service funcionando correctamente',
                'service' => 'fah-organizacion-service',
                'version' => '1.0.0',
                'puerto' => 8010,
                'timestamp' => now(),
                'database' => 'connected',
                'schemas' => ['organizacion', 'catalogos'],
                'status' => 'healthy'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en health check',
                'service' => 'fah-organizacion-service',
                'error' => $e->getMessage(),
                'status' => 'unhealthy'
            ], 500);
        }
    }

    /**
     * Estadísticas generales de organización
     * GET /api/organizacion/estadisticas
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'departamentos' => [
                    'total' => Departamento::count(),
                    'activos' => Departamento::activos()->count()
                ],
                'municipios' => [
                    'total' => Municipio::count(),
                    'activos' => Municipio::activos()->count()
                ],
                'ciudades' => [
                    'total' => Ciudad::count(),
                    'activos' => Ciudad::activos()->count(),
                    'por_tipo' => Ciudad::selectRaw('tipo_localidad, count(*) as total')
                        ->groupBy('tipo_localidad')
                        ->get()
                ],
                'ubicaciones_geograficas' => [
                    'total' => UbicacionGeografica::count(),
                    'activas' => UbicacionGeografica::activos()->count()
                ],
                'roles_funcionales' => [
                    'total' => RolFuncional::count(),
                    'activos' => RolFuncional::activos()->count(),
                    'por_nivel_autoridad' => RolFuncional::selectRaw('nivel_autoridad, count(*) as total')
                        ->groupBy('nivel_autoridad')
                        ->orderBy('nivel_autoridad', 'desc')
                        ->get()
                ],
                'estructura_militar' => [
                    'total' => EstructuraMilitar::count(),
                    'activas' => EstructuraMilitar::activos()->count(),
                    'unidades_raiz' => EstructuraMilitar::unidadesRaiz()->count(),
                    'por_nivel' => EstructuraMilitar::selectRaw('nivel_jerarquico, count(*) as total')
                        ->groupBy('nivel_jerarquico')
                        ->orderBy('nivel_jerarquico')
                        ->get()
                ],
                'cargos' => [
                    'total' => Cargo::count(),
                    'activos' => Cargo::activos()->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas de organización obtenidas correctamente',
                'data' => $estadisticas,
                'timestamp' => now()
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
