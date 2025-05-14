<template>
  <div class="xat-container">
    <div class="xat-sidebar">
      <h3>Conversaciones</h3>
      <ul class="conversation-list">
        <li v-for="conv in conversaciones" :key="conv.id_conversacion"
            @click="cargarConversacion(conv.id_conversacion)"
            :class="{ active: selectedConversationId === conv.id_conversacion }">
          <div class="conversation-info">
            <span class="conversation-title">
              {{ conv.titulo || getConversationTitle(conv) }}
            </span>
            <span class="conversation-last-message" v-if="conv.ultimoMensaje">
              {{ conv.ultimoMensaje.contenido }}
            </span>
          </div>
        </li>
      </ul>
      <a href="/xat/create" class="btn btn-primary btn-sm mt-3">Nueva conversación</a>
    </div>

    <div class="xat-main" v-if="conversacionActual">
      <div class="xat-header">
        <h4>{{ conversacionActual.titulo || getConversationTitle(conversacionActual) }}</h4>
      </div>

      <div class="xat-messages" ref="messageContainer">
        <div v-for="mensaje in conversacionActual.mensajes" :key="mensaje.id_mensaje"
             :class="['message', isOwnMessage(mensaje) ? 'message-own' : 'message-other']">
          <div class="message-avatar" v-if="!isOwnMessage(mensaje)">
            <img :src="`assets/usuarios/${mensaje.usuario.imagen_usuario}`" alt="Avatar">
          </div>
          <div class="message-content">
            <div class="message-header" v-if="!isOwnMessage(mensaje)">
              <span class="message-user">{{ mensaje.usuario.username }}</span>
            </div>
            <div class="message-text">{{ mensaje.contenido }}</div>
            <div class="message-time">{{ formatDate(mensaje.creado_en) }}</div>
          </div>
        </div>
      </div>

      <div class="xat-input">
        <form @submit.prevent="enviarMensaje">
          <div class="input-group">
            <input type="text" v-model="nuevoMensaje" class="form-control" placeholder="Escribe un mensaje...">
            <button type="submit" class="btn btn-primary">Enviar</button>
          </div>
        </form>
      </div>
    </div>

    <div class="xat-placeholder" v-else>
      <p>Selecciona una conversación para comenzar a xatear</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    initialConversationId: {
      type: [Number, String],
      default: null
    }
  },
  data() {
    return {
      usuarioActual: null,
      conversaciones: [],
      conversacionActual: null,
      selectedConversationId: null,
      nuevoMensaje: '',
      loading: false,
      // Create a direct axios instance without the base URL
      http: axios.create({
        baseURL: ''
      })
    };
  },
  created() {
    // Obtener el ID del usuario actual del meta tag
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta) {
      this.usuarioActual = userIdMeta.getAttribute('content');
      // Convert to number for consistent comparison
      this.usuarioActual = parseInt(this.usuarioActual);
      console.log('User ID found (as number):', this.usuarioActual);
    } else {
      console.error('User ID meta tag not found');
    }

    this.cargarConversaciones();

    // Si nos pasaron un ID inicial, cargar esa conversación
    if (this.initialConversationId) {
      this.selectedConversationId = parseInt(this.initialConversationId);
      this.cargarConversacion(this.selectedConversationId);
    }
  },
  mounted() {
    // Notify the app that we're in the xat interface
    document.dispatchEvent(new CustomEvent('xat-interface-active', { detail: true }));

    // Add an event listener to handle route changes or page refreshes
    window.addEventListener('beforeunload', this.handlePageLeave);
  },
  beforeDestroy() {
    // Notify the app that we're leaving the xat interface
    document.dispatchEvent(new CustomEvent('xat-interface-active', { detail: false }));

    // Remove event listener
    window.removeEventListener('beforeunload', this.handlePageLeave);
  },
  methods: {
    handlePageLeave() {
      // Reset the xat interface status when leaving
      document.dispatchEvent(new CustomEvent('xat-interface-active', { detail: false }));
    },
    isOwnMessage(mensaje) {
      // For debugging
      console.log(`Comparing message user ID: ${mensaje.id_usuario} (${typeof mensaje.id_usuario}) with current user ID: ${this.usuarioActual} (${typeof this.usuarioActual})`);
      // Ensure consistent comparison by converting both to numbers
      return parseInt(mensaje.id_usuario) === this.usuarioActual;
    },
    cargarConversaciones() {
      // Use direct http instance with correct URL
      this.http.get('xat/conversaciones')
        .then(response => {
          console.log('Conversaciones cargadas:', response.data);
          this.conversaciones = response.data;
        })
        .catch(error => {
          console.error('Error cargando conversaciones:', error);
          if (error.response) {
            console.error('Error details:', error.response.data);
          }
        });
    },
    cargarConversacion(id) {
      this.loading = true;
      this.selectedConversationId = id;

      // Use direct http instance with correct URL
      this.http.get(`xat/${id}/mensajes`)
        .then(response => {
          console.log('Conversación cargada:', response.data);
          this.conversacionActual = response.data;
          this.loading = false;
          this.$nextTick(() => {
            this.scrollToBottom();
          });
        })
        .catch(error => {
          console.error('Error cargando mensajes:', error);
          if (error.response) {
            console.error('Error details:', error.response.data);
          }
          this.loading = false;
        });
    },
    enviarMensaje() {
      if (!this.nuevoMensaje.trim() || !this.selectedConversationId) return;

      // Use direct http instance with correct URL
      this.http.post(`/xat/${this.selectedConversationId}/enviar`, {
        mensaje: this.nuevoMensaje
      })
        .then(response => {
          console.log('Mensaje enviado:', response.data);
          if (response.data.success) {
            if (!this.conversacionActual.mensajes) {
              this.conversacionActual.mensajes = [];
            }
            this.conversacionActual.mensajes.push(response.data.mensaje);
            this.nuevoMensaje = '';
            this.$nextTick(() => {
              this.scrollToBottom();
            });
          }
        })
        .catch(error => {
          console.error('Error enviando mensaje:', error);
          if (error.response) {
            console.error('Error details:', error.response.data);
          }
        });
    },
    scrollToBottom() {
      if (this.$refs.messageContainer) {
        this.$refs.messageContainer.scrollTop = this.$refs.messageContainer.scrollHeight;
      }
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleString();
    },
    getConversationTitle(conversation) {
      // Si no hay título, mostrar los nombres de los participantes excepto el usuario actual
      if (!conversation.usuarios) return 'xat';

      return conversation.usuarios
        .filter(u => u.id_usuario != this.usuarioActual)
        .map(u => u.username || u.nombre)
        .join(', ');
    }
  },
};
</script>

