<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstructuraMilitar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizacion.estructura_militar';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_unidad',
        'nombre_unidad',
        'tipo_estructura_id',
        'ubicacion_geografica_id',
        'unidad_padre_id',
        'nivel_jerarquico',
        'orden_horizontal',
        'capacidad_personal',
        'fecha_activacion',
        'fecha_desactivacion',
        'logo_url',
        'mision',
        'vision',
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
        'fecha_activacion',
        'fecha_desactivacion'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tipo_estructura_id' => 'integer',
        'ubicacion_geografica_id' => 'integer',
        'unidad_padre_id' => 'integer',
        'nivel_jerarquico' => 'integer',
        'orden_horizontal' => 'integer',
        'capacidad_personal' => 'integer',
        'fecha_activacion' => 'date',
        'fecha_desactivacion' => 'date',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    // Relaciones
    // NOTA: NO definimos relación tipoEstructura() porque está en otro microservicio
    // La información del tipo estructura se carga vía API en el controlador

    public function ubicacionGeografica()
    {
        return $this->belongsTo(UbicacionGeografica::class, 'ubicacion_geografica_id', 'id');
    }

    public function unidadPadre()
    {
        return $this->belongsTo(EstructuraMilitar::class, 'unidad_padre_id', 'id');
    }

    public function unidadesHijas()
    {
        return $this->hasMany(EstructuraMilitar::class, 'unidad_padre_id', 'id');
    }

    public function cargos()
    {
        return $this->hasMany(Cargo::class, 'estructura_militar_id', 'id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel_jerarquico', $nivel);
    }

    public function scopePorTipoEstructura($query, $tipoId)
    {
        return $query->where('tipo_estructura_id', $tipoId);
    }

    public function scopeOrdenadoPorJerarquia($query)
    {
        return $query->orderBy('nivel_jerarquico', 'asc')
            ->orderBy('orden_horizontal', 'asc');
    }

    public function scopeUnidadesRaiz($query)
    {
        return $query->whereNull('unidad_padre_id');
    }

    public function scopeUnidadesActivas($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('fecha_desactivacion')
                    ->orWhere('fecha_desactivacion', '>', now());
            });
    }

    // Accessor para nombre completo con jerarquía
    public function getNombreCompletoAttribute()
    {
        return $this->unidadPadre
            ? "{$this->unidadPadre->codigo_unidad} > {$this->codigo_unidad}"
            : $this->codigo_unidad;
    }

    // ✅ ACCESSOR PARA INFORMACIÓN COMPLETA CON TIPO ESTRUCTURA
    public function getUnidadCompletaAttribute()
    {
        $info = $this->codigo_unidad;

        if (isset($this->tipo_estructura)) {
            $info = "{$this->tipo_estructura['nombre_tipo']} - {$info}";
        }

        if ($this->unidadPadre) {
            $info = "{$this->unidadPadre->codigo_unidad} > {$info}";
        }

        return $info;
    }

    // ✅ MÉTODO HELPER PARA OBTENER JERARQUÍA COMPLETA
    public function getJerarquiaCompletaAttribute()
    {
        $jerarquia = [];
        $actual = $this;

        while ($actual) {
            array_unshift($jerarquia, $actual->codigo_unidad);
            $actual = $actual->unidadPadre;
        }

        return implode(' > ', $jerarquia);
    }
}
