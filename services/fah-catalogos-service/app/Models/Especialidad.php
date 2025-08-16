<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especialidad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'especialidades';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_especialidad',
        'nombre_especialidad',
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
