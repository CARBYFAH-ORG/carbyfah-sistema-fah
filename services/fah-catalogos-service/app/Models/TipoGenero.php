<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class TipoGenero extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipos_genero';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo',
        'nombre',
        'abreviatura',
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

    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }
}
