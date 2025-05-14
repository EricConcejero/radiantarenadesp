<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bracket extends Model
{
    use HasFactory;

    protected $table = 'brackets';
    public $timestamps = false;

    protected $fillable = ['id_partida', 'id_posicion', 'id_torneo'];
}

