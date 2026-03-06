import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { AxiosError } from 'axios'
import { useProjectsStore } from '@/stores/projects'
import type { Project } from '@/types/project'

vi.mock('@/services/projectService', () => ({
  projectService: {
    getProjects: vi.fn(),
    createProject: vi.fn(),
    updateProject: vi.fn(),
    toggleStatus: vi.fn(),
    getProject: vi.fn(),
  },
}))

import { projectService } from '@/services/projectService'

const mockUser = { id: 1, name: 'Admin', email: 'admin@test.com', is_admin: true }
const mockMeta = {
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 1,
  from: 1,
  to: 1,
}
const mockLinks = { first: null, last: null, prev: null, next: null }

const mockProject: Project = {
  id: 1,
  name: 'Website E-Commerce',
  description: 'Design platform belanja',
  status: 'active',
  owner: mockUser,
  task_counts: { total: 3 },
  created_at: '2026-03-01T00:00:00.000Z',
  updated_at: '2026-03-01T00:00:00.000Z',
}

describe('useProjectsStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  // ── fetchProjects ─────────────────────────────────────────────────────
  describe('fetchProjects', () => {
    it('sets projects and meta on success', async () => {
      vi.mocked(projectService.getProjects).mockResolvedValueOnce({
        success: true,
        message: '',
        data: { data: [mockProject], meta: mockMeta, links: mockLinks },
      })
      const store = useProjectsStore()
      await store.fetchProjects()
      expect(store.projects).toHaveLength(1)
      expect(store.projects[0]!.name).toBe('Website E-Commerce')
      expect(store.meta?.total).toBe(1)
      expect(store.isLoading).toBe(false)
    })

    it('sets error message on failure', async () => {
      vi.mocked(projectService.getProjects).mockRejectedValueOnce(new Error('Network error'))
      const store = useProjectsStore()
      await store.fetchProjects()
      expect(store.error).toContain('Gagal memuat daftar project')
      expect(store.isLoading).toBe(false)
    })

    it('sets isLoading true during fetch', async () => {
      let resolve!: (v: any) => void
      vi.mocked(projectService.getProjects).mockReturnValueOnce(
        new Promise((r) => { resolve = r }),
      )
      const store = useProjectsStore()
      const fetchPromise = store.fetchProjects()
      expect(store.isLoading).toBe(true)
      resolve({ success: true, message: '', data: { data: [], meta: mockMeta, links: mockLinks } })
      await fetchPromise
      expect(store.isLoading).toBe(false)
    })
  })

  // ── createProject ─────────────────────────────────────────────────────
  describe('createProject', () => {
    it('returns success and prepends project to list', async () => {
      vi.mocked(projectService.createProject).mockResolvedValueOnce({
        success: true,
        message: 'Project dibuat.',
        data: mockProject,
      })
      const store = useProjectsStore()
      const result = await store.createProject({ name: 'Website E-Commerce', description: '', status: 'active' })
      expect(result.success).toBe(true)
      expect(store.projects[0]!.id).toBe(1)
    })

    it('returns failure and sets fieldErrors on 422', async () => {
      const axiosError = new AxiosError('Unprocessable')
      axiosError.response = {
        status: 422,
        data: { success: false, message: 'Validasi gagal.', errors: { name: ['Nama wajib diisi.'] } },
      } as any
      vi.mocked(projectService.createProject).mockRejectedValueOnce(axiosError)
      const store = useProjectsStore()
      const result = await store.createProject({ name: '', description: '', status: 'active' })
      expect(result.success).toBe(false)
      expect(store.fieldErrors['name']).toBe('Nama wajib diisi.')
    })

    it('returns failure and sets global error on non-422', async () => {
      const axiosError = new AxiosError('Server error')
      axiosError.response = {
        status: 500,
        data: { success: false, message: 'Server sedang bermasalah.' },
      } as any
      vi.mocked(projectService.createProject).mockRejectedValueOnce(axiosError)
      const store = useProjectsStore()
      const result = await store.createProject({ name: 'Test', description: '', status: 'active' })
      expect(result.success).toBe(false)
      expect(store.error).toContain('Server sedang bermasalah.')
    })
  })

  // ── updateProject ─────────────────────────────────────────────────────
  describe('updateProject', () => {
    it('updates the project in place on success', async () => {
      const updatedProject = { ...mockProject, name: 'Updated Name' }
      vi.mocked(projectService.getProjects).mockResolvedValueOnce({
        success: true,
        message: '',
        data: { data: [mockProject], meta: mockMeta, links: mockLinks },
      })
      vi.mocked(projectService.updateProject).mockResolvedValueOnce({
        success: true,
        message: '',
        data: updatedProject,
      })
      const store = useProjectsStore()
      await store.fetchProjects()
      const result = await store.updateProject(1, { name: 'Updated Name' })
      expect(result.success).toBe(true)
      expect(store.projects[0]!.name).toBe('Updated Name')
    })
  })

  // ── clearErrors ───────────────────────────────────────────────────────
  describe('clearErrors', () => {
    it('resets error and fieldErrors', async () => {
      const axiosError = new AxiosError('Unprocessable')
      axiosError.response = {
        status: 422,
        data: { success: false, message: '', errors: { name: ['Required'] } },
      } as any
      vi.mocked(projectService.createProject).mockRejectedValueOnce(axiosError)
      const store = useProjectsStore()
      await store.createProject({ name: '', description: '', status: 'active' })
      expect(store.fieldErrors['name']).toBe('Required')
      store.clearErrors()
      expect(store.fieldErrors).toEqual({})
      expect(store.error).toBe('')
    })
  })
})
