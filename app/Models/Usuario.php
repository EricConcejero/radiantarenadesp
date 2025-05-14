<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'apellidos',
        'username',
        'correo',
        'password', // Asegúrate de que sea 'password' y no 'contrasenya'
        'tipo_usuario',
        'imagen_usuario',


    ];

    public function jugador()
    {
        return $this->hasOne(Jugador::class, 'id_jugador', 'id_usuario');
    }

    public function entrenador()
    {
        return $this->hasOne(Entrenador::class, 'id_entrenador', 'id_usuario');
    }

    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'id_administradores', 'id_usuario');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_entrenador', 'id_usuario');
    }

    public function equipo()
    {
        return $this->hasOneThrough(Equipo::class, Entrenador::class, 'id_entrenador', 'id_equipos', 'id_usuario', 'id_equipos');
    }

    public function conversaciones()
    {
        return $this->belongsToMany(Conversacion::class, 'conversacion_usuarios', 'id_usuario', 'id_conversacion')
                    ->withTimestamps();
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'id_usuario');
    }

    // Este método es importante para Auth
    public function getAuthPassword()
    {
        return $this->password;
    }
}

