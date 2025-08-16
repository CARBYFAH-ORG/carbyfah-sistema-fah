<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoPermiso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.tipos_permiso';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_permiso',
        'nombre_permiso',
        'descripcion',
        'nivel_criticidad',
        'requiere_autorizacion',
        'afecta_auditoria',
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
        'nivel_criticidad' => 'integer',
        'requiere_autorizacion' => 'boolean',
        'afecta_auditoria' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function permisos()
    {
        return $this->hasMany(PermisoAcceso::class, 'tipo_permiso_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdenadoPorCriticidad($query)
    {
        return $query->orderBy('nivel_criticidad', 'asc');
    }

    public function scopeCriticos($query)
    {
        return $query->where('nivel_criticidad', '>=', 4);
    }
}
