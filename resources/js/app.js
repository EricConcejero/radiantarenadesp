import './bootstrap';

import {createApp} from 'vue';
import Torneos from './components/Torneos.vue';
import IndividualTorneo from './components/IndividualTorneo.vue';
import Equipos from './components/Equipos.vue';
import Jugadores from './components/Jugadores.vue';
import Bracket from './components/Bracket.vue';
import JugadoresGeneral from './components/JugadoresGeneral.vue';
import UserProfile from './components/UserProfile.vue';
import xatComponent from './components/xatComponent.vue';
import xatButton from './components/xatButton.vue';
import JugadorIndividual from './components/JugadorIndividual.vue';
import Filtros from './components/Filtros.vue';
import axios from 'axios';

axios.defaults.baseURL = import.meta.env.VITE_APP_URL;

const app = createApp({});

    app.component('torneos', Torneos);
    app.component('individual-torneo', IndividualTorneo);
    app.component('equipos', Equipos);
    app.component('jugadores', Jugadores);
    app.component('bracket', Bracket);
    app.component('jugadores-general', JugadoresGeneral);
    app.component('user-profile', UserProfile);
    app.component('xat-component', xatComponent);
    app.component('xat-button', xatButton);
    app.component('jugador-individual', JugadorIndividual);
    app.component('filtros', Filtros);

    app.mount('#app');

