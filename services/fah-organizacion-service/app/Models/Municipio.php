<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizacion.municipios';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'departamento_id',
        'codigo_municipio',
        'nombre_municipio',
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
        'departamento_id' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    // Relaciones
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id');
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class, 'municipio_id', 'id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorDepartamento($query, $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }
}
