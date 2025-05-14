<template>
    <div class="jugadores-container">
        <h1 class="top-heading">Top 5 Jugadores</h1>
        <div class="podium-container">
            <div class="podium-grid">
                <!-- Ordena y muestra los mejores jugadores en posiciones específicas para el efecto podio -->
                <div v-for="(jugador, index) in top5Jugadores" :key="jugador.id_jugador" :class="`podium-position position-${index}`">
                    <div class="podium-link" @click="verPerfilJugador(jugador.id_jugador)">
                        <div class="circle-image-container">
                            <img :src="getImageUrl(jugador.usuario?.imagen_usuario)"
                                alt="Imagen del Jugador"
                                class="circle-image"
                                @error="handleImageError"/>
                            <div class="rank-badge">{{ index + 1 }}</div>
                        </div>
                        <div class="podium-block">
                            <h3>{{ jugador.usuario?.username }}</h3>
                            <div class="jugador-info-minimalist">
                                <p class="jugador-rango">{{ jugador.rango_valorant }}</p>
                                <p class="jugador-tag">{{ roles[jugador.id_rol] }}</p>
                                <p class="jugador-kda">KDA: {{ calcularKDA(jugador) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h1>Jugadores</h1>

        <!-- Controles de filtro integrados -->
        <div class="filtros-container">
            <!-- Filtro por estado de equipo -->
            <div class="filtro-dropdown" :class="{ active: activeDropdown === 'equipo' }">
                <button class="filter-btn" @click="toggleDropdown('equipo')">
                    <i class="fas fa-users-cog"></i> Estado de equipo
                    <span v-if="cargando && filtrandoPor === 'estadoEquipo'" class="loading-indicator">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
                <div class="dropdown-content">
                    <a href="#" @click.prevent="aplicarFiltroEquipo('todos')"
                        :class="{ 'active': filtroEquipo === 'todos' }">
                        Todos los jugadores
                    </a>
                    <a href="#" @click.prevent="aplicarFiltroEquipo('sinEquipo')"
                        :class="{ 'active': filtroEquipo === 'sinEquipo' }">
                        Jugadores sin equipo
                    </a>
                    <a href="#" @click.prevent="aplicarFiltroEquipo('conEquipo')"
                        :class="{ 'active': filtroEquipo === 'conEquipo' }">
                        Jugadores con equipo
                    </a>
                </div>
            </div>

            <!-- Filtro por equipo específico -->
            <div class="filtro-dropdown" :class="{ active: activeDropdown === 'equipoEspecifico' }">
                <button class="filter-btn" @click="toggleDropdown('equipoEspecifico')">
                    <i class="fas fa-shield-alt"></i> Filtrar por equipo
                    <span v-if="cargandoEquipos || (cargando && filtrandoPor === 'equipoEspecifico')" class="loading-indicator">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
                <div class="dropdown-content equipos-list">
                    <a href="#" @click.prevent="aplicarFiltroEquipoEspecifico(null)"
                        :class="{ 'active': equipoSeleccionado === null }">
                        Todos los equipos
                    </a>
                    <template v-if="equipos.length > 0">
                        <a href="#" v-for="equipo in equipos" :key="equipo.id_equipos"
                            @click.prevent="aplicarFiltroEquipoEspecifico(equipo.id_equipos)"
                            :class="{ 'active': equipoSeleccionado === equipo.id_equipos }">
                            <span class="equipo-tag">[{{ equipo.tag }}]</span> {{ equipo.nombre_equipo }}
                        </a>
                    </template>
                    <a href="#" v-else-if="cargandoEquipos" class="disabled">
                        <i class="fas fa-spinner fa-spin"></i> Cargando equipos...
                    </a>
                    <a href="#" v-else class="disabled">No se encontraron equipos</a>
                </div>
            </div>

            <!-- Filtro por orden -->
            <div class="filtro-dropdown" :class="{ active: activeDropdown === 'orden' }">
                <button class="filter-btn" @click="toggleDropdown('orden')">
                    <i class="fas fa-sort"></i> Ordenar por
                </button>
                <div class="dropdown-content">
                    <a href="#" @click.prevent="aplicarOrdenamiento('kda')"
                        :class="{ 'active': criterioOrden === 'kda' }">
                        KDA (Mayor a menor)
                    </a>
                    <a href="#" @click.prevent="aplicarOrdenamiento('rango')"
                        :class="{ 'active': criterioOrden === 'rango' }">
                        Rango (Mayor a menor)
                    </a>
                    <a href="#" @click.prevent="aplicarOrdenamiento('rol')"
                        :class="{ 'active': criterioOrden === 'rol' }">
                        Rol
                    </a>
                </div>
            </div>

            <!-- Búsqueda -->
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Buscar jugador..." v-model="busqueda"
                       class="search-input" @input="aplicarBusqueda">
                <span v-if="cargando && filtrandoPor === 'busqueda'" class="search-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </div>
        </div>

        <!-- Indicadores de filtros activos -->
        <div class="active-filters" v-if="filtroEquipo !== 'todos' || equipoSeleccionado || criterioOrden || busqueda">
            <span>Filtros activos:</span>
            <div class="filter-tag" v-if="filtroEquipo === 'sinEquipo'">
                Sin equipo <i class="fas fa-times" @click="aplicarFiltroEquipo('todos')"></i>
            </div>
            <div class="filter-tag" v-if="filtroEquipo === 'conEquipo'">
                Con equipo <i class="fas fa-times" @click="aplicarFiltroEquipo('todos')"></i>
            </div>
            <div class="filter-tag" v-if="equipoSeleccionado">
                {{ equipoNombre || 'Equipo' }}
                <i class="fas fa-times" @click="aplicarFiltroEquipoEspecifico(null)"></i>
            </div>
            <div class="filter-tag" v-if="criterioOrden">
                Ordenando por: {{ criterioOrden }}
                <i class="fas fa-times" @click="aplicarOrdenamiento('')"></i>
            </div>
            <div class="filter-tag" v-if="busqueda">
                Búsqueda: "{{ busqueda }}"
                <i class="fas fa-times" @click="limpiarBusqueda"></i>
            </div>

            <p class="borrar-filtros" @click="borrarTodosLosFiltros">
                Borrar filtros
            </p>
        </div>

        <!-- Indicador de carga para filtros -->
        <div v-if="cargando" class="filtro-aplicado">
            <span class="loading-indicator">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </span>
        </div>

        <!-- Indicador de carga -->
        <div v-if="cargando && !jugadoresFiltrados.length" class="loading-container">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p>Cargando jugadores...</p>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <div v-else-if="!jugadoresFiltrados.length" class="no-resultados">
            <i class="fas fa-search"></i>
            <p>No se encontraron jugadores con los filtros seleccionados</p>
            <button class="btn-reiniciar" @click="borrarTodosLosFiltros">Reiniciar filtros</button>
        </div>

        <!-- Nueva visualización en formato de tarjetas -->
        <div v-else class="jugadores-grid">
            <div v-for="jugador in jugadoresFiltrados" :key="jugador.id_jugador" class="jugador-card-small">
                <div class="jugador-link"
                     @click="verPerfilJugador(jugador.id_jugador)"
                     :title="`Ver perfil de ${jugador.usuario.nombre_jugador || jugador.usuario.username}`">
                    <div class="jugador-imagen-container">
                        <img :src="getImageUrl(jugador.usuario.imagen_usuario)"
                             alt="Imagen del Jugador"
                             class="jugador-imagen-small"
                             @error="handleImageError" />
                        <div class="view-profile-overlay">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div v-if="usuario && usuario.tipo_usuario === 'entrenador'" class="jugador-status">
                            <div class="coach-actions">
                                <span v-if="!jugador.id_entrenador"
                                      class="disponible-badge"
                                      @click.stop="mostrarConfirmacion(jugador)"
                                      title="Agregar al equipo">
                                    <i class="fas fa-plus-circle"></i>
                                </span>
                                <span v-else class="ocupado-badge" title="Jugador ya tiene entrenador">
                                    <i class="fas fa-user-check"></i>
                                </span>
                                <span class="mensaje-badge"
                                      @click.stop="mostrarModalMensaje(jugador)"
                                      title="Enviar mensaje">
                                    <i class="fas fa-envelope"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <h3 class="jugador-nombre">{{ jugador.usuario.nombre_jugador || jugador.usuario.username }}</h3>
                    <p class="jugador-info-mini">{{ jugador.rango_valorant }} - {{ roles[jugador.id_rol] }}</p>
                    <span class="view-profile-text">Ver perfil</span>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación de contratación -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal-content">
                <h3>Confirmar Contratación</h3>
                <p>¿Estás seguro de que deseas agregar a {{ jugadorSeleccionado?.usuario?.nombre_jugador || jugadorSeleccionado?.usuario?.username || 'este jugador' }} a tu equipo?</p>
                <div class="modal-buttons">
                    <button @click="confirmarContratacion" class="btn-confirm">Confirmar</button>
                    <button @click="showModal = false" class="btn-cancel">Cancelar</button>
                </div>
            </div>
        </div>

        <!-- Modal para enviar mensaje -->
        <div v-if="showModalMensaje" class="modal-overlay">
            <div class="modal-content mensaje-modal">
                <h3>Enviar mensaje a {{ jugadorSeleccionado?.usuario?.nombre_jugador || jugadorSeleccionado?.usuario?.username }}</h3>
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
    name: 'JugadoresGeneral',
    props: {
        usuario: {
            type: Object,
            required: false,
            default: null
        }
    },
    data() {
        return {
            jugadores: [],               // Lista completa para el top 5
            jugadoresFiltrados: [],      // Lista filtrada para mostrar
            cargando: true,              // Indicador de carga
            filtrandoPor: '',            // Tipo de filtro aplicado
            busqueda: '',                // Texto de búsqueda
            busquedaTimeout: null,       // Temporizador para búsqueda
            filtroEquipo: 'todos',       // Estado de equipo (todos, conEquipo, sinEquipo)
            equipoSeleccionado: null,    // ID del equipo seleccionado
            equipoNombre: '',            // Nombre del equipo seleccionado
            criterioOrden: '',           // Criterio de ordenamiento
            activeDropdown: null,        // Dropdown activo actual
            equipos: [],                 // Lista de equipos
            cargandoEquipos: false,      // Indicador de carga de equipos

            // Existentes
            showModal: false,
            showModalMensaje: false,
            mensajeTexto: '',
            jugadorSeleccionado: null,
            roles: {
                1: "Duelista",
                2: "Iniciador",
                3: "Centinela",
                4: "Controlador"
            },
            rangosValoracion: {
                'Hierro': 1,
                'Bronce': 2,
                'Plata': 3,
                'Oro': 4,
                'Platino': 5,
                'Diamante': 6,
                'Ascendente': 7,
                'Inmortal': 8,
                'Radiante': 9
            },
            imagenPorDefecto: 'image_default.png',
            baseUrl: ''
        }
    },
    computed: {
        isEntrenador() {
            return this.usuario && this.usuario.tipo_usuario === 'entrenador';
        },
        mejoresJugadores() {
            return this.jugadores
                .map(jugador => ({
                    ...jugador,
                    playerScore: this.calcularPlayerScore(jugador)
                }))
                .sort((a, b) => b.playerScore - a.playerScore)
                .slice(0, 5);
        },
        top5Jugadores() {
            return this.mejoresJugadores;
        }
    },
    methods: {
        // Métodos de filtrado
        aplicarFiltroEquipo(estado) {
            if (this.filtroEquipo === estado) return;

            this.filtroEquipo = estado;
            this.closeDropdowns();
            this.fetchJugadoresPorEstadoEquipo(estado);
        },

        aplicarFiltroEquipoEspecifico(equipoId) {
            if (this.equipoSeleccionado === equipoId) return;

            this.equipoSeleccionado = equipoId;
            if (equipoId) {
                const equipo = this.equipos.find(e => e.id_equipos === equipoId);
                this.equipoNombre = equipo ? equipo.nombre_equipo : 'Equipo';
            } else {
                this.equipoNombre = '';
            }
            this.closeDropdowns();
            this.fetchJugadoresPorEquipoEspecifico(equipoId);
        },

        aplicarOrdenamiento(criterio) {
            if (this.criterioOrden === criterio) {
                this.criterioOrden = '';
            } else {
                this.criterioOrden = criterio;
            }
            this.closeDropdowns();
            this.ordenarJugadores();
        },

        aplicarBusqueda() {
            if (this.busquedaTimeout) {
                clearTimeout(this.busquedaTimeout);
            }

            this.busquedaTimeout = setTimeout(() => {
                if (this.busqueda && this.busqueda.length >= 2) {
                    this.fetchJugadoresPorBusqueda(this.busqueda);
                } else if (!this.busqueda) {
                    this.fetchJugadoresPorEstadoEquipo(this.filtroEquipo);
                }
            }, 500);
        },

        limpiarBusqueda() {
            this.busqueda = '';
            this.fetchJugadoresPorEstadoEquipo(this.filtroEquipo);
        },

        // Métodos para obtener datos de la API
        fetchJugadoresPorEstadoEquipo(estado) {
            this.cargando = true;
            this.filtrandoPor = 'estadoEquipo';

            const url = `${this.baseUrl}jugadores/filtro-equipo/${estado}`;
            console.log(`Filtrando jugadores por estado: ${url}`);

            axios.get(url)
                .then(response => {
                    console.log('Jugadores filtrados por estado:', response.data);
                    this.jugadoresFiltrados = response.data;
                    this.ordenarJugadores();
                })
                .catch(error => {
                    console.error('Error al filtrar jugadores por estado:', error);
                    this.mostrarMensaje('Error al filtrar jugadores. Por favor, intenta más tarde.', 'error');
                    this.jugadoresFiltrados = [];
                })
                .finally(() => {
                    this.cargando = false;
                });
        },

        fetchJugadoresPorEquipoEspecifico(equipoId) {
            this.cargando = true;
            this.filtrandoPor = 'equipoEspecifico';

            const url = `${this.baseUrl}jugadores/filtro-equipo-especifico/${equipoId || 'null'}`;
            console.log(`Filtrando jugadores por equipo: ${url}`);

            axios.get(url)
                .then(response => {
                    console.log('Jugadores filtrados por equipo específico:', response.data);
                    this.jugadoresFiltrados = response.data;
                    this.ordenarJugadores();
                })
                .catch(error => {
                    console.error('Error al filtrar jugadores por equipo:', error);
                    this.mostrarMensaje('Error al filtrar jugadores por equipo. Por favor, intenta más tarde.', 'error');
                    this.jugadoresFiltrados = [];
                })
                .finally(() => {
                    this.cargando = false;
                });
        },

        fetchJugadoresPorBusqueda(busqueda) {
            this.cargando = true;
            this.filtrandoPor = 'busqueda';

            const url = `${this.baseUrl}jugadores/filtro-busqueda/${encodeURIComponent(busqueda)}`;
            console.log(`Buscando jugadores: ${url}`);

            axios.get(url)
                .then(response => {
                    console.log('Jugadores encontrados por búsqueda:', response.data);
                    this.jugadoresFiltrados = response.data;
                    this.ordenarJugadores();
                })
                .catch(error => {
                    console.error('Error al buscar jugadores:', error);
                    this.mostrarMensaje('Error al buscar jugadores. Por favor, intenta más tarde.', 'error');
                    this.jugadoresFiltrados = [];
                })
                .finally(() => {
                    this.cargando = false;
                });
        },

        fetchEquipos() {
            this.cargandoEquipos = true;

            const url = `${this.baseUrl}equipos/lista`;
            console.log('Cargando equipos desde URL:', url);

            axios.get(url)
                .then(response => {
                    console.log('Equipos cargados:', response.data);
                    this.equipos = response.data;
                })
                .catch(error => {
                    console.error('Error al cargar equipos:', error);
                    this.mostrarMensaje('Error al cargar los equipos. Por favor, intenta más tarde.', 'error');
                })
                .finally(() => {
                    this.cargandoEquipos = false;
                });
        },

        //jugadores general
        fetchJugadores() {
            const url = `${this.baseUrl}mercadojugadores`;
            return axios.get(url)
                .then(response => {
                    console.log('Datos recibidos (jugadores completos):', response.data);
                    this.jugadores = response.data;
                    // No asignamos jugadoresFiltrados aquí, se hace mediante el filtro inicial
                    return response;
                })
                .catch(error => {
                    console.error('Error al obtener jugadores:', error);
                    return Promise.reject(error);
                });
        },

        // Métodos de utilidad
        ordenarJugadores() {
            if (!this.criterioOrden || !this.jugadoresFiltrados.length) return;

            let jugadoresOrdenados = [...this.jugadoresFiltrados];

            if (this.criterioOrden === 'kda') {
                jugadoresOrdenados.sort((a, b) => {
                    const kdaA = a.deaths === 0 ? (a.kills + a.assists) : (a.kills + a.assists) / a.deaths;
                    const kdaB = b.deaths === 0 ? (b.kills + b.assists) : (b.kills + b.assists) / b.deaths;
                    return kdaB - kdaA; // De mayor a menor
                });
            } else if (this.criterioOrden === 'rango') {
                const rangos = this.rangosValoracion;
                // Reordenamos para que el valor más alto (Radiante: 9) aparezca primero
                jugadoresOrdenados.sort((a, b) => {
                    // Obtener el valor numérico para cada rango
                    const valorRangoA = rangos[a.rango_valorant] || 0;
                    const valorRangoB = rangos[b.rango_valorant] || 0;

                    // Ordenar de mayor a menor (Radiante primero, Hierro último)
                    return valorRangoB - valorRangoA;
                });
            } else if (this.criterioOrden === 'rol') {
                jugadoresOrdenados.sort((a, b) => a.id_rol - b.id_rol);
            }

            this.jugadoresFiltrados = jugadoresOrdenados;
        },

        obtenerNombreFiltro() {
            switch (this.filtrandoPor) {
                case 'estadoEquipo':
                    return 'Estado de equipo';
                case 'equipoEspecifico':
                    return 'Equipo específico';
                case 'busqueda':
                    return 'Búsqueda';
                default:
                    return 'Filtro';
            }
        },

        borrarTodosLosFiltros() {
            this.filtroEquipo = 'todos';
            this.equipoSeleccionado = null;
            this.equipoNombre = '';
            this.criterioOrden = '';
            this.busqueda = '';
            this.fetchJugadoresPorEstadoEquipo('todos');
        },

        toggleDropdown(dropdownId) {
            if (this.activeDropdown === dropdownId) {
                this.activeDropdown = null;
            } else {
                this.activeDropdown = dropdownId;

                // Si estamos abriendo el dropdown de equipos y no se han cargado
                if (dropdownId === 'equipoEspecifico' && this.equipos.length === 0) {
                    this.fetchEquipos();
                }
            }
        },

        closeDropdowns() {
            this.activeDropdown = null;
        },

        // Métodos existentes
        calcularKDA(jugador) {
            if (!jugador.deaths || jugador.deaths === 0) {
                return 'Perfect';
            }
            return ((jugador.kills + jugador.assists) / jugador.deaths).toFixed(2);
        },

        calcularPlayerScore(jugador) {
            const kda = jugador.deaths === 0 ?
                (jugador.kills + jugador.assists) :
                (jugador.kills + jugador.assists) / jugador.deaths;

            const rangoValue = this.rangosValoracion[jugador.rango_valorant] || 1;

            const rolFactor = {
                1: 1.2, // Duelista
                2: 1.1, // Iniciador
                3: 1.0, // Centinela
                4: 1.1  // Controlador
            }[jugador.id_rol] || 1;

            return (kda * 0.4 + rangoValue * 0.4) * rolFactor;
        },

        mostrarConfirmacion(jugador) {
            this.jugadorSeleccionado = jugador;
            this.showModal = true;
        },

        confirmarContratacion() {
            if (!this.jugadorSeleccionado || !this.usuario) {
                this.mostrarMensaje('Error: No se puede realizar la operación', 'error');
                return;
            }

            axios.post('/contratar-jugador', {
                jugador_id: this.jugadorSeleccionado.id_jugador,
                entrenador_id: this.usuario.id_usuario
            })
            .then(response => {
                // Actualizar la lista de jugadores
                this.fetchJugadores();
                this.showModal = false;
                this.jugadorSeleccionado = null;
                this.mostrarMensaje('Jugador contratado exitosamente', 'success');
                // Refrescar la lista filtrada
                this.fetchJugadoresPorEstadoEquipo(this.filtroEquipo);
            })
            .catch(error => {
                console.error('Error al contratar jugador:', error);
                this.mostrarMensaje('Error al contratar jugador: ' + error.response?.data?.message || 'Error desconocido', 'error');
            });
        },

        // Nuevo método para mostrar modal de mensaje
        mostrarModalMensaje(jugador) {
            this.jugadorSeleccionado = jugador;
            this.mensajeTexto = '';
            this.showModalMensaje = true;
        },

        // Nuevo método para enviar mensaje
        enviarMensaje() {
            if (!this.mensajeTexto.trim()) {
                this.mostrarMensaje('Por favor, escribe un mensaje antes de enviar.', 'error');
                return;
            }

            if (!this.jugadorSeleccionado || !this.usuario) {
                this.mostrarMensaje('Error: No se puede realizar la operación', 'error');
                return;
            }

            axios.post('/enviar-mensaje', {
                destinatario_id: this.jugadorSeleccionado.usuario.id_usuario,
                remitente_id: this.usuario.id_usuario,
                contenido: this.mensajeTexto
            })
            .then(response => {
                this.showModalMensaje = false;
                this.jugadorSeleccionado = null;
                this.mensajeTexto = '';
                this.mostrarMensaje('Mensaje enviado exitosamente', 'success');
            })
            .catch(error => {
                console.error('Error al enviar mensaje:', error);
                this.mostrarMensaje('Error al enviar mensaje: ' + error.response?.data?.message || 'Error desconocido', 'error');
            });
        },

        mostrarMensaje(mensaje, tipo) {
            // Implementación simple
            alert(mensaje);
        },

        getImageUrl(imageName) {
            if (!imageName) return `${this.baseUrl}assets/usuarios/${this.imagenPorDefecto}`;
            return `${this.baseUrl}assets/usuarios/${imageName}`;
        },

        handleImageError(event) {
            event.target.src = `${this.baseUrl}assets/usuarios/${this.imagenPorDefecto}`;
        },

        verPerfilJugador(jugadorId) {
            const base = window.location.pathname.includes('/public/')
                ? window.location.pathname.split('/public/')[0] + '/public/'
                : '/';

            window.location.href = `${base}jugadores/${jugadorId}`;
        }
    },
    mounted() {
        // Detectar si estamos en producción o desarrollo
        const isProduction = window.location.hostname === 'radiantarena.ericconcejero.me';

        if (isProduction) {
            // URL de producción
            this.baseUrl = 'http://radiantarena.ericconcejero.me/';
        } else if (window.location.pathname.includes('/public/')) {
            // URL local con public
            this.baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/public/') + 8);
        } else {
            // URL local sin public
            this.baseUrl = window.location.origin + '/';
        }

        console.log('Using base URL:', this.baseUrl);

        // Cargar jugadores para el top 5
        this.fetchJugadores()
            .finally(() => {
                // Una vez cargados los jugadores para el top 5, cargar los filtrados
                this.fetchJugadoresPorEstadoEquipo('todos');
            });

        // Manejador para cerrar dropdowns al hacer clic fuera
        document.addEventListener('click', (event) => {
            const isClickInside = event.target.closest('.filtro-dropdown');
            if (!isClickInside && this.activeDropdown) {
                this.closeDropdowns();
            }
        });
    }
};
</script>

<style scoped>
/* Nuevos estilos para el podio */
.jugadores-container {
    padding: 10px 30px 30px; /* Reducido el padding superior */
    text-align: center;
    background-color: #1E1E1E;
    color: #ffffff;
    border-radius: 12px;
    margin: 10px 20px 20px; /* Reducido el margen superior */
    min-height: 100vh; /* Asegura que el contenedor ocupe al menos toda la altura visible */
    display: flex;
    flex-direction: column;
}

.top-heading {
    color: #ffffff;
    font-size: 2.2em;
    margin: 10px 0 10px; /* Reducido el margen inferior */
    border-bottom: 1px solid #FE4454;
    padding-bottom: 10px;
    text-align: center;
}

.podium-container {
    margin: 20px auto 40px; /* Reducido el margen superior e inferior */
    max-width: 1080px;
    padding-bottom: 20px; /* Reducido el padding inferior */
}

.podium-grid {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    height: auto; /* Changed from fixed height to auto */
    min-height: 300px; /* Minimum height */
    padding-top: 80px; /* Add padding at top to position circles */
    margin-bottom: 20px;
    position: relative;
    gap: 20px;
}

.podium-position {
    position: relative;
    margin: 0 10px;
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
}

.podium-position:hover {
    transform: translateY(-10px);
}

/* Posiciones específicas para 5 jugadores */
.position-0 {
    z-index: 5;
    order: 3;
}

.position-1 {
    z-index: 4;
    order: 2;
    margin-bottom: -20px;
}

.position-2 {
    z-index: 3;
    order: 4;
    margin-bottom: -40px;
}

.position-3 {
    z-index: 2;
    order: 1;
    margin-bottom: -60px;
}

.position-4 {
    z-index: 1;
    order: 5;
    margin-bottom: -80px;
}

.circle-image-container {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto -25px;
    z-index: 10;
}

.position-0 .circle-image-container {
    width: 130px;
    height: 130px;
    margin-bottom: -30px;
}

.position-1 .circle-image-container,
.position-2 .circle-image-container {
    width: 110px;
    height: 110px;
}

.circle-image {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #FE4454;
    box-shadow: 0 0 15px rgba(254, 68, 84, 0.5);
    background-color: #2a2a2a;
}

.rank-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 30px;
    height: 30px;
    background-color: #FE4454;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border: 2px solid #2a2a2a;
}

