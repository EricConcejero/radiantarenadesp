<template>
  <div class="bracket-container">
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Cargando bracket...</p>
    </div>
    <div v-else-if="error" class="error-message">{{ error }}</div>
    <div v-else class="tournament-bracket">
      <div class="brackets-wrapper">
        <!-- upper bracket -->
        <div class="bracket-section">
          <div class="bracket-header">
            <h3>Bracket</h3> <!-- Cambiado de "Upper Bracket" a solo "Bracket" -->
          </div>

          <div class="bracket-flow">
            <!-- ronda 1 -->
            <div class="round">
              <h4 class="round-title">Cuartos de Final</h4>
              <div class="matches">
                <div v-for="(partida, index) in fases.upper_r1" :key="`upper-r1-${index}`" class="match-wrapper">
                  <div class="match">
                    <div class="match-teams">
                      <!-- equipo 1 -->
                      <div class="team" :class="{'winner': partida.ganador_id === partida.equipo1_id, 'eliminated': partida.perdedor_id === partida.equipo1_id}">
                        <div v-if="partida.equipo1" class="team-info">
                          <img v-if="partida.equipo1.logo" :src="getLogoUrl(partida.equipo1.logo)" class="team-logo" alt="Logo">
                          <div class="team-details">
                            <span class="team-name">{{ partida.equipo1.nombre_equipo }}</span>
                            <span class="team-tag">{{ partida.equipo1.tag }}</span>
                          </div>
                        </div>
                        <div v-else class="team-placeholder">Por definir</div>
                      </div>

                      <!-- Team 2 -->
                      <div class="team" :class="{'winner': partida.ganador_id === partida.equipo2_id, 'eliminated': partida.perdedor_id === partida.equipo2_id}">
                        <div v-if="partida.equipo2" class="team-info">
                          <img v-if="partida.equipo2.logo" :src="getLogoUrl(partida.equipo2.logo)" class="team-logo" alt="Logo">
                          <div class="team-details">
                            <span class="team-name">{{ partida.equipo2.nombre_equipo }}</span>
                            <span class="team-tag">{{ partida.equipo2.tag }}</span>
                          </div>
                        </div>
                        <div v-else class="team-placeholder">Por definir</div>
                      </div>
                    </div>
                    <!-- Inside your template where you need admin controls -->
                    <div v-if="isAdmin" class="admin-controls">
                      <button
                        class="winner-btn"
                        :class="{'selected': partida.ganador_id === partida.equipo1_id}"
                        @click="selectWinner(partida.id_partidas, partida.equipo1_id, partida.equipo1?.nombre_equipo || 'Equipo 1')">
                        Ganador: {{ partida.equipo1?.nombre_equipo || 'Equipo 1' }}
                      </button>
                      <button
                        class="winner-btn"
                        :class="{'selected': partida.ganador_id === partida.equipo2_id}"
                        @click="selectWinner(partida.id_partidas, partida.equipo2_id, partida.equipo2?.nombre_equipo || 'Equipo 2')">
                        Ganador: {{ partida.equipo2?.nombre_equipo || 'Equipo 2' }}
                      </button>
                    </div>
                  </div>
                  <div class="connector"></div>
                </div>
              </div>
            </div>

            <!-- Round 2 -->
            <div class="round">
              <h4 class="round-title">Semifinales</h4>
              <div class="matches">
                <div v-for="(partida, index) in fases.upper_r2" :key="`upper-r2-${index}`" class="match-wrapper">
                  <div class="match">
                    <div class="match-teams">
                      <!-- Team 1 -->
                      <div class="team" :class="{'winner': partida.ganador_id === partida.equipo1_id, 'eliminated': partida.perdedor_id === partida.equipo1_id}">
                        <div v-if="partida.equipo1" class="team-info">
                          <img v-if="partida.equipo1.logo" :src="getLogoUrl(partida.equipo1.logo)" class="team-logo" alt="Logo">
                          <div class="team-details">
                            <span class="team-name">{{ partida.equipo1.nombre_equipo }}</span>
                            <span class="team-tag">{{ partida.equipo1.tag }}</span>
                          </div>
                        </div>
                        <div v-else class="team-placeholder">Por definir</div>
                      </div>

                      <!-- Team 2 -->
                      <div class="team" :class="{'winner': partida.ganador_id === partida.equipo2_id, 'eliminated': partida.perdedor_id === partida.equipo2_id}">
                        <div v-if="partida.equipo2" class="team-info">
                          <img v-if="partida.equipo2.logo" :src="getLogoUrl(partida.equipo2.logo)" class="team-logo" alt="Logo">
                          <div class="team-details">
                            <span class="team-name">{{ partida.equipo2.nombre_equipo }}</span>
                            <span class="team-tag">{{ partida.equipo2.tag }}</span>
                          </div>
                        </div>
                        <div v-else class="team-placeholder">Por definir</div>
                      </div>
                    </div>
                    <!-- Inside your template where you need admin controls -->
                    <div v-if="isAdmin" class="admin-controls">
                      <button
                        class="winner-btn"
                        :class="{'selected': partida.ganador_id === partida.equipo1_id}"
                        @click="selectWinner(partida.id_partidas, partida.equipo1_id, partida.equipo1?.nombre_equipo || 'Equipo 1')">
                        Ganador: {{ partida.equipo1?.nombre_equipo || 'Equipo 1' }}
                      </button>
                      <button
                        class="winner-btn"
                        :class="{'selected': partida.ganador_id === partida.equipo2_id}"
                        @click="selectWinner(partida.id_partidas, partida.equipo2_id, partida.equipo2?.nombre_equipo || 'Equipo 2')">
                        Ganador: {{ partida.equipo2?.nombre_equipo || 'Equipo 2' }}
                      </button>
                    </div>
                  </div>
                  <div class="connector"></div>
                </div>
              </div>
            </div>

            <!-- Upper Final - Ahora es la Gran Final -->
            <div class="round">
              <h4 class="round-title">Gran Final</h4> <!-- Cambiado de "Final Upper" a "Gran Final" -->
              <div class="matches">
                <div class="match-wrapper">
                  <div class="match">
                    <div class="match-teams">
                      <!-- Team 1 -->
                      <div class="team" :class="{'winner': fases.upper_final?.ganador_id === fases.upper_final?.equipo1_id, 'eliminated': fases.upper_final?.perdedor_id === fases.upper_final?.equipo1_id}">
                        <div v-if="fases.upper_final?.equipo1" class="team-info">
                          <img v-if="fases.upper_final.equipo1.logo" :src="getLogoUrl(fases.upper_final.equipo1.logo)" class="team-logo" alt="Logo">
                          <div class="team-details">
                            <span class="team-name">{{ fases.upper_final.equipo1.nombre_equipo }}</span>
                            <span class="team-tag">{{ fases.upper_final.equipo1.tag }}</span>
                          </div>
                        </div>
                        <div v-else class="team-placeholder">Por definir</div>
                      </div>

                      <!-- Team 2 -->
                      <div class="team" :class="{'winner': fases.upper_final?.ganador_id === fases.upper_final?.equipo2_id, 'eliminated': fases.upper_final?.perdedor_id === fases.upper_final?.equipo2_id}">
                        <div v-if="fases.upper_final?.equipo2" class="team-info">
                          <img v-if="fases.upper_final.equipo2.logo" :src="getLogoUrl(fases.upper_final.equipo2.logo)" class="team-logo" alt="Logo">
                          <div class="team-details">
                            <span class="team-name">{{ fases.upper_final.equipo2.nombre_equipo }}</span>
                            <span class="team-tag">{{ fases.upper_final.equipo2.tag }}</span>
                          </div>
                        </div>
                        <div v-else class="team-placeholder">Por definir</div>
                      </div>
                    </div>
                    <!-- Inside your template where you need admin controls -->
                    <div v-if="isAdmin" class="admin-controls">
                      <button
                        class="winner-btn"
                        :class="{'selected': fases.upper_final?.ganador_id === fases.upper_final?.equipo1_id}"
                        @click="selectWinner(fases.upper_final?.id_partidas, fases.upper_final?.equipo1_id, fases.upper_final?.equipo1?.nombre_equipo || 'Equipo 1')">
                        Ganador: {{ fases.upper_final?.equipo1?.nombre_equipo || 'Equipo 1' }}
                      </button>
                      <button
                        class="winner-btn"
                        :class="{'selected': fases.upper_final?.ganador_id === fases.upper_final?.equipo2_id}"
                        @click="selectWinner(fases.upper_final?.id_partidas, fases.upper_final?.equipo2_id, fases.upper_final?.equipo2?.nombre_equipo || 'Equipo 2')">
                        Ganador: {{ fases.upper_final?.equipo2?.nombre_equipo || 'Equipo 2' }}
                      </button>
                    </div>
                  </div>
                  <div class="connector to-grand-final"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Lower Bracket - comentado para usar mas adelante -->
        <!--
        <div class="bracket-section">
          <div class="bracket-header">
            <h3>Lower Bracket</h3>
          </div>

          <div class="bracket-flow">
            <! Lower R1 -->
            <!-- ...existing code for lower_r1... -->

            <!-- Lower R2 -->
            <!-- ...existing code for lower_r2... -->

            <!-- Lower Semifinal -->
            <!-- ...existing code for lower_semifinal... -->

            <!-- Lower Final -->
            <!-- ...existing code for lower_final... -->
          </div>
        </div>

      </div>

      <!-- Grand Final Section - Comentado ya que ahora upper_final es la gran final -->
      <!--
      <div class="grand-final-section">
        <h3>Grand Final</h3>
        <div class="grand-final-match">
          ...existing code for grand_final...
        </div>
      </div>
      -->
    <!-- </div>
  </div> -->

  <!-- Confirmation Modal -->
  <div v-if="showModal" class="modal-overlay">
    <div class="modal">
      <h3>Confirmar Ganador</h3>
      <p>¿Estás seguro de que quieres seleccionar a <strong>{{ selectedTeamName }}</strong> como ganador?</p>
      <div class="modal-actions">
        <button @click="confirmWinner" class="confirm-btn">Confirmar</button>
        <button @click="cancelSelection" class="cancel-btn">Cancelar</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        torneoId: {
            type: [Number, String],
            required: true
        },
        usuario: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            partidas: [],
            loading: true,
            error: null,
            fases: {
                upper_r1: [],
                upper_r2: [],
                upper_final: null,
                lower_r2: [],
                lower_semifinal: null,
                lower_final: null,
                grand_final: null
            },
            showModal: false,
            selectedPartidaId: null,
            selectedEquipoId: null,
            selectedTeamName: '',
        };
    },
    computed: {
        isAdmin() {
            return this.usuario && this.usuario.tipo_usuario === 'administrador';
        }
    },
    methods: {
        async fetchPartidas() {
            try {
                const response = await axios.get('/partidas');
                this.partidas = response.data;
            } catch (error) {
                console.error('Error fetching partidas:', error);
            }
        },
        async selectWinner(partidaId, equipoId, teamName) {
            this.selectedPartidaId = partidaId;
            this.selectedEquipoId = equipoId;
            this.selectedTeamName = teamName;
            this.showModal = true; // Show the confirmation modal
        },
        async confirmWinner() {
            try {
                const response = await axios.post(`/partidas/${this.selectedPartidaId}/actualizar-ganador`, {
                    ganador_id: this.selectedEquipoId,
                });
                console.log(response.data.message);

                // Reload the bracket data to reflect the changes immediately
                await this.fetchBracket(); // Ensure the bracket is reloaded
            } catch (error) {
                console.error('Error updating winner:', error);
                if (error.response) {
                    console.error('Error details:', error.response.data);
                    alert(`Error: ${error.response.data.error || 'Error desconocido'}`);
                } else {
                    alert('Error de red o servidor no disponible.');
                }
            } finally {
                this.showModal = false;
            }
        },
        cancelSelection() {
            this.showModal = false;
        },

        fetchBracket() {
            this.loading = true;

            axios.get(`/torneos/${this.torneoId}/bracket`)
                .then(response => {
                    console.log('Bracket data loaded:', response.data);

                    const uniqueIds = new Set();
                    this.partidas = response.data.filter(partida => {
                        if (!uniqueIds.has(partida.id_partidas)) {
                            uniqueIds.add(partida.id_partidas);
                            return true;
                        }
                        return false;
                    });

                    this.organizarPartidas();
                    this.fetchEquiposInfo();
                })
                .catch(error => {
                    console.error('Error loading bracket:', error);
                    this.error = 'Error al cargar el bracket';
                    this.loading = false;
                });
        },

        organizarPartidas() {
            // Clear existing data
            this.fases = {
                upper_r1: [],
                upper_r2: [],
                upper_final: null,
                lower_r1: [],
                lower_r2: [],
                lower_semifinal: null,
                lower_final: null,
                grand_final: null
            };

            this.partidas.forEach(partida => {
                switch (partida.ronda) {
                    case 'upper_r1':
                        this.fases.upper_r1.push(partida);
                        break;
                    case 'upper_r2':
                        this.fases.upper_r2.push(partida);
                        break;
                    case 'upper_final':
                        this.fases.upper_final = partida;
                        break;
                    // no se muestra
                    case 'lower_r1':
                        this.fases.lower_r1.push(partida);
                        break;
                    case 'lower_r2':
                        this.fases.lower_r2.push(partida);
                        break;
                    case 'lower_semifinal':
                        this.fases.lower_semifinal = partida;
                        break;
                    case 'lower_final':
                        this.fases.lower_final = partida;
                        break;
                    case 'grand_final':
                        this.fases.grand_final = partida;
                        break;
                }
            });
        },

        fetchEquiposInfo() {
            const equipoIds = new Set();

            this.partidas.forEach(partida => {
                if (partida.equipo1_id) equipoIds.add(partida.equipo1_id);
                if (partida.equipo2_id) equipoIds.add(partida.equipo2_id);
            });

            if (equipoIds.size === 0) {
                console.warn('No team IDs found in matches.');
                this.loading = false;
                return;
            }

            console.log('Fetching team details for IDs:', Array.from(equipoIds));

            axios.get('/equipos', {
                params: { ids: Array.from(equipoIds).join(',') }
            })
            .then(response => {
                console.log('Team details loaded:', response.data);

                const equiposMap = {};
                response.data.forEach(equipo => {
                    equiposMap[equipo.id_equipos] = equipo;
                });

                this.partidas = this.partidas.map(partida => ({
                    ...partida,
                    equipo1: partida.equipo1_id ? equiposMap[partida.equipo1_id] : null,
                    equipo2: partida.equipo2_id ? equiposMap[partida.equipo2_id] : null
                }));

                this.organizarPartidas();
                this.loading = false;
            })
            .catch(error => {
                console.error('Error loading team details:', error);
                this.error = 'Error al cargar la información de equipos';
                this.loading = false;
            });
        },

        getLogoUrl(logo) {
            if (!logo) return null;
            return `/assets/equipos/${logo}`;
        }
    },
    mounted() {
        this.fetchBracket();
    }
};
</script>

