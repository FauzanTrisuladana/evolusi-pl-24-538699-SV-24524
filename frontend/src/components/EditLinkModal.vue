<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { linkApiService, ApiError } from '../services/api'
import type { LinkItem, UpdateLinkPayload } from '../types/link'

const props = defineProps<{
  show: boolean
  link: LinkItem | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'updated', message: string): void
}>()

const form = reactive<UpdateLinkPayload>({
  name: '',
  target_url: '',
  custom_alias: '',
  is_active: true,
})

const loading = ref(false)
const generalError = ref('')
const errors = reactive<Record<string, string[]>>({})

watch(
  () => props.link,
  (newLink) => {
    if (newLink) {
      form.name = newLink.name || ''
      form.target_url = newLink.target_url || ''
      form.custom_alias = newLink.custom_alias || ''
      form.is_active = newLink.is_active ?? true
      generalError.value = ''
      Object.keys(errors).forEach((key) => delete errors[key])
    }
  },
  { immediate: true }
)

async function handleSubmit() {
  if (!props.link) return
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
  if (!form.custom_alias.trim()) {
    errors.custom_alias = ['Alias wajib diisi.']
    return
  }

  loading.value = true
  try {
    const res = await linkApiService.updateLink(props.link.id, {
      name: form.name.trim(),
      target_url: form.target_url.trim(),
      custom_alias: form.custom_alias.trim(),
      is_active: form.is_active,
    })
    emit('updated', res.message || 'Link berhasil diperbarui!')
    emit('close')
  } catch (err: unknown) {
    if (err instanceof ApiError && err.errors) {
      Object.assign(errors, err.errors)
    } else {
      generalError.value =
        err instanceof Error ? err.message : 'Gagal memperbarui link.'
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
            <i class="bi bi-pencil-square text-danger me-2"></i>
            Edit Link
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
              <label class="form-label fw-semibold">Alias Short Link <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">/</span>
                <input
                  v-model="form.custom_alias"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': errors.custom_alias }"
                  placeholder="alias-kustom"
                  :disabled="loading"
                />
              </div>
              <div v-if="errors.custom_alias" class="invalid-feedback d-block">
                {{ errors.custom_alias[0] }}
              </div>
            </div>

            <!-- Is Active Toggle -->
            <div class="form-check form-switch mt-3">
              <input
                id="editIsActive"
                v-model="form.is_active"
                class="form-check-input"
                type="checkbox"
                :disabled="loading"
              />
              <label class="form-check-label fw-semibold" for="editIsActive">
                Status Aktif
              </label>
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
              <i v-else class="bi bi-save me-1"></i>
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
