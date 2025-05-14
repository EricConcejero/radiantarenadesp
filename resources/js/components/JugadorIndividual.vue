<template>
    <div class="jugador-detalle-container">
        <!-- Header con información básica del jugador -->
        <div v-if="jugador" class="jugador-header">
            <div class="jugador-banner">
                <div class="jugador-imagen-container">
                    <img :src="getImageUrl(jugador.usuario?.imagen_usuario)"
                         alt="Imagen del Jugador"
                         @error="handleImageError"
                         class="jugador-imagen" />
                </div>
                <div class="jugador-info-basica">
                    <h1>{{ jugador.usuario?.username || 'Sin nombre' }}</h1>
                    <div class="jugador-badges">
                        <span class="badge rango-badge" :style="{ backgroundColor: getRangoColor(jugador.rango_valorant) }">
                            {{ jugador.rango_valorant }}
                        </span>
                        <span class="badge rol-badge">{{ roles[jugador.id_rol] }}</span>
                        <span class="badge kda-badge">KDA: {{ calcularKDA(jugador) }}</span>
                        <span v-if="jugador.id_entrenador" class="badge team-badge">
                            <i class="fas fa-users"></i> En equipo
                        </span>
                        <span v-else class="badge free-badge">
                            <i class="fas fa-user"></i> Free agent
                        </span>
                    </div>
                    <p v-if="jugador.usuario" class="jugador-nombre-real">
                        {{ jugador.usuario.nombre }} {{ jugador.usuario.apellidos }}
                    </p>

                    <!-- Removed the general "Enviar Mensaje" button from here -->
                </div>
            </div>
        </div>

        <div v-if="jugador" class="jugador-contenido">
            <!-- Panel de estadísticas -->
            <div class="panel panel-stats">
                <h2 class="panel-title">Estadísticas</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value kills-color">{{ jugador.kills }}</div>
                        <div class="stat-label">Kills</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value deaths-color">{{ jugador.deaths }}</div>
                        <div class="stat-label">Deaths</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value assists-color">{{ jugador.assists }}</div>
                        <div class="stat-label">Assists</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value kda-color">{{ calcularKDA(jugador) }}</div>
                        <div class="stat-label">KDA</div>
                    </div>
                </div>
            </div>

            <!-- Panel de información adicional -->
            <div class="panel panel-info">
                <h2 class="panel-title">Información</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Rango:</div>
                        <div class="info-value">{{ jugador.rango_valorant }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Rol:</div>
                        <div class="info-value">{{ roles[jugador.id_rol] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Palmarés:</div>
                        <div class="info-value">{{ jugador.palmares || 'No disponible' }}</div>
                    </div>
                    <div class="info-item" v-if="equipo">
                        <div class="info-label">Equipo:</div>
                        <div class="info-value">
                            <a :href="`/equipos/${equipoId}`" class="equipo-link">{{ equipo.nombre_equipo }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de contacto y acciones - solo visible para entrenadores -->
            <div v-if="usuario && usuario.tipo_usuario === 'entrenador' && !jugador.id_entrenador" class="panel panel-actions">
                <h2 class="panel-title">Acciones</h2>
                <div class="actions-container">
                    <button @click="mostrarConfirmacion" class="action-btn contratar-btn">
                        <i class="fas fa-user-plus"></i> Agregar al Equipo
                    </button>
                    <button @click="mostrarModalMensaje" class="action-btn xat-btn">
                        <i class="fas fa-comments"></i> Enviar Mensaje
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading state -->
        <div v-else-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Cargando información del jugador...</p>
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>{{ error }}</p>
            <a :href="getBaseUrl() + 'jugadores'" class="btn-volver">Volver a jugadores</a>
        </div>

        <!-- Modal de confirmación -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal-content">
                <h3>Confirmar Contratación</h3>
                <p>¿Estás seguro de que deseas agregar a {{ jugador?.usuario?.nombre_jugador || jugador?.usuario?.username || 'este jugador' }} a tu equipo?</p>
                <div class="modal-buttons">
                    <button @click="confirmarContratacion" class="btn-confirm">Confirmar</button>
                    <button @click="showModal = false" class="btn-cancel">Cancelar</button>
                </div>
            </div>
        </div>

        <!-- Modal para enviar mensaje -->
        <div v-if="showModalMensaje" class="modal-overlay">
            <div class="modal-content mensaje-modal">
                <h3>Enviar mensaje a {{ jugador?.usuario?.nombre_jugador || jugador?.usuario?.username }}</h3>
                <textarea v-model="mensajeTexto" placeholder="Escribe tu mensaje aquí..." class="mensaje-textarea"></textarea>
                <div class="modal-buttons">
                    <button @click="enviarMensaje" class="btn-confirm">Enviar</button>
                    <button @click="showModalMensaje = false" class="btn-cancel">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'JugadorIndividual',
    props: {
        id: {
            type: [String, Number],
            required: true
        },
        usuario: {
            type: Object,
            required: false,
            default: null
        }
    },
    data() {
        return {
            jugador: null,
            equipo: null,
            equipoId: null,
            loading: true,
            error: null,
            showModal: false,
            showModalMensaje: false,
            mensajeTexto: '',
            roles: {
                1: "Duelista",
                2: "Iniciador",
                3: "Centinela",
                4: "Controlador"
            },
            // Add rank color mapping based on the Valorant rank colors in the image
            rangosColores: {
                'Hierro': '#5a5a5a',    // Iron - gray
                'Bronce': '#b97551',    // Bronze - bronze/brown
                'Plata': '#c0c0c0',     // Silver - silver
                'Oro': '#ffb74d',       // Gold - gold
                'Platino': '#36b3b3',   // Platinum - teal
                'Diamante': '#a183e0',  // Diamond - purple
                'Ascendente': '#21de9a', // Ascendant - green
                'Inmortal': '#ff4654',  // Immortal - red
                'Radiant': '#FFD700',  // Radiant - brighter gold color
            },
            baseUrl: '',
            imagenPorDefecto: 'image_default.png'
        };
    },
    methods: {
        fetchJugador() {
            this.loading = true;

            // Directly use the URL in the axios.get() call without a separate variable
            if (window.location.pathname.includes('/public/')) {
                // If we're in the public path, construct and use URL directly
                const basePathSegments = window.location.pathname.split('/public/')[0];
                const fullUrl = `${window.location.origin}${basePathSegments}/public/MercadoJugadores/${this.id}`;
                console.log('Fetching player data from:', fullUrl);

                axios.get(fullUrl)
                    .then(response => {
                        console.log('Player data received:', response.data);
                        this.jugador = response.data;

                        // Si el jugador tiene entrenador, buscar información del equipo
                        if (this.jugador.id_entrenador) {
                            this.fetchEquipo(this.jugador.id_entrenador);
                        }

                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error al obtener datos del jugador:', error);
                        if (error.response) {
                            console.error('Error details:', error.response.data);
                            console.error('Status:', error.response.status);
                        }
                        this.error = 'No se pudo cargar la información del jugador';
                        this.loading = false;
                    });
            } else {
                // If not in public path, use simple path directly in axios.get()
                console.log('Fetching player data from: /MercadoJugadores/' + this.id);

                axios.get(`api/MercadoJugadores/${this.id}`)
                    .then(response => {
                        console.log('Player data received:', response.data);
                        this.jugador = response.data;

                        // Si el jugador tiene entrenador, buscar información del equipo
                        if (this.jugador.id_entrenador) {
                            this.fetchEquipo(this.jugador.id_entrenador);
                        }

                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error al obtener datos del jugador:', error);
                        if (error.response) {
                            console.error('Error details:', error.response.data);
                            console.error('Status:', error.response.status);
                        }
                        this.error = 'No se pudo cargar la información del jugador';
                        this.loading = false;
                    });
            }
        },
        fetchEquipo(entrenadorId) {
            // Fix the URL to match the API route format properly
            const url = `${this.baseUrl}api/entrenador/${entrenadorId}/equipo`;
            console.log('Fetching team data from:', url);

            axios.get(url)
                .then(response => {
                    console.log('Team data received:', response.data);
                    this.equipo = response.data;
                    if (this.equipo) {
                        this.equipoId = this.equipo.id_equipos;
                    }
                })
                .catch(error => {
                    console.error('Error al obtener datos del equipo:', error);
                    if (error.response) {
                        console.error('Error details:', error.response.data);
                        console.error('Status:', error.response.status);
                    }
                });
        },
        calcularKDA(jugador) {
            if (!jugador.deaths || jugador.deaths === 0) {
                return 'Perfect';
            }
            return ((jugador.kills + jugador.assists) / jugador.deaths).toFixed(2);
        },
        getImageUrl(imageName) {
            if (!imageName) return `${this.baseUrl}assets/usuarios/${this.imagenPorDefecto}`;
            return `${this.baseUrl}assets/usuarios/${imageName}`;
        },
        getBaseUrl() {
            return this.baseUrl;
        },
        handleImageError(event) {
            event.target.src = `${this.baseUrl}assets/usuarios/${this.imagenPorDefecto}`;
        },
        mostrarConfirmacion() {
            this.showModal = true;
        },
        confirmarContratacion() {
            if (!this.jugador || !this.usuario) {
                this.mostrarMensaje('Error: No se puede realizar la operación', 'error');
                return;
            }

            axios.post('api/contratar-jugador', {
                jugador_id: this.jugador.id_jugador,
                entrenador_id: this.usuario.id_usuario
            })
            .then(response => {
                this.showModal = false;
                this.mostrarMensaje('Jugador contratado exitosamente', 'success');
                // Recargar datos para mostrar la actualización
                setTimeout(() => {
                    this.fetchJugador();
                }, 500);
            })
            .catch(error => {
                console.error('Error al contratar jugador:', error);
                this.mostrarMensaje('Error al contratar jugador: ' + error.response?.data?.message || 'Error desconocido', 'error');
            });
        },
        iniciarxat() {
            if (!this.jugador || !this.usuario || !this.jugador.usuario) {
                return;
            }

            // Using the existing xat route from your codebase
            window.location.href = `/xat/create?usuarios[]=${this.jugador.usuario.id_usuario}`;
        },

        mostrarModalMensaje() {
            if (!this.jugador || !this.usuario || !this.jugador.usuario) {
                return;
            }
            this.mensajeTexto = '';
            this.showModalMensaje = true;
        },

        enviarMensaje() {
            if (!this.mensajeTexto.trim()) {
                this.mostrarMensaje('Por favor, escribe un mensaje antes de enviar.', 'error');
                return;
            }

            if (!this.jugador || !this.usuario || !this.jugador.usuario) {
                this.mostrarMensaje('Error: No se puede realizar la operación', 'error');
                return;
            }

            const targetUserId = this.jugador.usuario.id_usuario;
            const currentUserId = this.usuario.id_usuario;

            // Determine the correct base URL for API requests
            let baseUrl = '';
            if (window.location.pathname.includes('/public/')) {
                const pathSegments = window.location.pathname.split('/public/')[0];
                baseUrl = `${window.location.origin}${pathSegments}/public`;
            } else {
                baseUrl = window.location.origin;
            }

            // First, check if a conversation already exists between these users
            axios.get(`${baseUrl}api/xat/conversaciones`)
                .then(response => {
                    const conversations = response.data;
                    // Look for an existing conversation with just these two users
                    const existingConversation = conversations.find(conv => {
                        return conv.usuarios.length === 2 &&
                               conv.usuarios.some(u => u.id_usuario == targetUserId) &&
                               conv.usuarios.some(u => u.id_usuario == currentUserId);
                    });

                    if (existingConversation) {
                        // Conversation exists, send message to existing conversation
                        axios.post(`${baseUrl}api/xat/${existingConversation.id_conversacion}/enviar`, {
                            mensaje: this.mensajeTexto
                        })
                        .then(response => {
                            // Close the modal and show success message
                            this.showModalMensaje = false;
                            this.mostrarMensaje('Mensaje enviado exitosamente', 'success');

                            // Optionally redirect to the xat
                            setTimeout(() => {
                                window.location.href = `${baseUrl}/xat/${existingConversation.id_conversacion}`;
                            }, 500);
                        })
                        .catch(error => {
                            console.error('Error sending message:', error);
                            this.mostrarMensaje('Error al enviar el mensaje', 'error');
                        });
                    } else {
                        // No conversation exists, create one with the custom message
                        axios.post(`${baseUrl}api/xat/store`, {
                            usuarios: [targetUserId],
                            mensaje: this.mensajeTexto
                        })
                        .then(response => {
                            // Close the modal and show success message
                            this.showModalMensaje = false;
                            this.mostrarMensaje('Mensaje enviado exitosamente', 'success');

                            // Redirect to the new conversation
                            if (response.data.conversacion) {
                                setTimeout(() => {
                                    window.location.href = `${baseUrl}/xat/${response.data.conversacion.id_conversacion}`;
                                }, 500);
                            }
                        })
                        .catch(error => {
                            console.error('Error creating conversation:', error);
                            this.mostrarMensaje('Error al crear la conversación', 'error');
                        });
                    }
                })
                .catch(error => {
                    console.error('Error checking existing conversations:', error);
                    this.mostrarMensaje('Error al verificar conversaciones existentes', 'error');
                });
        },

        mostrarMensaje(mensaje, tipo) {
            // Implementa aquí tu lógica de notificación
            alert(mensaje);
        },
        getRangoColor(rango) {
            return this.rangosColores[rango] || '#ffffff';
        }
    },
    mounted() {
        // Calculate base URL based on current path
        const path = window.location.pathname;
        if (path.includes('/public/')) {
            this.baseUrl = window.location.origin + path.substring(0, path.indexOf('/public/') + 8);
        } else {
            this.baseUrl = window.location.origin + '/';
        }
        console.log('JugadorIndividual using base URL:', this.baseUrl);

        this.fetchJugador();
    }
};
</script>

<style scoped>
.jugador-detalle-container {
    padding: 30px;
    background-color: #1E1E1E;
    color: #ffffff;
    border-radius: 12px;
    margin: 20px;
    min-height: 80vh;
}

.jugador-header {
    margin-bottom: 30px;
}

.jugador-banner {
    display: flex;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #333 0%, #222 100%);
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    position: relative;
    overflow: hidden;
}

.jugador-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, #FE4454, transparent);
}

.jugador-imagen-container {
    flex-shrink: 0;
    margin-right: 30px;
}

.jugador-imagen {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #FE4454;
    box-shadow: 0 0 20px rgba(254, 68, 84, 0.4);
}

.jugador-info-basica {
    flex: 1;
}

.jugador-info-basica h1 {
    color: #ffffff;
    font-size: 2.2rem;
    margin: 0 0 15px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.jugador-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.rango-badge {
    background-color: #9c27b0;
    color: white;
}

.rol-badge {
    background-color: #2196f3;
    color: white;
}

.kda-badge {
    background-color: #ff9800;
    color: white;
}

.team-badge {
    background-color: #4caf50;
    color: white;
}

.free-badge {
    background-color: #607d8b;
    color: white;
}

.jugador-nombre-real {
    color: #ccc;
    font-size: 1.1rem;
    font-style: italic;
}

.jugador-contenido {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.panel {
    background-color: #2a2a2a;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.panel-title {
    color: #FE4454;
    font-size: 1.4rem;
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #444;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background-color: #333;
    border-radius: 8px;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    color: #ccc;
}

.kills-color {
    color: #4CAF50;
}

.deaths-color {
    color: #F44336;
}

.assists-color {
    color: #2196F3;
}

.kda-color {
    color: #FFC107;
}

.info-grid {
    display: grid;
    gap: 15px;
}

.info-item {
    display: flex;
    padding: 10px;
    background-color: #333;
    border-radius: 8px;
}

.info-label {
    width: 100px;
    font-weight: bold;
    color: #aaa;
}

.info-value {
    flex: 1;
    color: white;
}

.equipo-link {
    color: #FE4454;
    text-decoration: none;
    transition: color 0.3s;
}

.equipo-link:hover {
    color: #ff7a8a;
    text-decoration: underline;
}

.panel-actions {
    background-color: #262626;
}

.actions-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.action-btn {
    padding: 12px;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.contratar-btn {
    background-color: #4CAF50;
}

.contratar-btn:hover {
    background-color: #45a049;
}

.xat-btn {
    background-color: #2196F3;
}

.xat-btn:hover {
    background-color: #0b7dda;
}

.loading-state, .error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 50vh;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #444;
    border-top-color: #FE4454;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

.error-state i {
    font-size: 3rem;
    color: #F44336;
    margin-bottom: 15px;
}

.btn-volver {
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #333;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    transition: background-color 0.3s;
}

.btn-volver:hover {
    background-color: #555;
}

/* Modal styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: #2a2a2a;
    padding: 20px;
    border-radius: 8px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.btn-confirm {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-cancel {
    background-color: #f44336;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive styles */
@media (max-width: 768px) {
    .jugador-banner {
        flex-direction: column;
        text-align: center;
    }

    .jugador-imagen-container {
        margin-right: 0;
        margin-bottom: 20px;
    }

    .jugador-badges {
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Estilos para el botón de mensaje directo */
.mensaje-directo-btn {
    padding: 8px 16px;
    background-color: #2196F3;
    color: white;
    border: none;
    border-radius: 5px;
    margin-top: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background-color 0.3s;
}

.mensaje-directo-btn:hover {
    background-color: #0b7dda;
}

/* Estilos para el modal de mensaje */
.mensaje-modal {
    width: 400px;
    max-width: 90%;
}

.mensaje-textarea {
    width: 100%;
    height: 120px;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #444;
    background-color: #333;
    color: white;
    resize: vertical;
}
</style>
