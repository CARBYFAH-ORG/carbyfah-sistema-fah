<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadatoArchivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_assets.metadatos_archivo';
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'archivo_digital_id',
        'clave_metadato',
        'valor_metadato',
        'tipo_dato',
        'es_indexable',
        'es_modificable',
        'orden_display',
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
        'archivo_digital_id' => 'integer',
        'es_indexable' => 'boolean',
        'es_modificable' => 'boolean',
        'orden_display' => 'integer',
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

    public function scopeIndexables($query)
    {
        return $query->where('es_indexable', true);
    }

    public function scopeModificables($query)
    {
        return $query->where('es_modificable', true);
    }

    public function scopePorArchivo($query, $archivoId)
    {
        return $query->where('archivo_digital_id', $archivoId);
    }

    public function scopeOrdenadoPorDisplay($query)
    {
        return $query->orderBy('orden_display', 'asc');
    }

    public function scopePorTipoDato($query, $tipo)
    {
        return $query->where('tipo_dato', $tipo);
    }

    public function getValorFormateadoAttribute()
    {
        switch ($this->tipo_dato) {
            case 'NUMBER':
                return is_numeric($this->valor_metadato) ? (float) $this->valor_metadato : $this->valor_metadato;
            case 'BOOLEAN':
                return filter_var($this->valor_metadato, FILTER_VALIDATE_BOOLEAN);
            case 'DATE':
                return $this->valor_metadato ? \Carbon\Carbon::parse($this->valor_metadato) : null;
            default:
                return $this->valor_metadato;
        }
    }
}
