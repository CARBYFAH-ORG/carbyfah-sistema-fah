<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepositorioStorage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.repositorios_storage';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre_repositorio',
        'tipo_storage_id',
        'estado_conexion_id',
        'configuracion_json',
        'capacidad_gb',
        'usado_gb',
        'url_base',
        'bucket_default',
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
        'tipo_storage_id' => 'integer',
        'estado_conexion_id' => 'integer',
        'configuracion_json' => 'array',
        'capacidad_gb' => 'integer',
        'usado_gb' => 'decimal:2',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function tipoStorage()
    {
        return $this->belongsTo(TipoStorage::class, 'tipo_storage_id', 'id');
    }

    public function estadoConexion()
    {
        return $this->belongsTo(EstadoConexion::class, 'estado_conexion_id', 'id');
    }

    public function archivos()
    {
        return $this->hasMany(ArchivoDigital::class, 'repositorio_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOperativos($query)
    {
        return $query->whereHas('estadoConexion', function ($q) {
            $q->where('permite_operaciones', true);
        });
    }

    public function getPorcentajeUsoAttribute()
    {
        if ($this->capacidad_gb == 0) return 0;
        return round(($this->usado_gb / $this->capacidad_gb) * 100, 2);
    }
}
