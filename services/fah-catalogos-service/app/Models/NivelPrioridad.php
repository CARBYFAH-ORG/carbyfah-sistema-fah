<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NivelPrioridad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'niveles_prioridad';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo',
        'nombre',
        'nivel_numerico',
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
        'nivel_numerico' => 'integer',
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
