import { defineStore } from 'pinia'
import { ref } from 'vue'
import { projectService } from '@/services/projectService'
import type { Project, PaginatedData, ProjectFilters, ProjectFormPayload } from '@/types/project'
import type { ApiError } from '@/types/auth'
import { AxiosError } from 'axios'

export const useProjectsStore = defineStore('projects', () => {
  const projects = ref<Project[]>([])
  const meta = ref<PaginatedData<Project>['meta'] | null>(null)
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref('')
  const fieldErrors = ref<Record<string, string>>({})

  function clearErrors(): void {
    error.value = ''
    fieldErrors.value = {}
  }

  async function fetchProjects(filters: Partial<ProjectFilters> = {}): Promise<void> {
    isLoading.value = true
    error.value = ''
    try {
      const response = await projectService.getProjects(filters)
      projects.value = response.data.data
      meta.value = response.data.meta
    } catch {
      error.value = 'Gagal memuat daftar project. Silakan coba lagi.'
    } finally {
      isLoading.value = false
    }
  }

  async function createProject(
    payload: ProjectFormPayload,
  ): Promise<{ success: boolean; errors?: Record<string, string> }> {
    isSaving.value = true
    clearErrors()
    try {
      const response = await projectService.createProject(payload)
      projects.value.unshift(response.data)
      if (meta.value) meta.value.total += 1
      return { success: true }
    } catch (err) {
      return handleSaveError(err)
    } finally {
      isSaving.value = false
    }
  }

  async function updateProject(
    id: number,
    payload: Partial<ProjectFormPayload>,
  ): Promise<{ success: boolean; errors?: Record<string, string> }> {
    isSaving.value = true
    clearErrors()
    try {
      const response = await projectService.updateProject(id, payload)
      const idx = projects.value.findIndex((p) => p.id === id)
      if (idx !== -1) projects.value[idx] = response.data
      return { success: true }
    } catch (err) {
      return handleSaveError(err)
    } finally {
      isSaving.value = false
    }
  }

  async function toggleStatus(id: number): Promise<void> {
    try {
      const response = await projectService.toggleStatus(id)
      const idx = projects.value.findIndex((p) => p.id === id)
      if (idx !== -1) projects.value[idx] = response.data
    } catch {
      error.value = 'Gagal mengubah status project.'
    }
  }

  function handleSaveError(err: unknown): { success: false; errors: Record<string, string> } {
    const fieldErrs: Record<string, string> = {}
    let globalMsg = 'Terjadi kesalahan. Silakan coba lagi.'

    if (err instanceof AxiosError && err.response) {
      const data = err.response.data as ApiError
      if (err.response.status === 422 && data.errors) {
        for (const [field, messages] of Object.entries(data.errors)) {
          fieldErrs[field] = messages[0] ?? ''
        }
      } else {
        globalMsg = data.message || globalMsg
      }
    }

    error.value = Object.keys(fieldErrs).length === 0 ? globalMsg : ''
    fieldErrors.value = fieldErrs
    return { success: false, errors: fieldErrs }
  }

  return {
    projects,
    meta,
    isLoading,
    isSaving,
    error,
    fieldErrors,
    clearErrors,
    fetchProjects,
    createProject,
    updateProject,
    toggleStatus,
  }
})
