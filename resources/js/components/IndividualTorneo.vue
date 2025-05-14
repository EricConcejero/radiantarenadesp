<template>
    <div class="torneo-detalle-container">
        <h1>Detalles del Torneo</h1>

        <div v-if="torneo">
            <div class="torneo-header">
                <img :src="`../../assets/torneos/${torneo.imagen_torneo}`" alt="Imagen del Torneo" class="torneo-image" />
                <div class="torneo-info">
                    <h2>{{ torneo.nombre_torneo }}</h2>
                    <p class="torneo-ubicacion">{{ torneo.ubicacion }}</p>
                    <p class="torneo-fecha">Fecha: {{ torneo.fecha_inico }} - {{ torneo.fecha_fin }}</p>

                    <!-- boton solo para entrenador -->
                    <button v-if="usuario && usuario.tipo_usuario === 'entrenador'" @click="showConfirmation = true" class="inscripcion-btn">
                        Inscribirse
                    </button>

                    <!-- mensaje solo para jugadores -->
                    <div v-else-if="usuario && usuario.tipo_usuario === 'jugador'" class="mensaje-info">
                        <i class="fas fa-info-circle"></i> Pide a tu entrenador que inscriba al equipo
                    </div>

                    <!-- mensaje para no logeados -->
                    <div v-else-if="!usuario" class="mensaje-login">
                        <i class="fas fa-lock"></i> Inicia sesión para ver opciones
                    </div>
                </div>
            </div>

            <div class="torneo-descripcion">
                <h3>Descripción</h3>
                <p>{{ torneo.descripcion || 'No hay descripción disponible.' }}</p>
            </div>

            <div class="torneo-detalles">
                <div class="detalle-item">
                    <span class="detalle-label">Premio:</span>
                    <span class="detalle-valor">{{ torneo.premio || 'No especificado' }}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Categoría:</span>
                    <span class="detalle-valor">{{ torneo.categoria || 'No especificada' }}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Formato:</span>
                    <span class="detalle-valor">{{ torneo.formato || 'No especificado' }}</span>
                </div>
            </div>
        </div>

        <!-- modal para confirmar que te quieres inscribir -->
        <div v-if="showConfirmation" class="modal-overlay">
            <div class="modal-content styled-modal">
                <h3>Confirmación</h3>
                <p>¿Estás seguro de que quieres inscribirte en esta liga?</p>
                <div class="modal-buttons">
                    <button @click="inscribirEquipo" class="btn-confirm">Aceptar</button>
                    <button @click="showConfirmation = false" class="btn-cancel">Cancelar</button>
                </div>
            </div>
        </div>

        <!-- Equipos inscritos (Gallery View) -->
        <div v-if="equiposInscritos.length > 0" class="equipos-inscritos-gallery">
            <h2>Equipos Inscritos</h2>
            <div class="team-members">
                <div v-for="equipo in equiposInscritos" :key="equipo.id_equipos" class="team-member">
                    <div class="member-photo">
                        <img
                            :src="`../../assets/equipos/${equipo.logo || 'gl.png'}`"
                            alt="Logo del Equipo"
                            class="equipo-logo"
                        />
                        <span class="role-badge team-badge">{{ equipo.tag }}</span>
                    </div>
                    <p class="member-name">{{ equipo.nombre_equipo }}</p>
                    <p class="equipo-region">{{ equipo.region }}</p>
                    <a :href="`equipos/${equipo.id_equipos}`" class="ver-equipo-btn">
                        <i class="fas fa-eye"></i> Ver Equipo
                    </a>
                </div>
            </div>
        </div>

        <div v-else-if="torneo" class="no-equipos">
            <p>Aún no hay equipos inscritos en este torneo.</p>
        </div>

        <div v-else class="loading">
            <p>Cargando información del torneo...</p>
        </div>

        <!-- bracket -->
        <div class="bracket-section">
            <h2>Bracket del Torneo</h2>

            <!-- Add this team counter - visible to everyone -->
            <div class="team-counter">
                <div class="counter-display">
                    <span class="counter-number">{{ equiposInscritos.length }}</span>
                    <span class="counter-separator">/</span>
                    <span class="counter-total">8</span>
                </div>
                <p class="counter-label">Equipos Inscritos</p>

                <!-- Progress bar -->
                <div class="progress-bar-container">
                    <div class="progress-bar" :style="{ width: `${(equiposInscritos.length / 8) * 100}%` }"></div>
                </div>

                <!-- Message based on team count -->
                <p class="counter-message" v-if="equiposInscritos.length < 8">
                    Se necesitan {{ 8 - equiposInscritos.length }} equipos más para generar el bracket
                </p>
                <p class="counter-message ready" v-else>
                    ¡Equipos completos! El bracket puede ser generado
                </p>
            </div>

            <!-- boton de generar bracket para administradores -->
            <div v-if="usuario && (usuario.tipo_usuario === 'administrador' || isOwner) && !bracketExists">
                <button @click="generarBracket" class="bracket-btn" :disabled="generatingBracket">
                    Generar Bracket
                </button>
                <p v-if="bracketMessage" class="bracket-message" :class="bracketMessageType">{{ bracketMessage }}</p>
            </div>

            <!-- Display bracket status -->
            <div v-if="bracketExists" class="bracket-info">
                <p>El bracket para este torneo ya está generado.</p>
                <a :href="`/radiantarena/radiantarena/laravel/public/torneos/individual/${torneo.id_torneos}/bracket`" class="ver-bracket-btn">Ver Bracket</a>

                <!-- Option to regenerate for admins -->
                <div v-if="usuario && (usuario.tipo_usuario === 'administrador' || isOwner)" class="regenerate-section">
                    <button @click="generarBracket" class="regenerate-btn" :disabled="generatingBracket">
                        Regenerar Bracket
                    </button>
                </div>
            </div>
            <div v-else class="bracket-info">
                <p>El bracket aún no ha sido generado para este torneo.</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'IndividualTorneo',
    props: {
        usuario: {
            type: Object,
            default: null, // Allow null for non-logged-in users
        },
        equipoId: {
            type: [Number, String],
            default: null, // Allow null if no equipoId is provided
        },
        equipoNombre: {
            type: String,
            default: '', // Default to an empty string if not provided
        },
    },
    data() {
        return {
            torneo: null,
            showConfirmation: false,
            equiposInscritos: [],
            bracketExists: false,
            generatingBracket: false,
            bracketMessage: '',
            bracketMessageType: '',
            isOwner: false, // Set this based on your logic
            isGeneratingBracket: false,
            bracketGenerated: false
        };
    },
    methods: {
        fetchTorneo() {
            const torneoId = window.location.pathname.split('/').pop();
            console.log('Extracted torneoId:', torneoId); // Debugging log
            axios.get(`/torneos/individual/${torneoId}`)
                .then(response => {
                    this.torneo = response.data;
                    console.log('Torneo cargado:', this.torneo); // Debugging log
                })
                .catch(error => {
                    console.error('Error al obtener el torneo:', error.response?.data || error.message);
                });
        },
        fetchEquiposInscritos() {
            if (!this.torneo || !this.torneo.id_torneos) {
                console.warn('Torneo data is not yet loaded or ID is missing.');
                return;
            }
            console.log('Fetching equipos inscritos for torneo ID:', this.torneo.id_torneos); // Debugging log
            axios.get(`/torneos/${this.torneo.id_torneos}/equipos-inscritos`)
                .then(response => {
                    this.equiposInscritos = response.data;
                    console.log('Equipos inscritos cargados:', this.equiposInscritos); // Debugging log
                })
                .catch(error => {
                    console.error('Error al obtener los equipos inscritos:', error.response?.data || error.message);
                });
        },
        inscribirEquipo() {
            if (!this.equipoId) {
                alert('No se puede inscribir porque el ID del equipo no está disponible.');
                return;
            }
            console.log('Inscribiendo equipo con ID:', this.equipoId, 'al torneo ID:', this.torneo?.id_torneos); // Debugging log
            axios.post('/inscribirse', {
                id_torneo: this.torneo?.id_torneos,
                id_equipo: this.equipoId,
                fecha_inscripcion: new Date().toISOString().split('T')[0],
            })
            .then(() => {
                alert('Equipo inscrito exitosamente.');
                this.showConfirmation = false;
                this.fetchEquiposInscritos();
            })
            .catch(error => {
                console.error('Error al inscribir el equipo:', error.response?.data || error.message);
            });
        },
        // Check if bracket exists for this tournament
        checkBracketExists() {
            if (!this.torneo) return;

            axios.get(`/torneos/${this.torneo.id_torneos}/bracket`)
                .then(response => {
                    this.bracketExists = response.data && response.data.length > 0;
                    console.log('Bracket exists check:', this.bracketExists);
                })
                .catch(error => {
                    console.error('Error checking bracket:', error);
                    this.bracketExists = false;
                });
        },

        // Generate bracket for this tournament
        generarBracket() {
            if (!this.torneo) return;

            this.generatingBracket = true;
            this.bracketMessage = 'Generando bracket...';
            this.bracketMessageType = 'info';

            axios.post('/brackets/generar', {
                torneo_id: this.torneo.id_torneos
            })
            .then(response => {
                console.log('Bracket generado:', response.data);
                this.bracketMessage = 'Bracket generado con éxito';
                this.bracketMessageType = 'success';

                // Re-check bracket status to update UI
                this.checkBracketExists();
            })
            // Rest of the method...
            .catch(error => {
                console.error('Error generando bracket:', error);
                // Log more detailed error information
                if (error.response) {
                    console.error('Error data:', error.response.data);
                    console.error('Error status:', error.response.status);
                    console.error('Error headers:', error.response.headers);

                    // Update the message with specific error if available
                    this.bracketMessage = error.response.data.error || 'Error generando el bracket';
                } else {
                    this.bracketMessage = 'Error generando el bracket';
                }
                this.bracketMessageType = 'error';
                this.bracketExists = false;
            })
            .finally(() => {
                this.generatingBracket = false;
            });
        },
    },
    mounted() {
        this.fetchTorneo();
        // Remove this call from here: this.fetchEquiposInscritos();

        // Add this to check if bracket exists when component is mounted
        this.$watch('torneo', (newVal) => {
            if (newVal) {
                this.checkBracketExists();
                this.fetchEquiposInscritos(); // Call here instead, after torneo is loaded
            }
        });
    },
};
</script>