<style>
/* Component-specific styles that don't rely on external CSS */
.xat-container {
  display: flex;
  height: 80vh;
  border: none;
  border-radius: 12px;
  overflow: hidden;
  background-color: #1E1E1E;
  color: #ffffff;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  margin: 20px auto;
  max-width: 1200px;
}

.xat-sidebar {
  width: 30%;
  background-color: #2a2a2a;
  border-right: 1px solid #333;
  padding: 1.2rem;
  overflow-y: auto;
}

.xat-sidebar h3 {
  color: #FE4454;
  font-size: 1.3rem;
  margin-bottom: 1.2rem;
  padding-bottom: 0.8rem;
  border-bottom: 1px solid #444;
  font-weight: bold;
}

.conversation-list {
  list-style-type: none;
  padding: 0;
  margin: 0;
  overflow-y: auto;
  flex-grow: 1;
}

.conversation-list li {
  padding: 12px;
  margin-bottom: 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: #333;
  border-left: 3px solid transparent;
  list-style: none;
}

.conversation-list li:hover {
  background-color: #3a3a3a;
  transform: translateY(-2px);
}

.conversation-list li.active {
  background-color: rgba(254, 68, 84, 0.15);
  border-left: 3px solid #FE4454;
}

.conversation-info {
  display: flex;
  flex-direction: column;
}

.conversation-title {
  font-weight: bold;
  color: #fff;
  margin-bottom: 4px;
  font-size: 1rem;
}

