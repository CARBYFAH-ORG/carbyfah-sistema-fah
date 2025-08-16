<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistorialCargo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal.historiales_cargos';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'perfil_militar_id',
        'cargo_id',
        'estructura_militar_id',
        'fecha_inicio',
        'fecha_fin',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
        'version'
    ];

    protected $hidden = [
        'deleted_at',
        'deleted_by'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',

        // Foreign keys
        'perfil_militar_id' => 'integer',
        'cargo_id' => 'integer',
        'estructura_militar_id' => 'integer'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    // Relación con perfil militar
    public function perfilMilitar()
    {
        return $this->belongsTo(PerfilMilitar::class, 'perfil_militar_id', 'id');
    }

    // Relación con cargo (organizacion schema)
    public function cargo()
    {
        return $this->belongsTo('App\Models\Cargo', 'cargo_id', 'id');
    }

    // Relación con estructura militar (organizacion schema)
    public function estructuraMilitar()
    {
        return $this->belongsTo('App\Models\EstructuraMilitar', 'estructura_militar_id', 'id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVigentes($query)
    {
        return $query->whereNull('fecha_fin');
    }

    public function scopeFinalizados($query)
    {
        return $query->whereNotNull('fecha_fin');
    }

    public function scopePorPersonal($query, $perfilId)
    {
        return $query->where('perfil_militar_id', $perfilId);
    }

    public function scopePorCargo($query, $cargoId)
    {
        return $query->where('cargo_id', $cargoId);
    }

    public function scopePorUnidad($query, $unidadId)
    {
        return $query->where('estructura_militar_id', $unidadId);
    }

    public function scopeEnPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->where(function ($q) use ($fechaInicio, $fechaFin) {
            $q->where(function ($q2) use ($fechaInicio, $fechaFin) {
                // Casos donde el historial intersecta con el período
                $q2->where('fecha_inicio', '<=', $fechaFin)
                    ->where(function ($q3) use ($fechaInicio) {
                        $q3->whereNull('fecha_fin')
                            ->orWhere('fecha_fin', '>=', $fechaInicio);
                    });
            });
        });
    }

    public function scopeOrdenadoCronologicamente($query, $direccion = 'desc')
    {
        return $query->orderBy('fecha_inicio', $direccion)
            ->orderBy('fecha_fin', $direccion);
    }

    public function scopeConDuracionMinima($query, $mesesMinimos)
    {
        return $query->whereRaw(
            "EXTRACT(EPOCH FROM (COALESCE(fecha_fin, CURRENT_DATE) - fecha_inicio)) / (60*60*24*30) >= ?",
            [$mesesMinimos]
        );
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getNombreMilitarAttribute()
    {
        return $this->perfilMilitar?->grado_completo;
    }

    public function getCargoNombreAttribute()
    {
        return $this->cargo?->nombre_cargo;
    }

    public function getUnidadNombreAttribute()
    {
        return $this->estructuraMilitar?->nombre_unidad;
    }

    public function getDuracionDiasAttribute()
    {
        $fechaFin = $this->fecha_fin ?? now();
        return $this->fecha_inicio->diffInDays($fechaFin);
    }

    public function getDuracionMesesAttribute()
    {
        $fechaFin = $this->fecha_fin ?? now();
        return $this->fecha_inicio->diffInMonths($fechaFin);
    }

    public function getDuracionAnosAttribute()
    {
        $fechaFin = $this->fecha_fin ?? now();
        return $this->fecha_inicio->diffInYears($fechaFin);
    }

    public function getDuracionFormateadaAttribute()
    {
        $anos = $this->duracion_anos;
        $meses = $this->duracion_meses % 12;

        $resultado = [];

        if ($anos > 0) {
            $resultado[] = $anos . ($anos == 1 ? ' año' : ' años');
        }

        if ($meses > 0) {
            $resultado[] = $meses . ($meses == 1 ? ' mes' : ' meses');
        }

        return implode(' y ', $resultado) ?: 'Menos de 1 mes';
    }

    public function getEstadoCargoAttribute()
    {
        if (!$this->is_active) {
            return 'INACTIVO';
        }

        return $this->fecha_fin ? 'FINALIZADO' : 'VIGENTE';
    }

    public function getPeriodoCargoAttribute()
    {
        $inicio = $this->fecha_inicio->format('d/m/Y');
        $fin = $this->fecha_fin ? $this->fecha_fin->format('d/m/Y') : 'Actual';
        return "{$inicio} - {$fin}";
    }

    public function getResumenCargoAttribute()
    {
        return [
            'militar' => $this->nombre_militar,
            'cargo' => $this->cargo_nombre,
            'unidad' => $this->unidad_nombre,
            'periodo' => $this->periodo_cargo,
            'duracion' => $this->duracion_formateada,
            'estado' => $this->estado_cargo
        ];
    }

    // =====================================================
    // MÉTODOS PERSONALIZADOS
    // =====================================================

    public function finalizar($fechaFin = null)
    {
        $this->fecha_fin = $fechaFin ?? now();
        return $this->save();
    }

    public function extender($nuevaFechaFin)
    {
        if ($this->fecha_fin) {
            $this->fecha_fin = $nuevaFechaFin;
            return $this->save();
        }

        return false; // No se puede extender un cargo vigente (sin fecha fin)
    }

    public function esMayorA($meses)
    {
        return $this->duracion_meses >= $meses;
    }

    public function esVigente()
    {
        return is_null($this->fecha_fin) && $this->is_active;
    }

    public function tieneConflictoFechas($nuevaFechaInicio, $nuevaFechaFin = null)
    {
        // Verificar conflictos con otros cargos del mismo militar
        $query = self::where('perfil_militar_id', $this->perfil_militar_id)
            ->where('id', '!=', $this->id)
            ->where('is_active', true);

        if ($nuevaFechaFin) {
            $query->where(function ($q) use ($nuevaFechaInicio, $nuevaFechaFin) {
                $q->where(function ($q2) use ($nuevaFechaInicio, $nuevaFechaFin) {
                    // Verificar solapamiento de fechas
                    $q2->where('fecha_inicio', '<=', $nuevaFechaFin)
                        ->where(function ($q3) use ($nuevaFechaInicio) {
                            $q3->whereNull('fecha_fin')
                                ->orWhere('fecha_fin', '>=', $nuevaFechaInicio);
                        });
                });
            });
        } else {
            // Nuevo cargo vigente - verificar si hay otros vigentes
            $query->whereNull('fecha_fin');
        }

        return $query->exists();
    }

    public function obtenerCargoAnterior()
    {
        return self::where('perfil_militar_id', $this->perfil_militar_id)
            ->where('fecha_inicio', '<', $this->fecha_inicio)
            ->ordenadoCronologicamente('desc')
            ->first();
    }

    public function obtenerCargoSiguiente()
    {
        return self::where('perfil_militar_id', $this->perfil_militar_id)
            ->where('fecha_inicio', '>', $this->fecha_inicio)
            ->ordenadoCronologicamente('asc')
            ->first();
    }

    public function esPromoccion()
    {
        $cargoAnterior = $this->obtenerCargoAnterior();

        if (!$cargoAnterior) {
            return null; // Primer cargo
        }

        // Comparar niveles jerárquicos
        return $this->cargo?->nivel_jerarquico > $cargoAnterior->cargo?->nivel_jerarquico;
    }

    public static function generarReporteHistorial($perfilMilitarId)
    {
        return self::porPersonal($perfilMilitarId)
            ->with(['cargo', 'estructuraMilitar'])
            ->ordenadoCronologicamente()
            ->get()
            ->map(function ($historial) {
                return $historial->resumen_cargo;
            });
    }
}
