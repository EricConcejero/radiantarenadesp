<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partida extends Model
{
    use HasFactory;

    protected $table = 'partidas';
    protected $primaryKey = 'id_partidas';
    public $timestamps = false;

    protected $fillable = [
        'equipo1_id',
        'equipo2_id',
        'ganador_id',
        'ronda',
        'torneo_id',
        'estado'     // Add this line
    ];

    // relacion con el primer equipo
    public function equipo1()
    {
        return $this->belongsTo(Equipo::class, 'equipo1_id', 'id_equipos');
    }

    // relacion con el segundo equipo
    public function equipo2()
    {
        return $this->belongsTo(Equipo::class, 'equipo2_id', 'id_equipos');
    }

    // relacion con el ganador
    public function ganador()
    {
        return $this->belongsTo(Equipo::class, 'ganador_id', 'id_equipos');
    }
}

