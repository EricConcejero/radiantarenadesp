<template>
 <div class="equipo-detalle-container">
    <!-- Información del equipo -->
    <div v-if="equipo" class="equipo-header">
      <div class="equipo-banner">
        <img :src="`../assets/equipos/${equipo.logo}`" alt="Imagen del Equipo" class="equipo-image" />
        <div class="equipo-info">
          <h1>{{ equipo.nombre_equipo }} [{{ equipo.tag }}]</h1>
          <p class="equipo-region">Región: {{ equipo.region }} | Experiencia: {{ equipo.palmares }} años</p>
        </div>
      </div>
    </div>

    <!-- Team members gallery -->
    <div v-if="equipo" class="team-gallery">
      <h2>Miembros del equipo</h2>
      <div class="team-members">
        <!-- Coach -->
        <div v-if="equipo.entrenador && equipo.entrenador.usuario" class="team-member coach">
          <div class="member-photo">
            <img
              :src="equipo.entrenador.usuario.imagen_usuario ?
                `../assets/usuarios/${equipo.entrenador.usuario.imagen_usuario}` :
                '../assets/usuarios/image_default.png'"
              alt="Foto del Entrenador"
            />
            <span class="role-badge coach-badge">Coach</span>
          </div>
          <p class="member-name">{{ equipo.entrenador.usuario.nombre }} {{ equipo.entrenador.usuario.apellidos }}</p>
        </div>

        <!-- Players -->
        <div v-for="jugador in jugadores" :key="jugador.id_jugador" class="team-member player">
          <div class="member-photo">
            <img
              :src="jugador.usuario && jugador.usuario.imagen_usuario ?
                `../assets/usuarios/${jugador.usuario.imagen_usuario}` :
                '../assets/usuarios/image_default.png'"
              alt="Foto del Jugador"
            />
            <span class="role-badge player-badge">{{ obtenerNombreRol(jugador.id_rol) }}</span>
          </div>
          <p class="member-name">{{ jugador.usuario ? jugador.usuario.nombre_jugador || jugador.usuario.username : 'Sin nombre' }}</p>
        </div>
      </div>
    </div>

    <!-- Información del entrenador -->
    <div v-if="equipo && equipo.entrenador" class="entrenador-info">
      <h2>Entrenador</h2>
      <div class="table-responsive">
        <table class="info-table entrenador-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Experiencia</th>
              <th>Palmarés</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ equipo.entrenador.usuario ? equipo.entrenador.usuario.nombre + ' ' + equipo.entrenador.usuario.apellidos : 'Sin nombre' }}</td>
              <td>{{ equipo.entrenador.experiencia || 'No especificado' }} años</td>
              <td>{{ equipo.entrenador.palmares || 'No especificado' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else class="no-entrenador">
      <p>Este equipo no tiene entrenador asignado.</p>
    </div>

    <!-- Tabla de jugadores -->
    <div class="jugadores-info">
      <h2>Jugadores del equipo</h2>
      <div v-if="jugadores && jugadores.length > 0" class="table-responsive">
        <table class="info-table jugadores-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Nickname</th>
              <th>Rango</th>
              <th>Rol</th>
              <th>K</th>
              <th>D</th>
              <th>A</th>
              <th>KDA</th>
              <th>Palmarés</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="jugador in jugadores" :key="jugador.id_jugador">
              <td>{{ jugador.usuario ? jugador.usuario.nombre + ' ' + jugador.usuario.apellidos : 'Sin nombre' }}</td>
              <td class="nickname">{{ jugador.usuario ? jugador.usuario.nombre_jugador || jugador.usuario.username : 'Sin nickname' }}</td>
              <td>{{ jugador.rango_valorant }}</td>
              <td>{{ obtenerNombreRol(jugador.id_rol) }}</td>
              <td class="kills">{{ jugador.kills }}</td>
              <td class="deaths">{{ jugador.deaths }}</td>
              <td class="assists">{{ jugador.assists }}</td>
              <td class="kda">{{ calcularKDA(jugador) }}</td>
              <td>{{ jugador.palmares }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="no-jugadores">
        <p>Este equipo no tiene jugadores registrados.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'Jugadores', // Nombre correcto del componente
    props: {
        id: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            equipo: null,
            jugadores: [],
            roles: {
                1: "Duelista",
                2: "Iniciador",
                3: "Centinela",
                4: "Controlador",
                5: "Coach"
            }
        };
    },
    methods: {
        fetchEquipo() {
            console.log('Obteniendo equipo con ID:', this.id);
            axios.get('equipos/' + this.id)
                .then(response => {
                    console.log('Respuesta recibida:', response.data);
                    this.equipo = response.data;

                    // Extraer los jugadores del equipo
                    if (this.equipo && this.equipo.entrenador && this.equipo.entrenador.jugadores) {
                        this.jugadores = this.equipo.entrenador.jugadores;
                    } else {
                        this.jugadores = [];
                    }
                })
                .catch(error => {
                    console.error('Error al obtener el equipo:', error);
                    console.error('Detalles del error:', error.response?.data || 'No hay detalles');
                    console.error('Status:', error.response?.status);
                });
        },
        obtenerNombreRol(rolId) {
            return this.roles[rolId] || 'Desconocido';
        },
        calcularKDA(jugador) {
            if (!jugador.deaths || jugador.deaths === 0) {
                return 'Perfect';
            }

            const kda = ((jugador.kills + jugador.assists) / jugador.deaths).toFixed(2);
            return kda;
        }
    },
    mounted() {
        this.fetchEquipo(); // Llamar al método correcto
    },
};
</script>

<style scoped>
.equipo-image{
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 20px; /* Espacio entre la imagen y el texto */
}

.equipo-banner {
    width: 350px;
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 20px;
}

.equipo-info {
    flex: 1;
    text-align: left;
}

.equipo-header {
    display: flex;
    justify-content: center;
}

.equipo-detalle-container {
    padding: 30px;
    background-color: #1E1E1E;
    color: #ffffff;
    border-radius: 12px;
    margin: 20px;
}

.equipo-header {
    margin-bottom: 30px;
    text-align: center;
}

.equipo-header h1 {
    color: #FE4454;
    font-size: 2em;
    margin-bottom: 5px;
}

.equipo-region {
    font-style: italic;
    color: #ccc;
}

h2 {
    color: #ffffff;
    border-bottom: 1px solid #FE4454;
    padding-bottom: 10px;
    margin-top: 30px;
    margin-bottom: 20px;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #2a2a2a;
}

/* Add this wrapper around your tables in the template */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 15px;
}

