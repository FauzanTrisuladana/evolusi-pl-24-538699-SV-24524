<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { linkApiService, ApiError } from '../services/api'
import type { LinkItem, LinkStats, ShortenLinkPayload } from '../types/link'

const emit = defineEmits<{
  (e: 'notify', message: string, type?: 'success' | 'danger'): void
}>()

const stats = ref<LinkStats>({
  active_links_count: 0,
  total_links_count: 0,
})
const loadingStats = ref(true)

const form = reactive<ShortenLinkPayload>({
  target_url: '',
  custom_alias: '',
})
const loadingShorten = ref(false)
const errors = reactive<Record<string, string[]>>({})
const generalError = ref('')

const recentShortened = ref<LinkItem[]>([])
const latestCreated = ref<LinkItem | null>(null)
const copied = ref(false)

async function fetchStats() {
  loadingStats.value = true
  try {
    const res = await linkApiService.getStats()
    stats.value = res.data
  } catch {
    // Graceful fallback
  } finally {
    loadingStats.value = false
  }
}

async function handleShorten() {
  generalError.value = ''
  Object.keys(errors).forEach((key) => delete errors[key])

  if (!form.target_url.trim()) {
    errors.target_url = ['URL tujuan wajib diisi.']
    return
  }

  loadingShorten.value = true
  try {
    const res = await linkApiService.shortenLink({
      target_url: form.target_url.trim(),
      custom_alias: form.custom_alias?.trim() || undefined,
    })

    latestCreated.value = res.data
    recentShortened.value.unshift(res.data)
    emit('notify', res.message || 'URL berhasil diperpendek!', 'success')

    // Reset form
    form.target_url = ''
    form.custom_alias = ''

    // Refresh stats
    fetchStats()
  } catch (err: unknown) {
    if (err instanceof ApiError && err.errors) {
      Object.assign(errors, err.errors)
    } else {
      generalError.value =
        err instanceof Error ? err.message : 'Gagal memperpendek link.'
    }
    emit('notify', generalError.value, 'danger')
  } finally {
    loadingShorten.value = false
  }
}

async function copyUrl(url: string) {
  try {
    await navigator.clipboard.writeText(url)
    copied.value = true
    emit('notify', 'Link berhasil disalin ke clipboard!', 'success')
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch {
    emit('notify', 'Gagal menyalin link.', 'danger')
  }
}

async function pasteFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (text) {
      form.target_url = text
    }
  } catch {
    // Clipboard permission denied or unavailable
  }
}

onMounted(() => {
  fetchStats()
})
</script>

