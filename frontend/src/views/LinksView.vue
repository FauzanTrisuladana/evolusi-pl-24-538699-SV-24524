<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { linkApiService } from '../services/api'
import type { LinkItem } from '../types/link'
import CreateLinkModal from '../components/CreateLinkModal.vue'
import EditLinkModal from '../components/EditLinkModal.vue'
import DeleteConfirmModal from '../components/DeleteConfirmModal.vue'

const emit = defineEmits<{
  (e: 'notify', message: string, type?: 'success' | 'danger'): void
}>()

const links = ref<LinkItem[]>([])
const loading = ref(true)
const errorMessage = ref('')

const searchQuery = ref('')
const statusFilter = ref<'all' | 'active' | 'inactive'>('all')

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedLink = ref<LinkItem | null>(null)
const copiedLinkId = ref<number | null>(null)

let debounceTimer: ReturnType<typeof setTimeout> | null = null

async function fetchLinks() {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: { search?: string; status?: string } = {}
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }
    if (statusFilter.value !== 'all') {
      params.status = statusFilter.value
    }
    const res = await linkApiService.getLinks(params)
    links.value = res.data || []
  } catch (err: unknown) {
    errorMessage.value =
      err instanceof Error
        ? err.message
        : 'Gagal memuat data dari Laravel API. Pastikan server Laravel aktif.'
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchLinks()
  }, 350)
})

watch(statusFilter, () => {
  fetchLinks()
})

function openEdit(link: LinkItem) {
  selectedLink.value = link
  showEditModal.value = true
}

function openDelete(link: LinkItem) {
  selectedLink.value = link
  showDeleteModal.value = true
}

function handleSaved(msg: string) {
  emit('notify', msg, 'success')
  fetchLinks()
}

function handleUpdated(msg: string) {
  emit('notify', msg, 'success')
  fetchLinks()
}

function handleDeleted(msg: string) {
  emit('notify', msg, 'success')
  fetchLinks()
}

async function copyUrl(link: LinkItem) {
  try {
    await navigator.clipboard.writeText(link.short_url)
    copiedLinkId.value = link.id
    emit('notify', `Link ${link.short_url} berhasil disalin!`, 'success')
    setTimeout(() => {
      if (copiedLinkId.value === link.id) {
        copiedLinkId.value = null
      }
    }, 2000)
  } catch {
    emit('notify', 'Gagal menyalin link.', 'danger')
  }
}

onMounted(() => {
  fetchLinks()
})
</script>