<style scoped>
.bracket-container {
    font-family: 'Roboto', 'Arial', sans-serif;
    padding: 30px 20px;
    background-color: #0F1923;
    color: #ffffff;
    border-radius: 12px;
    max-width: 1400px;
    margin: 0 auto;
    overflow-x: auto;
}

h2 {
    text-align: center;
    color: #FE4454;
    font-size: 2.2rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 40px;
    position: relative;
    padding-bottom: 15px;
}

h2:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 150px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #FE4454, transparent);
}

h3 {
    color: #FE4454;
    font-size: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 25px;
    text-align: center;
}

h4.round-title {
    color: #fff;
    font-size: 1.1rem;
    margin-bottom: 20px;
    text-align: center;
    background-color: rgba(254, 68, 84, 0.1);
    padding: 8px;
    border-radius: 5px;
    border-left: 3px solid #FE4454;
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

.brackets-wrapper {
    display: flex;
    flex-direction: column;
    gap: 40px;
    margin-bottom: 40px;
}

.bracket-section {
    background-color: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.bracket-header {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 10px;
}

.bracket-flow {
    display: flex;
    justify-content: space-between;
    gap: 40px;
}

.round {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 270px;
}

.matches {
    display: flex;
    flex-direction: column;
    gap: 40px;
    height: 100%;
    justify-content: space-around;
}

.match-wrapper {
    position: relative;
}

.match {
    background-color: #1a2733;
    border-radius: 10px;
    padding: 12px;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    border-left: 3px solid #FE4454;
    transition: transform 0.2s ease;
    z-index: 2;
    position: relative;
}

.match:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 12px rgba(0, 0, 0, 0.25);
}

.match-teams {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.team {
    padding: 8px;
    border-radius: 6px;
    background-color: rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
}

.team-placeholder {
    color: #777;
    font-style: italic;
    text-align: center;
    padding: 5px;
}

.team.winner {
    background-color: rgba(254, 68, 84, 0.15);
    border: 1px solid rgba(254, 68, 84, 0.3);
    box-shadow: 0 0 10px rgba(254, 68, 84, 0.1);
}

.team.eliminated {
    background-color: rgba(255, 0, 0, 0.15);
    border: 1px solid rgba(255, 0, 0, 0.3);
    box-shadow: 0 0 10px rgba(255, 0, 0, 0.1);
}

.team-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.team-logo {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.team-details {
    display: flex;
    flex-direction: column;
}

.team-name {
    font-weight: 500;
    font-size: 0.9rem;
    color: #eee;
}

.team-tag {
    font-size: 0.7rem;
    color: #999;
}

/* Connectors */
.connector {
    position: absolute;
    height: 50%;
    width: 20px;
    right: -20px;
    top: 25%;
    border-right: 2px solid rgba(254, 68, 84, 0.3);
    z-index: 1;
}

.connector::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    background-color: rgba(254, 68, 84, 0.3);
    right: 0;
    top: 50%;
}

.to-grand-final {
    border-right: 2px solid rgba(254, 68, 84, 0.5);
}

.to-grand-final::after {
    background-color: rgba(254, 68, 84, 0.5);
}

/* Grand Final Section */
.grand-final-section {
    margin-top: 60px;
    background-color: rgba(254, 68, 84, 0.05);
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(254, 68, 84, 0.2);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.grand-final-match {
    background: linear-gradient(to right, rgba(15, 25, 35, 0.8), rgba(254, 68, 84, 0.1), rgba(15, 25, 35, 0.8));
    border-radius: 10px;
    padding: 20px;
}

.grand-final-match .match-teams {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.grand-final-match .team {
    flex: 1;
    text-align: center;
    background-color: #1a2733;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.grand-final-match .team-info {
    flex-direction: column;
    align-items: center;
}

.grand-final-match .team-logo {
    width: 40px;
    height: 40px;
    margin-bottom: 10px;
}

.vs-badge {
    font-weight: bold;
    color: #FE4454;
    font-size: 1.5rem;
    padding: 10px;
    background-color: rgba(15, 25, 35, 0.8);
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 20px rgba(254, 68, 84, 0.3);
    border: 1px solid #FE4454;
}

/* Responsive */
@media (max-width: 992px) {
    .brackets-wrapper {
        gap: 20px;
    }

    .bracket-flow {
        overflow-x: auto;
        padding-bottom: 10px;
    }
}

@media (max-width: 768px) {
    .grand-final-match .match-teams {
        flex-direction: column;
    }

    .vs-badge {
        margin: 10px 0;
    }
}

/* Add these styles to your existing styles */

.admin-controls {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px dashed rgba(254, 68, 84, 0.3);
    display: flex;
    gap: 8px;
}

.winner-btn {
    flex: 1;
    background-color: rgba(0, 0, 0, 0.2);
    color: #fff;
    border: 1px solid rgba(254, 68, 84, 0.3);
    border-radius: 5px;
    padding: 8px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.winner-btn:hover {
    background-color: rgba(254, 68, 84, 0.15);
}

.winner-btn.selected {
    background-color: rgba(254, 68, 84, 0.3);
    border-color: #FE4454;
    box-shadow: 0 0 10px rgba(254, 68, 84, 0.2);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #1a2733;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  color: #fff;
  width: 300px;
}

.modal h3 {
  margin-bottom: 15px;
  font-size: 1.5rem;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.confirm-btn {
  background-color: #FE4454;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}

.cancel-btn {
  background-color: #777;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}

.confirm-btn:hover {
  background-color: #d32f3d;
}

.cancel-btn:hover {
  background-color: #555;
}
</style>

