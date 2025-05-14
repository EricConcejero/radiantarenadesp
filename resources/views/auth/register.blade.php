<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REGISTRO - RadiantArena</title>
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
    <link href="{{ asset('css/register.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="register-container">
        <!-- Lado izquierdo - branding -->
        <div class="branding-side">
            <div class="logo-container">
                <img src="{{ asset('assets/img/logo.png') }}" alt="RadiantArena Logo">
            </div>
            <h1>RadiantArena</h1>
            <h2>ÚNETE A MILES DE JUGADORES</h2>
            <div class="branding-description">
                Crea tu cuenta y sé parte de nuestra comunidad de Valorant. Compite en torneos, forma equipos y demuestra tus habilidades.
            </div>
        </div>

        <!-- Lado derecho - formulario -->
        <div class="form-side">
            <h3>REGISTRARSE</h3>

            @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre"><i class="fas fa-user"></i> Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="apellidos"><i class="fas fa-user"></i> Apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="username"><i class="fas fa-at"></i> Nombre de usuario</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="correo"><i class="fas fa-envelope"></i> Correo electrónico</label>
                        <input type="email" id="correo" name="correo" value="{{ old('correo') }}" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="imagenUsuario"><i class="fas fa-camera"></i> Foto de perfil</label>
                        <div class="custom-file-upload" id="drop-area">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Arrastra y suelta tu imagen o haz clic aquí</span>
                            <input type="file" id="imagenUsuario" name="imagenUsuario" accept="image/*" style="display: none;">
                        </div>
                        <div class="preview-area" id="preview-container">
                            <img id="preview-image" src="#" alt="Vista previa">
                        </div>
                    </div>
                </div>

                <button type="submit">CREAR CUENTA</button>

                <div class="form-footer">
                    <p>¿Ya tienes cuenta? <a href="{{ route('login') }}"><strong>Iniciar Sesión</strong></a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Script para la previsualización de la imagen
        document.addEventListener('DOMContentLoaded', function() {
            const dropArea = document.getElementById('drop-area');
            const fileInput = document.getElementById('imagenUsuario');
            const previewContainer = document.getElementById('preview-container');
            const previewImage = document.getElementById('preview-image');

            // Abre el selector de archivos al hacer clic
            dropArea.addEventListener('click', () => fileInput.click());

            // Maneja el cambio de archivo
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewContainer.style.display = 'block';
                        dropArea.style.height = '60px';
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Previene el comportamiento predeterminado de arrastrar y soltar
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Resalta el área cuando se arrastra un archivo
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropArea.classList.add('highlight');
            }

            function unhighlight() {
                dropArea.classList.remove('highlight');
            }

            // Maneja el evento de soltar
            dropArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;

                if (files && files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewContainer.style.display = 'block';
                        dropArea.style.height = '60px';
                    }
                    reader.readAsDataURL(files[0]);
                }
            }
        });
    </script>
</body>
</html>
