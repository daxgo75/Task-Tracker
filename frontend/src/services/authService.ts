import api from './api'
import type { ApiResponse, LoginPayload, LoginResponse } from '@/types/auth'

export const authService = {
  async login(payload: LoginPayload): Promise<ApiResponse<LoginResponse>> {
    const response = await api.post<ApiResponse<LoginResponse>>('/auth/login', payload)
    return response.data
  },

  async logout(): Promise<void> {
    await api.post('/auth/logout')
  },

  async me(): Promise<ApiResponse<{ id: number; name: string; email: string }>> {
    const response = await api.get('/auth/me')
    return response.data
  },
}
