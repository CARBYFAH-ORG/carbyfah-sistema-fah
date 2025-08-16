<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaContenido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.categorias_contenido';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_categoria',
        'nombre_categoria',
        'descripcion',
        'requiere_clasificacion',
        'permite_descarga_publica',
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
        'requiere_clasificacion' => 'boolean',
        'permite_descarga_publica' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function archivos()
    {
        return $this->hasMany(ArchivoDigital::class, 'categoria_contenido_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublicos($query)
    {
        return $query->where('permite_descarga_publica', true);
    }
}