<style scoped>

h1 {
    color: #ffffff;
    font-size: 2.2em;
    margin-bottom: 30px;
    border-bottom: 1px solid #FE4454;
    padding-bottom: 15px;
    text-align: center;
}

h2 {
    color: #FE4454;
    font-size: 1.8em;
    margin: 0 0 10px 0;
    text-align: left;
}

h3 {
    color: #FE4454;
    font-size: 1.4em;
    margin: 25px 0 15px 0;
    border-bottom: 1px solid #444;
    padding-bottom: 10px;
    text-align: left;
}

/* Retained essential styles */
.torneo-detalle-container {
    padding: 30px;
    background-color: #1E1E1E;
    color: #ffffff;
    border-radius: 12px;
    margin: 20px;
    text-align: center;
}

.modal-buttons {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.btn-cancel {
    background-color: #dc3545;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 1em;
    transition: background-color 0.3s;
}

.btn-cancel:hover {
    background-color: #c82333;
}

.torneo-header {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    text-align: left;
}

.torneo-image {
    width: 180px;
    height: 240px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 30px;
    box-shadow: 0 5px 15px rgba(254, 68, 84, 0.3);
}

.torneo-info {
    flex: 1;
}

.torneo-ubicacion {
    font-style: italic;
    color: #aaa;
    font-size: 1.2em;
    margin: 5px 0;
    text-align: left;
}

.torneo-fecha {
    color: #ccc;
    font-size: 1.1em;
    margin: 10px 0;
    text-align: left;
}

.torneo-descripcion {
    margin: 25px 0;
    padding: 15px;
    border-top: 1px solid #444;
    border-bottom: 1px solid #444;
    text-align: left;
}

.torneo-descripcion p {
    line-height: 1.6;
    color: #ddd;
}

.torneo-detalles {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-top: 20px;
    text-align: left;
}

.detalle-item {
    width: 30%;
    margin-bottom: 15px;
    padding: 10px;
    background-color: #333;
    border-radius: 5px;
}

.detalle-label {
    font-weight: bold;
    color: #FE4454;
    display: block;
    margin-bottom: 5px;
}

.detalle-valor {
    color: #fff;
}

.loading {
    color: #ccc;
    font-style: italic;
    padding: 30px;
    font-size: 1.2em;
}

@media (max-width: 768px) {
    .torneo-header {
        flex-direction: column;
        text-align: center;
    }

    .torneo-image {
        margin: 0 auto 20px;
    }

    .torneo-info {
        text-align: center;
    }

    .detalle-item {
        width: 100%;
    }

    h2, h3, .torneo-ubicacion, .torneo-fecha {
        text-align: center;
    }
}

/* Button and message styles */
.inscripcion-btn {
    background-color: #FE4454;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    font-weight: bold;
    margin-top: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    transition: background-color 0.3s;
}

.inscripcion-btn:hover {
    background-color: #d32f3d;
}

.mensaje-info, .mensaje-login {
    margin-top: 15px;
    padding: 10px;
    border-radius: 5px;
    display: flex;
    align-items: center;
}

.mensaje-info {
    background-color: rgba(255, 166, 0, 0.2);
    border: 1px solid orange;
    color: #ffc107;
}

.mensaje-login {
    background-color: rgba(3, 169, 244, 0.2);
    border: 1px solid #03A9F4;
    color: #03A9F4;
}

.mensaje-info i, .mensaje-login i {
    margin-right: 8px;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.styled-modal {
    background-color: #2a2a2a;
    color: #ffffff;
    border: 2px solid #FE4454;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.7);
    max-width: 400px;
    width: 90%;
}

.styled-modal h3 {
    color: #FE4454;
    font-size: 1.8em;
    margin-bottom: 20px;
    border-bottom: 1px solid #444;
    padding-bottom: 10px;
}

.styled-modal p {
    font-size: 1.2em;
    margin-bottom: 25px;
    color: #ddd;
}

.modal-buttons {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.btn-confirm {
    background-color: #28a745;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 1em;
    transition: background-color 0.3s;
}

.btn-confirm:hover {
    background-color: #218838;
}

.btn-cancel {
    background-color: #dc3545;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 1em;
    transition: background-color 0.3s;
}

.btn-cancel:hover {
    background-color: #c82333;
}

.equipos-inscritos {
    margin-top: 30px;
}

.equipos-inscritos table {
    width: 100%;
    border-collapse: collapse;
}

.equipos-inscritos th, .equipos-inscritos td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.equipos-inscritos th {
    background-color: #f4f4f4;
    font-weight: bold;
}

/* Add these new styles */
.bracket-section {
    margin-top: 30px;
    padding: 20px;
    background-color: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    text-align: center;
}

.bracket-btn {
    background-color: #FE4454;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 12px 25px;
    font-weight: bold;
    margin: 20px 0;
    cursor: pointer;
    font-size: 1.1em;
    transition: background-color 0.3s;
}

.bracket-btn:hover {
    background-color: #d32f3d;
}

.bracket-btn:disabled {
    background-color: #777;
    cursor: not-allowed;
}

.bracket-info {
    margin: 15px 0;
}

.ver-bracket-btn {
    display: inline-block;
    background-color: #333;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 5px;
    margin-top: 10px;
    transition: background-color 0.3s;
}

.ver-bracket-btn:hover {
    background-color: #444;
}

.bracket-message {
    padding: 10px;
    border-radius: 5px;
    margin: 15px 0;
}

.bracket-message.success {
    background-color: rgba(40, 167, 69, 0.2);
    border: 1px solid #28a745;
    color: #28a745;
}

.bracket-message.error {
    background-color: rgba(220, 53, 69, 0.2);
    border: 1px solid #dc3545;
    color: #dc3545;
}

.bracket-message.info {
    background-color: rgba(23, 162, 184, 0.2);
    border: 1px solid #17a2b8;
    color: #17a2b8;
}

/* Add these new styles */
.team-counter {
    background-color: #2a2a2a;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    text-align: center;
}

.counter-display {
    font-size: 2.5em;
    font-weight: bold;
    color: #fff;
    margin-bottom: 5px;
}

.counter-number {
    color: #FE4454;
}

.counter-separator {
    margin: 0 5px;
    color: #777;
}

.counter-total {
    color: #aaa;
}

.counter-label {
    font-size: 1.2em;
    color: #ddd;
    margin-bottom: 15px;
}

.progress-bar-container {
    background-color: #444;
    height: 12px;
    border-radius: 6px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-bar {
    height: 100%;
    background-color: #FE4454;
    transition: width 0.5s ease;
}

.counter-message {
    font-size: 1em;
    color: #ccc;
    margin-top: 10px;
}

.counter-message.ready {
    color: #28a745;
    font-weight: bold;
}

.regenerate-section {
    margin-top: 15px;
}

.regenerate-btn {
    background-color: #FE4454;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 8px 18px;
    font-weight: bold;
    margin-top: 10px;
    cursor: pointer;
    font-size: 0.9em;
    transition: background-color 0.3s;
}

.regenerate-btn:hover {
    background-color: #d32f3d;
}

/* Add these new styles */
.equipos-inscritos-gallery {
    margin-top: 30px;
}

.team-members {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
    background-color: #2a2a2a;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.team-member {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 110px;
    transition: transform 0.2s ease;
}

.member-photo {
    position: relative;
    margin-bottom: 8px;
}

.member-photo img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #333;
    transition: all 0.3s ease;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
}

.team-member:hover {
    transform: translateY(-5px);
}

.team-member:hover .member-photo img {
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(254, 68, 84, 0.5);
}

.role-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    background-color: #333;
    color: white;
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 10px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.team-badge {
    background-color: #FE4454;
}

.member-name {
    font-size: 0.9rem;
    text-align: center;
    color: #FE4454;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-top: 8px;
    font-weight: bold;
}

.equipo-region {
    font-style: italic;
    color: #ccc;
    font-size: 0.8rem;
    margin: 5px 0;
}

.ver-equipo-btn {
    display: inline-block;
    background-color: #333;
    color: white;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 5px;
    margin-top: 8px;
    transition: background-color 0.3s;
    font-size: 0.8rem;
    border: 1px solid rgba(254, 68, 84, 0.3);
}

.ver-equipo-btn:hover {
    background-color: rgba(254, 68, 84, 0.8);
}

/* Media queries for responsive design */
@media (max-width: 768px) {
    .team-members {
        justify-content: center;
    }

    .team-member {
        width: 120px;
        margin-bottom: 15px;
    }

    .member-photo img {
        width: 85px;
        height: 85px;
    }
}

@media (max-width: 480px) {
    .team-member {
        width: 100px;
    }

    .member-photo img {
        width: 80px;
        height: 80px;
    }

    .role-badge {
        font-size: 0.6rem;
        padding: 2px 6px;
    }

    .member-name {
        font-size: 0.8rem;
    }
}

.no-equipos {
    background-color: #2a2a2a;
    padding: 30px;
    border-radius: 8px;
    text-align: center;
    margin-top: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(254, 68, 84, 0.2);
}

.no-equipos p {
    color: #aaa;
    font-style: italic;
    font-size: 1.2em;
    margin-bottom: 0;
}

.no-equipos i {
    display: block;
    font-size: 2.5em;
    color: #FE4454;
    margin-bottom: 15px;
    opacity: 0.6;
}
</style>