.position-0 .rank-badge {
    background-color: gold;
    color: #333;
    width: 35px;
    height: 35px;
}

.position-1 .rank-badge {
    background-color: silver;
    color: #333;
}

.position-2 .rank-badge {
    background-color: #cd7f32; /* Bronze */
}

.position-3 .rank-badge,
.position-4 .rank-badge {
    background-color: #555;
    color: white;
}

.podium-block {
    background: linear-gradient(135deg, #333 0%, #1E1E1E 100%);
    border-top: 3px solid #FE4454;
    border-radius: 8px 8px 0 0;
    width: 140px;
    padding: 25px 15px 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    min-height: 120px; /* Fixed minimum height */
    height: auto; /* Let it expand as needed */
}

.position-0 .podium-block {
    width: 180px;
    background: linear-gradient(135deg, #444 0%, #2a2a2a 100%);
    border-top-color: gold;
    min-height: 130px; /* Slightly taller for the winner */
}

.position-1 .podium-block,
.position-2 .podium-block {
    width: 160px;
    min-height: 115px;
}

.position-3 .podium-block,
.position-4 .podium-block {
    min-height: 100px;
}

.podium-block h3 {
    color: #FE4454;
    font-size: 1.1em;
    margin: 5px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.position-0 .podium-block h3 {
    color: gold;
}

.position-1 .podium-block h3 {
    color: silver;
}

.position-2 .podium-block h3 {
    color: #cd7f32;
}

.jugador-info-minimalist {
    text-align: center;
    font-size: 0.8em;
    color: #ccc;
    padding-bottom: 10px; /* Add padding at bottom */
}

.jugador-info-minimalist p {
    margin: 3px 0;
}

.podium-link {
    text-decoration: none;
    color: inherit;
    cursor: pointer;  /* Add cursor pointer to indicate clickable */
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

/* Estilos para la visualización de jugadores */
h1 {
    color: #ffffff;
    font-size: 2.2em;
    margin-bottom: 20px;
    border-bottom: 1px solid #FE4454;
    padding-bottom: 15px;
    text-align: center;
}

.jugadores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.jugador-card-small {
    border: 1px solid #444;
    border-radius: 10px;
    padding: 15px 10px;
    background-color: #2a2a2a;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.jugador-card-small:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(254, 68, 84, 0.3);
    border-color: #FE4454;
}

.jugador-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    cursor: pointer;
}

.jugador-imagen-container {
    position: relative;
    margin-bottom: 10px;
}

.jugador-imagen-small {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #444;
    transition: border-color 0.3s;
}

.jugador-card-small:hover .jugador-imagen-small {
    border-color: #FE4454;
}

.view-profile-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s;
    color: white;
}

.jugador-card-small:hover .view-profile-overlay {
    opacity: 1;
}

.jugador-nombre {
    color: #FE4454;
    font-size: 1.1em;
    margin: 5px 0;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
}

.jugador-info-mini {
    color: #ccc;
    font-size: 0.8em;
    margin: 3px 0;
    text-align: center;
}

.jugador-status {
    position: absolute;
    bottom: -5px;
    right: -5px;
}

.coach-actions {
    display: flex;
    gap: 5px;
}

.disponible-badge, .ocupado-badge, .mensaje-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.2s;
    border: 2px solid #2a2a2a;
}

.disponible-badge {
    background-color: #4CAF50;
    color: white;
}

.mensaje-badge {
    background-color: #2196F3;
    color: white;
}

.disponible-badge:hover, .mensaje-badge:hover {
    transform: scale(1.2);
}

.ocupado-badge {
    background-color: #666;
    color: white;
    cursor: not-allowed;
}

.view-profile-text {
    color: #FE4454;
    font-size: 0.85em;
    opacity: 0;
    transition: opacity 0.3s;
    margin-top: 5px;
}

.jugador-card-small:hover .view-profile-text {
    opacity: 1;
}

/* Responsive styles */
@media (max-width: 992px) {
    .podium-grid {
        gap: 10px;
    }

    .podium-position {
        margin: 0 5px;
    }

    .podium-block {
        width: 130px;
    }

    .position-0 .podium-block {
        width: 160px;
    }

    .position-1 .podium-block,
    .position-2 .podium-block {
        width: 140px;
    }
}

@media (max-width: 768px) {
    .podium-grid {
        flex-wrap: wrap;
        height: auto;
        align-items: center;
    }

    .podium-position {
        margin: 30px 15px;
        order: 0 !important;
    }

    /* Reset margins for mobile */
    .position-1,
    .position-2,
    .position-3,
    .position-4 {
        margin-bottom: 0;
    }

    /* Show in order of rank on mobile */
    .position-0 {
        width: 100%;
        max-width: 280px;
        order: 1 !important;
    }

    .position-1 {
        order: 2 !important;
    }

    .position-2 {
        order: 3 !important;
    }

    .position-3 {
        order: 4 !important;
    }

    .position-4 {
        order: 5 !important;
    }

    .jugadores-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 15px;
    }

    .jugador-imagen-small {
        width: 60px;
        height: 60px;
    }

    .jugador-nombre {
        font-size: 0.9em;
    }

    .jugador-info-mini {
        font-size: 0.75em;
    }
}

