@extends('layouts.navbar')

@section('title', 'Crear Equipo')

@section('content')
<div class="create-team-container">
    <h1>Crear Nuevo Equipo</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <form action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="nombre_equipo">Nombre del Equipo</label>
            <input type="text" id="nombre_equipo" name="nombre_equipo" value="{{ old('nombre_equipo') }}" required>
        </div>

        <div class="form-group">
            <label for="logo">Logo del Equipo</label>
            <input type="file" id="logo" name="logo" required>
        </div>

        <div class="form-group">
            <label for="tag">Tag (Abreviatura)</label>
            <input type="text" id="tag" name="tag" value="{{ old('tag') }}" required>
        </div>

        <div class="form-group">
            <label for="region">Región</label>
            <input type="text" id="region" name="region" value="{{ old('region') }}" required>
        </div>

        <div class="form-group">
            <label for="palmares">Experiencia (Años)</label>
            <input type="number" id="palmares" name="palmares" value="{{ old('palmares') }}" required>
        </div>

        <button type="submit" class="btn-submit">Crear Equipo</button>
    </form>
</div>
@endsection

<style>
.create-team-container {
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

input[type="text"],
input[type="number"],
input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.btn-submit {
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

.btn-submit:hover {
    background-color: #45a049;
}

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
</style>
