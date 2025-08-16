<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroAcceso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.registro_accesos';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'archivo_digital_id',
        'usuario_id',
        'tipo_acceso',
        'fecha_acceso',
        'direccion_ip',
        'user_agent',
        'exitoso',
        'motivo_fallo',
        'tamano_descargado',
        'tiempo_acceso_segundos',
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
        'fecha_acceso'
    ];

    protected $casts = [
        'archivo_digital_id' => 'integer',
        'usuario_id' => 'integer',
        'exitoso' => 'boolean',
        'tamano_descargado' => 'integer',
        'tiempo_acceso_segundos' => 'integer',
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

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExitosos($query)
    {
        return $query->where('exitoso', true);
    }

    public function scopeFallidos($query)
    {
        return $query->where('exitoso', false);
    }

    public function scopePorTipoAcceso($query, $tipo)
    {
        return $query->where('tipo_acceso', $tipo);
    }

    public function scopePorArchivo($query, $archivoId)
    {
        return $query->where('archivo_digital_id', $archivoId);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_acceso', '>=', now()->subDays($dias));
    }

    public function scopeDescargas($query)
    {
        return $query->where('tipo_acceso', 'DOWNLOAD');
    }
}
