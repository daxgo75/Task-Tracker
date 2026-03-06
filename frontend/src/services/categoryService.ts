import api from './api'
import type { ApiResponse } from '@/types/auth'
import type { Category } from '@/types/task'

export const categoryService = {
  async getCategories(): Promise<ApiResponse<Category[]>> {
    const response = await api.get<ApiResponse<Category[]>>('/categories')
    return response.data
  },
}
