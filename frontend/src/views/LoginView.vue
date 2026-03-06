<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { LoginPayload, ApiError } from '@/types/auth'
import { AxiosError } from 'axios'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive<LoginPayload>({
  email: '',
  password: '',
})

const isLoading = ref(false)
const errors = reactive<Record<string, string>>({})
const globalError = ref('')

function clearErrors(): void {
  errors.email = ''
  errors.password = ''
  globalError.value = ''
}

const EMAIL_REGEX = /^[^\.\s@][^\s@]*@[^\s@]+\.[^\s@]+$/

function validateEmail(): void {
  if (!form.email) {
    errors.email = 'Email wajib diisi.'
  } else if (!EMAIL_REGEX.test(form.email)) {
    errors.email = 'Format email tidak valid. Contoh: nama@domain.com'
  } else {
    errors.email = ''
  }
}

async function handleLogin(): Promise<void> {
  clearErrors()

  // Client-side validation
  validateEmail()
  if (!form.password) errors.password = 'Password wajib diisi.'
  if (errors.email || errors.password) return

  isLoading.value = true

  try {
    await authStore.login(form)
    router.push({ name: 'dashboard' })
  } catch (err) {
    if (err instanceof AxiosError && err.response) {
      const data = err.response.data as ApiError

      if (data.errors) {
        // Field-level errors (422 validation OR 401 with specific field hints)
        for (const [field, messages] of Object.entries(data.errors)) {
          errors[field] = (messages as string[])[0] ?? ''
        }
      } else {
        globalError.value = data.message || 'Terjadi kesalahan. Silakan coba lagi.'
      }
    } else {
      globalError.value = 'Tidak dapat terhubung ke server. Periksa koneksi Anda.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <h1 class="login-title">
          <span class="title-task">Task</span><span class="title-tracker">Tracker</span>
        </h1>
        <p class="login-subtitle">Masuk ke akun kamu</p>
      </div>

      <form class="login-form" novalidate @submit.prevent="handleLogin">
        <div v-if="globalError" class="alert-error" role="alert">
          {{ globalError }}
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="text"
            inputmode="email"
            class="form-input"
            :class="{ 'form-input--error': errors.email }"
            placeholder="contoh@email.com"
            autocomplete="email"
            @input="errors.email = ''"
            @blur="validateEmail"
          />
          <span v-if="errors.email" class="field-error">{{ errors.email }}</span>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-input"
            :class="{ 'form-input--error': errors.password }"
            placeholder=""
            autocomplete="current-password"
            @input="errors.password = ''"
          />
          <span v-if="errors.password" class="field-error">{{ errors.password }}</span>
        </div>

        <button type="submit" class="btn-submit" :disabled="isLoading">
          <span v-if="isLoading" class="btn-spinner" aria-hidden="true"></span>
          {{ isLoading ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #0f172a;
  padding: 1rem;
}

.login-card {
  background-color: #ffffff;
  border-radius: 1rem;
  padding: 2.5rem 2rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 0.375rem;
  line-height: 1.2;
}

.title-task {
  color: #0f172a;
}

.title-tracker {
  color: #3b82f6;
}

.login-subtitle {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.alert-error {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  text-align: center;
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

.form-input {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.9375rem;
  color: #0f172a;
  background-color: #f8fafc;
  outline: none;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
  box-sizing: border-box;
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
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
}

.field-error {
  font-size: 0.8rem;
  color: #ef4444;
}

.btn-submit {
  width: 100%;
  padding: 0.75rem;
  background-color: #3b82f6;
  color: #ffffff;
  font-size: 0.9375rem;
  font-weight: 600;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.btn-submit:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
