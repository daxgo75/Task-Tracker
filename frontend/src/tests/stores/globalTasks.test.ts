import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { AxiosError } from 'axios'
import { useGlobalTasksStore } from '@/stores/globalTasks'
import type { Task } from '@/types/task'

vi.mock('@/services/taskService', () => ({
  taskService: {
    getGlobalTasks: vi.fn(),
    createTask: vi.fn(),
    updateTask: vi.fn(),
    deleteTask: vi.fn(),
    getProjectTasks: vi.fn(),
  },
}))

import { taskService } from '@/services/taskService'

const mockUser = { id: 1, name: 'Admin', email: 'admin@test.com', is_admin: true }
const mockMeta = {
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 1,
  from: 1,
  to: 1,
}
const mockLinks = { first: null, last: null, prev: null, next: null }

const mockTask: Task = {
  id: 1,
  project_id: 1,
  title: 'Setup CI/CD',
  description: 'Configure pipeline',
  due_date: '2026-03-09',
  category: { id: 3, name: 'Testing' },
  created_by: mockUser,
  project: { id: 1, name: 'Website E-Commerce', status: 'active' },
  is_deleted: false,
  deleted_at: null,
  created_at: '2026-03-01T00:00:00.000Z',
  updated_at: '2026-03-01T00:00:00.000Z',
}

describe('useGlobalTasksStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  // ── fetchTasks ────────────────────────────────────────────────────────
  describe('fetchTasks', () => {
    it('populates tasks and meta on success', async () => {
      vi.mocked(taskService.getGlobalTasks).mockResolvedValueOnce({
        success: true,
        message: '',
        data: { data: [mockTask], meta: mockMeta, links: mockLinks },
      })
      const store = useGlobalTasksStore()
      await store.fetchTasks()
      expect(store.tasks).toHaveLength(1)
      expect(store.tasks[0]!.title).toBe('Setup CI/CD')
      expect(store.meta.total).toBe(1)
      expect(store.isLoading).toBe(false)
    })

    it('sets error message on failure', async () => {
      vi.mocked(taskService.getGlobalTasks).mockRejectedValueOnce(new Error('Network error'))
      const store = useGlobalTasksStore()
      await store.fetchTasks()
      expect(store.error).toContain('Gagal memuat task')
      expect(store.isLoading).toBe(false)
    })
  })

  // ── createTask ────────────────────────────────────────────────────────
  describe('createTask', () => {
    it('returns success and prepends task to list', async () => {
      vi.mocked(taskService.createTask).mockResolvedValueOnce({
        success: true,
        message: '',
        data: mockTask,
      })
      const store = useGlobalTasksStore()
      const result = await store.createTask(1, {
        title: 'Setup CI/CD',
        category_id: 3,
        description: 'Configure pipeline',
        due_date: '2026-03-09',
      })
      expect(result.success).toBe(true)
      expect(store.tasks[0]!.id).toBe(1)
      expect(store.meta.total).toBe(1)
    })

    it('returns failure and sets fieldErrors on 422', async () => {
      const axiosError = new AxiosError('Unprocessable')
      axiosError.response = {
        status: 422,
        data: {
          success: false,
          message: 'Validasi gagal.',
          errors: { title: ['Judul wajib diisi.'] },
        },
      } as any
      vi.mocked(taskService.createTask).mockRejectedValueOnce(axiosError)
      const store = useGlobalTasksStore()
      const result = await store.createTask(1, {
        title: '',
        category_id: 1,
        description: '',
        due_date: '',
      })
      expect(result.success).toBe(false)
      expect(store.fieldErrors['title']).toBe('Judul wajib diisi.')
    })
  })

  // ── updateTask ────────────────────────────────────────────────────────
  describe('updateTask', () => {
    it('updates the task in place on success', async () => {
      const updatedTask = { ...mockTask, title: 'Updated title' }
      vi.mocked(taskService.getGlobalTasks).mockResolvedValueOnce({
        success: true,
        message: '',
        data: { data: [mockTask], meta: mockMeta, links: mockLinks },
      })
      vi.mocked(taskService.updateTask).mockResolvedValueOnce({
        success: true,
        message: '',
        data: updatedTask,
      })
      const store = useGlobalTasksStore()
      await store.fetchTasks()
      const result = await store.updateTask(1, { title: 'Updated title' })
      expect(result.success).toBe(true)
      expect(store.tasks[0]!.title).toBe('Updated title')
    })
  })

  // ── deleteTask ────────────────────────────────────────────────────────
  describe('deleteTask', () => {
    it('removes task from list and decrements total on success', async () => {
      vi.mocked(taskService.getGlobalTasks).mockResolvedValueOnce({
        success: true,
        message: '',
        data: { data: [mockTask], meta: mockMeta, links: mockLinks },
      })
      vi.mocked(taskService.deleteTask).mockResolvedValueOnce({
        success: true,
        message: '',
        data: null,
      })
      const store = useGlobalTasksStore()
      await store.fetchTasks()
      expect(store.tasks).toHaveLength(1)
      const result = await store.deleteTask(1)
      expect(result.success).toBe(true)
      expect(store.tasks).toHaveLength(0)
      expect(store.meta.total).toBe(0)
    })

    it('sets error and returns failure when delete fails', async () => {
      vi.mocked(taskService.deleteTask).mockRejectedValueOnce(new Error('Server error'))
      const store = useGlobalTasksStore()
      const result = await store.deleteTask(99)
      expect(result.success).toBe(false)
      expect(store.error).toContain('Gagal menghapus task')
    })
  })

  // ── clearErrors ───────────────────────────────────────────────────────
  describe('clearErrors', () => {
    it('resets error and fieldErrors', async () => {
      const axiosError = new AxiosError('Unprocessable')
      axiosError.response = {
        status: 422,
        data: { success: false, message: '', errors: { title: ['Required'] } },
      } as any
      vi.mocked(taskService.createTask).mockRejectedValueOnce(axiosError)
      const store = useGlobalTasksStore()
      await store.createTask(1, { title: '', category_id: 1, description: '', due_date: '' })
      expect(store.fieldErrors['title']).toBe('Required')
      store.clearErrors()
      expect(store.fieldErrors).toEqual({})
      expect(store.error).toBe('')
    })
  })
})
