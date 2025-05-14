<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    use HasFactory;

    protected $table = 'conversaciones';
    protected $primaryKey = 'id_conversacion';
    protected $fillable = ['titulo'];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'conversacion_usuarios', 'id_conversacion', 'id_usuario')
                    ->withTimestamps();
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'id_conversacion');
    }

    public function ultimoMensaje()
    {
        return $this->hasOne(Mensaje::class, 'id_conversacion')->latest();
    }
}