/* Estilos para el indicador de carga */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 50px 0;
    color: #FE4454;
}

.loading-container p {
    margin-top: 15px;
    color: #ccc;
}

.no-resultados {
    background-color: #2a2a2a;
    padding: 30px;
    border-radius: 8px;
    text-align: center;
    margin: 30px 0;
}

.no-resultados i {
    font-size: 3em;
    color: #FE4454;
    margin-bottom: 15px;
}

.no-resultados p {
    color: #ccc;
    font-size: 1.2em;
    margin-bottom: 20px;
}

.btn-reiniciar {
    background-color: #FE4454;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-reiniciar:hover {
    background-color: #ff6b7a;
}

/* Estilos para el indicador de filtro aplicado */
.filtro-aplicado {
    background-color: #333;
    padding: 10px 15px;
    border-radius: 5px;
    margin: 15px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Estilos para los filtros incorporados del componente Filtros.vue */
.filtros-container {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin: 20px 0;
  justify-content: flex-start;
  align-items: center;
  padding: 0 10px;
}

.filtro-dropdown {
  position: relative;
  display: inline-block;
}

.filter-btn {
  background-color: #333;
  color: white;
  border: 1px solid #FE4454;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-btn:hover {
  background-color: #444;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #2a2a2a;
  min-width: 200px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
  z-index: 50;
  border-radius: 5px;
  overflow: hidden;
  border: 1px solid #444;
  max-height: 300px;
  overflow-y: auto;
  scrollbar-width: thin; /* For Firefox */
  scrollbar-color: #FE4454 #333; /* For Firefox */
}

/* WebKit browsers (Chrome, Safari, Edge) */
.dropdown-content::-webkit-scrollbar {
  width: 8px;
}

.dropdown-content::-webkit-scrollbar-track {
  background: #333;
  border-radius: 10px;
}

.dropdown-content::-webkit-scrollbar-thumb {
  background: #FE4454;
  border-radius: 10px;
  border: 2px solid #333;
}

.dropdown-content::-webkit-scrollbar-thumb:hover {
  background: #ff6b7a;
}

.filtro-dropdown.active .dropdown-content {
  display: block;
}

.dropdown-content a {
  color: #ccc;
  padding: 10px 15px;
  text-decoration: none;
  display: block;
  transition: background-color 0.2s;
  text-align: left;
  border-bottom: 1px solid #444;
}

.dropdown-content a:last-child {
  border-bottom: none;
}

.dropdown-content a:hover {
  background-color: #333;
  color: #FE4454;
}

.dropdown-content a.active {
  background-color: #FE4454;
  color: white;
}

.dropdown-content a.disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

.equipos-list {
  max-height: 250px;
  scrollbar-width: thin;
  scrollbar-color: #FE4454 #333;
}

.search-container {
  position: relative;
  flex-grow: 1;
  max-width: 300px;
}

.search-input {
  width: 100%;
  padding: 8px 15px 8px 35px;
  border: 1px solid #444;
  background-color: #2a2a2a;
  color: white;
  border-radius: 5px;
}

.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #777;
}

.active-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 15px 0;
  align-items: center;
}

.filter-tag {
  background-color: #333;
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid #FE4454;
}

.filter-tag i {
  cursor: pointer;
}

.filter-tag i:hover {
  color: #FE4454;
}

.equipo-tag {
  color: #FE4454;
}

.loading-indicator {
  margin-left: 5px;
  font-size: 0.8em;
}

.dropdown-content a i {
  margin-right: 5px;
}

@media (max-width: 768px) {
  .filtros-container {
    flex-direction: column;
    align-items: stretch;
  }

  .search-container {
    max-width: 100%;
  }

  .filtro-dropdown {
    width: 100%;
  }
}

/* Añadimos un estilo para el botón de "Borrar filtros" */
.borrar-filtros {
  color: #FE4454;
  text-decoration: underline;
  cursor: pointer;
  margin-left: auto;
}

.borrar-filtros:hover {
  color: #ff6b7a;
}

/* Añadir estilos para el indicador de carga en la búsqueda */
.search-loading {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #777;
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
