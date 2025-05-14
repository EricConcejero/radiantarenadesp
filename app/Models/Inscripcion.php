<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscribirse'; // Correct table name
    public $timestamps = false;

    protected $fillable = ['id_equipo', 'id_torneos', 'fecha_inscripcion']; // Ensure all required fields are listed
}

