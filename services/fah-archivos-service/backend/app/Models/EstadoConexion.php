<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoConexion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.estados_conexion';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_estado',
        'nombre_estado',
        'descripcion',
        'permite_operaciones',
        'requiere_intervencion',
        'color_estado',
        'icono_estado',
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
        'permite_operaciones' => 'boolean',
        'requiere_intervencion' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function repositorios()
    {
        return $this->hasMany(RepositorioStorage::class, 'estado_conexion_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOperativos($query)
    {
        return $query->where('permite_operaciones', true);
    }
}
