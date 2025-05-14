@extends('layouts.navbar')

@section('title', 'Jugador Nuevo')
@section('content')

<div>
    <div class="create-player-container">
    <h1>Crear Jugador</h1>

    <form action="{{ route('newJugador') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="rango">Rango Valorant</label>
            <input type="text" id="rango" name="rango" required>
        </div>
        <div class="form-group">
            <label for="palmares">Palmares</label>
            <input type="text" id="palmares" name="palmares" required>
        </div>
        <div class="form-group">
            <label for="nombreJugador">Nombre Jugador</label>
            <input type="text" id="nombreJugador" name="nombreJugador" required>
        </div>
        <div class="form-group">
            <label for="tagJugador">Tag Juagdor</label>
            <input type="text" id="tagJugador" name="tagJugador" required>
        </div>
        <div class="form-group">
            <label for="rol">Rol</label>
            <select id="rol" name="rol" required>
                <option value="">Selecciona un rol</option>
                <option value="1">Duelista</option>
                <option value="2">Iniciador</option>
                <option value="3">Centinela</option>
                <option value="4">Controlador</option>
            </select>
        </div>

        <button type="submit">CREAR JUGADOR</button>

    </form>
</div>
@endsection

<style>
.create-player-container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #28292A;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

input, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}



button {
    display: block;
    width: 100%;
    padding: 12px;
    background-color: #FE4454;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    margin-top: 20px;
}

button:hover {
    background-color: #45a049;
}

/* Estilos para mensajes de error */
.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        margin: 20px;
        padding: 15px;
    }

    h1 {
        font-size: 1.5em;
    }

    input, select, button {
        padding: 10px;
    }
}
</style>

