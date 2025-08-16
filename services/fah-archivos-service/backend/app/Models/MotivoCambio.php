<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MotivoCambio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.motivos_cambio';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_motivo',
        'nombre_motivo',
        'descripcion',
        'requiere_justificacion',
        'genera_notificacion',
        'orden_criticidad',
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
        'requiere_justificacion' => 'boolean',
        'genera_notificacion' => 'boolean',
        'orden_criticidad' => 'integer',
        'is_active' => 'boolean',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    public function versiones()
    {
        return $this->hasMany(VersionArchivo::class, 'motivo_cambio_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdenadoPorCriticidad($query)
    {
        return $query->orderBy('orden_criticidad', 'asc');
    }

    public function scopeCriticos($query)
    {
        return $query->where('orden_criticidad', '>=', 4);
    }
}
