@extends('layouts.navbar')

@section('title', 'Editar Usuario')
@section('content')

<div class="edit-user-container">
    <h1>Editar Usuario</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('editar') }}" method="POST">
        @csrf
        <div>
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div>
            <label for="apellidos">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos" required>
        </div>
        <div>
            <label for="username">Nombre de usuario</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" required>
        </div>
        <div>
            <h3>Cambiar Contraseña</h3>
            <p >Deja estos campos en blanco si no quieres cambiar tu contraseña</p>

            <div>
                <label for="current_password">Contraseña Actual</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            <div>
                <label for="password">Nueva Contraseña</label>
                <input type="password" id="password" name="password">
            </div>
            <div>
                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation">
            </div>
        </div>
        <button type="submit">ACTUALIZAR</button>
    </form>
    <p>¿Quieres convertirte en jugador? <a href="{{route('showNewJugador')}}"><strong>Empieza la aventura</strong></a></p>
</div>
@endsection

<style>

    .edit-user-container {
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

h3 {
    margin: 20px 0 10px;
}

.form-group {
    margin-bottom: 15px;
}

form div {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #ffffff;
}

input {
    width: 100%;
    padding: 10px;
    border: 1px solid #444;
    border-radius: 5px;
    background-color: #2a2a2a;
    color: #ffffff;
    font-size: 1em;
    transition: border-color 0.3s;
}

input:focus {
    outline: none;
    border-color: #FE4454;
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
    transition: background-color 0.3s;
}

button:hover {
    background-color: #d32f3d;
}

p {
    color: #FE4454;
    text-align: center;
    margin-top: 20px;
}

p a {
    color: #FE4454;
    text-decoration: none;
    font-weight: bold;
}

p a:hover {
    text-decoration: underline;
}

/* Estilos para mensajes de error */
.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border: 1px solid #dc3545;
    color: #dc3545;
}

.alert-danger ul {
    margin: 0;
    padding-left: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .edit-user-container {
        margin: 20px;
        padding: 15px;
    }

    h1 {
        font-size: 1.5em;
    }

    input, button {
        padding: 10px;
    }
}

</style>
