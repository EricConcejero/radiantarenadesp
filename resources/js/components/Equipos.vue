<template>
    <div class="equipos-container">
        <h1>Equipos</h1>
        <div class="equipos-grid">
            <div v-for="equipo in equipos" :key="equipo.id_equipos" class="equipo-card">
                <a :href="`equipos/${equipo.id_equipos}`">
                    <img :src="`assets/equipos/${equipo.logo}`" alt="Imagen del Equipo" class="equipo-image"/>
                    <h2>{{ equipo.nombre_equipo }} <span class="equipo-tag">[{{ equipo.tag }}]</span></h2>
                    <div class="equipo-info">
                        <p class="equipo-region">Región: {{ equipo.region }}</p>
                        <p class="equipo-experiencia">Experiencia: {{ equipo.palmares }} años</p>
                    </div>
                    <div class="ver-mas">Ver detalles</div>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'Equipos',
    data() {
        return {
            equipos: [],
        };
    },
    methods: {
        fetchEquipos() {
            axios.get('equipos')
                .then(response => {
                    this.equipos = response.data;
                })
                .catch(error => {
                    console.error('Error al obtener los equipos:', error);
                });
        },
    },
    mounted() {
        this.fetchEquipos(); // Llama a la API al montar el componente
    },
};
</script>

<style scoped>
.equipos-container {
    padding: 30px;
    text-align: center;
    background-color: #1E1E1E;
    color: #ffffff;
    border-radius: 12px;
    margin: 20px;
}

h1 {
    color: #ffffff;
    font-size: 2.2em;
    margin-bottom: 30px;
    border-bottom: 1px solid #FE4454;
    padding-bottom: 15px;
    text-align: center;
}

.equipos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.equipo-card {
    border: 1px solid #FE4454;
    border-radius: 8px;
    padding: 20px 15px;
    background-color: #2a2a2a;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.equipo-card:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(254, 68, 84, 0.3);
}

.equipo-image {
    width: 120px;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}

.equipo-card h2 {
    color: #FE4454;
    font-size: 1.4em;
    margin: 10px 0;
    text-align: center;
    width: 100%;
}

.equipo-tag {
    color: #ccc;
    font-size: 0.85em;
}

.equipo-info {
    margin-top: 10px;
    padding: 10px 0;
    width: 100%;
    border-top: 1px solid #444;
    border-bottom: 1px solid #444;
}

.equipo-region {
    font-style: italic;
    color: #ccc;
    margin: 5px 0;
    font-size: 0.95em;
}

.equipo-experiencia {
    color: #ddd;
    margin: 5px 0;
    font-size: 0.95em;
}

a {
    text-decoration: none;
    color: inherit;
    display: block;
    width: 100%;
    height: 100%;
}

.ver-mas {
    margin-top: 15px;
    color: #FE4454;
    font-size: 0.9em;
    text-decoration: underline;
    opacity: 0;
    transition: opacity 0.2s;
}

.equipo-card:hover .ver-mas {
    opacity: 1;
}

@media (max-width: 768px) {
    .equipos-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }

    .equipo-image {
        width: 100px;
        height: 120px;
    }
}
</style>
