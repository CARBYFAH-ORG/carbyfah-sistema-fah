<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NivelAcceso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.niveles_acceso';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_nivel',
        'nombre_nivel',
        'descripcion',
        'orden_seguridad',
        'requiere_autenticacion',
        'requiere_autorizacion',
        'log_accesos',
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
        'orden_seguridad' => 'integer',
        'requiere_autenticacion' => 'boolean',
        'requiere_autorizacion' => 'boolean',
        'log_accesos' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function archivos()
    {
        return $this->hasMany(ArchivoDigital::class, 'nivel_acceso_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdenadoPorSeguridad($query)
    {
        return $query->orderBy('orden_seguridad', 'asc');
    }

    public function scopePublicos($query)
    {
        return $query->where('requiere_autenticacion', false);
    }
}