<template>
  <div class="links-view">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
      <div class="col-md-7 mb-3 mb-md-0">
        <h1 class="h2 fw-bold mb-1">
          <i class="bi bi-link-45deg text-danger me-2"></i>
          Semua Link
        </h1>
        <p class="text-muted mb-0">Kelola dan pantau seluruh tautan shortener publik dari Laravel API</p>
      </div>
      <div class="col-md-5 text-md-end">
        <button
          class="btn btn-danger btn-lg shadow-sm"
          type="button"
          @click="showCreateModal = true"
        >
          <i class="bi bi-plus-circle me-2"></i>
          Buat Link Baru
        </button>
      </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="card mb-4 shadow-sm border-0">
      <div class="card-body p-3">
        <div class="row g-3 align-items-center">
          <div class="col-md-8">
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                v-model="searchQuery"
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Cari berdasarkan nama, alias, atau URL target..."
              />
              <button
                v-if="searchQuery"
                class="btn btn-outline-secondary"
                type="button"
                title="Hapus pencarian"
                @click="searchQuery = ''"
              >
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>
          <div class="col-md-4">
            <select v-model="statusFilter" class="form-select">
              <option value="all">Semua Status</option>
              <option value="active">Hanya Aktif</option>
              <option value="inactive">Hanya Tidak Aktif</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Alert with Retry -->
    <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center justify-content-between mb-4 shadow-sm">
      <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
        <div>
          <div class="fw-bold">Terjadi Kesalahan</div>
          <div>{{ errorMessage }}</div>
        </div>
      </div>
      <button class="btn btn-sm btn-outline-danger" type="button" @click="fetchLinks">
        <i class="bi bi-arrow-clockwise me-1"></i>
        Coba Lagi
      </button>
    </div>

    <!-- Links Table Card -->
    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="text-muted mb-0">Mengambil data dari Laravel API...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="links.length === 0" class="text-center py-5 px-3">
          <div class="display-6 text-muted mb-3">
            <i class="bi bi-link-45deg"></i>
          </div>
          <h5 class="fw-semibold">Tidak Ada Link yang Ditemukan</h5>
          <p class="text-muted mb-3">
            {{ searchQuery ? 'Tidak ada link yang sesuai dengan kata kunci pencarian.' : 'Belum ada data link tersimpan. Buat link pertama Anda!' }}
          </p>
          <button
            v-if="!searchQuery"
            class="btn btn-danger"
            type="button"
            @click="showCreateModal = true"
          >
            <i class="bi bi-plus-circle me-1"></i>
            Buat Link Sekarang
          </button>
        </div>

        <!-- Data Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4 py-3 text-uppercase small fw-bold">Short Link</th>
                <th class="py-3 text-uppercase small fw-bold">Nama Link</th>
                <th class="py-3 text-uppercase small fw-bold" style="max-width: 300px;">URL Asli / Target</th>
                <th class="py-3 text-uppercase small fw-bold text-center">Status</th>
                <th class="py-3 text-uppercase small fw-bold text-center pe-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="link in links" :key="link.id">
                <!-- Short URL -->
                <td class="ps-4">
                  <div class="d-flex align-items-center gap-2">
                    <a
                      :href="link.short_url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="fw-semibold text-danger text-decoration-none"
                    >
                      /{{ link.custom_alias }}
                    </a>
                    <button
                      class="btn btn-sm btn-outline-secondary copy-btn"
                      type="button"
                      :title="copiedLinkId === link.id ? 'Tersalin!' : 'Salin URL'"
                      @click="copyUrl(link)"
                    >
                      <i
                        class="bi"
                        :class="copiedLinkId === link.id ? 'bi-check-lg text-success' : 'bi-clipboard'"
                      ></i>
                    </button>
                  </div>
                  <small class="text-muted font-monospace d-block" style="font-size: 0.75rem;">
                    {{ link.short_url }}
                  </small>
                </td>

                <!-- Name -->
                <td>
                  <span class="fw-semibold text-truncate d-inline-block" style="max-width: 220px;">
                    {{ link.name }}
                  </span>
                </td>

                <!-- Target URL -->
                <td style="max-width: 300px;">
                  <span
                    class="text-muted small text-truncate d-inline-block"
                    style="max-width: 100%;"
                    :title="link.target_url"
                  >
                    {{ link.target_url }}
                  </span>
                </td>

                <!-- Status Badge -->
                <td class="text-center">
                  <span
                    class="badge rounded-pill px-3 py-2"
                    :class="link.is_active ? 'badge-active' : 'badge-inactive'"
                  >
                    <i
                      class="bi me-1"
                      :class="link.is_active ? 'bi-check-circle-fill' : 'bi-dash-circle-fill'"
                    ></i>
                    {{ link.is_active ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="text-center pe-4">
                  <div class="btn-group" role="group">
                    <button
                      class="btn btn-sm btn-light text-secondary"
                      type="button"
                      title="Edit Link"
                      @click="openEdit(link)"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                    <a
                      :href="link.short_url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="btn btn-sm btn-light text-secondary"
                      title="Kunjungi Link"
                    >
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <button
                      class="btn btn-sm btn-light text-danger"
                      type="button"
                      title="Hapus Link"
                      @click="openDelete(link)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-if="links.length > 0" class="card-footer bg-light-subtle d-flex justify-content-between align-items-center px-4 py-3">
        <small class="text-muted">
          Menampilkan <strong>{{ links.length }}</strong> tautan
        </small>
        <button
          class="btn btn-sm btn-outline-secondary"
          type="button"
          :disabled="loading"
          @click="fetchLinks"
        >
          <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin-anim': loading }"></i>
          Segarkan Data
        </button>
      </div>
    </div>

    <!-- Modals -->
    <CreateLinkModal
      :show="showCreateModal"
      @close="showCreateModal = false"
      @saved="handleSaved"
    />
    <EditLinkModal
      :show="showEditModal"
      :link="selectedLink"
      @close="showEditModal = false"
      @updated="handleUpdated"
    />
    <DeleteConfirmModal
      :show="showDeleteModal"
      :link="selectedLink"
      @close="showDeleteModal = false"
      @deleted="handleDeleted"
    />
  </div>
</template>
