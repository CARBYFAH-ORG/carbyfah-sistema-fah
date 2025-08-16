<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizacion.departamentos';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'pais_id',
        'codigo_departamento',
        'nombre_departamento',
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
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    // ✅ NOTA: NO definimos relación pais() aquí porque está en otro microservicio
    // La información del país se carga vía API en el controlador

    public function municipios()
    {
        return $this->hasMany(Municipio::class, 'departamento_id', 'id');
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

    // ✅ ATRIBUTO VIRTUAL PARA INFORMACIÓN COMPLETA
    public function getDepartamentoCompletoAttribute()
    {
        return isset($this->pais)
            ? "{$this->pais['nombre_pais']} - {$this->nombre_departamento}"
            : $this->nombre_departamento;
    }
}
