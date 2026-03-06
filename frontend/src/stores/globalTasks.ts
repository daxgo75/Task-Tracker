import { defineStore } from 'pinia'
import { ref } from 'vue'
import { taskService } from '@/services/taskService'
import type { Task, TaskFormPayload, GlobalTaskFilters } from '@/types/task'
import type { PaginationMeta } from '@/types/project'
import type { ApiError } from '@/types/auth'
import { AxiosError } from 'axios'

const DEFAULT_META: PaginationMeta = {
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: null,
  to: null,
}

export const useGlobalTasksStore = defineStore('globalTasks', () => {
  const tasks = ref<Task[]>([])
  const meta = ref<PaginationMeta>({ ...DEFAULT_META })
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref('')
  const fieldErrors = ref<Record<string, string>>({})

  function clearErrors(): void {
    error.value = ''
    fieldErrors.value = {}
  }

  async function fetchTasks(filters: GlobalTaskFilters = {}): Promise<void> {
    isLoading.value = true
    error.value = ''
    try {
      const res = await taskService.getGlobalTasks(filters)
      tasks.value = res.data.data
      meta.value = res.data.meta
    } catch {
      error.value = 'Gagal memuat task. Silakan coba lagi.'
    } finally {
      isLoading.value = false
    }
  }

  async function createTask(
    projectId: number,
    payload: TaskFormPayload,
  ): Promise<{ success: boolean }> {
    isSaving.value = true
    clearErrors()
    try {
      const res = await taskService.createTask(projectId, payload)
      tasks.value.unshift(res.data)
      meta.value.total += 1
      return { success: true }
    } catch (err) {
      return handleSaveError(err)
    } finally {
      isSaving.value = false
    }
  }

  async function updateTask(
    id: number,
    payload: Partial<TaskFormPayload>,
  ): Promise<{ success: boolean }> {
    isSaving.value = true
    clearErrors()
    try {
      const res = await taskService.updateTask(id, payload)
      const idx = tasks.value.findIndex((t) => t.id === id)
      if (idx !== -1) tasks.value[idx] = res.data
      return { success: true }
    } catch (err) {
      return handleSaveError(err)
    } finally {
      isSaving.value = false
    }
  }

  async function deleteTask(id: number): Promise<{ success: boolean }> {
    isSaving.value = true
    try {
      await taskService.deleteTask(id)
      tasks.value = tasks.value.filter((t) => t.id !== id)
      meta.value.total = Math.max(0, meta.value.total - 1)
      return { success: true }
    } catch {
      error.value = 'Gagal menghapus task.'
      return { success: false }
    } finally {
      isSaving.value = false
    }
  }

  function handleSaveError(err: unknown): { success: false } {
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
    return { success: false }
  }

  return {
    tasks,
    meta,
    isLoading,
    isSaving,
    error,
    fieldErrors,
    clearErrors,
    fetchTasks,
    createTask,
    updateTask,
    deleteTask,
  }
})
