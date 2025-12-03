<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    // Nombre real de tu tabla
    protected $table = 'usuarios';

    // Si NO tienes created_at / updated_at en la tabla:
    public $timestamps = true;

    // Clave primaria
    protected $primaryKey = 'id';

    // Campos asignables en masa
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'correo',
        'contrasena',
        'rol',
        'estado',// 'Administrador' | 'Vendedor'
    ];

    // Campos ocultos (no se devuelven en JSON)
    protected $hidden = [
        'contrasena',
        'remember_token', // quítalo si no tienes esta columna
    ];

    // Si NO tienes remember_token en la tabla, desactívalo:
    public function getRememberTokenName()
    {
        return null; // evita que Eloquent intente usar remember_token
    }

    // Laravel espera un "password". Dile cuál es tu campo real:
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    // Hashea automáticamente al asignar contrasena
    public function setContrasenaAttribute($value)
    {
        // Evita doble hash si ya viene hasheada
        $this->attributes['contrasena'] = strlen($value) === 60 && preg_match('/^\$2y\$/', $value)
            ? $value
            : Hash::make($value);
    }

    // (Opcional) Nombre completo como accesor
    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}
