<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { linkApiService, ApiError } from '../services/api'
import type { CreateLinkPayload } from '../types/link'

const props = defineProps<{
  show: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'saved', message: string): void
}>()

const form = reactive<CreateLinkPayload>({
  name: '',
  target_url: '',
  custom_alias: '',
  is_active: true,
})

const loading = ref(false)
const generalError = ref('')
const errors = reactive<Record<string, string[]>>({})

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      // Reset form
      form.name = ''
      form.target_url = ''
      form.custom_alias = ''
      form.is_active = true
      generalError.value = ''
      Object.keys(errors).forEach((key) => delete errors[key])
    }
  }
)

async function handleSubmit() {
  generalError.value = ''
  Object.keys(errors).forEach((key) => delete errors[key])

  if (!form.name.trim()) {
    errors.name = ['Nama link wajib diisi.']
    return
  }
  if (!form.target_url.trim()) {
    errors.target_url = ['URL tujuan wajib diisi.']
    return
  }

  loading.value = true
  try {
    const res = await linkApiService.createLink({
      name: form.name.trim(),
      target_url: form.target_url.trim(),
      custom_alias: form.custom_alias?.trim() || undefined,
      is_active: form.is_active,
    })
    emit('saved', res.message || 'Link berhasil dibuat!')
    emit('close')
  } catch (err: unknown) {
    if (err instanceof ApiError && err.errors) {
      Object.assign(errors, err.errors)
    } else {
      generalError.value =
        err instanceof Error ? err.message : 'Gagal membuat link. Silakan coba lagi.'
    }
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
          <h5 class="modal-title fw-bold">
            <i class="bi bi-plus-circle text-danger me-2"></i>
            Buat Link Baru
          </h5>
          <button type="button" class="btn-close" aria-label="Close" @click="emit('close')"></button>
        </div>

        <form @submit.prevent="handleSubmit">
          <div class="modal-body p-4">
            <div v-if="generalError" class="alert alert-danger d-flex align-items-center mb-3">
              <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
              <div>{{ generalError }}</div>
            </div>

            <!-- Name Field -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Nama Link <span class="text-danger">*</span></label>
              <input
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                placeholder="Contoh: Campaign Pendaftaran"
                :disabled="loading"
              />
              <div v-if="errors.name" class="invalid-feedback">
                {{ errors.name[0] }}
              </div>
            </div>

            <!-- Target URL Field -->
            <div class="mb-3">
              <label class="form-label fw-semibold">URL Tujuan (Asli) <span class="text-danger">*</span></label>
              <input
                v-model="form.target_url"
                type="url"
                class="form-control"
                :class="{ 'is-invalid': errors.target_url }"
                placeholder="https://example.com/very/long/destination/url"
                :disabled="loading"
              />
              <div v-if="errors.target_url" class="invalid-feedback">
                {{ errors.target_url[0] }}
              </div>
            </div>

            <!-- Custom Alias Field -->
            <div class="mb-3">
              <label class="form-label fw-semibold">
                Custom Alias
                <span class="text-muted fw-normal small">(Opsional, otomatis dibuat jika kosong)</span>
              </label>
              <div class="input-group">
                <span class="input-group-text">/</span>
                <input
                  v-model="form.custom_alias"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': errors.custom_alias }"
                  placeholder="alias-kustom-kamu"
                  :disabled="loading"
                />
              </div>
              <div v-if="errors.custom_alias" class="invalid-feedback d-block">
                {{ errors.custom_alias[0] }}
              </div>
              <small class="text-muted d-block mt-1">
                Hanya huruf, angka, strip (-), dan garis bawah (_).
              </small>
            </div>

            <!-- Is Active Toggle -->
            <div class="form-check form-switch mt-3">
              <input
                id="createIsActive"
                v-model="form.is_active"
                class="form-check-input"
                type="checkbox"
                :disabled="loading"
              />
              <label class="form-check-label fw-semibold" for="createIsActive">
                Status Aktif
              </label>
              <small class="text-muted d-block">
                Link yang tidak aktif tidak dapat diakses dan dialihkan oleh publik.
              </small>
            </div>
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
            <button type="submit" class="btn btn-danger px-4" :disabled="loading">
              <span
                v-if="loading"
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              <i v-else class="bi bi-check2-circle me-1"></i>
              Simpan Link
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
