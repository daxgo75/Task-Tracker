export interface User {
  id: number
  name: string
  email: string
  is_admin: boolean
  created_at?: string
  updated_at?: string
}

export interface LoginPayload {
  email: string
  password: string
}

export interface LoginResponse {
  token: string
  user: User
}

export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
}

export interface ApiError {
  success: boolean
  message: string
  errors?: Record<string, string[]>
}
