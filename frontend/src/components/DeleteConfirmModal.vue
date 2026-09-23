<script setup lang="ts">
import { ref } from 'vue'
import { linkApiService } from '../services/api'
import type { LinkItem } from '../types/link'

const props = defineProps<{
  show: boolean
  link: LinkItem | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'deleted', message: string): void
}>()

const loading = ref(false)
const error = ref('')

async function handleDelete() {
  if (!props.link) return
  loading.value = true
  error.value = ''
  try {
    const res = await linkApiService.deleteLink(props.link.id)
    emit('deleted', res.message || 'Link berhasil dihapus!')
    emit('close')
  } catch (err: unknown) {
    error.value =
      err instanceof Error ? err.message : 'Gagal menghapus link.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div v-if="show" class="modal-backdrop fade show" style="background-color: rgba(0, 0, 0, 0.5)"></div>
  <div
    v-if="show"
    class="modal fade show d-block"
    tabindex="-1"
    role="dialog"
    aria-modal="true"
    @click.self="emit('close')"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold text-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Konfirmasi Hapus Link
          </h5>
          <button type="button" class="btn-close" aria-label="Close" @click="emit('close')"></button>
        </div>

        <div class="modal-body p-4">
          <div v-if="error" class="alert alert-danger mb-3">
            {{ error }}
          </div>
          <p class="mb-2">
            Apakah Anda yakin ingin menghapus link
            <strong class="text-danger">{{ link?.name }}</strong>?
          </p>
          <div class="p-3 bg-light rounded text-muted small font-monospace">
            <div><strong>Short URL:</strong> {{ link?.short_url }}</div>
            <div class="text-truncate"><strong>Target:</strong> {{ link?.target_url }}</div>
          </div>
          <small class="text-muted d-block mt-3">
            Tindakan ini tidak dapat dibatalkan. Link akan hilang permanen dari database.
          </small>
        </div>

        <div class="modal-footer border-top bg-light-subtle">
          <button
            type="button"
            class="btn btn-outline-secondary"
            :disabled="loading"
            @click="emit('close')"
          >
            Batal
          </button>
          <button
            type="button"
            class="btn btn-danger px-4"
            :disabled="loading"
            @click="handleDelete"
          >
            <span
              v-if="loading"
              class="spinner-border spinner-border-sm me-2"
              role="status"
              aria-hidden="true"
            ></span>
            <i v-else class="bi bi-trash me-1"></i>
            Hapus Link
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
