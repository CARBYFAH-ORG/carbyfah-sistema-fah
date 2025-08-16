<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class UsuarioSistema extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal.usuarios_sistema';
    protected $primaryKey = 'id';

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
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
        'version'
    ];

    protected $hidden = [
        'password_hash',
        'token_recuperacion',
        'deleted_at',
        'deleted_by'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'ultimo_acceso',
        'fecha_bloqueo',
        'fecha_cambio_password',
        'fecha_expiracion_token'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cuenta_bloqueada' => 'boolean',
        'requiere_cambio_password' => 'boolean',
        'intentos_fallidos' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'perfil_militar_id' => 'integer',
        'configuraciones_usuario' => 'array',
        'ultimo_acceso' => 'datetime',
        'fecha_bloqueo' => 'datetime',
        'fecha_cambio_password' => 'datetime',
        'fecha_expiracion_token' => 'datetime'
    ];

    // relaciones

    // relacion uno a uno con perfil militar
    public function perfilMilitar()
    {
        return $this->belongsTo(PerfilMilitar::class, 'perfil_militar_id', 'id');
    }

    // scopes para consultas

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBloqueados($query)
    {
        return $query->where('cuenta_bloqueada', true);
    }

    public function scopeDesbloqueados($query)
    {
        return $query->where('cuenta_bloqueada', false);
    }

    public function scopeRequierenCambioPassword($query)
    {
        return $query->where('requiere_cambio_password', true);
    }

    public function scopePorUsername($query, $username)
    {
        return $query->where('username', $username);
    }

    public function scopePorEmail($query, $email)
    {
        return $query->where('email_institucional', $email);
    }

    public function scopeConAccesoReciente($query, $dias = 30)
    {
        return $query->where('ultimo_acceso', '>=', now()->subDays($dias));
    }

    public function scopeSinAccesoReciente($query, $dias = 90)
    {
        return $query->where(function ($q) use ($dias) {
            $q->whereNull('ultimo_acceso')
                ->orWhere('ultimo_acceso', '<', now()->subDays($dias));
        });
    }

    public function scopeConTokenValido($query)
    {
        return $query->whereNotNull('token_recuperacion')
            ->where('fecha_expiracion_token', '>', now());
    }

    // accessors para atributos calculados

    public function getNombreCompletoAttribute()
    {
        return $this->perfilMilitar?->datosPersonales?->nombre_completo;
    }

    public function getGradoCompletoAttribute()
    {
        return $this->perfilMilitar?->nombre_completo_militar;
    }

    public function getUnidadActualAttribute()
    {
        return $this->perfilMilitar?->asignacionActual?->unidad_nombre;
    }

    public function getCargoActualAttribute()
    {
        return $this->perfilMilitar?->asignacionActual?->cargo_nombre;
    }

    public function getDiasUltimoAccesoAttribute()
    {
        return $this->ultimo_acceso ?
            now()->diffInDays($this->ultimo_acceso) : null;
    }

    public function getEstadoCuentaAttribute()
    {
        if (!$this->is_active) {
            return 'INACTIVA';
        }

        if ($this->cuenta_bloqueada) {
            return 'BLOQUEADA';
        }

        if ($this->requiere_cambio_password) {
            return 'REQUIERE_CAMBIO_PASSWORD';
        }

        return 'ACTIVA';
    }

    public function getPuedeAccederAttribute()
    {
        return $this->is_active &&
            !$this->cuenta_bloqueada &&
            $this->perfilMilitar?->is_active;
    }

    public function getTieneTokenValidoAttribute()
    {
        return $this->token_recuperacion &&
            $this->fecha_expiracion_token &&
            $this->fecha_expiracion_token > now();
    }

    // mutators para transformar datos

    public function setPasswordHashAttribute($value)
    {
        $this->attributes['password_hash'] = Hash::make($value);
        $this->attributes['fecha_cambio_password'] = now();
        $this->attributes['requiere_cambio_password'] = false;
    }

    // metodos personalizados

    public function verificarPassword($password)
    {
        return Hash::check($password, $this->password_hash);
    }

    public function registrarAcceso()
    {
        $this->ultimo_acceso = now();
        $this->intentos_fallidos = 0;
        return $this->save();
    }

    public function registrarIntentoFallido()
    {
        $this->intentos_fallidos++;

        // bloquear despues de 5 intentos fallidos
        if ($this->intentos_fallidos >= 5) {
            $this->bloquearCuenta('Máximo de intentos fallidos excedido');
        }

        return $this->save();
    }

    public function bloquearCuenta($motivo = null)
    {
        $this->cuenta_bloqueada = true;
        $this->fecha_bloqueo = now();
        $this->motivo_bloqueo = $motivo;
        return $this->save();
    }

    public function desbloquearCuenta()
    {
        $this->cuenta_bloqueada = false;
        $this->fecha_bloqueo = null;
        $this->motivo_bloqueo = null;
        $this->intentos_fallidos = 0;
        return $this->save();
    }

    public function generarTokenRecuperacion($horasValidez = 24)
    {
        $this->token_recuperacion = bin2hex(random_bytes(32));
        $this->fecha_expiracion_token = now()->addHours($horasValidez);
        return $this->save();
    }

    public function limpiarTokenRecuperacion()
    {
        $this->token_recuperacion = null;
        $this->fecha_expiracion_token = null;
        return $this->save();
    }

    public function cambiarPassword($nuevaPassword, $requiereCambio = false)
    {
        $this->password_hash = $nuevaPassword; // usa el mutator
        $this->requiere_cambio_password = $requiereCambio;
        $this->limpiarTokenRecuperacion();
        return $this->save();
    }

    public function actualizarConfiguracion($clave, $valor)
    {
        $configuraciones = $this->configuraciones_usuario ?? [];
        $configuraciones[$clave] = $valor;
        $this->configuraciones_usuario = $configuraciones;
        return $this->save();
    }

    public function obtenerConfiguracion($clave, $valorPorDefecto = null)
    {
        return $this->configuraciones_usuario[$clave] ?? $valorPorDefecto;
    }

    public function esPasswordTemporal()
    {
        // password temporal si requiere cambio y fue creado hace menos de 24 horas
        return $this->requiere_cambio_password &&
            $this->fecha_cambio_password &&
            $this->fecha_cambio_password->diffInHours(now()) < 24;
    }

    public function tiempoSinAcceso()
    {
        if (!$this->ultimo_acceso) {
            return 'Nunca ha accedido';
        }

        $dias = $this->dias_ultimo_acceso;

        if ($dias == 0) {
            return 'Hoy';
        } elseif ($dias == 1) {
            return 'Ayer';
        } elseif ($dias < 30) {
            return "Hace {$dias} días";
        } elseif ($dias < 365) {
            $meses = intval($dias / 30);
            return "Hace {$meses} " . ($meses == 1 ? 'mes' : 'meses');
        } else {
            $anos = intval($dias / 365);
            return "Hace {$anos} " . ($anos == 1 ? 'año' : 'años');
        }
    }

    public function validarIntegridadCuenta()
    {
        $errores = [];

        if (!$this->perfilMilitar) {
            $errores[] = 'Usuario sin perfil militar asociado';
        }

        if (!$this->perfilMilitar?->is_active) {
            $errores[] = 'Perfil militar inactivo';
        }

        if ($this->cuenta_bloqueada && !$this->motivo_bloqueo) {
            $errores[] = 'Cuenta bloqueada sin motivo especificado';
        }

        return $errores;
    }
}
