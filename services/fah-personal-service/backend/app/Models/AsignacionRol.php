<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignacionRol extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal.asignacion_roles';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'perfil_militar_id',
        'rol_funcional_id',
        'fecha_asignacion',
        'fecha_expiracion',
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
        'fecha_asignacion',
        'fecha_expiracion'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'fecha_asignacion' => 'date',
        'fecha_expiracion' => 'date',

        // Foreign keys
        'perfil_militar_id' => 'integer',
        'rol_funcional_id' => 'integer'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    // Relación con perfil militar
    public function perfilMilitar()
    {
        return $this->belongsTo(PerfilMilitar::class, 'perfil_militar_id', 'id');
    }

    // Relación con rol funcional (organizacion schema)
    public function rolFuncional()
    {
        return $this->belongsTo('App\Models\RolFuncional', 'rol_funcional_id', 'id');
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
            $q->whereNull('fecha_expiracion')
                ->orWhere('fecha_expiracion', '>=', now());
        });
    }

    public function scopeExpirados($query)
    {
        return $query->whereNotNull('fecha_expiracion')
            ->where('fecha_expiracion', '<', now());
    }

    public function scopePorVencer($query, $diasAlerta = 30)
    {
        return $query->whereNotNull('fecha_expiracion')
            ->whereBetween('fecha_expiracion', [
                now(),
                now()->addDays($diasAlerta)
            ]);
    }

    public function scopePorPersonal($query, $perfilId)
    {
        return $query->where('perfil_militar_id', $perfilId);
    }

    public function scopePorRol($query, $rolId)
    {
        return $query->where('rol_funcional_id', $rolId);
    }

    public function scopeAsignadosEntre($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_asignacion', [$fechaInicio, $fechaFin]);
    }

    public function scopeConNivelAutoridad($query, $nivelMinimo)
    {
        return $query->whereHas('rolFuncional', function ($q) use ($nivelMinimo) {
            $q->where('nivel_autoridad', '>=', $nivelMinimo);
        });
    }

    public function scopeOrdenadoPorAutoridad($query, $direccion = 'desc')
    {
        return $query->join(
            'organizacion.roles_funcionales',
            'personal.asignacion_roles.rol_funcional_id',
            '=',
            'organizacion.roles_funcionales.id'
        )
            ->orderBy('organizacion.roles_funcionales.nivel_autoridad', $direccion)
            ->select('personal.asignacion_roles.*');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getNombreMilitarAttribute()
    {
        return $this->perfilMilitar?->grado_completo;
    }

    public function getRolNombreAttribute()
    {
        return $this->rolFuncional?->nombre_rol;
    }

    public function getNivelAutoridadAttribute()
    {
        return $this->rolFuncional?->nivel_autoridad;
    }

    public function getDuracionDiasAttribute()
    {
        $fechaFin = $this->fecha_expiracion ?? now();
        return $this->fecha_asignacion->diffInDays($fechaFin);
    }

    public function getDiasRestantesAttribute()
    {
        if (!$this->fecha_expiracion) {
            return null; // Rol permanente
        }

        $dias = now()->diffInDays($this->fecha_expiracion, false);
        return $dias > 0 ? $dias : 0;
    }

    public function getEstadoRolAttribute()
    {
        if (!$this->is_active) {
            return 'INACTIVO';
        }

        if (!$this->fecha_expiracion) {
            return 'PERMANENTE';
        }

        if ($this->fecha_expiracion < now()) {
            return 'EXPIRADO';
        }

        if ($this->dias_restantes <= 30) {
            return 'POR_VENCER';
        }

        return 'VIGENTE';
    }

    public function getPeriodoRolAttribute()
    {
        $inicio = $this->fecha_asignacion->format('d/m/Y');
        $fin = $this->fecha_expiracion ?
            $this->fecha_expiracion->format('d/m/Y') :
            'Permanente';
        return "{$inicio} - {$fin}";
    }

    public function getEsVigenteAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->fecha_expiracion) {
            return true;
        }

        return $this->fecha_expiracion >= now();
    }

    public function getEsPermanenteAttribute()
    {
        return is_null($this->fecha_expiracion);
    }

    public function getAsignacionCompletaAttribute()
    {
        return [
            'militar' => $this->nombre_militar,
            'rol' => $this->rol_nombre,
            'nivel_autoridad' => $this->nivel_autoridad,
            'periodo' => $this->periodo_rol,
            'estado' => $this->estado_rol,
            'dias_restantes' => $this->dias_restantes
        ];
    }

    // =====================================================
    // MÉTODOS PERSONALIZADOS
    // =====================================================

    public function extender($nuevaFechaExpiracion)
    {
        if ($this->es_vigente) {
            $this->fecha_expiracion = $nuevaFechaExpiracion;
            return $this->save();
        }

        return false;
    }

    public function hacerPermanente()
    {
        $this->fecha_expiracion = null;
        return $this->save();
    }

    public function revocar($fechaRevocacion = null)
    {
        $this->fecha_expiracion = $fechaRevocacion ?? now();
        $this->is_active = false;
        return $this->save();
    }

    public function renovar($mesesAdicionales = 12)
    {
        if ($this->fecha_expiracion) {
            $nuevaFecha = $this->fecha_expiracion->addMonths($mesesAdicionales);
        } else {
            $nuevaFecha = now()->addMonths($mesesAdicionales);
        }

        $this->fecha_expiracion = $nuevaFecha;
        return $this->save();
    }

    public function necesitaRenovacion($diasAnticipacion = 30)
    {
        if (!$this->fecha_expiracion) {
            return false; // Roles permanentes no necesitan renovación
        }

        return $this->dias_restantes <= $diasAnticipacion;
    }

    public function puedeEjercerAutoridad($nivelRequerido)
    {
        return $this->es_vigente &&
            $this->nivel_autoridad >= $nivelRequerido;
    }

    public function tieneConflictoRoles($nuevaFechaInicio, $nuevaFechaFin = null)
    {
        // Verificar si el mismo militar tiene roles similares en el período
        $query = self::where('perfil_militar_id', $this->perfil_militar_id)
            ->where('id', '!=', $this->id)
            ->where('rol_funcional_id', $this->rol_funcional_id)
            ->where('is_active', true);

        if ($nuevaFechaFin) {
            $query->where(function ($q) use ($nuevaFechaInicio, $nuevaFechaFin) {
                $q->where('fecha_asignacion', '<=', $nuevaFechaFin)
                    ->where(function ($q2) use ($nuevaFechaInicio) {
                        $q2->whereNull('fecha_expiracion')
                            ->orWhere('fecha_expiracion', '>=', $nuevaFechaInicio);
                    });
            });
        } else {
            $query->where(function ($q) use ($nuevaFechaInicio) {
                $q->where('fecha_asignacion', '<=', $nuevaFechaInicio)
                    ->where(function ($q2) use ($nuevaFechaInicio) {
                        $q2->whereNull('fecha_expiracion')
                            ->orWhere('fecha_expiracion', '>=', $nuevaFechaInicio);
                    });
            });
        }

        return $query->exists();
    }

    public function obtenerHistorialRoles()
    {
        return self::where('perfil_militar_id', $this->perfil_militar_id)
            ->with('rolFuncional')
            ->orderBy('fecha_asignacion', 'desc')
            ->get();
    }

    public static function rolesVigentesPersonal($perfilMilitarId)
    {
        return self::porPersonal($perfilMilitarId)
            ->activos()
            ->vigentes()
            ->with('rolFuncional')
            ->ordenadoPorAutoridad()
            ->get();
    }

    public static function generarReporteAsignaciones($fechaInicio = null, $fechaFin = null)
    {
        $query = self::with(['perfilMilitar.datosPersonales', 'rolFuncional']);

        if ($fechaInicio && $fechaFin) {
            $query->asignadosEntre($fechaInicio, $fechaFin);
        }

        return $query->get()->map(function ($asignacion) {
            return $asignacion->asignacion_completa;
        });
    }

    public static function alertasVencimiento($diasAnticipacion = 30)
    {
        return self::activos()
            ->vigentes()
            ->porVencer($diasAnticipacion)
            ->with(['perfilMilitar.datosPersonales', 'rolFuncional'])
            ->get()
            ->map(function ($asignacion) {
                return array_merge($asignacion->asignacion_completa, [
                    'tipo_alerta' => $asignacion->dias_restantes <= 7 ? 'CRITICA' : 'ADVERTENCIA'
                ]);
            });
    }
}
