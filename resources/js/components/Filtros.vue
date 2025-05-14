<template>
  <div>
    <div class="filtros-container">
      <!-- Filtro por estado de equipo -->
      <div class="filtro-dropdown" :class="{ active: activeDropdown === 'equipo' }">
        <button class="filter-btn" @click="toggleDropdown('equipo')">
          <i class="fas fa-users-cog"></i> Estado de equipo
          <span v-if="cargandoJugadores && filtrandoPor === 'estadoEquipo'" class="loading-indicator">
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
          <span v-if="cargandoEquipos || (cargandoJugadores && filtrandoPor === 'equipoEspecifico')" class="loading-indicator">
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
        <input type="text" placeholder="Buscar jugador..." v-model="busquedaLocal" class="search-input" @input="aplicarBusqueda">
        <span v-if="cargandoJugadores && filtrandoPor === 'busqueda'" class="search-loading">
          <i class="fas fa-spinner fa-spin"></i>
        </span>
      </div>
    </div>

    <!-- Indicadores de filtros activos -->
    <div class="active-filters" v-if="filtroEquipo !== 'todos' || equipoSeleccionado || criterioOrden || busquedaLocal">
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
        Ordenando por: {{ criterioOrden }} <i class="fas fa-times" @click="aplicarOrdenamiento('')"></i>
      </div>
      <div class="filter-tag" v-if="busquedaLocal">
        Búsqueda: "{{ busquedaLocal }}" <i class="fas fa-times" @click="limpiarBusqueda"></i>
      </div>

      <p class="borrar-filtros" @click="borrarTodosLosFiltros">
        Borrar filtros
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Filtros',
  props: {
    resultado: {
      type: Array,
      default: () => []
    },
    jugadores: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      filtroEquipo: 'todos',
      equipoSeleccionado: null,
      equipoNombre: '',
      criterioOrden: '',
      busquedaLocal: '',
      equipos: [],
      cargandoEquipos: false,
      cargandoJugadores: false,
      filtrandoPor: '',
      activeDropdown: null,
      baseUrl: '',
      jugadoresFiltrados: [],
      busquedaTimeout: null
    }
  },
  methods: {
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
      // Si se hace clic en el mismo criterio, desactivarlo
      if (this.criterioOrden === criterio) {
        this.criterioOrden = '';
      } else {
        this.criterioOrden = criterio;
      }
      this.closeDropdowns();

      // Ordenar los jugadores actuales (sin hacer una nueva llamada a la API)
      this.ordenarJugadores();
    },

    aplicarBusqueda() {
      // Aplicar la búsqueda después de un tiempo de espera
      if (this.busquedaTimeout) {
        clearTimeout(this.busquedaTimeout);
      }

      this.busquedaTimeout = setTimeout(() => {
        if (this.busquedaLocal && this.busquedaLocal.length >= 2) {
          this.fetchJugadoresPorBusqueda(this.busquedaLocal);
        } else if (!this.busquedaLocal) {
          // Si se borra la búsqueda, volver al filtro de estado
          this.fetchJugadoresPorEstadoEquipo(this.filtroEquipo);
        }
      }, 500);
    },

    limpiarBusqueda() {
      this.busquedaLocal = '';
      // Volver al filtro de estado actual
      this.fetchJugadoresPorEstadoEquipo(this.filtroEquipo);
    },

    fetchJugadoresPorEstadoEquipo(estado) {
      this.cargandoJugadores = true;
      this.filtrandoPor = 'estadoEquipo';

      // Construir la URL base
      let baseUrl = this.getBaseUrl();

      const url = `${baseUrl}api/jugadores/filtro-equipo/${estado}`;
      console.log(`Filtrando jugadores por estado: ${url}`);

      axios.get(url)
        .then(response => {
          console.log('Jugadores filtrados por estado:', response.data);
          // Asignar los resultados, incluso si están vacíos
          this.jugadoresFiltrados = response.data;
          this.ordenarJugadores();
          this.emitirResultadosAComponentePadre();
        })
        .catch(error => {
          console.error('Error al filtrar jugadores por estado:', error);
          this.$emit('mostrarMensaje', 'Error al filtrar jugadores. Por favor, intenta más tarde.', 'error');
          // En caso de error, inicializar con array vacío para mostrar el mensaje de no resultados
          this.jugadoresFiltrados = [];
          this.emitirResultadosAComponentePadre();
        })
        .finally(() => {
          this.cargandoJugadores = false;
        });
    },

    fetchJugadoresPorEquipoEspecifico(equipoId) {
      this.cargandoJugadores = true;
      this.filtrandoPor = 'equipoEspecifico';

      // Construir la URL base
      let baseUrl = this.getBaseUrl();

      const url = `${baseUrl}api/jugadores/filtro-equipo-especifico/${equipoId || 'null'}`;
      console.log(`Filtrando jugadores por equipo: ${url}`);

      fetch(url)
        .then(response => {
          if (!response.ok) {
            throw new Error('Error en la respuesta de la API');
          }
          return response.json();
        })
        .then(data => {
          console.log('Jugadores filtrados por equipo específico:', data);
          this.jugadoresFiltrados = data;
          this.ordenarJugadores();
          this.emitirResultadosAComponentePadre();
        })
        .catch(error => {
          console.error('Error al filtrar jugadores por equipo:', error);
          this.$emit('mostrarMensaje', 'Error al filtrar jugadores por equipo. Por favor, intenta más tarde.', 'error');
        })
        .finally(() => {
          this.cargandoJugadores = false;
        });
    },

    fetchJugadoresPorBusqueda(busqueda) {
      this.cargandoJugadores = true;
      this.filtrandoPor = 'busqueda';

      // Construir la URL base
      let baseUrl = this.getBaseUrl();

      const url = `${baseUrl}api/jugadores/filtro-busqueda/${encodeURIComponent(busqueda)}`;
      console.log(`Buscando jugadores: ${url}`);

      // Usando una tercera forma de llamada HTTP
      const xhr = new XMLHttpRequest();
      xhr.open('GET', url, true);
      xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
          const data = JSON.parse(xhr.responseText);
          console.log('Jugadores encontrados por búsqueda:', data);
          this.jugadoresFiltrados = data;
          this.ordenarJugadores();
          this.emitirResultadosAComponentePadre();
        } else {
          console.error('Error al buscar jugadores:', xhr.statusText);
          this.$emit('mostrarMensaje', 'Error al buscar jugadores. Por favor, intenta más tarde.', 'error');
        }
        this.cargandoJugadores = false;
      };
      xhr.onerror = () => {
        console.error('Error de red al buscar jugadores');
        this.$emit('mostrarMensaje', 'Error de red al buscar jugadores. Por favor, intenta más tarde.', 'error');
        this.cargandoJugadores = false;
      };
      xhr.send();
    },

    ordenarJugadores() {
      if (!this.criterioOrden || !this.jugadoresFiltrados.length) return;

      let jugadoresOrdenados = [...this.jugadoresFiltrados];

      if (this.criterioOrden === 'kda') {
        jugadoresOrdenados.sort((a, b) => {
          const kdaA = a.deaths === 0 ? (a.kills + a.assists) : (a.kills + a.assists) / a.deaths;
          const kdaB = b.deaths === 0 ? (b.kills + b.assists) : (b.kills + b.assists) / b.deaths;
          return kdaB - kdaA;
        });
      } else if (this.criterioOrden === 'rango') {
        const rangos = {
          'Hierro': 1,
          'Bronce': 2,
          'Plata': 3,
          'Oro': 4,
          'Platino': 5,
          'Diamante': 6,
          'Ascendente': 7,
          'Inmortal': 8,
          'Radiante': 9
        };
        jugadoresOrdenados.sort((a, b) => rangos[b.rango_valorant] - rangos[a.rango_valorant]);
      } else if (this.criterioOrden === 'rol') {
        jugadoresOrdenados.sort((a, b) => a.id_rol - b.id_rol);
      }

      this.jugadoresFiltrados = jugadoresOrdenados;
    },

    emitirResultadosAComponentePadre() {
      console.log('Emitiendo resultados filtrados:', {
        jugadores: this.jugadoresFiltrados,
        criterioOrden: this.criterioOrden,
        filtroAplicado: this.filtrandoPor
      });

      this.$emit('jugadores-filtrados', {
        jugadores: this.jugadoresFiltrados,
        criterioOrden: this.criterioOrden,
        filtroAplicado: this.filtrandoPor
      });
    },

    borrarTodosLosFiltros() {
      this.filtroEquipo = 'todos';
      this.equipoSeleccionado = null;
      this.equipoNombre = '';
      this.criterioOrden = '';
      this.busquedaLocal = '';
      this.fetchJugadoresPorEstadoEquipo('todos');
    },

    toggleDropdown(dropdownId) {
      if (this.activeDropdown === dropdownId) {
        this.activeDropdown = null;
      } else {
        this.activeDropdown = dropdownId;

        // Si estamos abriendo el dropdown de equipos y no se han cargado aún
        if (dropdownId === 'equipoEspecifico' && this.equipos.length === 0) {
          this.fetchEquipos();
        }
      }
    },

    closeDropdowns() {
      this.activeDropdown = null;
    },

    fetchEquipos() {
      this.cargandoEquipos = true;

      // Usar el método getBaseUrl para obtener la URL base
      let baseUrl = this.getBaseUrl();

      // Endpoint para obtener la lista de equipos
      const url = `${baseUrl}equipos/lista`;
      console.log('Fetching equipos from URL:', url);

      axios.get(url)
        .then(response => {
          console.log('Equipos cargados:', response.data);
          this.equipos = response.data;
        })
        .catch(error => {
          console.error('Error al cargar equipos:', error);
          this.$emit('mostrarMensaje', 'Error al cargar los equipos. Por favor, intenta más tarde.', 'error');
        })
        .finally(() => {
          this.cargandoEquipos = false;
        });
    },

    getBaseUrl() {
      // Define base URL first - this needs to be consistent
      let baseUrl = '';
      if (window.location.pathname.includes('/public/')) {
        baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/public/') + 8);
      } else {
        baseUrl = window.location.origin + '/';
      }
      return baseUrl;
    }
  },
  mounted() {
    // Cargar todos los jugadores al inicio
    this.fetchJugadoresPorEstadoEquipo('todos');

    // Cargar la lista de equipos
    this.fetchEquipos();

    // Escuchar clics fuera de los dropdowns para cerrarlos
    document.addEventListener('click', (event) => {
      const isClickInside = event.target.closest('.filtro-dropdown');
      if (!isClickInside && this.activeDropdown) {
        this.closeDropdowns();
      }
    });

    // Guardar la URL base calculada
    this.baseUrl = this.getBaseUrl();
  }
}
</script>

<style scoped>
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
</style>
