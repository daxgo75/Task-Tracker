<script setup lang="ts">
withDefaults(
  defineProps<{
    isOpen: boolean
    title?: string
    message: string
    note?: string
    confirmLabel?: string
    isLoading?: boolean
    variant?: 'danger' | 'trash'
  }>(),
  {
    title: 'Konfirmasi',
    confirmLabel: 'Ya, Hapus',
    isLoading: false,
    variant: 'danger',
    note: undefined,
  }
)

const emit = defineEmits<{
  (e: 'confirm'): void
  (e: 'close'): void
}>()

function onOverlayClick(evt: MouseEvent): void {
  if ((evt.target as HTMLElement).classList.contains('confirm-overlay')) {
    emit('close')
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="confirm-overlay" @click="onOverlayClick">
        <div class="confirm-box" role="alertdialog" aria-modal="true">

          <!-- Close button -->
          <button class="confirm-close" aria-label="Tutup" @click="emit('close')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>

          <!-- Icon -->
          <div class="confirm-icon">
            <svg v-if="variant === 'trash'" viewBox="0 0 64 80" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="26" y="2" width="12" height="6" rx="3" fill="#9ca3af"/>
              <rect x="8" y="10" width="48" height="8" rx="3" fill="#9ca3af"/>
              <rect x="12" y="22" width="40" height="46" rx="4" fill="#d1d5db"/>
              <line x1="22" y1="28" x2="22" y2="62" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"/>
              <line x1="32" y1="28" x2="32" y2="62" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"/>
              <line x1="42" y1="28" x2="42" y2="62" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"/>
              <line x1="12" y1="38" x2="52" y2="38" stroke="#9ca3af" stroke-width="1.5"/>
              <line x1="12" y1="50" x2="52" y2="50" stroke="#9ca3af" stroke-width="1.5"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
              <line x1="12" y1="9" x2="12" y2="13" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
          </div>

          <h3 class="confirm-title">{{ title }}</h3>
          <p class="confirm-message" v-html="message" />
          <p v-if="note" class="confirm-note">{{ note }}</p>

          <div class="confirm-actions">
            <button class="btn btn--cancel" :disabled="isLoading" @click="emit('close')">Batal</button>
            <button class="btn btn--danger" :disabled="isLoading" @click="emit('confirm')">
              <span v-if="isLoading" class="spinner" />
              {{ confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  padding: 1rem;
}

.confirm-box {
  position: relative;
  background: #ffffff;
  border-radius: 1rem;
  width: 100%;
  max-width: 380px;
  padding: 2.5rem 2rem 2rem;
  text-align: center;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.18);
}

.confirm-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 1.75rem;
  height: 1.75rem;
  background: #f1f5f9;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  padding: 0;
  transition: background-color 0.15s;
}

.confirm-close:hover {
  background: #e2e8f0;
}

.confirm-close svg {
  width: 0.875rem;
  height: 0.875rem;
}

.confirm-icon {
  width: 5rem;
  height: 5rem;
  margin: 0 auto 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.confirm-icon svg {
  width: 100%;
  height: 100%;
}

.confirm-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 0.75rem;
}

.confirm-message {
  font-size: 0.9375rem;
  color: #4b5563;
  margin: 0;
  line-height: 1.65;
}

:deep(.confirm-message strong) {
  color: #111827;
  font-weight: 700;
}

.confirm-note {
  font-size: 0.8125rem;
  color: #9ca3af;
  margin: 0.375rem 0 0;
  line-height: 1.5;
}

.confirm-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  margin-top: 1.75rem;
}

.btn {
  padding: 0.6rem 1.75rem;
  border-radius: 0.5rem;
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  transition: background-color 0.15s, border-color 0.15s;
  min-width: 6rem;
  justify-content: center;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn--cancel {
  background: #ffffff;
  border: 1.5px solid #d1d5db;
  color: #374151;
}

.btn--cancel:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn--danger {
  background: #ef4444;
  border: 1.5px solid #ef4444;
  color: #ffffff;
}

.btn--danger:hover:not(:disabled) {
  background: #dc2626;
  border-color: #dc2626;
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
