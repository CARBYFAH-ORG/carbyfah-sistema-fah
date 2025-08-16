<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermisoAcceso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.permisos_acceso';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'archivo_digital_id',
        'persona_id',
        'rol_id',
        'tipo_permiso_id',
        'fecha_concedido',
        'concedido_por_id',
        'fecha_expiracion',
        'motivo_permiso',
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
        'fecha_concedido',
        'fecha_expiracion'
    ];

    protected $casts = [
        'archivo_digital_id' => 'integer',
        'persona_id' => 'integer',
        'rol_id' => 'integer',
        'tipo_permiso_id' => 'integer',
        'concedido_por_id' => 'integer',
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

    public function tipoPermiso()
    {
        return $this->belongsTo(TipoPermiso::class, 'tipo_permiso_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVigentes($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('fecha_expiracion')
                ->orWhere('fecha_expiracion', '>', now());
        });
    }

    public function scopePorArchivo($query, $archivoId)
    {
        return $query->where('archivo_digital_id', $archivoId);
    }

    public function scopePorPersona($query, $personaId)
    {
        return $query->where('persona_id', $personaId);
    }

    public function scopePorRol($query, $rolId)
    {
        return $query->where('rol_id', $rolId);
    }

    public function getEsVigenteAttribute()
    {
        if ($this->fecha_expiracion === null) return true;
        return $this->fecha_expiracion > now();
    }
}