.info-table th,
.info-table td {
    border: 1px solid #444;
    padding: 12px;
    text-align: center;
}

.info-table th {
    background-color: #333;
    color: #FE4454;
    font-weight: bold;
}

.info-table tr:nth-child(even) {
    background-color: #222;
}

.info-table tr:hover {
    background-color: #383838;
}

.kills {
    color: #4CAF50;
    font-weight: bold;
}

.deaths {
    color: #F44336;
    font-weight: bold;
}

.assists {
    color: #2196F3;
    font-weight: bold;
}

.kda {
    color: #FFC107;
    font-weight: bold;
}

.no-entrenador,
.no-jugadores {
    background-color: #2a2a2a;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    color: #999;
    font-style: italic;
    margin-top: 20px;
}

.entrenador-info,
.jugadores-info {
    margin-top: 30px;
}

/* Team Gallery Styles */
.team-gallery {
    margin-top: 30px;
}

.team-members {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
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
    width: 140px;
    transition: transform 0.2s ease;
}

.team-member:hover {
    transform: translateY(-5px);
}

.member-photo {
    position: relative;
    margin-bottom: 10px;
}

.member-photo img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #333;
    transition: all 0.3s ease;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
}

.coach .member-photo img {
    border-color: #FE4454;
    width: 110px;
    height: 110px;
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

.coach-badge {
    background-color: #FE4454;
}

.player-badge {
    background-color: #4CAF50;
}

.member-name {
    font-size: 0.9rem;
    text-align: center;
    color: #fff;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-top: 8px;
    font-weight: 500;
}

.coach .member-name {
    color: #FE4454;
    font-weight: bold;
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

    .coach .member-photo img {
        width: 95px;
        height: 95px;
    }
}

@media (max-width: 480px) {
    .team-member {
        width: 100px;
    }

    .member-photo img,
    .coach .member-photo img {
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

/* Add these media queries for responsive tables */
@media (max-width: 768px) {
    .info-table th,
    .info-table td {
        padding: 8px;
        font-size: 0.9em;
    }

    .equipo-banner {
        width: 100%;
        flex-direction: column;
        text-align: center;
    }

    .equipo-image {
        margin-right: 0;
        margin-bottom: 15px;
    }

    .equipo-info {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .info-table th,
    .info-table td {
        padding: 6px;
        font-size: 0.8em;
    }

    /* Optional: Hide less important columns on very small screens */
    .jugadores-table th:nth-child(5),
    .jugadores-table td:nth-child(5),
    .jugadores-table th:nth-child(6),
    .jugadores-table td:nth-child(6),
    .jugadores-table th:nth-child(7),
    .jugadores-table td:nth-child(7) {
        display: none;
    }

    /* Keep KDA visible as it's a summary */
    .jugadores-table th:nth-child(8),
    .jugadores-table td:nth-child(8) {
        display: table-cell;
    }
}
</style>