.conversation-last-message {
  font-size: 0.8rem;
  color: #aaa;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.btn-primary, .btn-sm, .mt-3, a.btn {
  background-color: #FE4454;
  border: none;
  padding: 8px 16px;
  border-radius: 5px;
  font-weight: bold;
  transition: background-color 0.3s, transform 0.2s;
  box-shadow: 0 4px 10px rgba(254, 68, 84, 0.2);
  color: white;
  display: inline-block;
  text-align: center;
  text-decoration: none;
  margin-top: 10px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-primary:hover, .btn-sm:hover, .mt-3:hover, a.btn:hover {
  background-color: #d32f3d;
  transform: translateY(-2px);
  color: white;
  text-decoration: none;
}

.xat-main {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  background-color: #1a2733;
}

.xat-header {
  padding: 16px;
  background-color: #202a36;
  border-bottom: 1px solid #333;
  text-align: left;
}

.xat-header h4 {
  color: #FE4454;
  margin: 0;
  font-size: 1.2rem;
}

.xat-messages {
  flex-grow: 1;
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  background-color: #1E1E1E;
}

.message {
  margin-bottom: 20px;
  display: flex;
  max-width: 80%;
  position: relative;
}

.message-own {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.message-other {
  align-self: flex-start;
}

.message-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  margin: 0 10px;
  overflow: hidden;
  border: 2px solid #333;
  flex-shrink: 0;
  background-color: #444;
}

.message-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Simplify message styling - no tails/bubbles, just colored boxes */
.message-content {
  padding: 12px 15px;
  border-radius: 16px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
  min-width: 100px;
  position: relative;
}

/* Other people's messages */
.message-other .message-content {
  background-color: #333;
  border: 1px solid #444;
  border-top-left-radius: 4px;
}

/* Your messages */
.message-own .message-content {
  background-color: rgba(254, 68, 84, 0.8);
  color: white;
  border-top-right-radius: 4px;
}

/* Remove all previous pseudo-elements that were causing issues */
.message::before, .message::after,
.message-content::before, .message-content::after {
  display: none;
}

.message-header {
  margin-bottom: 5px;
  font-weight: bold;
  color: #FE4454;
}

.message-text {
  line-height: 1.4;
  word-break: break-word;
  color: inherit;
}

.message-own .message-text {
  color: white;
  font-weight: 500;
}

.message-other .message-text {
  color: #fff;
}

.message-time {
  font-size: 0.7rem;
  text-align: right;
  margin-top: 5px;
  opacity: 0.7;
}

.message-own .message-time {
  color: rgba(255, 255, 255, 0.9);
}

.message-other .message-time {
  color: #999;
}

.xat-input {
  padding: 16px;
  background-color: #202a36;
  border-top: 1px solid #333;
}

.input-group {
  display: flex;
  gap: 8px;
}

.xat-input input, .form-control {
  background-color: #333;
  border: 1px solid #444;
  color: #fff;
  padding: 10px 15px;
  border-radius: 20px;
  flex-grow: 1;
  transition: border 0.3s ease;
  width: 100%;
  box-sizing: border-box;
  font-size: 0.9rem;
}

.xat-input input:focus, .form-control:focus {
  outline: none;
  border-color: #FE4454;
  box-shadow: 0 0 0 1px rgba(254, 68, 84, 0.3);
}

.xat-input button {
  border-radius: 20px;
  padding: 10px 20px;
}

.xat-placeholder {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #1a2733;
  color: #888;
  flex-direction: column;
  padding: 30px;
}

.xat-placeholder p {
  font-size: 1.2rem;
  margin-bottom: 15px;
  color: #888;
}

@media (max-width: 768px) {
  .xat-container {
    flex-direction: column;
    height: 90vh;
    margin: 10px;
  }

  .xat-sidebar {
    width: 100%;
    max-height: 30%;
    padding: 10px;
  }

  .conversation-list li {
    padding: 8px;
    margin-bottom: 5px;
  }

  .message {
    max-width: 90%;
  }

  .xat-header, .xat-input {
    padding: 10px;
  }

  .xat-messages {
    padding: 15px;
  }

  .btn-primary, .btn-sm, .mt-3, a.btn {
    padding: 6px 12px;
    font-size: 0.8rem;
  }
}
</style>
