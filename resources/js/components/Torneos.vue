<template>
    <div class="torneos-container">
        <h1 class="page-title">Torneos <span class="accent">Valorant</span></h1>

        <div class="torneos-grid">
            <div v-for="torneo in torneos" :key="torneo.id_torneos" class="torneo-card">
                <a :href="`torneos/individual/${torneo.id_torneos}`" class="torneo-link">
                    <div class="torneo-image-container">
                        <img :src="`assets/torneos/${torneo.imagen_torneo}`" alt="Imagen del Torneo" class="torneo-image" />
                        <div class="torneo-overlay">
                            <span class="view-details">VER DETALLES</span>
                        </div>
                    </div>
                    <div class="torneo-content">
                        <h2 class="torneo-title">{{ torneo.nombre_torneo }}</h2>
                        <div class="torneo-info">
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ torneo.ubicacion }}</span>
                            </div>
                            <div class="info-item">
                                <i class="far fa-calendar-alt"></i>
                                <span>{{ formatDate(torneo.fecha_inico) }} - {{ formatDate(torneo.fecha_fin) }}</span>
                            </div>
                        </div>
                        <div class="torneo-badge" :class="getTorneoStatusClass(torneo)">
                            <span>{{ getTorneoStatusText(torneo) }}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="cargando && !torneos.length" class="loading-container">
            <div class="spinner"></div>
            <p>Cargando torneos...</p>
        </div>

        <!-- No tournaments found -->
        <div v-if="!cargando && !torneos.length" class="no-torneos">
            <i class="fas fa-trophy"></i>
            <p>No hay torneos disponibles en este momento</p>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'Torneos',
    data() {
        return {
            torneos: [],
            cargando: true,
            equiposInscritos: {},  // Objeto para almacenar el número de equipos inscritos por torneo
            partidasInfo: {}       // Objeto para almacenar la información de partidas por torneo
        };
    },
    methods: {
        fetchTorneos() {
            this.cargando = true;

            axios.get('torneos')
                .then(response => {
                    this.torneos = response.data;
                    // Una vez obtenidos los torneos, obtener info de inscripciones para cada uno
                    this.fetchInscripcionesInfo();
                })
                .catch(error => {
                    console.error('Error al obtener los torneos:', error);
                    this.cargando = false;
                });
        },

        fetchInscripcionesInfo() {
            // Crear un array de promesas para obtener la info de inscripciones de cada torneo
            const promises = this.torneos.map(torneo =>
                axios.get(`/torneos/${torneo.id_torneos}/equipos-inscritos`)
                    .then(response => {
                        this.equiposInscritos[torneo.id_torneos] = response.data.length;
                        return axios.get(`/torneos/${torneo.id_torneos}/bracket`)
                            .then(bracketResponse => {
                                this.partidasInfo[torneo.id_torneos] = {
                                    hasMatches: bracketResponse.data && bracketResponse.data.length > 0,
                                    matches: bracketResponse.data
                                };
                            })
                            .catch(() => {
                                this.partidasInfo[torneo.id_torneos] = { hasMatches: false, matches: [] };
                            });
                    })
                    .catch(error => {
                        console.error(`Error al obtener datos del torneo ${torneo.id_torneos}:`, error);
                        this.equiposInscritos[torneo.id_torneos] = 0;
                        this.partidasInfo[torneo.id_torneos] = { hasMatches: false, matches: [] };
                    })
            );

            // Cuando todas las promesas se resuelvan, actualizar el estado de carga
            Promise.all(promises)
                .finally(() => {
                    this.cargando = false;
                });
        },

        formatDate(dateString) {
            // Format date for better display
            try {
                const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
                return new Date(dateString).toLocaleDateString('es-ES', options);
            } catch (e) {
                return dateString;
            }
        },

        getTorneoStatusText(torneo) {
            const now = new Date();
            const startDate = new Date(torneo.fecha_inico);
            const endDate = new Date(torneo.fecha_fin);
            const equiposCount = this.equiposInscritos[torneo.id_torneos] || 0;
            const hasMatches = this.partidasInfo[torneo.id_torneos]?.hasMatches || false;

            // Si el torneo ya terminó
            if (now > endDate) {
                return 'FINALIZADO';
            }

            // Si el torneo está en curso y ya tiene partidas generadas
            if (now >= startDate && now <= endDate && hasMatches) {
                return 'EN CURSO';
            }

            // Si el torneo tiene todas las plazas ocupadas (8 equipos)
            if (equiposCount >= 8) {
                return 'INSCRIPCIÓN CERRADA';
            }

            // Si el torneo está por iniciar y tiene menos de 8 equipos
            return `INSCRIPCIÓN ABIERTA (${equiposCount}/8)`;
        },

        getTorneoStatusClass(torneo) {
            const now = new Date();
            const startDate = new Date(torneo.fecha_inico);
            const endDate = new Date(torneo.fecha_fin);
            const equiposCount = this.equiposInscritos[torneo.id_torneos] || 0;
            const hasMatches = this.partidasInfo[torneo.id_torneos]?.hasMatches || false;

            if (now > endDate) {
                return 'torneo-badge-finished';
            }

            if (now >= startDate && now <= endDate && hasMatches) {
                return 'torneo-badge-in-progress';
            }

            if (equiposCount >= 8) {
                return 'torneo-badge-closed';
            }

            return 'torneo-badge-open';
        }
    },
    mounted() {
        this.fetchTorneos();
    },
};
</script>

