<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    use HasFactory;

    protected $table = 'mensajes';
    protected $primaryKey = 'id_mensaje';
    protected $fillable = ['id_conversacion', 'id_usuario', 'contenido', 'leido'];

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'id_conversacion');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
