<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivoDigital extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.archivos_digitales';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre_archivo',
        'nombre_original',
        'ruta_storage',
        'repositorio_id',
        'tipo_archivo_id',
        'categoria_contenido_id',
        'nivel_acceso_id',
        'tamano_bytes',
        'checksum_md5',
        'hash_contenido',
        'subido_por_id',
        'fecha_subida',
        'unidad_origen_id',
        'descripcion_archivo',
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
        'deleted_at',
        'fecha_subida'
    ];

    protected $casts = [
        'repositorio_id' => 'integer',
        'tipo_archivo_id' => 'integer',
        'categoria_contenido_id' => 'integer',
        'nivel_acceso_id' => 'integer',
        'tamano_bytes' => 'integer',
        'subido_por_id' => 'integer',
        'unidad_origen_id' => 'integer',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    // Relaciones
    public function repositorio()
    {
        return $this->belongsTo(RepositorioStorage::class, 'repositorio_id', 'id');
    }

    public function tipoArchivo()
    {
        return $this->belongsTo(TipoArchivo::class, 'tipo_archivo_id', 'id');
    }

    public function categoriaContenido()
    {
        return $this->belongsTo(CategoriaContenido::class, 'categoria_contenido_id', 'id');
    }

    public function nivelAcceso()
    {
        return $this->belongsTo(NivelAcceso::class, 'nivel_acceso_id', 'id');
    }

    public function versiones()
    {
        return $this->hasMany(VersionArchivo::class, 'archivo_digital_id', 'id');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoAcceso::class, 'archivo_digital_id', 'id');
    }

    public function accesos()
    {
        return $this->hasMany(RegistroAcceso::class, 'archivo_digital_id', 'id');
    }

    public function metadatos()
    {
        return $this->hasMany(MetadatoArchivo::class, 'archivo_digital_id', 'id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_contenido_id', $categoriaId);
    }

    public function scopePorNivelAcceso($query, $nivelId)
    {
        return $query->where('nivel_acceso_id', $nivelId);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_subida', '>=', now()->subDays($dias));
    }

    // Accessors
    public function getTamanoFormateadoAttribute()
    {
        $bytes = $this->tamano_bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getExtensionAttribute()
    {
        return strtolower(pathinfo($this->nombre_original, PATHINFO_EXTENSION));
    }
}
