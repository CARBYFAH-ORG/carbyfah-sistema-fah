<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ciudad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizacion.ciudades';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'municipio_id',
        'codigo_ciudad',
        'nombre_ciudad',
        'tipo_localidad',
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
        'municipio_id' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    // Relaciones
    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id', 'id');
    }

    public function ubicacionesGeograficas()
    {
        return $this->hasMany(UbicacionGeografica::class, 'ciudad_id', 'id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorMunicipio($query, $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_localidad', $tipo);
    }
}
