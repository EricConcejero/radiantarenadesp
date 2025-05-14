<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversacionUsuario extends Model
{
    use HasFactory;

    protected $table = 'conversacion_usuarios';
    protected $primaryKey = 'id_conversacion_usuario';
    protected $fillable = ['id_conversacion', 'id_usuario'];

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'id_conversacion');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
