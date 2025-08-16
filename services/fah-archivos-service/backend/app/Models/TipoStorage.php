<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoStorage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.tipos_storage';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_tipo',
        'nombre_tipo',
        'descripcion',
        'requiere_configuracion',
        'soporta_versionado',
        'soporta_encriptacion',
        'costo_relativo',
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
        'requiere_configuracion' => 'boolean',
        'soporta_versionado' => 'boolean',
        'soporta_encriptacion' => 'boolean',
        'costo_relativo' => 'integer',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function repositorios()
    {
        return $this->hasMany(RepositorioStorage::class, 'tipo_storage_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }
}
