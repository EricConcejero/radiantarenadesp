<template>
    <div class="profile-container">
        <div v-if="loading" class="loading">
            <div class="spinner"></div>
            <p>Cargando perfil...</p>
        </div>
        <div v-else-if="error" class="error-message">{{ error }}</div>
        <div v-else class="profile-content">
            <div class="profile-header">
                <div class="profile-image-container">
                    <img
                        :src="usuario.imagen_usuario ? `assets/usuarios/${usuario.imagen_usuario}` : 'assets/usuarios/image_default.png'"
                        alt="Foto de perfil"
                        class="profile-image"
                    />
                </div>
                <div class="profile-title">
                    <h1>{{ usuario.nombre }} {{ usuario.apellidos }}</h1>
                    <p class="username">@{{ usuario.username }}</p>
                    <span class="user-type" :class="userTypeClass">{{ userTypeLabel }}</span>
                </div>
            </div>

            <div class="profile-body">
                <div class="profile-section">
                    <h2>Información Personal</h2>
                    <div class="profile-info-grid">
                        <div class="info-item">
                            <span class="info-label">Nombre</span>
                            <span class="info-value">{{ usuario.nombre }} {{ usuario.apellidos }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Correo</span>
                            <span class="info-value">{{ usuario.correo }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Username</span>
                            <span class="info-value">{{ usuario.username }}</span>
                        </div>
                    </div>
                </div>

                <!-- Jugador section -->
                <div v-if="usuario.jugador" class="profile-section">
                    <h2>Información del Jugador</h2>
                    <div class="profile-info-grid">
                        <div class="info-item">
                            <span class="info-label">Rango Valorant</span>
                            <span class="info-value">{{ usuario.jugador.rango_valorant }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Nickname</span>
                            <span class="info-value">{{ usuario.jugador.nombre_jugador || usuario.username }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">TAG</span>
                            <span class="info-value">{{ usuario.jugador.tag_jugador || 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rol</span>
                            <span class="info-value">{{ getRolName(usuario.jugador.rol) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Player's Team section -->
                <div v-if="usuario.jugador && usuario.jugador.equipo" class="profile-section team-section">
                    <h2>Mi Equipo</h2>
                    <div class="team-card">
                        <img
                            :src="`assets/equipos/${usuario.jugador.equipo.logo}`"
                            alt="Logo del equipo"
                            class="team-logo"
                        />
                        <div class="team-info">
                            <h3>{{ usuario.jugador.equipo.nombre_equipo }}</h3>
                            <p class="team-tag">[{{ usuario.jugador.equipo.tag }}]</p>
                            <a :href="`equipos/${usuario.jugador.equipo.id_equipos}`" class="team-link">
                                Ver equipo
                            </a>
                        </div>
                    </div>
                </div>

                <div v-else-if="usuario.jugador && !usuario.jugador.equipo" class="profile-section team-section no-team">
                    <h2>Mi Equipo</h2>
                    <div class="no-team-message">
                        <i class="fas fa-users-slash"></i>
                        <p>No estás asociado a ningún equipo actualmente</p>
                        <a href="equipos" class="team-link no-team-link">
                            <i class="fas fa-search"></i> Buscar Equipos
                        </a>
                    </div>
                </div>

                <!-- Entrenador section -->
                <div v-if="usuario.entrenador" class="profile-section">
                    <h2>Información del Entrenador</h2>
                    <div class="profile-info-grid">
                        <div class="info-item">
                            <span class="info-label">Experiencia</span>
                            <span class="info-value">{{ usuario.entrenador.experiencia }} años</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Palmarés</span>
                            <span class="info-value">{{ usuario.entrenador.palmares }}</span>
                        </div>
                    </div>
                </div>

                <!-- Entrenador's Equipo section -->
                <div v-if="usuario.equipo" class="profile-section team-section">
                    <h2>Equipo</h2>
                    <div class="team-card">
                        <img
                            :src="`assets/equipos/${usuario.equipo.logo}`"
                            alt="Logo del equipo"
                            class="team-logo"
                        />
                        <div class="team-info">
                            <h3>{{ usuario.equipo.nombre_equipo }}</h3>
                            <p class="team-tag">[{{ usuario.equipo.tag }}]</p>
                            <a :href="`equipos/${usuario.equipo.id_equipos}`" class="team-link">
                                Ver equipo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-footer">
                <a :href="`edit-profile`" class="edit-btn">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'UserProfile',
    data() {
        return {
            usuario: {},
            loading: true,
            error: null,
            roles: {
                1: "Duelista",
                2: "Iniciador",
                3: "Centinela",
                4: "Controlador"
            }
        };
    },
    computed: {
        userTypeLabel() {
            const types = {
                'usuario': 'Usuario',
                'jugador': 'Jugador',
                'entrenador': 'Entrenador',
                'administrador': 'Administrador'
            };
            return types[this.usuario.tipo_usuario] || 'Usuario';
        },
        userTypeClass() {
            return `type-${this.usuario.tipo_usuario}`;
        }
    },
    methods: {
        fetchProfileData() {
            axios.get('/profile')
                .then(response => {
                    this.usuario = response.data;
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error al cargar datos del perfil:', error);
                    this.error = 'Error al cargar los datos del perfil. Por favor, intenta nuevamente.';
                    this.loading = false;
                });
        },
        getRolName(rolId) {
            return this.roles[rolId] || 'Desconocido';
        }
    },
    mounted() {
        this.fetchProfileData();
    }
};
</script>

<style scoped>
.profile-container {
    padding: 30px;
    background-color: #1E1E1E;
    color: #ffffff;
    border-radius: 12px;
    margin: 20px;
}

.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #444;
}

.profile-image-container {
    margin-right: 25px;
}

.profile-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #FE4454;
    box-shadow: 0 5px 15px rgba(254, 68, 84, 0.3);
}

.profile-title h1 {
    color: #ffffff;
    font-size: 1.8em;
    margin-bottom: 5px;
}

.username {
    color: #ccc;
    font-size: 1.1em;
    margin-bottom: 8px;
}

.user-type {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8em;
    font-weight: bold;
    margin-top: 5px;
}

.type-usuario {
    background-color: #666;
    color: white;
}

.type-jugador {
    background-color: #4CAF50;
    color: white;
}

.type-entrenador {
    background-color: #2196F3;
    color: white;
}

.type-administrador {
    background-color: #FE4454;
    color: white;
}

.profile-section {
    margin-bottom: 30px;
    background-color: #2a2a2a;
    padding: 20px;
    border-radius: 10px;
}

.profile-section h2 {
    color: #FE4454;
    font-size: 1.4em;
    margin-bottom: 15px;
    border-bottom: 1px solid #444;
    padding-bottom: 10px;
}

.profile-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    margin-bottom: 15px;
}

.info-label {
    display: block;
    color: #aaa;
    font-size: 0.9em;
    margin-bottom: 5px;
}

.info-value {
    font-size: 1.1em;
    color: #fff;
}

.team-section {
    text-align: center;
}

.team-card {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
    background-color: #333;
    border-radius: 10px;
    width: 250px;
}

.team-logo {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 15px;
}

.team-info h3 {
    color: #FE4454;
    margin-bottom: 5px;
}

.team-tag {
    color: #ccc;
    margin-bottom: 15px;
}

.team-link {
    display: inline-block;
    background-color: #FE4454;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s;
}

.team-link:hover {
    background-color: #d32f3d;
}

.no-team-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px;
    background-color: #333;
    border-radius: 10px;
    width: 250px;
    margin: 0 auto;
}

.no-team-message i {
    font-size: 3em;
    color: #666;
    margin-bottom: 15px;
}

.no-team-message p {
    color: #aaa;
    font-style: italic;
    margin-bottom: 20px;
}

.no-team-link {
    margin-top: 10px;
    background-color: #333;
    border: 1px solid #FE4454;
}

.no-team-link:hover {
    background-color: rgba(254, 68, 84, 0.2);
}

.profile-footer {
    margin-top: 30px;
    text-align: center;
}

.edit-btn {
    display: inline-block;
    background-color: #FE4454;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s;
}

.edit-btn:hover {
    background-color: #d32f3d;
}

.loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: #bbb;
}

.spinner {
    border: 4px solid rgba(255, 255, 255, 0.1);
    border-left-color: #FE4454;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-message {
    color: #FE4454;
    text-align: center;
    padding: 40px;
    font-size: 1.2rem;
    background-color: rgba(254, 68, 84, 0.1);
    border-radius: 8px;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }

    .profile-image-container {
        margin-right: 0;
        margin-bottom: 15px;
    }

    .profile-info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
