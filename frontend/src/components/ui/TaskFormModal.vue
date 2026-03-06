<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { Task, TaskFormPayload } from '@/types/task'
import type { Category } from '@/types/task'

const props = defineProps<{
  isOpen: boolean
  task: Task | null
  isSaving: boolean
  fieldErrors: Record<string, string>
  categories: Category[]
  globalError?: string
}>()

const emit = defineEmits<{
  (e: 'submit', payload: TaskFormPayload): void
  (e: 'close'): void
}>()

const form = ref<TaskFormPayload>({
  title: '',
  category_id: 0,
  description: '',
  due_date: '',
})

watch(
  () => props.isOpen,
  (open) => {
    if (open) {
      if (props.task) {
        form.value = {
          title: props.task.title,
          category_id: props.task.category.id,
          description: props.task.description ?? '',
          due_date: props.task.due_date ?? '',
        }
      } else {
        form.value = { title: '', category_id: 0, description: '', due_date: '' }
      }
    }
  },
  { immediate: true }
)

const isEdit = computed(() => props.task !== null)

function onSubmit(): void {
  emit('submit', { ...form.value })
}

function onOverlayClick(evt: MouseEvent): void {
  if ((evt.target as HTMLElement).classList.contains('modal-overlay')) {
    emit('close')
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="modal-overlay" @click="onOverlayClick">
        <div class="modal-box" role="dialog" aria-modal="true">
          <div class="modal-header">
            <h3>{{ isEdit ? 'Edit Task' : 'Tambah Task' }}</h3>
            <button class="modal-close" aria-label="Tutup" @click="emit('close')">×</button>
          </div>

          <form class="modal-body" @submit.prevent="onSubmit">
            <!-- Global save error -->
            <div v-if="globalError" class="modal-alert-error" role="alert">{{ globalError }}</div>

            <!-- Title -->
            <div class="field">
              <label class="field__label" for="task-title">Judul Task <span class="req">*</span></label>
              <input
                id="task-title"
                v-model="form.title"
                type="text"
                class="field__input"
                :class="{ 'field__input--error': fieldErrors['title'] }"
                maxlength="255"
              />
              <p v-if="fieldErrors['title']" class="field__error">{{ fieldErrors['title'] }}</p>
            </div>

            <!-- Category -->
            <div class="field">
              <label class="field__label" for="task-cat">Kategori <span class="req">*</span></label>
              <select
                id="task-cat"
                v-model="form.category_id"
                class="field__input"
                :class="{ 'field__input--error': fieldErrors['category_id'] }"
              >
                <option value="0" disabled>Pilih kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <p v-if="fieldErrors['category_id']" class="field__error">{{ fieldErrors['category_id'] }}</p>
            </div>

            <!-- Description -->
            <div class="field">
              <label class="field__label" for="task-desc">Deskripsi</label>
              <textarea
                id="task-desc"
                v-model="form.description"
                class="field__input field__textarea"
                rows="3"
              />
            </div>

            <!-- Due Date -->
            <div class="field">
              <label class="field__label" for="task-due">Tanggal Deadline</label>
              <input
                id="task-due"
                v-model="form.due_date"
                type="date"
                class="field__input"
                :class="{ 'field__input--error': fieldErrors['due_date'] }"
              />
              <p v-if="fieldErrors['due_date']" class="field__error">{{ fieldErrors['due_date'] }}</p>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn--cancel" @click="emit('close')">Batal</button>
              <button type="submit" class="btn btn--submit" :disabled="isSaving">
                <span v-if="isSaving" class="spinner" />
                {{ isEdit ? 'Simpan' : 'Tambah' }}
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
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 1rem;
}

.modal-box {
  background: #ffffff;
  border-radius: 0.75rem;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.125rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.modal-header h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #94a3b8;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}

.modal-close:hover {
  color: #475569;
}

.modal-body {
  padding: 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.field__label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #374151;
}

.req {
  color: #ef4444;
}

.field__input {
  padding: 0.5rem 0.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
  background: #ffffff;
  font-family: inherit;
}

.field__input:focus {
  border-color: #3b82f6;
}

.field__input--error {
  border-color: #ef4444;
}

.field__textarea {
  resize: vertical;
  min-height: 5rem;
}

.field__error {
  font-size: 0.75rem;
  color: #ef4444;
  margin: 0;
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

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.625rem;
  margin-top: 0.5rem;
}

.btn {
  padding: 0.5rem 1.125rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  transition: opacity 0.15s;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn--cancel {
  background: #f1f5f9;
  color: #475569;
}

.btn--cancel:hover:not(:disabled) {
  background: #e2e8f0;
}

.btn--submit {
  background: #3b82f6;
  color: #ffffff;
}

.btn--submit:hover:not(:disabled) {
  background: #2563eb;
}

.spinner {
  width: 0.875rem;
  height: 0.875rem;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Modal transition */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
