<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaArchivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.categorias_archivo';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_categoria',
        'nombre_categoria',
        'descripcion',
        'extension_principal',
        'icono_categoria',
        'color_categoria',
        'orden_listado',
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
        'orden_listado' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function tiposArchivo()
    {
        return $this->hasMany(TipoArchivo::class, 'categoria_archivo_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdenadoPorListado($query)
    {
        return $query->orderBy('orden_listado', 'asc');
    }
}