<style scoped>
.torneos-container {
    padding: 30px;
    text-align: center;
    background-color: #1E1E1E;
    color: #ffffff;
    border-radius: 12px;
    margin: 20px;
    min-height: 80vh;
    position: relative;
}

.page-title {
    color: #ffffff;
    font-size: 2.5em;
    margin-bottom: 40px;
    padding-bottom: 15px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    display: inline-block;
}

.page-title::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 3px;
    background: linear-gradient(90deg, transparent, #FE4454, transparent);
}

.accent {
    color: #FE4454;
}

.torneos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    margin-top: 30px;
}

.torneo-card {
    background: linear-gradient(145deg, #252525, #1a1a1a);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    transition: all 0.4s ease;
    transform-origin: center;
    position: relative;
    border: 1px solid rgba(254, 68, 84, 0.1);
    height: 100%;
}

.torneo-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(254, 68, 84, 0.3);
    border-color: rgba(254, 68, 84, 0.5);
}

.torneo-card::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #FE4454, transparent);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.5s ease;
}

.torneo-card:hover::before {
    transform: scaleX(1);
}

.torneo-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.torneo-image-container {
    position: relative;
    overflow: hidden;
    height: 200px;
    width: 100%;
}

.torneo-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.torneo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.torneo-card:hover .torneo-image {
    transform: scale(1.1);
}

.torneo-card:hover .torneo-overlay {
    opacity: 1;
}

.view-details {
    color: white;
    font-weight: bold;
    border: 2px solid #FE4454;
    padding: 10px 20px;
    font-size: 0.9em;
    letter-spacing: 1px;
    background-color: rgba(254, 68, 84, 0.3);
    transition: all 0.3s ease;
}

.torneo-card:hover .view-details {
    background-color: #FE4454;
    transform: scale(1.1);
}

.torneo-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    justify-content: space-between;
}

.torneo-title {
    color: #FE4454;
    font-size: 1.3em;
    margin: 0 0 15px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    position: relative;
    padding-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.torneo-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2px;
    background: #FE4454;
}

.torneo-info {
    margin-top: 15px;
    color: #ccc;
    font-size: 0.9em;
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-align: left;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-item i {
    color: #FE4454;
    width: 16px;
}

.torneo-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    margin-top: 20px;
    font-size: 0.8em;
    font-weight: bold;
    align-self: flex-start;
}

/* New status badge styles */
.torneo-badge-open {
    background-color: rgba(76, 175, 80, 0.2);
    color: #4CAF50;
    border: 1px solid rgba(76, 175, 80, 0.5);
}

.torneo-badge-closed {
    background-color: rgba(255, 152, 0, 0.2);
    color: #FF9800;
    border: 1px solid rgba(255, 152, 0, 0.5);
}

.torneo-badge-in-progress {
    background-color: rgba(33, 150, 243, 0.2);
    color: #2196F3;
    border: 1px solid rgba(33, 150, 243, 0.5);
}

.torneo-badge-finished {
    background-color: rgba(158, 158, 158, 0.2);
    color: #9E9E9E;
    border: 1px solid rgba(158, 158, 158, 0.5);
}

/* Loading state */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 50px 0;
    min-height: 300px;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(254, 68, 84, 0.1);
    border-top-color: #FE4454;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-container p {
    color: #aaa;
    font-size: 1.2em;
}

/* No tournaments */
.no-torneos {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 0;
    min-height: 300px;
    background-color: rgba(30, 30, 30, 0.5);
    border-radius: 10px;
    border: 1px solid #333;
}

.no-torneos i {
    font-size: 4em;
    color: #FE4454;
    margin-bottom: 20px;
    opacity: 0.7;
}

.no-torneos p {
    color: #aaa;
    font-size: 1.2em;
}

/* Responsive styles */
@media (max-width: 1200px) {
    .torneos-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .torneos-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

@media (max-width: 580px) {
    .torneos-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .torneos-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .page-title {
        font-size: 2em;
    }

    .torneo-image-container {
        height: 180px;
    }
}

@media (max-width: 480px) {
    .torneos-grid {
        grid-template-columns: 1fr;
    }

    .torneos-container {
        padding: 20px 15px;
    }
}
</style>
