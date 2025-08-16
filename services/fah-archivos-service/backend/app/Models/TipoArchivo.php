<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoArchivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.tipos_archivo';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'categoria_archivo_id',
        'extension',
        'mime_type',
        'tamano_maximo_mb',
        'descripcion',
        'requiere_antivirus',
        'compresion_automatica',
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
        'categoria_archivo_id' => 'integer',
        'tamano_maximo_mb' => 'integer',
        'requiere_antivirus' => 'boolean',
        'compresion_automatica' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function categoriaArchivo()
    {
        return $this->belongsTo(CategoriaArchivo::class, 'categoria_archivo_id', 'id');
    }

    public function archivos()
    {
        return $this->hasMany(ArchivoDigital::class, 'tipo_archivo_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorExtension($query, $extension)
    {
        return $query->where('extension', $extension);
    }
}
