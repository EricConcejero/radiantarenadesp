<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';
    protected $primaryKey = 'id_equipos';
    public $timestamps = false;
    protected $fillable = ['nombre_equipo', 'logo', 'tag', 'region', 'palmares'];

    public function entrenador()
{
    return $this->hasOne(Entrenador::class, 'id_equipos', 'id_equipos');
}

    public function partidas()
    {
        return $this->hasMany(Partida::class, 'id_equipos', 'id_equipos');
    }
}
