<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'grados';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'categoria_personal_id',
        'codigo_grado',
        'nombre_grado',
        'abreviatura',
        'orden_jerarquico',
        'insignia_url',
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
        'orden_jerarquico' => 'integer',
        'categoria_personal_id' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function categoriaPersonal()
    {
        return $this->belongsTo(CategoriaPersonal::class, 'categoria_personal_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdenadoPorJerarquia($query)
    {
        return $query->orderBy('orden_jerarquico', 'asc');
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_personal_id', $categoriaId);
    }

    public function getGradoCompletoAttribute()
    {
        return $this->categoriaPersonal
            ? "{$this->categoriaPersonal->nombre_categoria} - {$this->nombre_grado}"
            : $this->nombre_grado;
    }
}
