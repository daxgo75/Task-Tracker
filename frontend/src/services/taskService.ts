import api from './api'
import type { ApiResponse } from '@/types/auth'
import type { Task, TaskFormPayload, GlobalTaskFilters } from '@/types/task'
import type { PaginatedData } from '@/types/project'

export const taskService = {
  async getGlobalTasks(
    filters: GlobalTaskFilters = {},
  ): Promise<ApiResponse<PaginatedData<Task>>> {
    const params: Record<string, string | number> = {}
    if (filters.search) params.search = filters.search
    if (filters.category_id) params.category_id = filters.category_id
    if (filters.project_id) params.project_id = filters.project_id
    if (filters.per_page) params.per_page = filters.per_page
    if (filters.page) params.page = filters.page
    const response = await api.get<ApiResponse<PaginatedData<Task>>>('/tasks', { params })
    return response.data
  },

  async getProjectTasks(
    projectId: number,
    params: Record<string, string | number> = {},
  ): Promise<ApiResponse<PaginatedData<Task>>> {
    const response = await api.get<ApiResponse<PaginatedData<Task>>>(
      `/projects/${projectId}/tasks`,
      { params: { per_page: 100, ...params } },
    )
    return response.data
  },

  async createTask(projectId: number, payload: TaskFormPayload): Promise<ApiResponse<Task>> {
    const response = await api.post<ApiResponse<Task>>(
      `/projects/${projectId}/tasks`,
      payload,
    )
    return response.data
  },

  async updateTask(id: number, payload: Partial<TaskFormPayload>): Promise<ApiResponse<Task>> {
    const response = await api.put<ApiResponse<Task>>(`/tasks/${id}`, payload)
    return response.data
  },

  async deleteTask(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/tasks/${id}`)
    return response.data
  },
}
