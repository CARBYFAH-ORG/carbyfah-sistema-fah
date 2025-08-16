<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignacionActual extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal.asignaciones_actuales';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'perfil_militar_id',
        'estructura_militar_id',
        'cargo_id',
        'fecha_inicio_asignacion',
        'fecha_fin_asignacion',
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
        'fecha_inicio_asignacion',
        'fecha_fin_asignacion'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'fecha_inicio_asignacion' => 'date',
        'fecha_fin_asignacion' => 'date',

        // Foreign keys
        'perfil_militar_id' => 'integer',
        'estructura_militar_id' => 'integer',
        'cargo_id' => 'integer'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    // Relación con perfil militar
    public function perfilMilitar()
    {
        return $this->belongsTo(PerfilMilitar::class, 'perfil_militar_id', 'id');
    }

    // Relación con estructura militar (organizacion schema)
    public function estructuraMilitar()
    {
        return $this->belongsTo('App\Models\EstructuraMilitar', 'estructura_militar_id', 'id');
    }

    // Relación con cargo (organizacion schema)
    public function cargo()
    {
        return $this->belongsTo('App\Models\Cargo', 'cargo_id', 'id');
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
        return $query->where(function ($q) {
            $q->whereNull('fecha_fin_asignacion')
                ->orWhere('fecha_fin_asignacion', '>=', now());
        });
    }

    public function scopeVencidas($query)
    {
        return $query->whereNotNull('fecha_fin_asignacion')
            ->where('fecha_fin_asignacion', '<', now());
    }

    public function scopePorUnidad($query, $unidadId)
    {
        return $query->where('estructura_militar_id', $unidadId);
    }

    public function scopePorCargo($query, $cargoId)
    {
        return $query->where('cargo_id', $cargoId);
    }

    public function scopePorPersonal($query, $perfilId)
    {
        return $query->where('perfil_militar_id', $perfilId);
    }

    public function scopeIniciadasEntre($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_inicio_asignacion', [$fechaInicio, $fechaFin]);
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getNombreMilitarAttribute()
    {
        return $this->perfilMilitar?->grado_completo;
    }

    public function getUnidadNombreAttribute()
    {
        return $this->estructuraMilitar?->nombre_unidad;
    }

    public function getCargoNombreAttribute()
    {
        return $this->cargo?->nombre_cargo;
    }

    public function getDuracionDiasAttribute()
    {
        $fechaFin = $this->fecha_fin_asignacion ?? now();
        return $this->fecha_inicio_asignacion->diffInDays($fechaFin);
    }

    public function getDuracionMesesAttribute()
    {
        $fechaFin = $this->fecha_fin_asignacion ?? now();
        return $this->fecha_inicio_asignacion->diffInMonths($fechaFin);
    }

    public function getEsVigenteAttribute()
    {
        if (!$this->fecha_fin_asignacion) {
            return true;
        }

        return $this->fecha_fin_asignacion >= now();
    }

    public function getEstadoAsignacionAttribute()
    {
        if (!$this->is_active) {
            return 'INACTIVA';
        }

        if ($this->es_vigente) {
            return 'VIGENTE';
        }

        return 'VENCIDA';
    }

    public function getAsignacionCompletaAttribute()
    {
        return [
            'militar' => $this->nombre_militar,
            'unidad' => $this->unidad_nombre,
            'cargo' => $this->cargo_nombre,
            'desde' => $this->fecha_inicio_asignacion->format('d/m/Y'),
            'hasta' => $this->fecha_fin_asignacion?->format('d/m/Y') ?? 'Indefinida',
            'estado' => $this->estado_asignacion
        ];
    }

    // =====================================================
    // MÉTODOS PERSONALIZADOS
    // =====================================================

    public function extender($nuevaFechaFin)
    {
        $this->fecha_fin_asignacion = $nuevaFechaFin;
        return $this->save();
    }

    public function finalizar($fechaFin = null)
    {
        $this->fecha_fin_asignacion = $fechaFin ?? now();
        return $this->save();
    }

    public function esTemporalPorVencer($diasAlerta = 30)
    {
        if (!$this->fecha_fin_asignacion) {
            return false;
        }

        return $this->fecha_fin_asignacion->diffInDays(now()) <= $diasAlerta;
    }

    public function puedeSerExtendida()
    {
        return $this->es_vigente && $this->is_active;
    }

    public function tieneConflictosFecha($nuevaFechaInicio, $nuevaFechaFin = null)
    {
        // Verificar si hay otras asignaciones del mismo militar en las fechas
        $query = self::where('perfil_militar_id', $this->perfil_militar_id)
            ->where('id', '!=', $this->id)
            ->where('is_active', true);

        if ($nuevaFechaFin) {
            $query->where(function ($q) use ($nuevaFechaInicio, $nuevaFechaFin) {
                $q->whereBetween('fecha_inicio_asignacion', [$nuevaFechaInicio, $nuevaFechaFin])
                    ->orWhereBetween('fecha_fin_asignacion', [$nuevaFechaInicio, $nuevaFechaFin])
                    ->orWhere(function ($q2) use ($nuevaFechaInicio, $nuevaFechaFin) {
                        $q2->where('fecha_inicio_asignacion', '<=', $nuevaFechaInicio)
                            ->where(function ($q3) use ($nuevaFechaFin) {
                                $q3->whereNull('fecha_fin_asignacion')
                                    ->orWhere('fecha_fin_asignacion', '>=', $nuevaFechaFin);
                            });
                    });
            });
        } else {
            $query->where(function ($q) use ($nuevaFechaInicio) {
                $q->where('fecha_inicio_asignacion', '<=', $nuevaFechaInicio)
                    ->where(function ($q2) use ($nuevaFechaInicio) {
                        $q2->whereNull('fecha_fin_asignacion')
                            ->orWhere('fecha_fin_asignacion', '>=', $nuevaFechaInicio);
                    });
            });
        }

        return $query->exists();
    }
}
