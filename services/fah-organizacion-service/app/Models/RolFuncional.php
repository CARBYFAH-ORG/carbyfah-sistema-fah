<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolFuncional extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizacion.roles_funcionales';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_rol',
        'nombre_rol',
        'nivel_autoridad',
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
        'nivel_autoridad' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdenadoPorAutoridad($query)
    {
        return $query->orderBy('nivel_autoridad', 'desc');
    }

    public function scopePorNivelAutoridad($query, $nivel)
    {
        return $query->where('nivel_autoridad', $nivel);
    }
}
