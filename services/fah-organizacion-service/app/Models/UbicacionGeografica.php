<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UbicacionGeografica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizacion.ubicaciones_geograficas';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_ubicacion',
        'nombre_ubicacion',
        'pais_id',
        'departamento_id',
        'municipio_id',
        'ciudad_id',
        'latitud',
        'longitud',
        'direccion_referencia',
        'altitud_metros',
        'telefono_principal',
        'telefono_emergencia',
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
        'deleted_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pais_id' => 'integer',
        'departamento_id' => 'integer',
        'municipio_id' => 'integer',
        'ciudad_id' => 'integer',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'altitud_metros' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    // Relaciones
    // NOTA: NO definimos relación pais() porque está en otro microservicio
    // La información del país se carga vía API en el controlador

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id', 'id');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id', 'id');
    }

    public function estructurasMilitares()
    {
        return $this->hasMany(EstructuraMilitar::class, 'ubicacion_geografica_id', 'id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorPais($query, $paisId)
    {
        return $query->where('pais_id', $paisId);
    }

    public function scopePorDepartamento($query, $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    public function scopePorMunicipio($query, $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    public function scopePorCiudad($query, $ciudadId)
    {
        return $query->where('ciudad_id', $ciudadId);
    }

    // Accessor para coordenadas completas
    public function getCoordenadasAttribute()
    {
        return [
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'string' => "{$this->latitud}, {$this->longitud}"
        ];
    }

    // ✅ ATRIBUTO VIRTUAL PARA DIRECCIÓN COMPLETA
    public function getDireccionCompletaAttribute()
    {
        $direccion = [];

        if (isset($this->pais)) {
            $direccion[] = $this->pais['nombre'];
        }
        if (isset($this->departamento)) {
            $direccion[] = $this->departamento->nombre_departamento;
        }
        if (isset($this->municipio)) {
            $direccion[] = $this->municipio->nombre_municipio;
        }
        if (isset($this->ciudad)) {
            $direccion[] = $this->ciudad->nombre_ciudad;
        }

        return implode(', ', $direccion);
    }
}