<template>
  <div class="quick-shorten-view">
    <!-- Header -->
    <div class="text-center mb-5">
      <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold mb-2">
        <i class="bi bi-lightning-charge-fill me-1"></i>
        Instant URL Shortener
      </span>
      <h1 class="display-6 fw-bold mb-2">Perpendek Tautan dalam Hitungan Detik</h1>
      <p class="text-muted mx-auto" style="max-width: 600px;">
        Solusi cepat membuat tautan singkat tanpa perlu registrasi akun. Didukung oleh Laravel 11 REST API.
      </p>
    </div>

    <!-- Stats Cards Grid -->
    <div class="row g-3 mb-5">
      <!-- Total Links -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 stat-card h-100">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">Total Tautan</span>
              <h2 class="display-6 fw-bold mb-0 mt-1">
                <span v-if="loadingStats" class="spinner-border spinner-border-sm text-secondary"></span>
                <span v-else>{{ stats.total_links_count }}</span>
              </h2>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger">
              <i class="bi bi-link-45deg"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Links -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 stat-card h-100">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">Tautan Aktif</span>
              <h2 class="display-6 fw-bold mb-0 mt-1 text-success">
                <span v-if="loadingStats" class="spinner-border spinner-border-sm text-secondary"></span>
                <span v-else>{{ stats.active_links_count }}</span>
              </h2>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
              <i class="bi bi-check-circle"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Inactive Links -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 stat-card h-100">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">Tautan Tidak Aktif</span>
              <h2 class="display-6 fw-bold mb-0 mt-1 text-warning">
                <span v-if="loadingStats" class="spinner-border spinner-border-sm text-secondary"></span>
                <span v-else>{{ Math.max(0, stats.total_links_count - stats.active_links_count) }}</span>
              </h2>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning">
              <i class="bi bi-pause-circle"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Shorten Form Card -->
    <div class="card shadow-sm border-0 mb-5">
      <div class="card-body p-4 p-md-5">
        <h4 class="fw-bold mb-3">
          <i class="bi bi-magic text-danger me-2"></i>
          Perpendek URL Sekarang
        </h4>

        <div v-if="generalError" class="alert alert-danger mb-4">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ generalError }}
        </div>

        <form @submit.prevent="handleShorten">
          <!-- Target URL -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Masukkan URL Panjang</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text">
                <i class="bi bi-globe2 text-muted"></i>
              </span>
              <input
                v-model="form.target_url"
                type="url"
                class="form-control"
                :class="{ 'is-invalid': errors.target_url }"
                placeholder="https://example.com/artikel-panjang-banget-yang-mau-dipersingkat"
                :disabled="loadingShorten"
              />
              <button
                class="btn btn-outline-secondary"
                type="button"
                title="Tempel dari Clipboard"
                @click="pasteFromClipboard"
              >
                <i class="bi bi-clipboard-plus"></i>
                <span class="d-none d-sm-inline ms-1">Tempel</span>
              </button>
            </div>
            <div v-if="errors.target_url" class="invalid-feedback d-block mt-1">
              {{ errors.target_url[0] }}
            </div>
          </div>

          <!-- Custom Alias (Optional) -->
          <div class="mb-4">
            <label class="form-label fw-semibold">
              Kustomisasi Alias
              <span class="text-muted fw-normal small">(Opsional)</span>
            </label>
            <div class="input-group">
              <span class="input-group-text">/</span>
              <input
                v-model="form.custom_alias"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.custom_alias }"
                placeholder="contoh-alias"
                :disabled="loadingShorten"
              />
            </div>
            <div v-if="errors.custom_alias" class="invalid-feedback d-block mt-1">
              {{ errors.custom_alias[0] }}
            </div>
            <small class="text-muted d-block mt-1">
              Jika dikosongkan, sistem akan mengenerate 6 karakter acak otomatis.
            </small>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            class="btn btn-danger btn-lg px-5 w-100 w-md-auto"
            :disabled="loadingShorten"
          >
            <span
              v-if="loadingShorten"
              class="spinner-border spinner-border-sm me-2"
              role="status"
              aria-hidden="true"
            ></span>
            <i v-else class="bi bi-scissors me-2"></i>
            Perpendek URL
          </button>
        </form>

        <!-- Result Box -->
        <div v-if="latestCreated" class="mt-4 p-4 rounded-3 bg-light border border-success-subtle">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
            <h5 class="fw-bold mb-0 text-success">URL Berhasil Diperpendek!</h5>
          </div>
          <p class="text-muted small mb-3">Tautan siap dibagikan:</p>

          <div class="input-group input-group-lg mb-3">
            <input
              type="text"
              class="form-control font-monospace fw-bold text-danger"
              :value="latestCreated.short_url"
              readonly
            />
            <button
              class="btn btn-danger"
              type="button"
              @click="copyUrl(latestCreated.short_url)"
            >
              <i class="bi" :class="copied ? 'bi-check-lg' : 'bi-clipboard'"></i>
              <span class="ms-1">{{ copied ? 'Tersalin!' : 'Salin' }}</span>
            </button>
            <a
              :href="latestCreated.short_url"
              target="_blank"
              rel="noopener noreferrer"
              class="btn btn-outline-secondary"
            >
              <i class="bi bi-box-arrow-up-right"></i>
            </a>
          </div>

          <div class="text-muted small text-truncate">
            <strong>Target:</strong> {{ latestCreated.target_url }}
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Links in this Session -->
    <div v-if="recentShortened.length > 0" class="card shadow-sm border-0">
      <div class="card-header bg-transparent py-3">
        <h5 class="fw-bold mb-0">
          <i class="bi bi-clock-history text-danger me-2"></i>
          Riwayat Pemendekan Sesi Ini
        </h5>
      </div>
      <ul class="list-group list-group-flush">
        <li
          v-for="item in recentShortened"
          :key="item.id"
          class="list-group-item d-flex justify-content-between align-items-center px-4 py-3"
        >
          <div class="text-truncate me-3">
            <a
              :href="item.short_url"
              target="_blank"
              rel="noopener noreferrer"
              class="fw-bold text-danger text-decoration-none"
            >
              {{ item.short_url }}
            </a>
            <div class="text-muted small text-truncate" style="max-width: 500px;">
              {{ item.target_url }}
            </div>
          </div>
          <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            @click="copyUrl(item.short_url)"
          >
            <i class="bi bi-clipboard"></i>
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>
