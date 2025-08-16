<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VersionArchivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.versiones_archivo';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'archivo_digital_id',
        'numero_version',
        'ruta_storage_version',
        'tamano_bytes',
        'checksum_md5',
        'modificado_por_id',
        'fecha_modificacion',
        'comentarios_version',
        'motivo_cambio_id',
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
        'fecha_modificacion'
    ];

    protected $casts = [
        'archivo_digital_id' => 'integer',
        'numero_version' => 'integer',
        'tamano_bytes' => 'integer',
        'modificado_por_id' => 'integer',
        'motivo_cambio_id' => 'integer',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function archivo()
    {
        return $this->belongsTo(ArchivoDigital::class, 'archivo_digital_id', 'id');
    }

    public function motivoCambio()
    {
        return $this->belongsTo(MotivoCambio::class, 'motivo_cambio_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdenadoPorVersion($query)
    {
        return $query->orderBy('numero_version', 'desc');
    }

    public function scopePorArchivo($query, $archivoId)
    {
        return $query->where('archivo_digital_id', $archivoId);
    }
}
