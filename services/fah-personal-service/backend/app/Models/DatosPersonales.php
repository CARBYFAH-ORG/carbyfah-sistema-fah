<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DatosPersonales extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal.datos_personales';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'numero_identidad',
        'primer_nombre',
        'segundo_nombre',
        'tercer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tercer_apellido',
        'fecha_nacimiento',

        // lugar de nacimiento
        'pais_nacimiento_id',
        'departamento_nacimiento_id',
        'municipio_nacimiento_id',
        'ciudad_nacimiento_id',
        'lugar_nacimiento_especifico',

        'nacionalidad',

        // residencia actual
        'pais_residencia_id',
        'departamento_residencia_id',
        'municipio_residencia_id',
        'ciudad_residencia_id',
        'direccion_residencia_especifica',

        // datos personales
        'tipo_genero_id',
        'estado_civil',
        'telefono_personal',
        'telefono_emergencia',
        'email_personal',

        // contacto emergencia
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'contacto_emergencia_relacion',

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
        'fecha_nacimiento'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'fecha_nacimiento' => 'date',

        // claves foraneas
        'pais_nacimiento_id' => 'integer',
        'departamento_nacimiento_id' => 'integer',
        'municipio_nacimiento_id' => 'integer',
        'ciudad_nacimiento_id' => 'integer',
        'pais_residencia_id' => 'integer',
        'departamento_residencia_id' => 'integer',
        'municipio_residencia_id' => 'integer',
        'ciudad_residencia_id' => 'integer',
        'tipo_genero_id' => 'integer'
    ];

    // relaciones

    // relacion uno a uno con perfil militar
    public function perfilMilitar()
    {
        return $this->hasOne(PerfilMilitar::class, 'datos_personales_id', 'id');
    }

    // relaciones con catalogos
    public function tipoGenero()
    {
        return $this->belongsTo('App\Models\TipoGenero', 'tipo_genero_id', 'id');
    }

    // relaciones geograficas nacimiento
    public function paisNacimiento()
    {
        return $this->belongsTo('App\Models\Pais', 'pais_nacimiento_id', 'id');
    }

    public function departamentoNacimiento()
    {
        return $this->belongsTo('App\Models\Departamento', 'departamento_nacimiento_id', 'id');
    }

    public function municipioNacimiento()
    {
        return $this->belongsTo('App\Models\Municipio', 'municipio_nacimiento_id', 'id');
    }

    public function ciudadNacimiento()
    {
        return $this->belongsTo('App\Models\Ciudad', 'ciudad_nacimiento_id', 'id');
    }

    // relaciones geograficas residencia
    public function paisResidencia()
    {
        return $this->belongsTo('App\Models\Pais', 'pais_residencia_id', 'id');
    }

    public function departamentoResidencia()
    {
        return $this->belongsTo('App\Models\Departamento', 'departamento_residencia_id', 'id');
    }

    public function municipioResidencia()
    {
        return $this->belongsTo('App\Models\Municipio', 'municipio_residencia_id', 'id');
    }

    public function ciudadResidencia()
    {
        return $this->belongsTo('App\Models\Ciudad', 'ciudad_residencia_id', 'id');
    }

    // scopes para consultas

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorIdentidad($query, $numeroIdentidad)
    {
        return $query->where('numero_identidad', $numeroIdentidad);
    }

    public function scopePorNombre($query, $nombre)
    {
        return $query->where(function ($q) use ($nombre) {
            $q->where('primer_nombre', 'ILIKE', "%{$nombre}%")
                ->orWhere('segundo_nombre', 'ILIKE', "%{$nombre}%")
                ->orWhere('primer_apellido', 'ILIKE', "%{$nombre}%")
                ->orWhere('segundo_apellido', 'ILIKE', "%{$nombre}%");
        });
    }

    public function scopePorGenero($query, $generoId)
    {
        return $query->where('tipo_genero_id', $generoId);
    }

    // accessors para atributos calculados

    public function getNombreCompletoAttribute()
    {
        $nombres = collect([
            $this->primer_nombre,
            $this->segundo_nombre,
            $this->tercer_nombre
        ])->filter()->implode(' ');

        $apellidos = collect([
            $this->primer_apellido,
            $this->segundo_apellido,
            $this->tercer_apellido
        ])->filter()->implode(' ');

        return "{$nombres} {$apellidos}";
    }

    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ?
            now()->diffInYears($this->fecha_nacimiento) : null;
    }

    public function getLugarNacimientoCompletoAttribute()
    {
        $lugares = collect([
            $this->ciudadNacimiento?->nombre_ciudad,
            $this->municipioNacimiento?->nombre_municipio,
            $this->departamentoNacimiento?->nombre_departamento,
            $this->paisNacimiento?->nombre
        ])->filter()->implode(', ');

        return $this->lugar_nacimiento_especifico ?
            "{$this->lugar_nacimiento_especifico}, {$lugares}" : $lugares;
    }

    public function getDireccionCompletaAttribute()
    {
        $lugares = collect([
            $this->ciudadResidencia?->nombre_ciudad,
            $this->municipioResidencia?->nombre_municipio,
            $this->departamentoResidencia?->nombre_departamento,
            $this->paisResidencia?->nombre
        ])->filter()->implode(', ');

        return $this->direccion_residencia_especifica ?
            "{$this->direccion_residencia_especifica}, {$lugares}" : $lugares;
    }
}
