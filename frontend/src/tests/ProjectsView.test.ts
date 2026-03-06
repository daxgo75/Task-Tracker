import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { nextTick } from 'vue'
import { createRouter, createMemoryHistory } from 'vue-router'
import ProjectsView from '@/views/ProjectsView.vue'

vi.mock('@/services/projectService', () => ({
  projectService: {
    getProjects: vi.fn(),
    getProject: vi.fn(),
    createProject: vi.fn(),
    updateProject: vi.fn(),
    toggleStatus: vi.fn(),
  },
}))

import { projectService } from '@/services/projectService'
import type { Project } from '@/types/project'

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
  owner: { id: 1, name: 'Admin', email: 'admin@test.com', is_admin: true },
  task_counts: { total: 3 },
  created_at: '2026-03-01T00:00:00.000Z',
  updated_at: '2026-03-01T00:00:00.000Z',
}

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/projects', name: 'projects', component: ProjectsView },
    { path: '/projects/:id', name: 'project-detail', component: { template: '<div />' } },
  ],
})

const ProjectFormModalStub = {
  name: 'ProjectFormModal',
  props: ['isOpen', 'project', 'isSaving', 'fieldErrors'],
  emits: ['submit', 'close'],
  template: '<div />',
}

function mountProjects() {
  return mount(ProjectsView, {
    global: {
      plugins: [createPinia(), router],
      stubs: { ProjectFormModal: ProjectFormModalStub, ProjectStatusBadge: true },
    },
  })
}

describe('ProjectsView', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('renders page title "Project" and add button', () => {
    vi.mocked(projectService.getProjects).mockResolvedValueOnce({
      success: true,
      message: '',
      data: { data: [], meta: mockMeta, links: mockLinks },
    })
    const wrapper = mountProjects()
    expect(wrapper.find('.projects__title').text()).toBe('Project')
    expect(wrapper.find('.btn--primary').text()).toContain('Tambah Project')
  })

  it('shows loading skeleton rows while fetching', async () => {
    vi.mocked(projectService.getProjects).mockResolvedValueOnce({
      success: true,
      message: '',
      data: { data: [], meta: mockMeta, links: mockLinks },
    })
    const wrapper = mountProjects()
    await nextTick() // allow isLoading = true to propagate before promise resolves
    expect(wrapper.findAll('.skeleton-row').length).toBeGreaterThan(0)
  })

  it('shows projects in table after successful load', async () => {
    vi.mocked(projectService.getProjects).mockResolvedValueOnce({
      success: true,
      message: '',
      data: { data: [mockProject], meta: mockMeta, links: mockLinks },
    })
    const wrapper = mountProjects()
    await flushPromises()
    expect(wrapper.find('.project-name').text()).toBe('Website E-Commerce')
  })

  it('shows empty state when no projects found', async () => {
    vi.mocked(projectService.getProjects).mockResolvedValueOnce({
      success: true,
      message: '',
      data: { data: [], meta: { ...mockMeta, total: 0 }, links: mockLinks },
    })
    const wrapper = mountProjects()
    await flushPromises()
    expect(wrapper.find('.table__empty').exists()).toBe(true)
    expect(wrapper.find('.table__empty').text()).toContain('Tidak ada project ditemukan')
  })

  it('opens create modal when "+ Tambah Project" is clicked', async () => {
    vi.mocked(projectService.getProjects).mockResolvedValueOnce({
      success: true,
      message: '',
      data: { data: [], meta: mockMeta, links: mockLinks },
    })
    const wrapper = mountProjects()
    await flushPromises()
    await wrapper.find('.btn--primary').trigger('click')
    const modal = wrapper.findComponent({ name: 'ProjectFormModal' })
    expect(modal.props('isOpen')).toBe(true)
  })

  it('opens edit modal with project data when Edit is clicked', async () => {
    vi.mocked(projectService.getProjects).mockResolvedValueOnce({
      success: true,
      message: '',
      data: { data: [mockProject], meta: mockMeta, links: mockLinks },
    })
    const wrapper = mountProjects()
    await flushPromises()
    await wrapper.find('.action-btn--edit').trigger('click')
    const modal = wrapper.findComponent({ name: 'ProjectFormModal' })
    expect(modal.props('isOpen')).toBe(true)
  })

  it('shows error alert when store reports an error', async () => {
    vi.mocked(projectService.getProjects).mockRejectedValueOnce(new Error('Network error'))
    const wrapper = mountProjects()
    await flushPromises()
    expect(wrapper.find('[role="alert"]').exists()).toBe(true)
    expect(wrapper.find('[role="alert"]').text()).toContain('Gagal memuat daftar project')
  })
})
