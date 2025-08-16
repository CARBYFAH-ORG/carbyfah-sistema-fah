<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoEstadoGeneral extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipos_estado_general';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo',
        'nombre',
        'permite_operaciones',
        'es_estado_final',
        'requiere_justificacion',
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
        'permite_operaciones' => 'boolean',
        'es_estado_final' => 'boolean',
        'requiere_justificacion' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }
}
