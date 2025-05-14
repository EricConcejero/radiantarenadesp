<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
    <link href="{{ asset('css/nav.css') }}"  rel="stylesheet" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- CSRF Token for API requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Explicitly add user ID for Vue components -->
    @auth
        <meta name="user-id" content="{{ Auth::id() }}">
    @endauth

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo">
        </div>

        <!-- menu de movil -->
        <div class="hamburger-menu" id="hamburgerMenu">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Desktop Navigation -->
        <ul class="nav-links desktop-nav">
            <li><a href="{{ route('landing')}}">INICIO</a></li>
            <li><a href="{{ route('torneos')}}">TORNEOS</a></li>
            <li><a href="{{ route('equipos')}}">EQUIPOS</a></li>
            <li><a href="{{ route('jugadores')}}">JUGADORES</a></li>
        </ul>

        <!-- Mobile Navigation (Hidden by Default) -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-header">
                <div class="close-menu" id="closeMenu">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            <ul class="mobile-nav-links">
                <li><a href="#">INICIO</a></li>
                <li><a href="{{ route('torneos')}}">TORNEOS</a></li>
                <li><a href="{{ route('equipos')}}">EQUIPO</a></li>
                <li><a href="{{ route('jugadores')}}">JUGADORES</a></li>
                @if(Auth::check())
                    <li class="mobile-user-info">
                        <span>Hola, {{ Auth::user()->username }}</span>
                        <a href="{{ route('showEdit') }}"><i class="fas fa-edit"></i> Editar Usuario</a>
                        <a href="{{ route('equipos.create') }}"><i class="fas fa-plus"></i> Crear Equipo</a>
                        <a href="{{ route('showNewJugador') }}"><i class="fas fa-gamepad"></i> Sé Jugador</a>
                        <a href="{{ route('logout') }}"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </li>
                @else
                    <li><a href="{{ route('login') }}" class="mobile-btn-login">INICIA SESIÓN</a></li>
                @endif
            </ul>
        </div>

        <div class="login-button">
            @if(Auth::check())
                <div class="user-info">
                    <span>Hola, {{ Auth::user()->username }}</span>
                    <div class="user-dropdown">
                            @php
                                $fotoPerfil = DB::table('usuarios')->where('id_usuario', Auth::user()->id_usuario)->value('imagen_usuario');
                            @endphp

                            @if ($fotoPerfil && $fotoPerfil !== 'null')
                                <img src="{{ asset('assets/usuarios/' . $fotoPerfil) }}" alt="Perfil" class="profile-pic" id="profileDropdown">
                            @else
                            <img src="{{ asset('assets/usuarios/image_default.png')}}" alt="Perfil" class="profile-pic" id="profileDropdown">
                            @endif
                        <div class="dropdown-content" id="userDropdown">
                            <a href="{{ route('profile') }}"><i class="fas fa-user"></i> Ver Perfil</a>
                            <a href="{{ route('showNewJugador') }}"><i class="fas fa-gamepad"></i> Sé Jugador</a>
                            <a href="{{ route('equipos.create') }}"><i class="fas fa-plus"></i> Crear Equipo</a>
                            <a href="{{ route('showEdit') }}"><i class="fas fa-edit"></i> Editar Usuario</a>
                            <a href="{{ route('logout') }}"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn-login">INICIA SESIÓN</a>
            @endif
        </div>
    </nav>

    <!-- Single Vue mounting point with proper ID -->
    <div id="app">
        <main class="py-4">
            @yield('content')
        </main>

        <!-- Add the chat button component for authenticated users -->
        @auth
            <chat-button></chat-button>
        @endauth
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // script for dropdown menu
        const dropdown = document.getElementById('profileDropdown');
        const dropdownMenu = document.getElementById('userDropdown');

        if (dropdown) {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (dropdownMenu.classList.contains('show') && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }

        // script for hamburger menu
        const hamburger = document.getElementById('hamburgerMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        const closeMenu = document.getElementById('closeMenu');

        if (hamburger && mobileMenu) {
            hamburger.addEventListener('click', function() {
                mobileMenu.style.display = 'block';
                setTimeout(() => {
                    mobileMenu.classList.add('active');
                }, 10);
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            });

            closeMenu.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                setTimeout(() => {
                    mobileMenu.style.display = 'none';
                }, 300);
                document.body.style.overflow = ''; // Enable scrolling
            });
        }
    });
    </script>
</body>
</html>
