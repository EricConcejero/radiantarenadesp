<template>
  <div v-if="!xatInterfaceActive" class="xat-button-container">
    <a :href="xatRoute" class="xat-button" title="Abrir xat">
      <i class="fas fa-comments"></i>
      <span v-if="unreadCount > 0" class="badge badge-danger">{{ unreadCount }}</span>
    </a>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      unreadCount: 0,
      xatRoute: '/xat',
      userId: null,
      xatInterfaceActive: false, // New property to track if we're in xat interface
      intervalId: null
    }
  },
  mounted() {
    console.log('xat button component mounted');

    // Get user ID from meta tag
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta) {
      this.userId = userIdMeta.getAttribute('content');
      console.log('User ID from meta tag:', this.userId);
      this.getUnreadCount();

      // Create interval to check messages, but store the ID so it can be cleared if needed
      this.intervalId = setInterval(this.getUnreadCount, 30000);
    } else {
      console.error('User ID meta tag not found');
    }

    // Check if the current URL contains "xat" to determine if we're in the xat interface
    this.xatInterfaceActive = window.location.pathname.includes('/xat');

    // Listen for events from the xatComponent
    document.addEventListener('xat-interface-active', this.handlexatInterfaceStatus);
  },
  beforeUnmount() {
    // Clear interval when component is destroyed
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }

    // Remove event listener
    document.removeEventListener('xat-interface-active', this.handlexatInterfaceStatus);
  },
  methods: {
    handlexatInterfaceStatus(event) {
      this.xatInterfaceActive = event.detail;
      console.log('xat interface active:', this.xatInterfaceActive);
    },
    getUnreadCount() {
      if (!this.userId) {
        console.error('Cannot fetch unread count: No user ID');
        return;
      }

      // Create a direct axios instance without the base URL
      const http = axios.create({
        baseURL: ''
      });

      // Use the direct path without the API prefix
      http.get('/xat/unread-count')
        .then(response => {
          console.log('Unread count response:', response.data);
          this.unreadCount = response.data.count;
        })
        .catch(error => {
          console.error('Error fetching unread messages count:', error);
          // Add better error logging
          if (error.response) {
            console.error('Response data:', error.response.data);
          }
        });
    }
  }
}
</script>

<style scoped>
.xat-button-container {
  position: fixed;
  bottom: 30px;
  right: 30px;
  z-index: 999;
}

.xat-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: #d32f3d;
  color: white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  text-decoration: none;
}

.xat-button:hover {
  background-color: #be2834;
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
  color: white;
}

.xat-button i {
  font-size: 24px;
}

.badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background-color: #dc3545;
  color: white;
  border-radius: 50%;
  min-width: 18px;
  height: 18px;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2px;
}
</style>
