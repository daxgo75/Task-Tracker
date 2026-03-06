import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { taskService } from '@/services/taskService'
import { useCategoriesStore } from './categories'
import type { Task, TaskFormPayload, KanbanColumnId } from '@/types/task'
import type { ApiError } from '@/types/auth'
import { AxiosError } from 'axios'

export const useTasksStore = defineStore('tasks', () => {
  const tasks = ref<Task[]>([])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref('')
  const fieldErrors = ref<Record<string, string>>({})

  const tasksByCategory = computed(() => {
    const map: Record<string, Task[]> = {
      Todo: [],
      InProgress: [],
      Testing: [],
      Done: [],
      Pending: [],
    }
    for (const task of tasks.value) {
      const key = task.category.name
      if (key in map) {
        map[key]!.push(task)
      }
    }
    return map as Record<KanbanColumnId, Task[]>
  })

  const totalCount = computed(() => tasks.value.length)
  const doneCount = computed(
    () => tasks.value.filter((t) => t.category.name === 'Done').length,
  )

  function clearErrors(): void {
    error.value = ''
    fieldErrors.value = {}
  }

  function reset(): void {
    tasks.value = []
    error.value = ''
    fieldErrors.value = {}
  }

  async function fetchTasks(projectId: number): Promise<void> {
    isLoading.value = true
    error.value = ''
    try {
      const response = await taskService.getProjectTasks(projectId)
      tasks.value = response.data.data
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
      const response = await taskService.createTask(projectId, payload)
      tasks.value.push(response.data)
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
      const response = await taskService.updateTask(id, payload)
      const idx = tasks.value.findIndex((t) => t.id === id)
      if (idx !== -1) tasks.value[idx] = response.data
      return { success: true }
    } catch (err) {
      return handleSaveError(err)
    } finally {
      isSaving.value = false
    }
  }

  async function moveTask(taskId: number, targetCategoryName: string): Promise<void> {
    const categoriesStore = useCategoriesStore()
    const targetCategory = categoriesStore.findByName(targetCategoryName)
    if (!targetCategory) return

    // Optimistic update
    const idx = tasks.value.findIndex((t) => t.id === taskId)
    if (idx === -1) return
    const previousCategory = tasks.value[idx]!.category
    tasks.value[idx] = { ...tasks.value[idx]!, category: targetCategory }

    try {
      const response = await taskService.updateTask(taskId, { category_id: targetCategory.id })
      tasks.value[idx] = response.data
    } catch {
      // Revert on failure
      tasks.value[idx] = { ...tasks.value[idx]!, category: previousCategory }
      error.value = 'Gagal memindahkan task.'
    }
  }

  async function deleteTask(id: number): Promise<{ success: boolean }> {
    isSaving.value = true
    try {
      await taskService.deleteTask(id)
      tasks.value = tasks.value.filter((t) => t.id !== id)
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
    tasksByCategory,
    totalCount,
    doneCount,
    isLoading,
    isSaving,
    error,
    fieldErrors,
    clearErrors,
    reset,
    fetchTasks,
    createTask,
    updateTask,
    moveTask,
    deleteTask,
  }
})
