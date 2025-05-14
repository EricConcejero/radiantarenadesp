<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jugador extends Model
{
    use HasFactory;

    protected $table = 'jugadores';
    protected $primaryKey = 'id_jugador';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ['id_jugador', 'rango_valorant', 'kills', 'deaths', 'assists', 'palmares', 'nombre_jugador', 'tag_jugador' , 'id_entrenador', 'id_rol'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_jugador', 'id_usuario');
    }

    public function entrenador()
    {
        return $this->belongsTo(Entrenador::class, 'id_entrenador');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}

