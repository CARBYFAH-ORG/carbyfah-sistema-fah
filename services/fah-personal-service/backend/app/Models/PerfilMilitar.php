<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerfilMilitar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal.perfiles_militares';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'datos_personales_id',
        'serie_militar',
        'fecha_ingreso_fah',
        'fecha_retiro_fah',
        'motivo_retiro',
        'categoria_personal_id',
        'especialidad_id',
        'grado_actual_id',
        'estado_servicio',
        'situacion',
        'tiempo_servicio_anos',
        'tiempo_servicio_meses',
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
        'fecha_ingreso_fah',
        'fecha_retiro_fah'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'fecha_ingreso_fah' => 'date',
        'fecha_retiro_fah' => 'date',
        'tiempo_servicio_anos' => 'integer',
        'tiempo_servicio_meses' => 'integer',

        // Foreign keys
        'datos_personales_id' => 'integer',
        'categoria_personal_id' => 'integer',
        'especialidad_id' => 'integer',
        'grado_actual_id' => 'integer'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    // Relación 1:1 con datos personales
    public function datosPersonales()
    {
        return $this->belongsTo(DatosPersonales::class, 'datos_personales_id', 'id');
    }

    // Relaciones con catálogos
    public function categoriaPersonal()
    {
        return $this->belongsTo('App\Models\CategoriaPersonal', 'categoria_personal_id', 'id');
    }

    public function especialidad()
    {
        return $this->belongsTo('App\Models\Especialidad', 'especialidad_id', 'id');
    }

    public function gradoActual()
    {
        return $this->belongsTo('App\Models\Grado', 'grado_actual_id', 'id');
    }

    // Relaciones con otras tablas
    public function asignacionActual()
    {
        return $this->hasOne(AsignacionActual::class, 'perfil_militar_id', 'id');
    }

    public function usuarioSistema()
    {
        return $this->hasOne(UsuarioSistema::class, 'perfil_militar_id', 'id');
    }

    public function historialesCargos()
    {
        return $this->hasMany(HistorialCargo::class, 'perfil_militar_id', 'id');
    }

    public function asignacionRoles()
    {
        return $this->hasMany(AsignacionRol::class, 'perfil_militar_id', 'id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEnServicio($query)
    {
        return $query->where('estado_servicio', 'ACTIVO');
    }

    public function scopeRetirados($query)
    {
        return $query->where('estado_servicio', 'RETIRADO');
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_personal_id', $categoriaId);
    }

    public function scopePorEspecialidad($query, $especialidadId)
    {
        return $query->where('especialidad_id', $especialidadId);
    }

    public function scopePorGrado($query, $gradoId)
    {
        return $query->where('grado_actual_id', $gradoId);
    }

    public function scopePorSerie($query, $serieMilitar)
    {
        return $query->where('serie_militar', $serieMilitar);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('situacion', 'DISPONIBLE');
    }

    public function scopeConTiempoServicio($query, $anosMinimos)
    {
        return $query->where('tiempo_servicio_anos', '>=', $anosMinimos);
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getNombreCompletoAttribute()
    {
        return $this->datosPersonales?->nombre_completo;
    }

    public function getGradoCompletoAttribute()
    {
        return $this->gradoActual ?
            "{$this->gradoActual->abreviatura} {$this->datosPersonales->nombre_completo}" :
            $this->datosPersonales?->nombre_completo;
    }

    public function getTiempoServicioCompletoAttribute()
    {
        $anos = $this->tiempo_servicio_anos ?? 0;
        $meses = $this->tiempo_servicio_meses ?? 0;

        $resultado = [];

        if ($anos > 0) {
            $resultado[] = $anos . ($anos == 1 ? ' año' : ' años');
        }

        if ($meses > 0) {
            $resultado[] = $meses . ($meses == 1 ? ' mes' : ' meses');
        }

        return implode(' y ', $resultado) ?: '0 años';
    }

    public function getEstaActivoAttribute()
    {
        return $this->estado_servicio === 'ACTIVO' && $this->is_active;
    }

    public function getUnidadActualAttribute()
    {
        return $this->asignacionActual?->estructuraMilitar?->nombre_unidad;
    }

    public function getCargoActualAttribute()
    {
        return $this->asignacionActual?->cargo?->nombre_cargo;
    }

    // =====================================================
    // MÉTODOS PERSONALIZADOS
    // =====================================================

    public function calcularTiempoServicio()
    {
        if (!$this->fecha_ingreso_fah) {
            return;
        }

        $fechaFin = $this->fecha_retiro_fah ?? now();
        $diff = $fechaFin->diffInMonths($this->fecha_ingreso_fah);

        $this->tiempo_servicio_anos = intval($diff / 12);
        $this->tiempo_servicio_meses = $diff % 12;

        return $this;
    }

    public function puedeAccederSistema()
    {
        return $this->esta_activo && $this->usuarioSistema?->is_active;
    }

    public function obtenerRolesActivos()
    {
        return $this->asignacionRoles()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('fecha_expiracion')
                    ->orWhere('fecha_expiracion', '>=', now());
            })
            ->with('rolFuncional')
            ->get();
    }
}
