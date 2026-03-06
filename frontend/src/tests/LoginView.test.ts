import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'

// Mock authService at the module level
vi.mock('@/services/authService', () => ({
  authService: {
    login: vi.fn(),
  },
}))

import { authService } from '@/services/authService'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginView },
    { path: '/dashboard', name: 'dashboard', component: { template: '<div>Dashboard</div>' } },
  ],
})

function mountLogin() {
  return mount(LoginView, {
    global: {
      plugins: [createPinia(), router],
    },
  })
}

describe('LoginView', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('renders email and password fields', () => {
    const wrapper = mountLogin()
    expect(wrapper.find('#email').exists()).toBe(true)
    expect(wrapper.find('#password').exists()).toBe(true)
  })

  it('renders the submit button with correct label', () => {
    const wrapper = mountLogin()
    expect(wrapper.find('.btn-submit').text()).toBe('Masuk')
  })

  it('shows field-level error for account not found (401 with errors.email)', async () => {
    const { AxiosError } = await import('axios')
    const axiosError = new AxiosError('Unauthorized')
    axiosError.response = {
      status: 401,
      data: {
        success: false,
        message: 'Akun dengan email tersebut tidak ditemukan.',
        errors: { email: ['Akun dengan email ini tidak ditemukan. Pastikan email yang Anda masukkan benar.'] },
      },
    } as any

    vi.mocked(authService.login).mockRejectedValueOnce(axiosError)

    const wrapper = mountLogin()
    await wrapper.find('#email').setValue('noexist@email.com')
    await wrapper.find('#password').setValue('password')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.find('.field-error').exists()).toBe(true)
    expect(wrapper.find('.field-error').text()).toContain('Akun dengan email ini tidak ditemukan')
  })

  it('shows field-level error for wrong password (401 with errors.password)', async () => {
    const { AxiosError } = await import('axios')
    const axiosError = new AxiosError('Unauthorized')
    axiosError.response = {
      status: 401,
      data: {
        success: false,
        message: 'Password yang Anda masukkan salah.',
        errors: { password: ['Password yang Anda masukkan salah. Silakan coba lagi.'] },
      },
    } as any

    vi.mocked(authService.login).mockRejectedValueOnce(axiosError)

    const wrapper = mountLogin()
    await wrapper.find('#email').setValue('wrong@email.com')
    await wrapper.find('#password').setValue('wrongpass')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    const fieldErrors = wrapper.findAll('.field-error')
    const passwordError = fieldErrors.find(el => el.text().includes('Password yang Anda masukkan salah'))
    expect(passwordError).toBeTruthy()
  })

  it('shows per-field validation errors (422)', async () => {
    const { AxiosError } = await import('axios')
    const axiosError = new AxiosError('Unprocessable')
    axiosError.response = {
      status: 422,
      data: {
        success: false,
        message: 'Validasi gagal.',
        errors: { email: ['Format email tidak valid.'] },
      },
    } as any

    vi.mocked(authService.login).mockRejectedValueOnce(axiosError)

    const wrapper = mountLogin()
    await wrapper.find('#email').setValue('invalid@notreal.com')
    await wrapper.find('#password').setValue('password')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.find('.field-error').text()).toBe('Format email tidak valid.')
  })

  it('shows client-side format error on blur with invalid email', async () => {
    const wrapper = mountLogin()
    const emailInput = wrapper.find('#email')
    await emailInput.setValue('bukan-email')
    await emailInput.trigger('blur')
    expect(wrapper.find('.field-error').text()).toContain('Format email tidak valid')
  })

  it('clears email error when user starts typing', async () => {
    const wrapper = mountLogin()
    const emailInput = wrapper.find('#email')
    await emailInput.setValue('bukan-email')
    await emailInput.trigger('blur')
    expect(wrapper.find('.field-error').exists()).toBe(true)
    await emailInput.setValue('bukan-email-fix')
    await emailInput.trigger('input')
    expect(wrapper.find('.field-error').exists()).toBe(false)
  })

  it('redirects to dashboard on successful login', async () => {
    vi.mocked(authService.login).mockResolvedValueOnce({
      success: true,
      message: 'Login berhasil.',
      data: {
        token: 'test-token-123',
        user: { id: 1, name: 'Admin', email: 'admin@test.com', is_admin: false, created_at: '', updated_at: '' },
      },
    })

    await router.push('/login')
    await router.isReady()

    const wrapper = mountLogin()
    await wrapper.find('#email').setValue('admin@test.com')
    await wrapper.find('#password').setValue('password')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(router.currentRoute.value.name).toBe('dashboard')
  })
})
