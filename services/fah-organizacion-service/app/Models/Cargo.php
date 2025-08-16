<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizacion.cargos';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'estructura_militar_id',
        'codigo_cargo',
        'nombre_cargo',
        'nivel_jerarquico',
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
        'estructura_militar_id' => 'integer',
        'nivel_jerarquico' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    // Relaciones
    public function estructuraMilitar()
    {
        return $this->belongsTo(EstructuraMilitar::class, 'estructura_militar_id', 'id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorEstructura($query, $estructuraId)
    {
        return $query->where('estructura_militar_id', $estructuraId);
    }

    public function scopeOrdenadoPorJerarquia($query)
    {
        return $query->orderBy('nivel_jerarquico', 'desc');
    }

    // Accessor para cargo completo con unidad
    public function getCargoCompletoAttribute()
    {
        return $this->estructuraMilitar
            ? "{$this->nombre_cargo} - {$this->estructuraMilitar->codigo_unidad}"
            : $this->nombre_cargo;
    }
}
