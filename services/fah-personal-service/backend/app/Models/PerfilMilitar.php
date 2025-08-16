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

        // claves foraneas
        'datos_personales_id' => 'integer',
        'categoria_personal_id' => 'integer',
        'especialidad_id' => 'integer',
        'grado_actual_id' => 'integer'
    ];

    // relaciones

    // relacion uno a uno con datos personales
    public function datosPersonales()
    {
        return $this->belongsTo(DatosPersonales::class, 'datos_personales_id', 'id');
    }

    // relaciones con catalogos
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

    // relaciones con otras tablas del servicio personal
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

    // scopes para consultas

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

    public function scopeDisponibles($query)
    {
        return $query->where('situacion', 'DISPONIBLE');
    }

    public function scopePorSerie($query, $serieMilitar)
    {
        return $query->where('serie_militar', $serieMilitar);
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

    public function scopeConTiempoServicio($query, $anosMinimos)
    {
        return $query->where('tiempo_servicio_anos', '>=', $anosMinimos);
    }

    // accessors para atributos calculados

    public function getNombreCompletoMilitarAttribute()
    {
        $nombre = $this->datosPersonales ? $this->datosPersonales->nombre_completo : '';
        return $this->serie_militar ? "{$this->serie_militar} - {$nombre}" : $nombre;
    }

    public function getTiempoServicioCompletoAttribute()
    {
        if (!$this->tiempo_servicio_anos && !$this->tiempo_servicio_meses) {
            return null;
        }

        $anos = $this->tiempo_servicio_anos ?? 0;
        $meses = $this->tiempo_servicio_meses ?? 0;

        $resultado = [];
        if ($anos > 0) {
            $resultado[] = $anos . ($anos == 1 ? ' año' : ' años');
        }
        if ($meses > 0) {
            $resultado[] = $meses . ($meses == 1 ? ' mes' : ' meses');
        }

        return implode(' y ', $resultado);
    }

    public function getEstaEnServicioAttribute()
    {
        return $this->estado_servicio === 'ACTIVO';
    }

    public function getEstaDisponibleAttribute()
    {
        return $this->situacion === 'DISPONIBLE';
    }

    public function getEsOficcialAttribute()
    {
        return $this->categoriaPersonal &&
            $this->categoriaPersonal->codigo_categoria === 'OFICIAL';
    }

    // metodos para calculo automatico

    public function calcularTiempoServicio()
    {
        if (!$this->fecha_ingreso_fah) {
            return $this;
        }

        $fechaCalculo = $this->fecha_retiro_fah ?? now();
        $diff = $this->fecha_ingreso_fah->diff($fechaCalculo);

        $this->tiempo_servicio_anos = $diff->y;
        $this->tiempo_servicio_meses = $diff->m;

        return $this;
    }

    public function cambiarEstadoServicio($nuevoEstado, $motivo = null)
    {
        $estadosValidos = ['ACTIVO', 'RETIRADO', 'SUSPENDIDO'];

        if (!in_array($nuevoEstado, $estadosValidos)) {
            throw new \InvalidArgumentException('Estado de servicio no válido');
        }

        $this->estado_servicio = $nuevoEstado;

        if ($nuevoEstado === 'RETIRADO') {
            $this->fecha_retiro_fah = now();
            $this->motivo_retiro = $motivo;
            $this->situacion = 'RETIRADO';
        }

        return $this;
    }

    public function cambiarSituacion($nuevaSituacion)
    {
        $situacionesValidas = [
            'DISPONIBLE',
            'COMISION',
            'PERMISO',
            'MISION',
            'CURSO',
            'VACACIONES',
            'REPOSO',
            'RETIRADO'
        ];

        if (!in_array($nuevaSituacion, $situacionesValidas)) {
            throw new \InvalidArgumentException('Situación no válida');
        }

        $this->situacion = $nuevaSituacion;

        return $this;
    }

    public function promover($nuevoGradoId)
    {
        $this->grado_actual_id = $nuevoGradoId;
        $this->updated_by = 1; // TODO: obtener del usuario autenticado
        $this->version = $this->version + 1;

        return $this;
    }
}
