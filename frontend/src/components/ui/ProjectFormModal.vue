<script setup lang="ts">
import { reactive, watch, computed } from 'vue'
import type { Project, ProjectFormPayload, ProjectStatus } from '@/types/project'

const props = defineProps<{
  isOpen: boolean
  project: Project | null
  isSaving: boolean
  fieldErrors: Record<string, string>
  globalError?: string
}>()

const emit = defineEmits<{
  (e: 'submit', payload: ProjectFormPayload): void
  (e: 'close'): void
}>()

const isEdit = computed(() => props.project !== null)

const form = reactive<ProjectFormPayload>({
  name: '',
  description: '',
  status: 'active',
})

watch(
  () => props.project,
  (project) => {
    if (project) {
      form.name = project.name
      form.description = project.description ?? ''
      form.status = project.status
    } else {
      form.name = ''
      form.description = ''
      form.status = 'active'
    }
  },
  { immediate: true },
)

function handleSubmit(): void {
  emit('submit', { ...form })
}

function handleClose(): void {
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="modal-overlay" @mousedown.self="handleClose">
        <div class="modal" role="dialog" aria-modal="true" :aria-label="isEdit ? 'Edit Project' : 'Tambah Project'">
          <div class="modal__header">
            <h2 class="modal__title">{{ isEdit ? 'Edit Project' : 'Tambah Project' }}</h2>
            <button class="modal__close" aria-label="Tutup" @click="handleClose">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </button>
          </div>

          <form class="modal__body" novalidate @submit.prevent="handleSubmit">
            <!-- Global save error -->
            <div v-if="globalError" class="modal-alert-error" role="alert">{{ globalError }}</div>

            <!-- Name -->
            <div class="form-group">
              <label for="proj-name" class="form-label">
                Nama Project <span class="form-required">*</span>
              </label>
              <input
                id="proj-name"
                v-model="form.name"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': fieldErrors.name }"
                maxlength="255"
                autocomplete="off"
                required
              />
              <span v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</span>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label for="proj-desc" class="form-label">Deskripsi</label>
              <textarea
                id="proj-desc"
                v-model="form.description"
                class="form-input form-textarea"
                :class="{ 'form-input--error': fieldErrors.description }"
                maxlength="2000"
                rows="3"
              />
              <span v-if="fieldErrors.description" class="field-error">{{ fieldErrors.description }}</span>
            </div>

            <!-- Status -->
            <div class="form-group">
              <label for="proj-status" class="form-label">Status</label>
              <select
                id="proj-status"
                v-model="form.status"
                class="form-input form-select"
                :class="{ 'form-input--error': fieldErrors.status }"
              >
                <option value="active">Active</option>
                <option value="archived">Archived</option>
              </select>
              <span v-if="fieldErrors.status" class="field-error">{{ fieldErrors.status }}</span>
            </div>

            <div class="modal__footer">
              <button type="button" class="btn btn--secondary" :disabled="isSaving" @click="handleClose">
                Batal
              </button>
              <button type="submit" class="btn btn--primary" :disabled="isSaving">
                <span v-if="isSaving" class="btn-spinner" aria-hidden="true" />
                {{ isSaving ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Buat Project' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal {
  background: #ffffff;
  border-radius: 0.75rem;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.modal__title {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.modal__close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 0.375rem;
  border: none;
  background: none;
  color: #94a3b8;
  cursor: pointer;
  transition: background-color 0.15s, color 0.15s;
}

.modal__close:hover {
  background-color: #f1f5f9;
  color: #0f172a;
}

.modal__close svg {
  width: 1rem;
  height: 1rem;
}

.modal__body {
  padding: 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal__footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 0.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.form-required {
  color: #ef4444;
  margin-left: 0.125rem;
}

.form-input {
  padding: 0.625rem 0.875rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #0f172a;
  background-color: #f8fafc;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  font-family: inherit;
  box-sizing: border-box;
  width: 100%;
}

.form-input:focus {
  border-color: #3b82f6;
  background-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.form-input--error {
  border-color: #ef4444;
}

.form-input--error:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
}

.form-textarea {
  resize: vertical;
  min-height: 5rem;
}

.modal-alert-error {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.625rem 0.875rem;
  border-radius: 0.5rem;
  font-size: 0.8125rem;
  line-height: 1.5;
}

.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1rem;
  padding-right: 2.5rem;
  cursor: pointer;
}

.field-error {
  font-size: 0.8rem;
  color: #ef4444;
}

.btn {
  padding: 0.5rem 1.25rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s, opacity 0.15s;
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn--primary {
  background-color: #3b82f6;
  color: #ffffff;
}

.btn--primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn--secondary {
  background-color: #f1f5f9;
  color: #374151;
}

.btn--secondary:hover:not(:disabled) {
  background-color: #e2e8f0;
}

.btn-spinner {
  width: 0.875rem;
  height: 0.875rem;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-active .modal,
.modal-leave-active .modal {
  transition: transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal,
.modal-leave-to .modal {
  transform: scale(0.95) translateY(-0.5rem);
}
</style>
