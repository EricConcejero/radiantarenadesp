<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INICIO SESIÓN - RadiantArena</title>
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-container">
        <!-- Lado izquierdo - branding -->
        <div class="branding-side">
            <div class="logo-container">
                <img src="{{ asset('assets/img/logo.png') }}" alt="RadiantArena Logo">
            </div>
            <h1>RadiantArena</h1>
            <h2>¡TE ESPERAMOS!</h2>
            <div class="branding-description">
                Únete a la comunidad competitiva de Valorant en España.
                Participa en torneos, forma tu equipo y demuestra tu talento.
            </div>
        </div>

        <!-- Lado derecho - formulario -->
        <div class="form-side">
            <h3>INICIO SESIÓN</h3>

            @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Usuario</label>
                    <input type="text" name="username" id="username" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">INICIAR SESIÓN</button>

                <div class="form-footer">
                    <p>¿No tienes cuenta? <a href="{{ route('register') }}"><strong>Regístrate</strong></a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
