<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerfilMilitar extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tabla específica en schema personal
     */
    protected $table = 'personal.perfiles_militares';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'id';

    /**
     * Campos que se pueden asignar masivamente
     */
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
        'is_active'
    ];

    /**
     * Campos que deben ser tratados como fechas
     */
    protected $dates = [
        'fecha_ingreso_fah',
        'fecha_retiro_fah',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Conversión automática de tipos de datos
     */
    protected $casts = [
        'is_active' => 'boolean',
        'tiempo_servicio_anos' => 'integer',
        'tiempo_servicio_meses' => 'integer'
    ];

    /**
     * Campo para eliminación suave
     */
    protected $deleteAtColumn = 'deleted_at';

    /**
     * Relación con DatosPersonales (1:1)
     */
    public function datosPersonales()
    {
        return $this->belongsTo(DatosPersonales::class, 'datos_personales_id', 'id');
    }

    /**
     * Relación con Grado actual
     */
    public function gradoActual()
    {
        return $this->belongsTo(Grado::class, 'grado_actual_id', 'id');
    }

    /**
     * Relación con Especialidad
     */
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id', 'id');
    }

    /**
     * Relación con Categoría de Personal
     */
    public function categoriaPersonal()
    {
        return $this->belongsTo(CategoriaPersonal::class, 'categoria_personal_id', 'id');
    }

    /**
     * Relación inversa con UsuarioSistema (1:1)
     */
    public function usuarioSistema()
    {
        return $this->hasOne(UsuarioSistema::class, 'perfil_militar_id', 'id');
    }

    /**
     * Relación con AsignacionActual
     */
    public function asignacionActual()
    {
        return $this->hasOne(AsignacionActual::class, 'perfil_militar_id', 'id')
            ->whereNull('fecha_fin_asignacion');
    }

    /**
     * Obtener nombre completo del militar
     */
    public function obtenerNombreCompleto()
    {
        if ($this->datosPersonales) {
            $datos = $this->datosPersonales;
            return trim("{$datos->primer_nombre} {$datos->segundo_nombre} {$datos->primer_apellido} {$datos->segundo_apellido}");
        }
        return 'Sin datos personales';
    }

    /**
     * Obtener identificación militar completa
     */
    public function obtenerIdentificacionCompleta()
    {
        $grado = $this->gradoActual ? $this->gradoActual->nombre_grado : 'Sin grado';
        $nombre = $this->obtenerNombreCompleto();
        $serie = $this->serie_militar ? "({$this->serie_militar})" : '';

        return "{$grado} {$nombre} {$serie}";
    }

    /**
     * Verificar si está en servicio activo
     */
    public function estaEnServicioActivo()
    {
        return $this->estado_servicio === 'ACTIVO' && $this->is_active;
    }

    /**
     * Calcular años de servicio desde ingreso
     */
    public function calcularAnosServicio()
    {
        if ($this->fecha_ingreso_fah) {
            return now()->diffInYears($this->fecha_ingreso_fah);
        }
        return 0;
    }
}
