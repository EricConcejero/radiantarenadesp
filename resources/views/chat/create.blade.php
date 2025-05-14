@extends('layouts.navbar')

@section('content')
<div class="container">
    <div class="chat-create-container">
        <h1>Nueva Conversación</h1>

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('chat.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="usuarios">Seleccionar Usuarios</label>
                <div class="users-container">
                    @foreach($usuarios as $usuario)
                        <div class="user-item">
                            <input type="checkbox" name="usuarios[]" id="usuario_{{ $usuario->id_usuario }}" value="{{ $usuario->id_usuario }}"
                                @if(request()->has('usuarios') && in_array($usuario->id_usuario, request('usuarios'))) checked @endif>
                            <label for="usuario_{{ $usuario->id_usuario }}" class="user-label">
                                <img src="{{ asset('assets/usuarios/' . ($usuario->imagen_usuario ?? 'image_default.png')) }}" alt="{{ $usuario->username }}" class="user-avatar">
                                <span class="user-name">{{ $usuario->username }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label for="titulo">Título de la conversación (opcional)</label>
                <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Título de la conversación">
            </div>

            <div class="form-group">
                <label for="mensaje">Primer mensaje</label>
                <textarea id="mensaje" name="mensaje" class="form-control" rows="4" required placeholder="Escribe tu primer mensaje...">{{ request('mensaje', 'Hola! Me gustaría hablar contigo.') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Crear Conversación</button>
                <a href="{{ route('chat.index') }}" class="btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .chat-create-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 25px;
        background-color: #2a2a2a;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        color: #ffffff;
    }

    h1 {
        color: #FE4454;
        margin-bottom: 25px;
        font-size: 1.8rem;
        border-bottom: 1px solid #444;
        padding-bottom: 15px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    label {
        display: block;
        margin-bottom: 10px;
        color: #ddd;
        font-weight: 600;
    }

    .users-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        max-height: 300px;
        overflow-y: auto;
        padding: 10px;
        background-color: #333;
        border-radius: 8px;
    }

    .user-item {
        display: flex;
        align-items: center;
    }

    .user-item input[type="checkbox"] {
        display: none;
    }

    .user-label {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background-color: #444;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .user-item input[type="checkbox"]:checked + .user-label {
        background-color: rgba(254, 68, 84, 0.3);
        border: 1px solid #FE4454;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
        border: 2px solid #555;
    }

    .user-name {
        color: #fff;
        font-size: 0.9rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        background-color: #333;
        border: 1px solid #444;
        border-radius: 6px;
        color: #fff;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #FE4454;
        box-shadow: 0 0 0 2px rgba(254, 68, 84, 0.2);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-submit, .btn-cancel {
        padding: 12px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-submit {
        background-color: #FE4454;
        color: white;
    }

    .btn-submit:hover {
        background-color: #e53545;
        transform: translateY(-2px);
    }

    .btn-cancel {
        background-color: #444;
        color: #ddd;
    }

    .btn-cancel:hover {
        background-color: #555;
    }

    .alert-danger {
        background-color: rgba(244, 67, 54, 0.2);
        border: 1px solid #f44336;
        color: #f44336;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
</style>
@endpush
