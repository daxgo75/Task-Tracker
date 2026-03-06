import api from './api'
import type { ApiResponse } from '@/types/auth'
import type {
  Project,
  PaginatedData,
  ProjectFilters,
  ProjectFormPayload,
} from '@/types/project'

export const projectService = {
  async getProjects(
    filters: Partial<ProjectFilters> = {},
  ): Promise<ApiResponse<PaginatedData<Project>>> {
    const params: Record<string, string | number> = {}
    if (filters.search) params.search = filters.search
    if (filters.status) params.status = filters.status
    if (filters.per_page) params.per_page = filters.per_page
    if (filters.page) params.page = filters.page

    const response = await api.get<ApiResponse<PaginatedData<Project>>>('/projects', { params })
    return response.data
  },

  async getProject(id: number): Promise<ApiResponse<Project>> {
    const response = await api.get<ApiResponse<Project>>(`/projects/${id}`)
    return response.data
  },

  async createProject(payload: ProjectFormPayload): Promise<ApiResponse<Project>> {
    const response = await api.post<ApiResponse<Project>>('/projects', payload)
    return response.data
  },

  async updateProject(id: number, payload: Partial<ProjectFormPayload>): Promise<ApiResponse<Project>> {
    const response = await api.put<ApiResponse<Project>>(`/projects/${id}`, payload)
    return response.data
  },

  async toggleStatus(id: number): Promise<ApiResponse<Project>> {
    const response = await api.patch<ApiResponse<Project>>(`/projects/${id}/toggle-status`)
    return response.data
  },
}
