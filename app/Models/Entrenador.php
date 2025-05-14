<?php

namespace App\Models;

use App\Models\Equipo;
use App\Models\Jugador;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entrenador extends Model
{
    use HasFactory;

    protected $table = 'entrenadores';
    protected $primaryKey = 'id_entrenador';
    public $timestamps = false;
    protected $fillable = [
        'id_equipos',
        'id_entrenador'
    ];


    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipos', 'id_equipos');
    }

    public function jugadores()
    {
        return $this->hasMany(Jugador::class, 'id_entrenador');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_entrenador', 'id_usuario');
    }
}

