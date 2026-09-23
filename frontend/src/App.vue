<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, RouterView } from 'vue-router'
import { getBaseApiUrl } from './services/api'
import './assets/main.css'

const isDarkMode = ref(false)
const apiUrl = ref('')
try {
  apiUrl.value = getBaseApiUrl()
} catch {
  apiUrl.value = 'VITE_API_URL belum diset'
}

interface Toast {
  id: number
  message: string
  type: 'success' | 'danger'
}

const toasts = ref<Toast[]>([])
let toastIdCounter = 0

function showToast(message: string, type: 'success' | 'danger' = 'success') {
  const id = ++toastIdCounter
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }, 3500)
}

function removeToast(id: number) {
  toasts.value = toasts.value.filter((t) => t.id !== id)
}

function toggleDarkMode() {
  isDarkMode.value = !isDarkMode.value
  if (isDarkMode.value) {
    document.body.classList.add('dark-mode')
    localStorage.setItem('theme', 'dark')
  } else {
    document.body.classList.remove('dark-mode')
    localStorage.setItem('theme', 'light')
  }
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  if (
    savedTheme === 'dark' ||
    (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)
  ) {
    isDarkMode.value = true
    document.body.classList.add('dark-mode')
  }
})
</script>

<template>
  <div class="app-container d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <header class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
      <div class="container-fluid px-4">
        <!-- Brand -->
        <RouterLink to="/" class="navbar-brand d-flex align-items-center fw-bold text-danger me-4">
          <i class="bi bi-link-45deg fs-3 me-2"></i>
          <span>Shortener<span class="text-dark">App</span></span>
        </RouterLink>

        <!-- Navbar Links -->
        <div class="navbar-nav me-auto flex-row gap-3">
          <RouterLink
            to="/"
            class="nav-link px-2 fw-semibold d-flex align-items-center"
            exact-active-class="text-danger border-bottom border-danger border-2"
          >
            <i class="bi bi-list-ul me-1"></i>
            Semua Link
          </RouterLink>
          <RouterLink
            to="/quick-shorten"
            class="nav-link px-2 fw-semibold d-flex align-items-center"
            exact-active-class="text-danger border-bottom border-danger border-2"
          >
            <i class="bi bi-lightning-charge me-1"></i>
            Quick Shorten & Stats
          </RouterLink>
        </div>

        <!-- Right Side: API Endpoint badge & Dark Mode -->
        <div class="d-flex align-items-center gap-3">
          <!-- API Indicator Badge -->
          <span
            class="badge bg-light text-secondary border d-none d-md-inline-flex align-items-center font-monospace py-2 px-3"
            title="API Endpoint terkonfigurasi dari VITE_API_URL"
          >
            <span class="p-1 bg-success rounded-circle me-2"></span>
            API: {{ apiUrl }}
          </span>

          <!-- Dark Mode Toggle Button -->
          <button
            class="btn btn-light rounded-3"
            type="button"
            :title="isDarkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
            @click="toggleDarkMode"
          >
            <i class="bi" :class="isDarkMode ? 'bi-moon-stars-fill text-warning' : 'bi-brightness-high text-warning'"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="container-fluid px-4 py-4 flex-grow-1">
      <RouterView @notify="showToast" />
    </main>

    <!-- Footer -->
    <footer class="border-top py-3 text-center text-muted small bg-white mt-auto">
      <div class="container-fluid px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
        <div>
          &copy; 2026 Shortener App. Powered by <strong>Vue 3</strong> & <strong>Laravel 11 API</strong>.
        </div>
        <div>
          Endpoint: <code class="text-danger">{{ apiUrl }}</code>
        </div>
      </div>
    </footer>

    <!-- Global Toast Container -->
    <div
      class="toast-container position-fixed bottom-0 end-0 p-3"
      style="z-index: 1100"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast show shadow-lg border-0 mb-2"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div
          class="toast-header text-white"
          :class="toast.type === 'danger' ? 'bg-danger' : 'bg-success'"
        >
          <i
            class="bi me-2"
            :class="toast.type === 'danger' ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill'"
          ></i>
          <strong class="me-auto">
            {{ toast.type === 'danger' ? 'Pemberitahuan Error' : 'Berhasil' }}
          </strong>
          <small class="text-white-50">Baru saja</small>
          <button
            type="button"
            class="btn-close btn-close-white"
            aria-label="Close"
            @click="removeToast(toast.id)"
          ></button>
        </div>
        <div class="toast-body bg-white text-dark">
          {{ toast.message }}
        </div>
      </div>
    </div>
  </div>
</template>
