<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class UsuarioSistema extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens;

    /**
     * Tabla específica en schema personal
     */
    protected $table = 'personal.usuarios_sistema';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'id';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'perfil_militar_id',
        'username',
        'email_institucional',
        'password_hash',
        'ultimo_acceso',
        'intentos_fallidos',
        'cuenta_bloqueada',
        'fecha_bloqueo',
        'motivo_bloqueo',
        'requiere_cambio_password',
        'fecha_cambio_password',
        'token_recuperacion',
        'fecha_expiracion_token',
        'configuraciones_usuario',
        'is_active'
    ];

    /**
     * Campos ocultos en respuestas JSON
     */
    protected $hidden = [
        'password_hash',
        'token_recuperacion'
    ];

    /**
     * Campos que deben ser tratados como fechas
     */
    protected $dates = [
        'ultimo_acceso',
        'fecha_bloqueo',
        'fecha_cambio_password',
        'fecha_expiracion_token',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Conversión automática de tipos de datos
     */
    protected $casts = [
        'configuraciones_usuario' => 'array',
        'cuenta_bloqueada' => 'boolean',
        'requiere_cambio_password' => 'boolean',
        'is_active' => 'boolean',
        'intentos_fallidos' => 'integer'
    ];

    /**
     * Campo para eliminación suave
     */
    protected $deleteAtColumn = 'deleted_at';

    /**
     * Relación con PerfilMilitar (1:1)
     * Un usuario pertenece a un perfil militar
     */
    public function perfilMilitar()
    {
        return $this->belongsTo(PerfilMilitar::class, 'perfil_militar_id', 'id');
    }

    /**
     * Obtener información completa del militar
     */
    public function obtenerInfoCompleta()
    {
        return $this->with([
            'perfilMilitar.datosPersonales',
            'perfilMilitar.gradoActual',
            'perfilMilitar.especialidad',
            'perfilMilitar.asignacionActual.estructuraMilitar'
        ])->find($this->id);
    }

    /**
     * Verificar si el usuario puede acceder al sistema
     */
    public function puedeAcceder()
    {
        return $this->is_active && !$this->cuenta_bloqueada;
    }

    /**
     * Incrementar intentos fallidos de login
     */
    public function incrementarIntentosFallidos()
    {
        $this->intentos_fallidos++;

        // Bloquear después de 3 intentos
        if ($this->intentos_fallidos >= 3) {
            $this->cuenta_bloqueada = true;
            $this->fecha_bloqueo = now();
            $this->motivo_bloqueo = 'Máximo de intentos fallidos alcanzado';
        }

        $this->save();
    }

    /**
     * Resetear contador de intentos fallidos
     */
    public function resetearIntentosFallidos()
    {
        $this->intentos_fallidos = 0;
        $this->save();
    }
}
