<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Torneo extends Model
{
    use HasFactory;

    protected $table = 'torneos';
    protected $primaryKey = 'id_torneos'; // Ensure the correct primary key is set
    public $timestamps = false;
    protected $fillable = ['nombre_torneo', 'normas', 'ubicacion', 'fecha_inico', 'fecha_fin', 'id_administrador', 'imagen_torneo'];

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'id_administrador', 'id_administradores');
    }

    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'inscribirse', 'id_torneos', 'id_equipo')->withTimestamps();
    }
}

